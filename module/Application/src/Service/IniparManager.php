<?php

namespace Application\Service;

use Application\Entity\Inipar;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use User\Service\AuthManager;

/**
 * This service is used for invoking user-defined RBAC dynamic assertions.
 */
class IniparManager
{
  /**
   * Auth service.
   * @var Zend\Authentication\Authentication
   */
  private $authService;

  /**
   * Entity manager.
   * @var EntityManager
   */
  private $entityManager;

  /**
   * IniparManager constructor.
   *
   * @param EntityManager $entityManager
   * @param AuthManager $authService
   */
  public function __construct($authService, $entityManager)
  {
    $this->entityManager  = $entityManager;
    $this->authService    = $authService;
  }

  /**
   * Получение значения переменной
   *
   * @param $name
   * @param bool $default
   * @param null $user
   *
   * @return bool
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function get($name, $default = false, $user = null)
  {
    if ($this->authService->hasIdentity()) {
      $this->set('lastAction', time(), (string) $this->authService->getIdentity());
    }

    $inipar = $this->entityManager->getRepository(Inipar::class)->findBy(['name' => $name, 'user' => $user]);

    if (count($inipar))
      return $inipar[0]->getValue();

    return $default;
  }

  /**
   * Запись значения переменной
   *
   * @param $name
   * @param $value
   * @param null $user
   *
   * @return bool
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @todo: реализовать запись истории изменений системных параметров
   */
  public function set($name, $value, $user = null)
  {
    $inipar = $this->entityManager->getRepository(Inipar::class)->findBy(['name' => $name, 'user' => $user]);

    if (null == $inipar) {
      $inipar = new Inipar();
      $inipar->setName($name);
      $inipar->setUser($user);
      $inipar->setValue($value);

      $this->entityManager->persist($inipar);
    } else {
      $inipar[0]->setValue((null == $value) ? '' : $value);
    }

    $this->entityManager->flush();

    return true;
  }
}



