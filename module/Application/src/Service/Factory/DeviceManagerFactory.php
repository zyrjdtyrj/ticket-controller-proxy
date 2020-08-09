<?php

namespace Application\Service\Factory;

use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Interop\Container\ContainerInterface;

class DeviceManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $eventManager   = $container->get(EventManager::class);

    return new DeviceManager($entityManager, $eventManager);
  }
}