<?php

namespace User\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use User\Entity\User;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class AuthAdapter implements AdapterInterface
{
  /**
   * User login.
   * @var string
   */
  private $login;

  /**
   * Password
   * @var string
   */
  private $password;

  /**
   * Entity manager.
   * @var EntityManager
   */
  private $entityManager;

  /**
   * Constructor.
   */
  public function __construct($entityManager)
  {
    $this->entityManager = $entityManager;
  }

  /**
   * Sets user login.
   */
  public function setLogin($login)
  {
    $this->login = $login;
  }

  /**
   * Sets password.
   */
  public function setPassword($password)
  {
    $this->password = (string)$password;
  }

  /**
   * Performs an authentication attempt.
   */
  public function authenticate()
  {
    // Check the database if there is a user with such email.
    $user = $this->entityManager->getRepository(User::class)
      ->findOneByLogin($this->login);

    // If there is no such user, return 'Identity Not Found' status.
    if ($user == null) {
      return new Result(
        Result::FAILURE_IDENTITY_NOT_FOUND,
        null,
        ['Invalid credentials.']);
    }

    // If the user with such email exists, we need to check if it is active or retired.
    // Do not allow retired users to log in.
    if ($user->getStatus() == User::STATUS_RETIRED) {
      return new Result(
        Result::FAILURE,
        null,
        ['User is retired.']);
    }

    // Now we need to calculate hash based on user-entered password and compare
    // it with the password hash stored in database.
    $bcrypt = new Bcrypt();
    $passwordHash = $user->getPassword();

    if ($bcrypt->verify($this->password, $passwordHash)) {
      // Great! The password hash matches. Return user identity (email) to be
      // saved in session for later use.
      return new Result(
        Result::SUCCESS,
        $this->login,
        ['Authenticated successfully.']);
    }

    // If password check didn't pass return 'Invalid Credential' failure status.
    return new Result(
      Result::FAILURE_CREDENTIAL_INVALID,
      null,
      ['Invalid credentials.']);
  }
}


