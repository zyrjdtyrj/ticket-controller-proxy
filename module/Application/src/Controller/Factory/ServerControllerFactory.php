<?php

namespace Application\Controller\Factory;

use Application\Controller\ServerController;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ServerControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $serverManager  = $container->get(ServerManager::class);
    $rbacManager    = $container->get(RbacManager::class);
    $iniParManager  = $container->get(IniparManager::class);

    return new ServerController($serverManager, $rbacManager, $iniParManager);
  }
}