<?php

namespace Application\Controller\Factory;

use Application\Controller\EventController;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class EventControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $rbacManager    = $container->get(RbacManager::class);
    $iniParManager  = $container->get(IniparManager::class);
    $eventManager   = $container->get(EventManager::class);

    return new EventController($entityManager, $rbacManager, $iniParManager, $eventManager);
  }
}
