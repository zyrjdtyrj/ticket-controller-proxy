<?php

namespace Application\Service\Factory;

use Application\Service\IniparManager;
use Interop\Container\ContainerInterface;
use Application\Service\TicketManager;

class TicketManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $entityManager = $container->get('doctrine.entitymanager.orm_default');
    $iniParManager  = $container->get(IniparManager::class);

    return new TicketManager($entityManager, $iniParManager);
  }
}
