<?php

namespace Application\Controller\Factory;

use Application\Controller\ApiController;
use Application\Service\ApiManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\LogManager;
use Application\Service\TicketManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApiControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $iniParManager  = $container->get(IniparManager::class);
    $apiManager     = $container->get(ApiManager::class);
    $logManager     = $container->get(LogManager::class);
    $eventManager   = $container->get(EventManager::class);
    $ticketManager  = $container->get(TicketManager::class);

    return new ApiController($entityManager, $iniParManager, $apiManager, $logManager, $eventManager, $ticketManager);
  }
}