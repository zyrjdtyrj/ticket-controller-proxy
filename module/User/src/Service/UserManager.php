<?php

namespace User\Service;

use User\Entity\User;
use User\Entity\Role;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

/**
 * This service is responsible for adding/editing users
 * and changing user password.
 */
class UserManager
{
  /**
   * Doctrine entity manager.
   * @var Doctrine\ORM\EntityManager
   */
  private $entityManager;

  /**
   * Role manager.
   * @var User\Service\RoleManager
   */
  private $roleManager;

  /**
   * Permission manager.
   * @var User\Service\PermissionManager
   */
  private $permissionManager;

  /**
   * Constructs the service.
   */
  public function __construct($entityManager, $roleManager, $permissionManager)
  {
    $this->entityManager = $entityManager;
    $this->roleManager = $roleManager;
    $this->permissionManager = $permissionManager;
  }

  /**
   * This method adds a new user.
   *
   * @param $data
   * @return User
   * @throws \Exception
   */
  public function addUser($data)
  {
    // Do not allow several users with the same email address.
    if ($this->checkUserExists($data['login'])) {
      throw new \Exception("Пользователь с таким логином [" . $data['login'] . "] уже существует.");
    }

    // Create new User entity.
    $user = new User();
    $user->setEmail($data['email']);
    $user->setLogin($data['login']);
    $user->setFullName($data['full_name']);

    // Encrypt password and store the password in encrypted state.
    $bcrypt = new Bcrypt();
    $passwordHash = $bcrypt->create($data['password']);
    $user->setPassword($passwordHash);

    $user->setStatus($data['status']);

    $currentDate = date('Y-m-d H:i:s');
    $user->setDateCreated($currentDate);

    // Assign roles to user.
    $this->assignRoles($user, $data['roles']);

    // Add the entity to the entity manager.
    $this->entityManager->persist($user);

    // Apply changes to database.
    $this->entityManager->flush();

    return $user;
  }

  /**
   * This method updates data of an existing user.
   *
   * @param $user User
   * @param $data
   * @return bool
   * @throws \Exception
   */
  public function updateUser($user, $data)
  {
    $user->setEmail($data['email']);
    $user->setFullName($data['full_name']);
    $user->setStatus($data['status']);

    // Assign roles to user.
    $this->assignRoles($user, $data['roles']);

    // Apply changes to database.
    $this->entityManager->flush();

    return true;
  }

  /**
   * A helper method which assigns new roles to the user.
   *
   * @param $user
   * @param $roleIds
   * @throws \Exception
   */
  private function assignRoles($user, $roleIds)
  {
    // Remove old user role(s).
    $user->getRoles()->clear();

    // Assign new role(s).
    foreach ($roleIds as $roleId) {
      $role = $this->entityManager->getRepository(Role::class)
        ->find($roleId);
      if ($role == null) {
        throw new \Exception('Not found role by ID');
      }

      $user->addRole($role);
    }
  }

  /**
   * This method checks if at least one user presents, and if not, creates
   * 'Admin' user with email 'admin@example.com' and password 'Secur1ty'.
   */
  public function createAdminUserIfNotExists()
  {
    $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
    if ($user == null) {

      $this->permissionManager->createDefaultPermissionsIfNotExist();
      $this->roleManager->createDefaultRolesIfNotExist();

      $user = new User();
      $user->setEmail('admin@example.com');
      $user->setLogin('admin');
      $user->setFullName('Admin');
      $bcrypt = new Bcrypt();
      $passwordHash = $bcrypt->create('2348399');
      $user->setPassword($passwordHash);
      $user->setStatus(User::STATUS_ACTIVE);
      $user->setDateCreated(date('Y-m-d H:i:s'));

      // Assign user Administrator role
      $adminRole = $this->entityManager->getRepository(Role::class)
        ->findOneByName('Administrator');
      if ($adminRole == null) {
        throw new \Exception('Administrator role doesn\'t exist');
      }

      $user->getRoles()->add($adminRole);

      $this->entityManager->persist($user);

      $this->entityManager->flush();
    }
  }

  /**
   * Checks whether an active user with given email address already exists in the database.
   *
   * @param $login string
   * @return bool
   */
  public function checkUserExists($login)
  {

    $user = $this->entityManager->getRepository(User::class)->findOneByLogin($login);

    return $user !== null;
  }

  /**
   * Checks that the given password is correct.
   */
  public function validatePassword($user, $password)
  {
    $bcrypt = new Bcrypt();
    $passwordHash = $user->getPassword();

    if ($bcrypt->verify($password, $passwordHash)) {
      return true;
    }

    return false;
  }

  /**
   * Generates a password reset token for the user. This token is then stored in database and
   * sent to the user's E-mail address. When the user clicks the link in E-mail message, he is
   * directed to the Set Password page.
   */
  public function generatePasswordResetToken($user)
  {
    // Generate a token.
    $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
    $user->setPasswordResetToken($token);

    $currentDate = date('Y-m-d H:i:s');
    $user->setPasswordResetTokenCreationDate($currentDate);

    $this->entityManager->flush();

    $subject = 'Password Reset';

    $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $passwordResetUrl = 'http://' . $httpHost . '/set-password?token=' . $token;

    $body = 'Please follow the link below to reset your password:\n';
    $body .= "$passwordResetUrl\n";
    $body .= "If you haven't asked to reset your password, please ignore this message.\n";

    // Send email to user.
    mail($user->getEmail(), $subject, $body);
  }

  /**
   * Checks whether the given password reset token is a valid one.
   */
  public function validatePasswordResetToken($passwordResetToken)
  {
    $user = $this->entityManager->getRepository(User::class)
      ->findOneByPasswordResetToken($passwordResetToken);

    if ($user == null) {
      return false;
    }

    $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
    $tokenCreationDate = strtotime($tokenCreationDate);

    $currentDate = strtotime('now');

    if ($currentDate - $tokenCreationDate > 24 * 60 * 60) {
      return false; // expired
    }

    return true;
  }

  /**
   * This method sets new password by password reset token.
   */
  public function setNewPasswordByToken($passwordResetToken, $newPassword)
  {
    if (!$this->validatePasswordResetToken($passwordResetToken)) {
      return false;
    }

    $user = $this->entityManager->getRepository(User::class)
      ->findOneByPasswordResetToken($passwordResetToken);

    if ($user === null) {
      return false;
    }

    // Set new password for user
    $bcrypt = new Bcrypt();
    $passwordHash = $bcrypt->create($newPassword);
    $user->setPassword($passwordHash);

    // Remove password reset token
    $user->setPasswordResetToken(null);
    $user->setPasswordResetTokenCreationDate(null);

    $this->entityManager->flush();

    return true;
  }

  /**
   * This method is used to change the password for the given user. To change the password,
   * one must know the old password.
   */
  public function changePassword($user, $data)
  {
    $oldPassword = $data['old_password'];

    // Check that old password is correct
    if (!$this->validatePassword($user, $oldPassword)) {
      return false;
    }

    $newPassword = $data['new_password'];

    // Check password length
    if (strlen($newPassword) < 6 || strlen($newPassword) > 64) {
      return false;
    }

    // Set new password for user
    $bcrypt = new Bcrypt();
    $passwordHash = $bcrypt->create($newPassword);
    $user->setPassword($passwordHash);

    // Apply changes
    $this->entityManager->flush();

    return true;
  }
}

