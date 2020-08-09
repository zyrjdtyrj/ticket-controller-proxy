<?php

namespace Application\Controller\Factory;

use Application\Service\ApiManager;
use Application\Service\HistoryManager;
use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\IndexController;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
  {
    $entityManager  = $container->get('doctrine.entitymanager.orm_default');
    $iniParManager  = $container->get(IniparManager::class);
    $rbacManager    = $container->get(RbacManager::class);
    $ticketManager  = $container->get(TicketManager::class);
    $historyManager = $container->get(HistoryManager::class);
    $apiManager     = $container->get(ApiManager::class);

    // Instantiate the controller and inject dependencies
    return new IndexController($entityManager, $iniParManager, $rbacManager, $ticketManager, $historyManager, $apiManager);
  }
}