<?php

namespace User\Service;

use Doctrine\ORM\EntityManager;
use User\Entity\Permission;

/**
 * This service is responsible for adding/editing permissions.
 */
class PermissionManager
{
  /**
   * Doctrine entity manager.
   * @var EntityManager
   */
  private $entityManager;

  /**
   * RBAC manager.
   * @var RbacManager
   */
  private $rbacManager;

  /**
   * Constructs the service.
   */
  public function __construct($entityManager, $rbacManager)
  {
    $this->entityManager = $entityManager;
    $this->rbacManager = $rbacManager;
  }

  /**
   * Adds a new permission.
   *
   * @param array $data
   *
   * @throws
   *
   * @return Permission
   */
  public function addPermission($data)
  {
    $existingPermission = $this->entityManager->getRepository(Permission::class)
      ->findOneByName($data['name']);
    if ($existingPermission != null) {
      throw new \Exception('Permission with such name already exists');
    }

    $permission = new Permission();
    $permission->setName($data['name']);
    $permission->setDescription($data['description']);
    $permission->setDateCreated(date('Y-m-d H:i:s'));

    $this->entityManager->persist($permission);

    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);

    return $permission;
  }

  /**
   * Updates an existing permission.
   * @param Permission $permission
   * @param array $data
   */
  public function updatePermission($permission, $data)
  {
    $existingPermission = $this->entityManager->getRepository(Permission::class)
      ->findOneByName($data['name']);
    if ($existingPermission != null && $existingPermission != $permission) {
      throw new \Exception('Another permission with such name already exists');
    }

    $permission->setName($data['name']);
    $permission->setDescription($data['description']);

    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }

  /**
   * Deletes the given permission.
   */
  public function deletePermission($permission)
  {
    $this->entityManager->remove($permission);
    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }

  /**
   * This method creates the default set of permissions if no permissions exist at all.
   */
  public function createDefaultPermissionsIfNotExist()
  {
    $permission = $this->entityManager->getRepository(Permission::class)
      ->findOneBy([]);
    if ($permission != null)
      return; // Some permissions already exist; do nothing.

    $defaultPermissions = [
      'user.manage'       => 'Управление пользователями',
      'permission.manage' => 'Управление разрешениями',
      'role.manage'       => 'Управление ролями',
      'profile.any.view'  => 'Просмотр любого профиля',
      'profile.own.view'  => 'Просмотр своего профиля',
      'handbook.manage'   => 'Управление справочниками',
      'settings.manage'   => 'Управление настройками системы',
      'sync.action'       => 'Выполнение синхронизации',
      'history.view'      => 'Просмотр истории',
      'history.own.view'  => 'Просмотр своей истории',
      'history.any.view'  => 'Просмотр всей истории',
      'device.view'       => 'Просмотр устройств',
      'device.manage'     => 'Управление устройствами',
      'event.view'        => 'Просмотр панели управления событиями',
      'event.manage'      => 'Управление событиями через панель управления',
      'server.manage'     => 'Управление списком серверов',
    ];

    foreach ($defaultPermissions as $name => $description) {
      $permission = new Permission();
      $permission->setName($name);
      $permission->setDescription($description);
      $permission->setDateCreated(date('Y-m-d H:i:s'));

      $this->entityManager->persist($permission);
    }

    $this->entityManager->flush();

    // Reload RBAC container.
    $this->rbacManager->init(true);
  }
}

