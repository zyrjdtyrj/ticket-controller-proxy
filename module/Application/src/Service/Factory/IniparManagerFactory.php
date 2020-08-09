<?php

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Application\Service\IniparManager;

class IniparManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $authService = $container->get(\Zend\Authentication\AuthenticationService::class);

    $entityManager = $container->get('doctrine.entitymanager.orm_default');

    return new IniparManager($authService, $entityManager);
  }
}
