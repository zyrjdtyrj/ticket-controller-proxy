<?php

namespace Application\Form;

use Application\Entity\Server;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class ServerForm extends Form
{
  private $scenario;

  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var Server
   */
  private $server;

  /**
   * ServerForm constructor.
   *
   * @param string        $scenario
   * @param EntityManager $entityManager
   * @param Server        $server
   */
  public function __construct($scenario, $entityManager, $server)
  {
    $this->scenario       = $scenario;
    $this->entityManager  = $entityManager;
    $this->server         = $server;

    parent::__construct('server-form');

    $this->setAttribute('method', 'post');

    $this->addElements();
    $this->addInputFilter();
  }

  protected function addElements()
  {
    // поле "Имя"
    $this->add([
      'type'        => 'text',
      'name'        => 'name',
      'attributes'  => [
        'id'        => 'name',
        'value'     => (isset($this->server)) ? $this->server->getName() : '',
      ],
      'options'     => [
        'label' => 'Название'
      ],
    ]);

    // поле "Адрес"
    $this->add([
      'type'        => 'text',
      'name'        => 'address',
      'attributes'  => [
        'id'        => 'address',
        'value'     => (isset($this->server)) ? $this->server->getAddress() : '',
      ],
      'options'     => [
        'label' => 'Адрес'
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
            'max' => 250,
          ]
        ],
      ],
    ]);

    $inputFilter->add([
      'name'        => 'address',
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
            'max' => 250,
          ]
        ],
      ],
    ]);
  }
}
