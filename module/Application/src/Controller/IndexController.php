<?php

namespace Application\Controller;

use Application\Entity\Log;
use Application\Entity\Stat;
use Application\Service\ApiManager;
use Application\Service\HistoryManager;
use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\User;

/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class IndexController extends AbstractActionController
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
   * @var RbacManager
   */
  private $rbacManager;

  /**
   * @var TicketManager
   */
  private $ticketManager;

  /**
   * @var HistoryManager
   */
  private $historyManager;

  /**
   * @var ApiManager
   */
  private $apiManager;

  /**
   * IndexController constructor.
   * @param EntityManager   $entityManager
   * @param IniparManager   $iniParManager
   * @param RbacManager     $rbacManager
   * @param TicketManager   $ticketManager
   * @param HistoryManager  $historyManager
   * @param ApiManager      $apiManager
   */
  public function __construct($entityManager, $iniParManager, $rbacManager, $ticketManager, $historyManager, $apiManager)
  {
    $this->entityManager  = $entityManager;
    $this->iniParManager  = $iniParManager;
    $this->rbacManager    = $rbacManager;
    $this->ticketManager  = $ticketManager;
    $this->historyManager = $historyManager;
    $this->apiManager     = $apiManager;
  }

  /**
   * This is the default "index" action of the controller. It displays the
   * Home page.
   */
  public function indexAction()
  {
    return new ViewModel();
  }

  /**
   * Загружаемая по AJAX страница статистики
   *
   * @return ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function statAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $this->layout()->setTemplate('layout/empty');
    }

    $result = [];

    // запрос состояния proxy сервиса
    $result['proxyMode'] = $this->iniParManager->get('proxyMode', 'online');

    if ('offline' === $result['proxyMode']) {
      // запрашиваем время перевода в режим offline
      $result['offlineModeTime'] = $this->iniParManager->get('offlineModeTime');
    }

    // запрос состояния соединения с билетным сервером
    $serverStatus = $this->apiManager->exec('CheckStatus');

    if ('Ok' == $serverStatus['Status']) {
      $result['serverStatus'] = 'online';
    } else {
      $result['serverStatus'] = 'offline';
    }

    // запрос последних 60 статистических данных
    $statQuery = $this->entityManager->getRepository(Stat::class)->findBy([], ['date' => 'DESC'], 60);

    krsort($statQuery);

    foreach ($statQuery as $stat) {
      $result['stat'][] = '{"date": "'. date('Y-m-d H:i:s', $stat->getDate()) .'", "value": '. $stat->getRequestCount() .'}';
      $speedDownload = ($stat->getSpeedDownload()) ? $stat->getSpeedDownload() : 0;
      $result['stat2'][] = '{"date": "'. date('Y-m-d H:i:s', $stat->getDate()) .'", "value": '. $speedDownload .'}';
    }

    /*
    // общее количество билетов события
    $result['ticketCount'] = $this->entityManager->getRepository(Ticket::class)->count(['eventId' => $eventId]);

    // дата последней синхронизации билетов
    $lastTicketSyncTime = @date('H:i:s d.m.Y', $this->iniParManager->get('lastTicketSyncTime', 0));
    $result['lastTicketSyncTime'] = (false != $lastTicketSyncTime) ? $lastTicketSyncTime : 'никогда';

    // список устройств
    $deviceList = $this->entityManager->getRepository(Device::class)->findBy(['eventId' => $eventId], ['id' => 'ASC']);
    $result['device'] = [];
    foreach ($deviceList as $device) {
      // запрос последней активности устройства
      $lastAction = $this->iniParManager->get('lastAction', 0, $device->getName() .'_'. $device->getId());
      // если активность была больше минуты назад - статус оффлайн
      if (time() - $lastAction > 60)
        $deviceStatus = 'offline';
      else
        $deviceStatus = 'online';

      // запрос количества операций - произведённых устройством
      $historyCount = $this->entityManager->getRepository(History::class)->count(['device' => $device->getName()]);

      $result['device'][] = [
        'id'      => $device->getId(),
        'name'    => $device->getName(),
        'status'  => $deviceStatus,
        'action'  => $historyCount,
      ];
    }
    */

    /**
     * @var Stat $requestCountQuery
     */
    $requestCountQuery = $this->entityManager->getRepository(Stat::class)->findOneBy([], ['date' => 'DESC']);

    if (null !== $requestCountQuery) {
      $result['speedRequest'] = $requestCountQuery->getRequestCount();

      // скорость обработки запросов сервером
      $result['speedServer'] = $requestCountQuery->getSpeedDownload();
    }

    return new ViewModel([
      'result' => $result,
    ]);
  }

  /**
   * Экшн для крона
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function checkAction()
  {
    $this->layout()->setTemplate('layout/empty');

    // статус работы прокси-сервера
    $proxyMode = $this->iniParManager->get('proxyMode', 'online');

    // запрос состояния соединения с билетным сервером
    $serverStatus = $this->apiManager->exec('CheckStatus');

    if (isset($serverStatus['Status']) && 'Ok' == $serverStatus['Status'] && 'offline' == $proxyMode) {
      // переводим обратно статус прокси сервера, предварительно синхронизировав все билеты

      /**
       * СИНХРОНИЗАЦИЯ
       */

      // собираем изменённые билеты с момента включения offline-режима
      $offlineTime = $this->iniParManager->get('offlineModeTime');
      $post['Tickets'] = $this->ticketManager->getModifiedTicket($offlineTime);

      $this->apiManager->exec('SynTickets', ['Event' => $this->iniParManager->get('eventId'), 'Post' => $post]);

      $this->iniParManager->set('proxyMode', 'online');
    }

    $timeDiff = time() - 60;

    // скорость поступления запросов
    $qb = $this->entityManager->createQueryBuilder();

    $qb->select('count(h.id) as count_request')
      ->from(Log::class, 'h')
      //->where($qb->expr()->eq('h.eventId', '?1'))
      ->where($qb->expr()->gt('h.date', '?1'))
      //->setParameter(1, $eventId)
      ->setParameter(1, (int)$timeDiff);

    $query = $qb->getQuery()->getArrayResult();
    $requestCount = round((int)$query[0]['count_request'], 2);

    $stat = new Stat();
    $stat->setDate(time());
    $stat->setRequestCount($requestCount);
    if (isset($serverStatus['speed_upload']))
      $stat->setSpeedUpload($serverStatus['speed_upload']);
    else
      $stat->setSpeedUpload(0);
    if (isset($serverStatus['speed_download']))
      $stat->setSpeedDownload($serverStatus['speed_download']);
    else
      $stat->setSpeedDownload(0);

    $this->entityManager->persist($stat);
    $this->entityManager->flush();
  }

  /**
   * Страница метрик работы
   */
  public function metrikaAction()
  {
    $date = $this->params()->fromPost('date');

    if ('' !== $date) {
      $startTime = strtotime($date . ' 00:00:00');
      $endTime = strtotime($date . ' 23:59:59');

      // запрос статистических данных
      $c = new Criteria();
      $c->where(Criteria::expr()->gte('date', $startTime));
      $c->andWhere(Criteria::expr()->lte('date', $endTime));
      $c->orderBy(['date' => Criteria::ASC]);
      $statQuery = $this->entityManager->getRepository(Stat::class)->matching($c);

      $result = [];
      foreach ($statQuery as $stat) {
        $result['stat'][] = '{"date": "' . date('Y-m-d H:i:s', $stat->getDate()) . '", "value": ' . $stat->getRequestCount() . '}';
        $speedDownload = ($stat->getSpeedDownload()) ? $stat->getSpeedDownload() : 0;
        $result['stat2'][] = '{"date": "' . date('Y-m-d H:i:s', $stat->getDate()) . '", "value": ' . $speedDownload . '}';
      }

      return new ViewModel([
        'date'    => $date,
        'result'  => $result
      ]);
    } else {
      return new ViewModel();
    }
  }

  /**
   * The "settings" action displays the info about currently logged in user.
   */
  public function profileAction()
  {
    $id = $this->params()->fromRoute('id');

    if ($id != null) {
      $user = $this->entityManager->getRepository(User::class)
        ->find($id);
    } else {
      $user = $this->currentUser();
    }

    if ($user == null) {
      $this->getResponse()->setStatusCode(404);
      return true;
    }

    if (!$this->access('profile.any.view') &&
      !$this->access('profile.own.view', ['user' => $user])) {
      return $this->redirect()->toRoute('not-authorized');
    }

    return new ViewModel([
      'user' => $user
    ]);
  }
}
