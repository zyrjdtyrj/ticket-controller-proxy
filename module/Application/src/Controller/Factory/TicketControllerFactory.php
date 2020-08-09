<?php

namespace Application\Controller\Factory;

use Application\Controller\TicketController;
use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class TicketControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $iniParManager  = $container->get(IniparManager::class);
    $rbacManager    = $container->get(RbacManager::class);
    $ticketManager  = $container->get(TicketManager::class);

    return new TicketController($entityManager, $iniParManager, $rbacManager, $ticketManager);
  }
}
