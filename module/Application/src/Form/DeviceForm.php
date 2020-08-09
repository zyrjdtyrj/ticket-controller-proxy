<?php

namespace Application\Form;

use Application\Entity\Device;
use Application\Service\EventManager;
use Application\Validator\DeviceExistValidator;
use Application\Validator\DeviceIdExistValidator;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class DeviceForm extends Form
{
  private $scenario;

  private $entityManager;

  /**
   * @var Device
   */
  private $device;

  private $event;

  /**
   * @var EventManager
   */
  private $eventManager;


  /**
   * DeviceForm constructor.
   *
   * @param string $scenario
   * @param EntityManager $entityManager
   * @param null $device
   * @param int $event
   * @param EventManager $eventManager
   */
  public function __construct($scenario, $entityManager, $event, $device, $eventManager)
  {
    $this->scenario       = $scenario;
    $this->entityManager  = $entityManager;
    $this->device         = $device;
    $this->event          = $event;
    $this->eventManager   = $eventManager;

    parent::__construct('device-form');

    $this->setAttribute('method', 'post');

    $this->addElements();
    $this->addInputFilter();
  }

  protected function addElements()
  {
    // поле "Идентификатор"
    $this->add([
      'type'        => 'number',
      'name'        => 'id',
      'attributes'  => [
        'id'        => 'id',
        'size'      => 11,
        'maxlength' => 11,
        'value'     => (isset($this->device)) ? $this->device->getId() : '',
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
        'id'        => 'name',
        'value'     => (isset($this->device)) ? $this->device->getName() : '',
      ],
      'options'     => [
        'label' => 'Имя'
      ],
    ]);

    // поле "Событие"
    $this->add([
      'type'        => 'select',
      'name'        => 'eventId',
      'attributes'  => [
        'id'    => 'eventId',
        'value' => (isset($this->device)) ? $this->device->getEventId() : '',
      ],
      'options'     => [
        'label'         => 'Событие',
        'value_options' => $this->eventManager->getEventsShortArray(),
      ],
    ]);

    // поле "Группы обслуживания"
    $this->add([
      'type'        => 'text',
      'name'        => 'groups',
      'attributes'  => [
        'id'        => 'groups',
        'value'     => (isset($this->device)) ? $this->device->getGroups() : '',
      ],
      'options'     => [
        'label' => 'Группы доступа'
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

  private function addInputFilter()
  {
    $inputFilter = new InputFilter();
    $this->setInputFilter($inputFilter);

    $inputFilter->add([
      'name'        => 'id',
      'required'    => true,
      'filters'     => [
        [
          'name'  => 'StringTrim',
        ]
      ],
      'validators'  => [
        [
          'name'    => 'StringLength',
          'options' => [
            'min' => 1,
            'max' => 11,
          ]
        ],
        [
          'name'    => DeviceIdExistValidator::class,
          'options' => [
            'entityManager' => $this->entityManager,
            'device'        => $this->device,
          ],
        ],
      ],
    ]);

    $inputFilter->add([
      'name'        => 'name',
      'required'    => true,
      'filters'     => [
        [
          'name'  => 'StringTrim',
        ]
      ],
      'validators'  => [
        [
          'name'    => 'StringLength',
          'options' => [
            'min' => 1,
            'max' => 128,
          ]
        ],
        [
          'name'    => DeviceExistValidator::class,
          'options' => [
            'entityManager' => $this->entityManager,
            'device'        => $this->device,
            'event'         => $this->event,
          ],
        ]
      ],
    ]);
  }
}