<?php

namespace Application\Controller\Factory;

use Application\Controller\DeviceController;
use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class DeviceControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $rbacManager    = $container->get(RbacManager::class);
    $iniParManager  = $container->get(IniparManager::class);
    $ticketManager  = $container->get(TicketManager::class);
    $eventManager   = $container->get(EventManager::class);
    $deviceManager  = $container->get(DeviceManager::class);

    return new DeviceController(
      $entityManager,
      $rbacManager,
      $iniParManager,
      $ticketManager,
      $eventManager,
      $deviceManager
    );
  }
}