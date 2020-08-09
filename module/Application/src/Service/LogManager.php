<?php

namespace Application\Service;

use Application\Entity\Log;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Zend\Paginator\Paginator;

class LogManager
{
  /**
   * @var EntityManager $entityManager
   */
  private $entityManager;

  /**
   * LogManager constructor.
   *
   * @param EntityManager $entityManager
   */
  public function __construct($entityManager)
  {
    $this->entityManager = $entityManager;
  }

  /**
   * Получение страницы журнала использования API
   *
   * @param $page
   *
   * @return Paginator
   */
  public function getLogPage($page)
  {
    // получаем историю
    $qb = $this->entityManager->createQueryBuilder();

    $qb->select('l')
      ->from(Log::class, 'l')
      ->orderBy('l.date', 'DESC');

    $historyList = $qb->getQuery();

    $adapter = new DoctrineAdapter(new ORMPaginator($historyList, false));
    $paginator = new Paginator($adapter);
    $paginator->setItemCountPerPage(40);
    $paginator->setCurrentPageNumber($page);

    return $paginator;
  }
}
