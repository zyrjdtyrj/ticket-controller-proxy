<?php

namespace Application\Controller\Factory;

use Application\Service\ApiManager;
use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use Application\Service\TicketManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\SettingsController;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class SettingsControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $iniParManager  = $container->get(IniparManager::class);
    $rbacManager    = $container->get(RbacManager::class);
    $ticketManager  = $container->get(TicketManager::class);
    $apiManager     = $container->get(ApiManager::class);
    $eventManager   = $container->get(EventManager::class);
    $deviceManager  = $container->get(DeviceManager::class);
    $serverManager  = $container->get(ServerManager::class);

    // Instantiate the controller and inject dependencies
    return new SettingsController($entityManager, $iniParManager, $rbacManager, $apiManager, $ticketManager, $eventManager, $deviceManager, $serverManager);
  }
}