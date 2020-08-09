<?php

namespace Application\Service\Factory;

use Application\Service\ServerManager;
use Interop\Container\ContainerInterface;

class ServerManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $entityManager = $container->get('doctrine.entitymanager.orm_default');

    return new ServerManager($entityManager);
  }
}