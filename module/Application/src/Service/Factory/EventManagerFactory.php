<?php

namespace Application\Service\Factory;

use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use Interop\Container\ContainerInterface;
use User\Service\PermissionManager;
use User\Service\RoleManager;

class EventManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $entityManager      = $container->get('doctrine.entitymanager.orm_default');
    $permissionManager  = $container->get(PermissionManager::class);
    $roleManager        = $container->get(RoleManager::class);
    $iniParManager      = $container->get(IniparManager::class);
    $serverManager      = $container->get(ServerManager::class);

    return new EventManager($entityManager, $permissionManager, $roleManager, $iniParManager, $serverManager);
  }
}
