<?php

namespace Application\Service;

use Application\Entity\History;
use Doctrine\ORM\EntityManager;
use User\Entity\User;

class HistoryManager
{
  /**
   * Entity manager.
   * @var Doctrine\ORM\EntityManager
   */
  private $entityManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * TicketManager constructor.
   *
   * @param iniParManager $iniParManager
   * @param EntityManager $entityManager
   */
  public function __construct($entityManager, $iniParManager)
  {
    $this->entityManager  = $entityManager;
    $this->iniParManager  = $iniParManager;
  }

  public function getHistory($ticketId, $subEventId)
  {

  }

  /**
   * Получение последней записи из истории билета
   *
   * @param $ticketId
   * @param null $subEventId
   *
   * @return object|null
   */
  public function getLastRecord($ticketId, $subEventId = null)
  {
    if (null == $subEventId) $subEventId = $this->iniParManager->get('subEventId');

    $history = $this->entityManager->getRepository(History::class)
      ->findOneBy([
        'ticketId'    => $ticketId,
        'subEventId'  => $subEventId
      ]);

    return $history;
  }

  /**
   * Получение наименование терминала последнего зарегистрировано билет
   *
   * @param $ticketId
   * @param null $subEventId
   *
   * @return string
   */
  public function getTerminal($ticketId, $subEventId = null)
  {
    $history = $this->getLastRecord($ticketId, $subEventId);

    if (null == $history) return '';

    $user = $this->entityManager->getRepository(User::class)->findOneById($history->getUser());

    return $user->getFullName();
  }
}