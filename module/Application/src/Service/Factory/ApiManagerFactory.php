<?php

namespace Application\Service\Factory;

use Application\Service\ApiManager;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use Interop\Container\ContainerInterface;

class ApiManagerFactory
{
  public function __invoke(ContainerInterface $container)
  {
    $iniParManager  = $container->get(IniparManager::class);
    $serverManager  = $container->get(ServerManager::class);

    return new ApiManager($iniParManager, $serverManager);
  }
}