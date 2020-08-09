<?php

namespace User\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use User\Entity\Role;
use User\Entity\Permission;
use User\Entity\RoleHierarchy;
use User\Entity\User;

/**
 * This service is responsible for adding/editing roles.
 */
class RoleManager
{
  /**
   * Doctrine entity manager.
   * @var Doctrine\ORM\EntityManager
   */
  private $entityManager;

  /**
   * RBAC manager.
   * @var User\Service\RbacManager
   */
  private $rbacManager;
  
  /**
   * RoleManager constructor.
   *
   * @param EntityManager $entityManager
   * @param RbacManager $rbacManager
   */
  public function __construct(EntityManager $entityManager, RbacManager $rbacManager)
  {
    $this->entityManager = $entityManager;
    $this->rbacManager = $rbacManager;
  }

  /**
   * Добавление новой роли
   *
   * @param array $data
   * @throws \Exception
   */
  public function addRole($data)
  {
    $existingRole = $this->entityManager->getRepository(Role::class)
      ->findOneByName($data['name']);
    if ($existingRole != null) {
      throw new \Exception('Role with such name already exists');
    }

    $role = new Role;
    $role->setName($data['name']);
    $role->setDescription($data['description']);
    $role->setDateCreated(date('Y-m-d H:i:s'));

    if (false !== array_key_exists('parent', $data['parent'])) {
      $parentRole = $this->entityManager->getRepository(Role::class)->findOneById($data['parent']);

      $role->getParentRoles()->add($parentRole);
    }

    $this->entityManager->persist($role);

    // Apply changes to database.
    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }
  
  /**
   * Updates an existing role.
   *
   * @param Role $role
   * @param array $data
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function updateRole(Role $role, $data)
  {
    $existingRole = $this->entityManager->getRepository(Role::class)
      ->findOneByName($data['name']);
    if ($existingRole != null && $existingRole != $role) {
      throw new \Exception('Another role with such name already exists');
    }

    $role->setName($data['name']);
    $role->setDescription($data['description']);
    
    if (false !== array_key_exists('parent', $data)) {
      // очищаем
      $role->getParentRoles()->clear();

      $parentRole = $this->entityManager->getRepository(Role::class)->findOneById($data['parent']);
      
      // добавляем новую
      $role->getParentRoles()->add($parentRole);
    }

    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }
  
  /**
   * Deletes the given role.
   *
   * @param $role
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function deleteRole(Role $role)
  {
    $this->entityManager->remove($role);
    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }
  
  /**
   * This method creates the default set of roles if no roles exist at all.
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function createDefaultRolesIfNotExist()
  {
    $role = $this->entityManager->getRepository(Role::class)
      ->findOneBy([]);
    if ($role != null)
      return; // Some roles already exist; do nothing.

    $defaultRoles = [
      'Guest' => [
        'description' => 'Посетитель сайта, может просмотреть общую информацию и авторизоваться',
        'parent' => null,
        'permissions' => [
          'profile.own.view',
        ],
      ],
      'Administrator' => [
        'description' => 'Главный управляющий proxy-сервером',
        'parent' => 'Guest',
        'permissions' => [
          'user.manage',
          'role.manage',
          'permission.manage',
          'profile.any.view',
          'handbook.manage',
          'settings.manage',
          'sync.action',
          'server.manage'
        ],
      ],
    ];

    foreach ($defaultRoles as $name => $info) {

      // Create new role
      $role = new Role();
      $role->setName($name);
      $role->setDescription($info['description']);
      $role->setDateCreated(date('Y-m-d H:i:s'));
      
      // Assign parent role
      if ($info['parent'] != null) {
        $parentRole = $this->entityManager->getRepository(Role::class)
          ->findOneByName($info['parent']);
        if ($parentRole == null) {
          throw new \Exception('Parent role ' . $info['parent'] . ' doesn\'t exist');
        }
        
        $role->getParentRoles()->add($parentRole);
      }

      $this->entityManager->persist($role);

      // Assign permissions to role
      $permissions = $this->entityManager->getRepository(Permission::class)
        ->findByName($info['permissions']);
      foreach ($permissions as $permission) {
        $role->getPermissions()->add($permission);
      }

      // Apply changes to database.
      $this->entityManager->flush();

      // Reload RBAC container.
      $this->rbacManager->init(true);
    }
  }

  /**
   * Retrieves all permissions from the given role and its child roles.
   * @param Role $role
   */
  public function getEffectivePermissions($role)
  {
    $effectivePermissions = [];

    foreach ($role->getChildRoles() as $childRole) {
      $childPermissions = $this->getEffectivePermissions($childRole);
      foreach ($childPermissions as $name => $inherited) {
        $effectivePermissions[$name] = 'inherited';
      }
    }

    foreach ($role->getPermissions() as $permission) {
      if (!isset($effectivePermissions[$permission->getName()])) {
        $effectivePermissions[$permission->getName()] = 'own';
      }
    }

    return $effectivePermissions;
  }

  /**
   * Updates permissions of a role.
   */
  public function updateRolePermissions($role, $data)
  {
    // Remove old permissions.
    $role->getPermissions()->clear();

    // Assign new permissions to role
    foreach ($data['permissions'] as $name => $isChecked) {
      if (!$isChecked)
        continue;

      $permission = $this->entityManager->getRepository(Permission::class)
        ->findOneByName($name);
      if ($permission == null) {
        throw new \Exception('Permission with such name doesn\'t exist');
      }

      $role->getPermissions()->add($permission);
    }

    // Apply changes to database.
    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }

  /**
   * Добавление разрешения роли
   *
   * @param Role        $role
   * @param Permission  $permission
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function addRolePermission($role, $permission)
  {
    $role->getPermissions()->add($permission);

    $this->entityManager->flush();

    $this->rbacManager->init(true);
  }

  /**
   * Получение роли по её названию
   *
   * @param string $roleName
   *
   * @return mixed
   */
  public function getRoleByName($roleName)
  {
    $role = $this->entityManager->getRepository(Role::class)->findOneByName($roleName);

    return $role;
  }
}

