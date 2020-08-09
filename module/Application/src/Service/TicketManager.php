<?php

namespace Application\Service;

use Application\Entity\History;
use Application\Entity\Ticket;
use Application\Entity\TicketUsed;
use Doctrine\ORM\EntityManager;

/**
 * This service is used for invoking user-defined RBAC dynamic assertions.
 */
class TicketManager
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

  /**
   * @param int         $ticketId
   * @param bool|string $subEventId
   * @return bool|object|null
   */
  private function getTicketUsedItem($ticketId, $subEventId = false)
  {
    if (false == $subEventId) $subEventId = $this->iniParManager->get('subEventId');

    $ticketUsed = $this->entityManager->getRepository(TicketUsed::class)->findOneBy(['ticketId' => $ticketId, 'subEventId' => $subEventId]);

    if (!$ticketUsed) return false;

    return $ticketUsed;
  }

  /**
   * Получение использования билета
   *
   * @param integer     $ticketId
   * @param bool|string $subEventId
   * @return array|bool
   */
  public function getTicketUsed($ticketId, $subEventId = false)
  {
    $ticketUsed = $this->getTicketUsedItem($ticketId, $subEventId);

    return $ticketUsed->getUsed();
  }

  /**
   * Получение времени использования билета
   *
   * @param integer     $ticketId
   * @param bool|string $subEventId
   * @return mixed
   */
  public function getTicketUsedDate($ticketId, $subEventId = false)
  {
    $ticketUsed = $this->getTicketUsedItem($ticketId, $subEventId);

    return $ticketUsed->getUsedDate();
  }

  /**
   * Установка использования билета
   *
   * @param $ticketId
   * @param $used
   * @param bool $subEventId
   *
   * @return bool
   * @throws \Exception
   */
  public function setTicketUsed($ticketId, $used, $subEventId = false)
  {
    if (false == $subEventId) $subEventId = $this->iniParManager->get('subEventId');

    $ticketUsed = $this->getTicketUsedItem($ticketId, $subEventId);

    if (false === $ticketUsed) {
      $ticketUsed = new TicketUsed();
      $ticketUsed->setTicketId($ticketId);
      $ticketUsed->setSubEventId($subEventId);
    }

    $ticketUsed->setUsed($used);

    $this->entityManager->persist($ticketUsed);

    return true;
  }

  /**
   * Установка времени использования билета
   *
   * @param $ticketId
   * @param null $date
   * @param bool $subEventId
   *
   * @return bool
   * @throws \Exception
   */
  public function setTicketUsedDate($ticketId, $date = null, $subEventId = false)
  {
    if (false == $subEventId) $subEventId = $this->iniParManager->get('subEventId');

    $ticketUsed = $this->getTicketUsedItem($ticketId, $subEventId);

    if (false === $ticketUsed) {
      $ticketUsed = new TicketUsed();
      $ticketUsed->setTicketId($ticketId);
      $ticketUsed->setSubEventId($subEventId);
    }

    if (null == $date)
      $date = time();

    $ticketUsed->setUsedDate($date);

    $this->entityManager->persist($ticketUsed);

    return true;
  }

  /**
   * Получение билета
   *
   * @param $ticketId
   *
   * @return mixed
   */
  public function getTicket($ticketId)
  {
    return $this->entityManager->getRepository(Ticket::class)->findOneById($ticketId);
  }

  /**
   * Получение количества билетов на событии
   *
   * @param $eventId
   *
   * @return mixed
   */
  public function getTicketCount($eventId)
  {
    $ticketCount = $this->entityManager->getRepository(Ticket::class)->count(['eventId' => $eventId]);

    return $ticketCount;
  }

  public function getModifiedTicket($dateStart)
  {
    $qb = $this->entityManager->createQueryBuilder();

    $qb->select('t.id')
      ->from(Ticket::class, 't')
      ->where($qb->expr()->gte('t.modified_time', '?1'))
      ->setParameter(1, $dateStart);

    $ticketList = $qb->getQuery()->getArrayResult();

    $post = [];
    foreach ($ticketList as $ticket) {
      /**
       * @var Ticket $item
       */
      $item = $this->getTicket($ticket['id']);

      // поднимаем историю
      $historyList = $this->entityManager->getRepository(History::class)->findBy(['ticketId' => $ticket['id']], ['date' => 'ASC']);
      $ticketHistory = [];
      /**
       * @var History $history
       */
      foreach ($historyList as $history) {
        $ticketHistory[] = [
          'ip'  => $history->getIp(),
          'n'   => $history->getDevice(),
          'd'   => date('c', $history->getDate()),
          's'   => $history->getStatus(),
        ];
      }

      $post[] = [
        'id'  => $item->getId(),
        's'   => $item->getStatus(),
        'd'   => date('c', $item->getModifiedTime()),
        'h'   => $ticketHistory,
      ];
    }

    return $post;
  }
}



