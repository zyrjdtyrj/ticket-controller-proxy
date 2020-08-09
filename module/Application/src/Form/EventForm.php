<?php

namespace Application\Form;

use Application\Entity\Event;
use Application\Service\EventManager;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;

class EventForm extends Form
{
  private $scenario;

  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var EventManager
   */
  private $eventManager;

  /**
   * @var Event
   */
  private $event;

  /**
   * EventForm constructor.
   *
   * @param $scenario
   * @param $entityManager
   * @param $eventManager
   * @param $eventId
   */
  public function __construct($scenario, $entityManager, $eventManager, $eventId = null)
  {
    $this->scenario       = $scenario;
    $this->entityManager  = $entityManager;
    $this->eventManager   = $eventManager;

    if (null !== $eventId) {
      $this->event = $this->eventManager->getEvent($eventId);
    }

    parent::__construct('event-form');

    $this->setAttribute('method', 'post');

    $this->addElement();
  }

  /**
   * Добавление элементов формы
   */
  protected function addElement()
  {
    // поле "Идентификатор"
    $this->add([
      'type'        => 'number',
      'name'        => 'id',
      'attributes'  => [
        'id'        => 'id',
        'size'      => 11,
        'maxlength' => 11,
        'value'     => (isset($this->event)) ? $this->event->getId() : '',
      ],
      'options'     => [
        'label' => 'Идентификатор',
      ],
    ]);

    // поле "Имя"
    $this->add([
      'type'        => 'text',
      'name'        => 'name',
      'attributes'  => [
        'id'    => 'name',
        'value' => (isset($this->event)) ? $this->event->getName() : '',
      ],
      'options'     => [
        'label' => 'Наименование'
      ],
    ]);

    // поле "Начало"
    $this->add([
      'type'        => 'datetimeselect',
      'name'        => 'date_begin',
      'attributes'  => [
        'id'    => 'date_begin',
        'value' => (isset($this->event)) ? date('H:i:s d.m.Y', $this->event->getDateBegin()) : '',
      ],
      'options'     => [
        'label' => 'Дата начала'
      ],
    ]);

    // поле "Конец"
    $this->add([
      'type'        => 'datetimeselect',
      'name'        => 'date_end',
      'attributes'  => [
        'id'    => 'date_end',
        'value' => (isset($this->event)) ? date('H:i:s d.m.Y', $this->event->getDateEnd()) : '',
      ],
      'options'     => [
        'label' => 'Дата конца'
      ],
    ]);

    // Add the Submit button
    $this->add([
      'type'        => 'submit',
      'name'        => 'submit',
      'attributes'  => [
        'value' => 'Создать',
        'id'    => 'submit',
      ],
    ]);

    // Add the CSRF field
    $this->add([
      'type'        => 'csrf',
      'name'        => 'csrf',
      'options'     => [
        'csrf_options' => [
          'timeout' => 600
        ]
      ],
    ]);
  }
}