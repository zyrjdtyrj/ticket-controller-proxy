<?php

namespace Application\Controller;

use Application\Entity\Device;
use Application\Entity\Event;
use Application\Entity\EventGroup;
use Application\Entity\History;
use Application\Entity\Log;
use Application\Entity\Ticket;
use Application\Service\ApiManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\LogManager;
use Application\Service\TicketManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ApiController extends AbstractActionController
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * @var ApiManager
   */
  private $apiManager;

  /**
   * @var LogManager
   */
  private $logManager;

  /**
   * @var EventManager
   */
  private $eventManager;

  /**
   * @var TicketManager
   */
  private $ticketManager;

  /**
   * @var string
   */
  private $proxyMode;

  /**
   * @var string
   */
  private $ip;

  private $result;

  /**
   * DeviceController constructor.
   *
   * @param EntityManager $entityManager
   * @param IniparManager $iniParManager
   * @param ApiManager    $apiManager
   * @param LogManager    $logManager
   * @param EventManager  $eventManager
   * @param TicketManager $ticketManager
   */
  public function __construct($entityManager, $iniParManager, $apiManager, $logManager, $eventManager, $ticketManager)
  {
    $this->entityManager  = $entityManager;
    $this->iniParManager  = $iniParManager;
    $this->apiManager     = $apiManager;
    $this->logManager     = $logManager;
    $this->eventManager   = $eventManager;
    $this->ticketManager  = $ticketManager;

    // ответ по-умолчанию
    $this->result = [
      'Status'    => 'Ok',
      'DeviceIP'  => $this->ip,
    ];
  }

  /**
   * Выполение действий перед диспетчеризацией
   *
   * @param MvcEvent $e
   *
   * @return mixed
   */
  public function onDispatch(MvcEvent $e)
  {
    // разрешаем кросдоменные запросы
    $header = $this->getResponse()->getHeaders();
    $header->addHeaderLine('Access-Control-Allow-Origin: *');
    $header->addHeaderLine('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    $header->addHeaderLine('Access-Control-Allow-Headers: origin, X-Requested-With');

    // режим работы прокси-сервиса
    $this->proxyMode = $this->iniParManager->get('proxyMode',' online');

    // ip-адрес вызываещего
    $this->ip = $this->getRequest()->getServer()->get('REMOTE_ADDR');

    return parent::onDispatch($e);
  }

  /**
   * Основной API proxy сервис
   *
   * @return JsonModel|\Zend\View\Model\ViewModel
   * @throws \Doctrine\ORM\ORMException
   */
  public function indexAction() {
    // получаем данные
    $action = $this->params()->fromQuery('Action');
    $device = $this->params()->fromQuery('Device');
    $time   = $this->params()->fromQuery('Time');

    $eventId  = $this->params()->fromQuery('Event', null);
    $flag = true;
    if (null !== $eventId) {
      // проверка полученного имени девайса со списком девайсов события
      $flag = false;

      /**
       * @var Event $event
       */
      $event = $this->eventManager->getEvent($eventId);

      // получаем устройства события
      $eventDeviceList = $event->getDevices();

      // проходим по всех устройствам
      /**
       * @var Device $eventDevice
       */
      foreach ($eventDeviceList as $eventDevice) {
        if ($eventDevice->getName() === $device) {
          $flag = true;
        }
      }
    }

    if (true === $flag) {
      // запись использования api
      $logItem = new Log();
      $logItem->setDate(time());
      $logItem->setDevice($device);
      $logItem->setIp($this->ip);
      $logItem->setMethod($action);
      $logItem->setParams(http_build_query($this->params()->fromQuery()));

      $this->entityManager->persist($logItem);

      if (null !== $action) {
        if (null !== $device && null !== $time) {
          switch ($action) {
            // получение статуса сервера
            case 'CheckStatus':
              // получаем дополнительные параметры
              $time = $this->params()->fromQuery('Time');

              if (null !== $time) {
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'CheckStatus',
                    [
                      'Device' => $device,
                      'Time' => $time,
                    ]
                  );
                }

                if ('offline' === $this->proxyMode) {
                  $timeShift = time() - strtotime($time);

                  $result = $this->result;
                  $result['TimeShift'] = $timeShift;
                  $result['IP'] = $this->ip;
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }

              break;
            // получение событий
            case 'GetEvents':
              if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                $result = $this->apiManager->exec('GetEvents', ['Device' => $device, 'Time' => $time]);

                if ('Ok' == $result['Status']) {
                  foreach ($result['Events'] as $event) {
                    $this->eventManager->addEvent($event);
                  }
                }
              }

              if ('offline' === $this->proxyMode) {
                $result = $this->result;

                $eventList = $this->eventManager->getEvents();
                /**
                 * @var Event $event
                 */
                foreach ($eventList as $event) {
                  // получение групп
                  $groups = $this->entityManager->getRepository(EventGroup::class)->findByEventId($event->getId());
                  $groupList = [];
                  /**
                   * @var EventGroup $group
                   */
                  foreach ($groups as $group) {
                    $groupList[] = [
                      'ID' => $group->getId(),
                      'Name' => $group->getName(),
                      'Color' => $group->getColor(),
                    ];
                  }

                  $result['Events'][] = [
                    'ID' => $event->getId(),
                    'Name' => $event->getName(),
                    'DateBegin' => date('c', $event->getDateBegin()),
                    'DateEnd' => date('c', $event->getDateEnd()),
                    'Groups' => $groupList,
                  ];
                }
              }

              break;
            // получение билетов
            case 'GetTickets':
              // получаем дополнительные обязательные параметры
              $eventId = $this->params()->fromQuery('Event');
              $tickets = $this->params()->fromQuery('Tickets');

              if (null !== $eventId) {
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'GetTickets',
                    [
                      'Device' => $device,
                      'Time' => $time,
                      'Event' => $eventId,
                      'Tickets' => $tickets,
                    ]
                  );

                  if ('Ok' === $result['Status']) {
                    $event = $this->eventManager->getEvent($eventId);

                    foreach ($result['Tickets'] as $ticket) {
                      /**
                       * @var Ticket $ticket
                       */
                      $item = $this->ticketManager->getTicket($ticket['i']);

                      if (null === $item) {
                        $item = new Ticket();
                      }

                      $item->setId($ticket['i']);
                      $item->setEvent($event);
                      $item->setUnumber($ticket['p']);
                      $item->setFio($ticket['f']);
                      $item->setStatus($ticket['s']);
                      if (isset($ticket['g'])) $item->setGroups($ticket['g']);
                      if (isset($ticket['l'])) $item->setModifiedTime(strtotime($ticket['l']));
                      if (isset($ticket['n'])) $item->setPlace($ticket['n']) ;

                      $this->entityManager->persist($item);
                    }
                  }

                }

                if ('offline' === $this->proxyMode) {
                  $result = $this->result;

                  $eventId = $this->eventManager->getTrueEventId($eventId);

                  $criteria = new Criteria();
                  $criteria->where(Criteria::expr()->eq('eventId', $eventId));
                  if (null !== $tickets) $criteria->andWhere(Criteria::expr()->in('id', explode(',', $tickets)));
                  $ticketList = $this->entityManager->getRepository(Ticket::class)->matching($criteria);
                  foreach ($ticketList as $ticket) {
                    $result['Tickets'][] = [
                      'i' => $ticket->getId(),
                      'f' => $ticket->getFio(),
                      'p' => $ticket->getUnumber(),
                      's' => $ticket->getStatus(),
                      'g' => $ticket->getGroups(),
                      'l' => date('c', $ticket->getModifiedTime()),
                      'n' => $ticket->getPlace(),
                    ];
                  }
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }
              break;
            // списка изменённых билетов
            case 'GetModifiedTickets':
              // получаем дополнительные параметры
              $eventId = $this->params()->fromQuery('Event');
              $timeModifiedFrom = $this->params()->fromQuery('TimeModifiedFrom');

              if (null !== $eventId) {
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'GetModifiedTickets',
                    [
                      'Device' => $device,
                      'Time' => $time,
                      'Event' => $eventId,
                      'TimeModifiedFrom' => $timeModifiedFrom,
                    ]
                  );

                  if ('Ok' === $result['Status']) {
                    // обновляем локальные данные
                    foreach ($result['Tickets'] as $ticket) {
                      $item = $this->entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticket['i']]);
                      if (null !== $item) {
                        $item->setStatus($ticket['s']);
                        $item->setModifiedTime(strtotime($ticket['l']));

                        $this->entityManager->persist($item);
                      }
                    }
                  }
                }

                if ('offline' === $this->proxyMode) {
                  $result = $this->result;

                  $eventId = $this->eventManager->getTrueEventId($eventId);

                  $criteria = new Criteria();
                  $criteria->where(Criteria::expr()->eq('eventId', $eventId));
                  if (null !== $timeModifiedFrom) $criteria->andWhere(Criteria::expr()->gte('modified_time', strtotime($timeModifiedFrom)));
                  $ticketList = $this->entityManager->getRepository(Ticket::class)->matching($criteria);

                  /**
                   * @var Ticket $ticket
                   */
                  $tickets = [];
                  foreach ($ticketList as $ticket) {
                    $tickets[] = [
                      'i' => $ticket->getId(),
                      's' => $ticket->getStatus(),
                      'l' => date('c', $ticket->getModifiedTime()),
                    ];
                  }
                  $result['Tickets'] = $tickets;
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }
              break;
            // запрос полной информации о билете
            case 'GetTicketsInfo':
              // получаем дополнительные параметры
              $eventId = $this->params()->fromQuery('Event');
              $ticketId = $this->params()->fromQuery('Ticket');

              if (null !== $eventId && null !== $ticketId) {
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'GetTicketsInfo',
                    [
                      'Device' => $device,
                      'Time' => $time,
                      'Event' => $eventId,
                      'Ticket' => $ticketId,
                    ]
                  );

                  if ('Ok' === $result['Status']) {
                    // обновляем локальные данные
                    //foreach ($result['Tickets'] as $ticket) {
                    /**
                     * @var Ticket $item
                     */
                    $item = $this->entityManager->getRepository(Ticket::class)->findOneById($result['Tickets'][0]['i']);

                    if (null !== $item) {
                      $item->setFio($result['Tickets'][0]['f']);
                      $item->setCity($result['Tickets'][0]['c']);
                      if (isset($result['Tickets'][0]['p'])) $item->setUnumber($result['Tickets'][0]['p']);
                      $item->setType($result['Tickets'][0]['t']);
                      $item->setPhone($result['Tickets'][0]['m']);
                      $item->setOnumber($result['Tickets'][0]['o']);
                      $item->setStatus($result['Tickets'][0]['s']);
                      if (isset($result['Tickets'][0]['d'])) $item->setModifiedTime(strtotime($result['Tickets'][0]['d']));
                      if (isset($result['Tickets'][0]['n'])) $item->setPlace($result['Tickets'][0]['n']);
                      if (isset($result['Tickets'][0]['l'])) $item->setLog($result['Tickets'][0]['l']);
                      if (isset($result['Tickets'][0]['g'])) $item->setGroups($result['Tickets'][0]['g']);

                      $this->entityManager->persist($item);

                      // обновление истории
                      if (isset($result['Tickets'][0]['h'])) {
                        foreach ($result['Tickets'][0]['h'] as $history) {

                          // проверяем, если такая запись в истории
                          $criteria = new Criteria();
                          $criteria->where(Criteria::expr()->eq('ticketId', $result['Tickets'][0]['i']));
                          $criteria->andWhere(Criteria::expr()->eq('date', strtotime($history['d'])));
                          $criteria->andWhere(Criteria::expr()->eq('status', $history['s']));
                          $count = $this->entityManager->getRepository(History::class)->matching($criteria)->count();
                          if (0 == $count) {
                            // добавляем
                            /**
                             * @var History $item
                             */
                            $item = new History();
                            $item->setDate(strtotime($history['d']));
                            $item->setTicketId($result['Tickets'][0]['i']);
                            $item->setDevice($history['n']);
                            $item->setIp($history['ip']);
                            $item->setStatus($history['s']);

                            $this->entityManager->persist($item);
                          }
                        }
                      }
                    }
                    //}
                  }
                }

                if ('offline' === $this->proxyMode) {
                  $result = $this->result;

                  /**
                   * @var Ticket $ticket
                   */
                  $ticket = $this->entityManager->getRepository(Ticket::class)->findOneById($ticketId);

                  $historyList = $this->entityManager->getRepository(History::class)->findBy(['ticketId' => $ticket->getId()], ['date' => 'ASC']);
                  $history = [];
                  /**
                   * @var History $historyItem
                   */
                  foreach ($historyList as $historyItem) {
                    $history[] = [
                      'n'   => $historyItem->getDevice(),
                      'ip'  => $historyItem->getIp(),
                      's'   => $historyItem->getStatus(),
                      'd'   => date('c', $historyItem->getDate()),
                    ];
                  }

                  $result['Tickets'][] = [
                    'i' => $ticket->getId(),
                    'f' => $ticket->getFio(),
                    'c' => $ticket->getCity(),
                    'p' => $ticket->getUnumber(),
                    't' => $ticket->getType(),
                    'm' => $ticket->getPhone(),
                    'o' => $ticket->getOnumber(),
                    's' => $ticket->getStatus(),
                    'd' => $ticket->getModifiedTime(),
                    'h' => $history,
                    'n' => $ticket->getPlace(),
                    'l' => $ticket->getLog(),
                    'g' => $ticket->getGroups(),
                  ];
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }

              break;
            // запрос на изменение статуса билета
            case 'SetTicket':
              // получение дополнительных параметров
              $eventId = $this->params()->fromQuery('Event');
              $ticketId = $this->params()->fromQuery('Ticket');
              $status = $this->params()->fromQuery('Status');
              $deviceIP = $this->params()->fromQuery('DeviceIP');

              if (null !== $eventId && null !== $ticketId && null !== $status) {
                if (null === $deviceIP) $deviceIP = $this->ip;
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'SetTicket',
                    [
                      'Device' => $device,
                      'Time' => $time,
                      'Event' => $eventId,
                      'Ticket' => $ticketId,
                      'Status' => $status,
                      'DeviceIP' => $deviceIP,
                    ]
                  );

                  if ('Ok' === $result['Status']) {
                    // обновляем локальные данные
                    /**
                     * @var Ticket $item
                     */
                    $item = $this->entityManager->getRepository(Ticket::class)->findOneById($ticketId);

                    if (null !== $item) {
                      $item->setStatus($result['TicketStatus']);
                      $item->setModifiedTime(strtotime($result['TicketModified']));

                      $this->entityManager->persist($item);

                      // делаем запись в истории
                      $historyItem = new History();
                      $historyItem->setDevice($device);
                      $historyItem->setStatus($status);
                      $historyItem->setTicketId($ticketId);
                      $historyItem->setIp($deviceIP);
                      $historyItem->setDate(strtotime($time));

                      $this->entityManager->persist($historyItem);
                    }
                  }
                }

                if ('offline' == $this->proxyMode) {
                  $result = $this->result;

                  /**
                   * @var Ticket $item
                   */
                  $item = $this->entityManager->getRepository(Ticket::class)->findOneById($ticketId);

                  if (null !== $item) {
                    if ($item->getStatus() !== $status) {
                      $item->setStatus($status);
                      $item->setModifiedTime(time());

                      $this->entityManager->persist($item);

                      // делаем запись в истории
                      $historyItem = new History();
                      $historyItem->setDevice($device);
                      $historyItem->setStatus($status);
                      $historyItem->setTicketId($ticketId);
                      $historyItem->setIp($deviceIP);
                      $historyItem->setDate(strtotime($time));

                      $this->entityManager->persist($historyItem);

                      $this->entityManager->persist($historyItem);

                      $result['TicketStatus'] = $status;
                      $result['TicketModified'] = date('c', strtotime($time));
                      $result['TicketId'] = $ticketId;
                    } else {
                      $error = 'Возникла ошибка';

                      switch ($status) {
                        case 'IN':
                          $error = 'Талон погашен, гость внутри!';
                          break;
                        case 'OUT':
                          $error = 'Гость уже вышел!';
                          break;
                      }

                      $result = [
                        'Status' => 'Error',
                        'Message' => $error,
                      ];
                    }
                  } else {
                    $result = [
                      'Status' => 'Error',
                      'Message' => 'Такого билета не существует',
                    ];
                  }
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }

              break;
            // синхронизация билетов
            case 'SynTickets':
              // получение дополнительных параметров
              $eventId = $this->params()->fromQuery('Event');
              $timeSynFrom = $this->params()->fromQuery('TimeSynFrom');
              $postData = $this->params()->fromPost('Tickets');

              if (null !== $eventId && null !== $postData && null !== $timeSynFrom) {
                if ('online' === $this->proxyMode || 'proxy' === $this->proxyMode) {
                  $result = $this->apiManager->exec(
                    'SynTickets',
                    [
                      'Device' => $device,
                      'Time' => $time,
                      'Event' => $eventId,
                      'TimeSynFrom' => $timeSynFrom,
                      'Post' => ['Tickets' => $postData],
                    ]
                  );

                  if ('Ok' === $result['Status']) {
                    //$postData = json_decode($postData, true);

                    // обновление локальных данных
                    foreach ($postData as $item) {
                      /**
                       * @var Ticket $ticket
                       */
                      $ticket = $this->entityManager->getRepository(Ticket::class)->findOneById($item['id']);

                      if (null !== $ticket) {
                        $ticket->setStatus($item['s']);
                        $ticket->setModifiedTime(strtotime($item['d']));

                        $this->entityManager->persist($ticket);

                        // обновление истории
                        if (isset($item['h'])) {
                          foreach ($item['h'] as $history) {
                            // проверяем, если такая запись в истории
                            $criteria = new Criteria();
                            $criteria->where(Criteria::expr()->eq('ticketId', $item['id']));
                            $criteria->andWhere(Criteria::expr()->eq('date', strtotime($history['d'])));
                            $criteria->andWhere(Criteria::expr()->eq('status', $history['s']));
                            $count = $this->entityManager->getRepository(History::class)->matching($criteria)->count();
                            if (0 == $count) {
                              // добавляем
                              /**
                               * @var History $item
                               */
                              $historyItem = new History();
                              $historyItem->setDate(strtotime($history['d']));
                              $historyItem->setTicketId($item['id']);
                              $historyItem->setDevice($history['n']);
                              $historyItem->setIp(($history['ip']) ? $history['ip'] : $this->ip);
                              $historyItem->setStatus($history['s']);

                              $this->entityManager->persist($historyItem);
                            }
                          }
                        }
                      }
                    }

                    // обновление данных о билетах, если они пришли от билетного сервера
                    if (isset($result['Tickets'])) {
                      foreach ($result['Tickets'] as $item) {
                        $ticket = $this->entityManager->getRepository(Ticket::class)->findoOneById($item['i']);
                        if (null !== $ticket) {
                          $ticket->setStatus($item['s']);
                          $ticket->setModifiedTime(strtotime($item['l']));

                          $this->entityManager->persist($ticket);
                        }
                      }
                    }
                  }
                }

                if ('offline' === $this->proxyMode) {
                  $postData = json_decode($postData, true);
                  // обновление локальных данных
                  foreach ($postData as $item) {
                    /**
                     * @var Ticket $ticket
                     */
                    $ticket = $this->entityManager->getRepository(Ticket::class)->findOneById($item['id']);

                    if (null !== $ticket) {
                      $ticket->setStatus($item['s']);
                      $ticket->setModifiedTime($item['d']);

                      $this->entityManager->persist($ticket);

                      // обновление истории
                      if (isset($item['h'])) {
                        foreach ($item['h'] as $history) {
                          // проверяем, если такая запись в истории
                          $criteria = new Criteria();
                          $criteria->where(Criteria::expr()->eq('ticketId', $item['id']));
                          $criteria->andWhere(Criteria::expr()->eq('date', strtotime($history['d'])));
                          $criteria->andWhere(Criteria::expr()->eq('status', $history['s']));
                          $count = $this->entityManager->getRepository(History::class)->matching($criteria)->count();
                          if (0 == $count) {
                            // добавляем
                            /**
                             * @var History $item
                             */
                            $historyItem = new History();
                            $historyItem->setDate(strtotime($history['d']));
                            $historyItem->setTicketId($item['id']);
                            $historyItem->setDevice($history['n']);
                            $historyItem->setIp(($history['ip']) ? $history['ip'] : $this->ip);
                            $historyItem->setStatus($history['s']);

                            $this->entityManager->persist($historyItem);
                          }
                        }
                      }

                      $result = [
                        'Status' => 'Ok',
                      ];
                    } else {
                      $result = [
                        'Status' => 'Error',
                        'Message' => 'Такого билета нет',
                      ];
                    }
                  }
                }
              } else {
                $result = [
                  'Status' => 'Error',
                  'Message' => 'Отсутствуют обязательные параметры',
                ];
              }

              break;
            default:
              $result = [
                'Status' => 'Error',
                'Message' => 'Неизвестный метод [' . $action . ']',
              ];
              break;
          }
        } else {
          $result = [
            'Status' => 'Error',
            'Message' => 'Отсутствуют обязательные параметры',
          ];
        }
      } else {
        $result = [
          'Status' => 'Error',
          'Message' => 'Не передан параметр Action',
        ];
      }
    } else {
      $result = [
        'Status'  => 'Error',
        'Message' => 'Устройству с таким наименованием не разрешено пользоваться API',
      ];
    }

    $this->entityManager->flush();

    return new JsonModel($result);
  }

  /**
   * Страница тестирования API
   */
  public function testAction() {}

  /**
   * Выполнение запросов тестирования API
   *
   * @return JsonModel
   */
  public function execAction()
  {
    $params = $this->params()->fromPost();

    $method = $params['Method'];

    unset($params['Method']);

    $result = $this->apiManager->exec($method, $params);

    return new JsonModel($result);
  }

  /**
   * Журнал использования API
   *
   * @return ViewModel
   */
  public function logAction()
  {
    $page = $this->params()->fromQuery('page', 1);

    // получаем историю
    $paginator = $this->logManager->getLogPage($page);

    return new ViewModel([
      'historyList'   => $paginator,
    ]);
  }
}
