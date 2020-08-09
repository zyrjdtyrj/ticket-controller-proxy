<?php

namespace Application\Controller;

use Application\Entity\Event;
use Application\Entity\EventGroup;
use Application\Entity\History;
use Application\Entity\Ticket;
use Application\Service\ApiManager;
use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use Application\Service\TicketManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class SettingsController extends AbstractActionController
{
  /**
   * Entity manager.
   * @var EntityManager
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
   * @var ApiManager
   */
  private $apiManager;

  /**
   * @var EventManager
   */
  private $eventManager;

  /**
   * @var DeviceManager
   */
  private $deviceManager;

  /**
   * @var ServerManager
   */
  private $serverManager;

  /**
   * IndexController constructor.
   * @param EntityManager     $entityManager
   * @param IniparManager     $iniParManager
   * @param RbacManager       $rbacManager
   * @param TicketManager     $ticketManager
   * @param ApiManager        $apiManager
   * @param EventManager      $eventManager
   * @param DeviceManager     $deviceManager
   * @param ServerManager     $serverManager
   */
  public function __construct($entityManager, $iniParManager, $rbacManager, $apiManager, $ticketManager, $eventManager, $deviceManager, $serverManager)
  {
    $this->entityManager      = $entityManager;
    $this->iniParManager      = $iniParManager;
    $this->rbacManager        = $rbacManager;
    $this->ticketManager      = $ticketManager;
    $this->apiManager         = $apiManager;
    $this->eventManager       = $eventManager;
    $this->deviceManager      = $deviceManager;
    $this->serverManager      = $serverManager;
  }

  /**
   * Главная страница
   *
   * @return ViewModel
   */
  public function indexAction()
  {
    $serverId = $this->iniParManager->get('server');

    // последняя синхронизация
    $lastEventSyncTime    = date('H:i:s d.m.Y', $this->iniParManager->get('lastEventSyncTime_'. $serverId, ''));

    if (false === $lastEventSyncTime) $lastEventSyncTime = 'никогда';

    $eventList = $this->eventManager->getEvents();

    $events = [];
    /**
     * @var Event $event
     */
    foreach ($eventList as $event) {
      $deviceCount    = $this->deviceManager->getDeviceCount($event->getId());
      $ticketCount    = $this->ticketManager->getTicketCount($event->getId());
      $synDeviceTime  = $this->iniParManager->get('event_'. $event->getEventId() .'_SynDeviceTime_'. $serverId, 0);
      $synTicketTime  = $this->iniParManager->get('event_'. $event->getEventId() .'_SynTicketTime_'. $serverId, 0);

      // разбираем группы события
      $groups = [];
      if ($event->getGroups()) {
        /**
         * @var EventGroup $group
         */
        foreach ($event->getGroups() as $group) {
          $groups[] = [
            'id'    => $group->getId(),
            'name'  => $group->getGroupId(),
            'color' => $group->getColor(),
          ];
        }
      }

      $events[] = [
        'id'            => $event->getId(),
        'name'          => $event->getName(),
        'eventId'       => $event->getEventId(),
        'dateBegin'     => (0 != $event->getDateBegin()) ? date('H:i:s d.m.Y', $event->getDateBegin()) : '',
        'dateEnd'       => (0 != $event->getDateEnd()) ? date('H:i:s d.m.Y', $event->getDateEnd()) : '',
        'synDeviceTime' => (0 != $synDeviceTime) ? date('H:i:s d.m.Y', $synDeviceTime) : 'никогда',
        'synTicketTime' => (0 != $synTicketTime) ? date('H:i:s d.m.Y', $synTicketTime) : 'никогда',
        'deviceCount'   => $deviceCount,
        'ticketCount'   => $ticketCount,
        'groups'        => $groups,
      ];
    }

    // список серверов
    $serverList = $this->serverManager->getShortList();

    // адрес текущего сервера
    try {
      $serverAddress = $this->serverManager->getServerAddress($this->iniParManager->get('server'));
    } catch (\Exception $e) {
      $serverAddress = '';
    }

    return new ViewModel([
      'iniParManager'       => $this->iniParManager,
      'eventList'           => $events,
      'lastEventSyncTime'   => $lastEventSyncTime,
      'rbacManager'         => $this->rbacManager,
      'serverList'          => $serverList,
      'serverAddress'       => $serverAddress,
    ]);
  }

  /**
   * Сохранение изменённых параметров
   *
   * @return \Zend\Http\Response
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function editAction()
  {
    // сохранение параметров
    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      foreach ($data as $par => $value) {
        // уведомление, если поменялся сервер
        if ('server' == $par && $value != $this->iniParManager->get('server', '0')) {
          $this->flashMessenger()->addInfoMessage('Необходимо выполнить синхронизацию  событий и билетов!');
        }

        // отметка времени, когда включился режим OFFLINE
        if ('proxyMode' == $par && 'offline' == $value) {
          $this->iniParManager->set('offlineModeTime', time());
        }

        // действия при переводе из режима OFFLINE в любой другой
        if ('proxyMode' == $par && 'offline' == $this->iniParManager->get('proxyMode') && $value != 'offline') {
          /**
           * СИНХРОНИЗАЦИЯ
           */

          // собираем изменённые билеты с момента включения offline-режима
          $offlineTime = $this->iniParManager->get('offlineModeTime');
          $post['Tickets'] = $this->ticketManager->getModifiedTicket($offlineTime);

          $this->apiManager->exec('SynTickets', ['Event' => $this->iniParManager->get('eventId'), 'Post' => $post]);
        }

        $this->iniParManager->set($par, $value);
      }

      $this->flashMessenger()->addSuccessMessage('Настройки сохранены');
    }

    return $this->redirect()->toRoute(
      'settings',
      ['action' => 'index']
    );
  }

  /**
   * Сихнонизация устройств, событий и билетов
   *
   * @return \Zend\Http\Response
   *
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function synchronizationAction()
  {
    $param    = $this->params()->fromRoute('param');
    $eventId  = $this->params()->fromRoute('eventId');
    $serverId = $this->iniParManager->get('server');

    switch ($param) {
      case 'event':
        // синхронизация списка событий
        $response = $this->apiManager->exec('GetEvents');

        if (null !== $response) {
          if ('Ok' === $response['Status']) {
            // счётчики обработанных событий
            $newE     = 0;
            $updateE  = 0;
            $totalE   = count($response['Events']);

            foreach ($response['Events'] as $event) {
              $status = $this->eventManager->addEvent($event);

              if ('new' == $status) {
                $newE++;
              } elseif ('update' == $status) {
                $updateE++;
              }
            }

            $this->flashMessenger()->addSuccessMessage('Список мероприятий обновлён. Получено ' . $totalE . ' событий (новых - ' . $newE . ', обновлено - ' . $updateE . ').');

            $this->iniParManager->set('lastEventSyncTime_'. $serverId, time());
          } else {
            $this->flashMessenger()->addErrorMessage('Ошибка синхронизации событий: ' . $response['Message']);
          }
        } else {
          $this->flashMessenger()->addErrorMessage('Ошибка синхронизации событий: Запрос не вернул результата.');
        }

        break;
      case 'ticket':
        // синхронизация билетов мероприятия
        if ('' != $eventId) {
          // запрос списка билетов с талонами
          $response =$this->apiManager->exec('GetTickets', ['Event' => $eventId]);

          if (null !== $response) {
            if ('Ok' === $response['Status']) {
              // счётчики обработанных билетов
              $newT     = 0;
              $updateT  = 0;
              $syncT    = 0;
              if (isset($response['Tickets'])) $totalT   = count($response['Tickets']); else $totalT = 0;

              $synTicketTime = $this->iniParManager->get('event_'. $eventId. '_SynTicketTime_'. $serverId, 0);

              foreach ($response['Tickets'] as $ticket) {
                /**
                 * @var Ticket $item
                 */
                $item = $this->ticketManager->getTicket($ticket['i']);

                if (null == $item) {
                  $newT++;
                  $item = new Ticket();
                  $item->setId($ticket['i']);
                  $item->setSyncTime(time());
                } else {
                  $updateT++;
                  if ($item->getSyncTime() > $synTicketTime && 0 !== $synTicketTime) {
                    $syncT++;
                    // типа отправка состояния на сервер

                    // сбор информации об истории
                    $ticketHistoryList = $this->entityManager->getRepository(History::class)->findBy(['ticketId' => $ticket['i']], ['date' => 'ASC']);
                    /**
                     * @var History $ticketHistory
                     */
                    $history = [];
                    foreach ($ticketHistoryList as $ticketHistory) {
                      $history[] = [
                        's'   => $ticketHistory->getStatus(),
                        'ip'  => $ticketHistory->getIp(),
                        'n'   => $ticketHistory->getDevice(),
                        'd'   => date('c', $ticketHistory->getDate()),
                      ];
                    }

                    $response = $this->apiManager->exec(
                      'SynTickets',
                      [
                        'Event' => $eventId,
                        'Post'  => [
                          'Tickets' => [
                            'id'  => $item->getId(),
                            's'   => $item->getStatus(),
                            'd'   => date('c', $item->getModifiedTime()),
                            'h'   => $history,
                          ]
                        ]
                      ]
                    );
                  } else {
                    $item->setSyncTime(time());
                  }
                }

                $item->setFio($ticket['f']);
                $item->setUnumber($ticket['p']);
                if (isset($ticket['n'])) $item->setPlace($ticket['n']);
                $item->setStatus($ticket['s']);
                $item->setEvent($this->eventManager->getEvent($eventId));
                if (isset($ticket['g'])) $item->setGroups(serialize($ticket['g']));
                if (isset($ticket['l'])) $item->setModifiedTime(strtotime($ticket['l']));

                $this->entityManager->persist($item);
              }

              // расширенное обновление информации о билетах
              $response = $this->apiManager->exec(
                'GetTicketsInfo',
                [
                  'Event'   => $eventId,
                ]
              );

              if (isset($response['Status']) && 'Ok' === $response['Status']) {
                foreach ($response['Tickets'] as $ticket) {
                  /**
                   * @var Ticket $ticket
                   */
                  $item = $this->entityManager->getRepository(Ticket::class)->findOneById($ticket['i']);

                  if ($item) {
                    $item->setFio($ticket['f']);
                    $item->setCity($ticket['c']);
                    $item->setUnumber($ticket['p']);
                    $item->setType($ticket['t']);
                    $item->setPhone($ticket['m']);
                    $item->setOnumber($ticket['o']);
                    $item->setStatus($ticket['s']);
                    if (isset($ticket['d'])) $item->setModifiedTime(strtotime($ticket['d']));
                    if (isset($ticket['h'])) {
                      // история изменений статуса
                      foreach ($ticket['h'] as $history) {
                        // запрос на существование записи истории
                        $historyItem = $this->entityManager->getRepository(History::class)->findOneBy(['ticketId' => $ticket['i'], 'date' => strtotime($history['d'])]);

                        if (null === $historyItem) {
                          $historyItem = new History();
                        }

                        $historyItem->setDate(strtotime($history['d']));
                        $historyItem->setTicketId($ticket['i']);
                        $historyItem->setDevice($history['n']);
                        if (isset($history['ip'])) $historyItem->setIp($history['ip']);
                        $historyItem->setStatus($history['s']);

                        $this->entityManager->persist($historyItem);
                      }
                    }
                    if (isset($ticket['n'])) $item->setPlace($ticket['n']);
                    if (isset($ticket['g'])) $item->setGroups($ticket['g']);

                    $this->entityManager->persist($item);
                  }
                }
              }

              $this->flashMessenger()->addSuccessMessage('Билеты синхронизированы. Получено ' . $totalT . ' билетов (новых - ' . $newT . ', обновлено - ' . $updateT . ', синхронизовано - '. $syncT .').');

              $this->iniParManager->set('event_'. $eventId .'_SynTicketTime_'. $serverId, time());

              $this->entityManager->flush();
            } else {
              $this->flashMessenger()->addErrorMessage('Ошибка синхронизации билетов:' . $response->Message);
            }
          } else {
            $this->flashMessenger()->addErrorMessage('Ошибка синхронизации билетов: Запрос не вернул результата.');
          }
        } else {
          $this->flashMessenger()->addErrorMessage('Не определено событие');
        }

        break;
      case 'device':
        if (null !== $eventId) {
          // запрос списка билетов с талонами
          $response =$this->apiManager->exec('GetDevices', ['Event' => $eventId]);

          if (null !== $response) {
            if ('Ok' === $response['Status']) {
              if ('*' === $response['Devices']) {
                $this->flashMessenger()->addInfoMessage('Список устройств не получен. Задаётся локально с помощью панели управления.');
              } else {
                $newD = 0;
                $updateD = 0;
                $totalD = count($response['Devices']);

                // заполняем список новыми устройствами
                foreach ($response['Devices'] as $device) {
                  $status = $this->deviceManager->addDevice($device, $eventId);

                  if ('new' == $status) {
                    $newD++;
                  } elseif ('update' == $status) {
                    $updateD++;
                  }
                }

                $this->flashMessenger()->addSuccessMessage('Список устройств обновлён. Получено ' . $totalD . ' устройств (новых - ' . $newD . ', обновлено - ' . $updateD . ').');
              }

              $this->iniParManager->set('event_' . $eventId . '_SynDeviceTime_'. $serverId, time());
            } else {
              $this->flashMessenger()->addErrorMessage('Ошибка синхронизации устройств:' . $response->Message);
            }
          } else {
            $this->flashMessenger()->addErrorMessage('Ошибка синхронизации устройств: Запрос не вернул результата.');
          }
        } else {
          $this->flashMessenger()->addErrorMessage('Не определено событие');
        }

        break;
    }

    return $this->redirect()->toRoute(
      'settings',
      ['action' => 'index']
    );
  }

  /**
   * Работа для крона.
   * Загрузка расширенной информации для билетов.
   */
  public function cronAction()
  {

  }
}

