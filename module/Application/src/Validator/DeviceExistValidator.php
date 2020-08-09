<?php

namespace Application\Validator;

use Application\Entity\Device;
use Doctrine\ORM\EntityManager;
use Zend\Validator\AbstractValidator;

class DeviceExistValidator extends AbstractValidator
{
  protected $options = [
    'entityManager' => null,
    'device'        => null,
    'eventId'       => null,
  ];

  const NOT_SCALAR    = 'notScalar';
  const DEVICE_EXIST  = 'deviceExist';

  protected $messageTemplates = [
    self::NOT_SCALAR    => 'The NAME must be a scalar value',
    self::DEVICE_EXIST  => 'Another device with such NAME already exists',
  ];

  public function __construct($options = null)
  {
    if (is_array($options)) {
      $this->options['entityManager'] = $options['entityManager'];
      $this->options['device']        = $options['device'];
      $this->options['eventId']       = $options['event'];
    }

    parent::__construct($this->options);
  }

  /**
   * @param mixed $value
   *
   * @return bool
   */
  public function isValid($value)
  {
    if (!is_scalar($value)) {
      $this->error(self::NOT_SCALAR);
      return false;
    }

    /**
     * @var EntityManager
     */
    $entityManager = $this->options['entityManager'];

    $device = $entityManager->getRepository(Device::class)->findOneBy(['eventId' => $this->options['eventId'], 'name' => $value]);

    if ($this->options['device'] == null) {
      $isValid = ($device == null);
    } else {
      if ($this->options['device']->getName() != $value && $device != null) {
        $isValid = false;
      } else {
        $isValid = true;
      }
    }

    if (!$isValid) {
      $this->error(self::DEVICE_EXIST);
    }

    return $isValid;
  }
}