<?php

namespace Application\Controller;

use Application\Entity\Device;
use Application\Entity\Event;
use Application\Entity\History;
use Application\Entity\Ticket;
use Application\Form\DeviceForm;
use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class DeviceController extends AbstractActionController
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var RbacManager
   */
  private $rbacManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * @var TicketManager
   */
  private $ticketManager;

  /**
   * @var EventManager
   */
  private $eventManager;

  /**
   * @var DeviceManager
   */
  private $deviceManager;

  /**
   * DeviceController constructor.
   *
   * @param EntityManager $entityManager
   * @param RbacManager   $rbacManager
   * @param IniparManager $iniParManager
   * @param TicketManager $ticketManager
   * @param EventManager  $eventManager
   */
  public function __construct($entityManager, $rbacManager, $iniParManager, $ticketManager, $eventManager, $deviceManager)
  {
    $this->entityManager  = $entityManager;
    $this->rbacManager    = $rbacManager;
    $this->iniParManager  = $iniParManager;
    $this->ticketManager  = $ticketManager;
    $this->eventManager   = $eventManager;
    $this->deviceManager  = $deviceManager;
  }

  /**
   * Заглавная страница
   *
   * @return ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function indexAction()
  {
    $eventId = $this->params()->fromRoute('id');

    $deviceList = [];

    if (null === $eventId) {
      $eventList = $this->eventManager->getEvents();

      // проходим по всем событиям
      /**
       * @var Event $event
       */
      foreach ($eventList as $event) {
        $devices = [];
        $eventDeviceList = $event->getDevices();
        /**
         * @var Device $eventDevice
         */
        foreach ($eventDeviceList as $eventDevice) {
          $devices[] = [
            'id' => $eventDevice->getId(),
            'name' => $eventDevice->getName(),
            'groups' => $eventDevice->getGroups(),
          ];
        }

        $deviceList[] = [
          'id' => $event->getId(),
          'name' => $event->getName(),
          'devices' => $devices,
        ];
      }
    } else {
      /**
       * @var Event $event
       */
      $event = $this->eventManager->getEvent($eventId);

      $devices = [];
      $eventDeviceList = $event->getDevices();
      /**
       * @var Device $eventDevice
       */
      foreach ($eventDeviceList as $eventDevice) {
        $devices[] = [
          'id' => $eventDevice->getId(),
          'name' => $eventDevice->getName(),
          'groups' => $eventDevice->getGroups(),
        ];
      }

      $deviceList[] = [
        'id'      => $event->getId(),
        'name'    => $event->getName(),
        'devices' => $devices,
      ];
    }

    return new ViewModel([
      'deviceList'  => $deviceList,
      'rbacManager' => $this->rbacManager,
      'eventId'     => $eventId
    ]);
  }

  /**
   * Добавление устройства
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function addAction()
  {
    if (!$this->rbacManager->isGranted(null, 'device.manage')) {
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $eventId  = $this->params()->fromPost('eventId');
    $id       = $this->params()->fromRoute('id');

    $form = new DeviceForm('create', $this->entityManager, $eventId, null, $this->eventManager);

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      $form->setData($data);

      if ($form->isValid()) {
        $data = $form->getData();

        $event = $this->eventManager->getEvent($eventId);

        $device = new Device();
        $device->setId($data['id']);
        $device->setEvent($event);
        $device->setName($data['name']);
        $device->setGroups($data['groups']);

        $this->entityManager->persist($device);

        $this->entityManager->flush();

        $this->flashMessenger()->addSuccessMessage('Новое устройство добавлено');

        return $this->redirect()->toRoute('device', ['action' => 'index']);
      }
    }

    return new ViewModel([
      'form'  => $form,
      'id'    => $id,
    ]);
  }

  /**
   * Редактирование устройства
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function editAction()
  {
    if (!$this->rbacManager->isGranted(null, 'device.manage')) {
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $deviceId = $this->params()->fromRoute('id', -1);
    if ($deviceId < 1) {
      $this->flashMessenger()->addErrorMessage('Такого устройства не существует');
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    /**
     * @var Device $device
     */
    $device = $this->deviceManager->getDevice($deviceId);

    if (null === $device) {
      $this->flashMessenger()->addErrorMessage('Такого устройства не существует');
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $eventId = $device->getEventId();

    $form = new DeviceForm('update', $this->entityManager, $eventId, $device, $this->eventManager);

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      $form->setData($data);

      if ($form->isValid()) {
        $data = $form->getData();

        $device->setId($data['id']);
        $device->setEventId($eventId);
        $device->setName($data['name']);
        $device->setGroups($data['groups']);

        $this->entityManager->persist($device);

        $this->entityManager->flush();

        $this->flashMessenger()->addSuccessMessage('Устройство обновлено');

        return $this->redirect()->toRoute('device', ['action' => 'index']);
      }
    }

    return new ViewModel([
      'form'    => $form,
      'device'  => $device
    ]);
  }

  /**
   * Удаление устройства
   *
   * @return \Zend\Http\Response
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function deleteAction()
  {
    if (!$this->rbacManager->isGranted(null, 'device.manage')) {
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $deviceId = $this->params()->fromRoute('id', -1);
    if ($deviceId < 1) {
      $this->flashMessenger()->addErrorMessage('Такого устройства не существует');
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $device = $this->entityManager->getRepository(Device::class)->find($deviceId);

    if (null === $device) {
      $this->flashMessenger()->addErrorMessage('Такого устройства не существует');
      return $this->redirect()->toRoute('device', ['action' => 'index']);
    }

    $this->entityManager->remove($device);
    $this->entityManager->flush();

    // Add a flash message.
    $this->flashMessenger()->addSuccessMessage('Устройство удалено.');

    // Redirect to "index" page
    return $this->redirect()->toRoute('device', ['action' => 'index']);
  }

  /**
   * История
   *
   * @return ViewModel
   */
  public function historyAction()
  {
    $page   = $this->params()->fromQuery('page', 1);
    $device = $this->params()->fromQuery('device');

    // получаем историю
    $qb = $this->entityManager->createQueryBuilder();

    $qb->select('h')
      ->from(History::class, 'h')
      ->where($qb->expr()->eq('h.device', '?1'))
      ->orderBy('h.date', 'DESC')
      ->setParameter(1, $device);

    $historyList = $qb->getQuery();

    $adapter = new DoctrineAdapter(new ORMPaginator($historyList, false));
    $paginator = new Paginator($adapter);
    $paginator->setItemCountPerPage(40);
    $paginator->setCurrentPageNumber($page);

    $ticketList = [];
    foreach ($paginator as $history) {
      $ticketList[] = $history->getTicketId();
    }

    // получаем билеты
    $ticketList = $this->entityManager->getRepository(Ticket::class)->findById($ticketList);

    return new ViewModel([
      'ticketList'    => $ticketList,
      'ticketManager' => $this->ticketManager,
      'historyList'   => $paginator,
    ]);
  }
}
