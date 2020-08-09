<?php

namespace Application\Form;

use Zend\Form\Form;

class SearchForm extends Form
{
  public function __construct()
  {
    parent::__construct('search-form');

    $this->setAttribute('method', 'get');

    $this->addElements();
    $this->addInputFilter();
  }

  protected function addElements()
  {
    $this->add([
      'type'  => 'text',
      'name'  => 'search',
      'options' => [
        'label' => 'Поиск'
      ],
    ]);

    $this->add([
      'type'  => 'select',
      'name'  => 'filter',
      'options' => [
        'label' => 'Фильтр',
        'value_options' => [
          'all'     => 'Все',
          'used'    => 'Выдан',
          'unused'  => 'Не выдан'
        ]
      ],
    ]);

    $this->add([
      'type'  => 'submit',
      'name'  => 'submit',
      'attributes'  => [
        'value' => 'Показать'
      ],
    ]);
  }

  private function addInputFilter() {}
}