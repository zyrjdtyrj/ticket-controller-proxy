<?php

namespace Application\Service\Factory;

use Application\Service\LogManager;
use Interop\Container\ContainerInterface;

class LogManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $entityManager = $container->get('doctrine.entitymanager.orm_default');

    return new LogManager($entityManager);
  }
}
