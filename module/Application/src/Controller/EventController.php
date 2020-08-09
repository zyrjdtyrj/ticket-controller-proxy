<?php

namespace Application\Controller;

use Application\Entity\Event;
use Application\Form\EventForm;
use Application\Service\EventManager;
use Application\Service\IniparManager;
use Doctrine\ORM\EntityManager;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EventController extends AbstractActionController
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
   * @var EventManager
   */
  private $eventManager;

  /**
   * EventController constructor.
   *
   * @param EntityManager $entityManager
   * @param RbacManager   $rbacManager
   * @param IniparManager $iniParManager
   * @param EventManager  $eventManager
   */
  public function __construct($entityManager, $rbacManager, $iniParManager, $eventManager)
  {
    $this->entityManager  = $entityManager;
    $this->rbacManager    = $rbacManager;
    $this->iniParManager  = $iniParManager;
    $this->eventManager   = $eventManager;
  }

  /**
   * Заглавная страница
   *
   * @return ViewModel
   */
  public function indexAction()
  {
    $events = [];

    $eventList = $this->eventManager->getEvents();
    /**
     * @var Event $event
     */
    foreach ($eventList as $event) {
      $events[] = [
        'id'          => $event->getId(),
        'eventId'     => $event->getEventId(),
        'name'        => $event->getName(),
        'syncTime'    => $event->getSyncTime(),
        'dateBegin'   => (0 != $event->getDateBegin()) ? date('H:i:s d.m.Y', $event->getDateBegin()) : '',
        'dateEnd'     => (0 != $event->getDateEnd()) ? date('H:i:s d.m.Y', $event->getDateEnd()) : '',
        'deviceCount' => $event->getDevices()->count(),
        'ticketCount' => $event->getTickets()->count(),
      ];
    }

    return new ViewModel([
      'events'        => $events,
      'rbacManager'   => $this->rbacManager,
      'iniParManager' => $this->iniParManager,
    ]);
  }

  /**
   * Редактирование события
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function editAction()
  {
    if (false === $this->rbacManager->isGranted(null, 'event.manage')) {
      $this->flashMessenger()->addErrorMessage('У вас нет прав на редактирование событий');
      return $this->redirect()->toRoute('event', ['action' => 'index']);
    }

    $eventId = $this->params()->fromRoute('eventId', -1);

    if (1 > $eventId) {
      $this->flashMessenger()->addErrorMessage('Такого события не не существует');
      return $this->redirect()->toRoute('event', ['action' => 'index']);
    }

    /**
     * @var Event $event
     */
    $event = $this->eventManager->getEvent($eventId);

    if (null === $event) {
      $this->flashMessenger()->addErrorMessage('Такого события не не существует');
      return $this->redirect()->toRoute('event', ['action' => 'index']);
    }

    $form = new EventForm('update', $this->entityManager, $this->eventManager, $eventId);

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      $form->setData($data);

      if ($form->isValid()) {
        // добавляем дату начала и окончания события
        $event->setDateBegin(mktime($data['date_begin']['hour'], $data['date_begin']['minute'], 0, $data['date_begin']['month'], $data['date_begin']['day'], $data['date_begin']['year']));
        $event->setDateEnd(mktime($data['date_end']['hour'], $data['date_end']['minute'], 0, $data['date_end']['month'], $data['date_end']['day'], $data['date_end']['year']));

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->flashMessenger()->addSuccessMessage('Событие обновлено');

        return $this->redirect()->toRoute('event', ['action' => 'index']);
      }
    }

    return new ViewModel([
      'form'  => $form,
    ]);
  }
}
