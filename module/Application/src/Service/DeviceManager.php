<?php

namespace Application\Service;

use Application\Entity\Device;
use Application\Entity\Event;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class DeviceManager
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var EventManager
   */
  private $eventManager;

  /**
   * DeviceManager constructor.
   *
   * @param EntityManager $entityManager
   * @param EventManager  $eventManager
   */
  public function __construct($entityManager, $eventManager)
  {
    $this->entityManager  = $entityManager;
    $this->eventManager   = $eventManager;
  }

  /**
   * Получение устройства по идентификатору устройства
   *
   * @param $deviceId
   *
   * @return mixed
   */
  public function getDevice($deviceId)
  {
    $device = $this->entityManager->getRepository(Device::class)->findOneById($deviceId);

    return $device;
  }

  /**
   * Получение устройства по наименованию устройства
   *
   * @param $deviceName
   *
   * @return mixed
   */
  public function getDeviceByName($deviceName)
  {
    $device = $this->entityManager->getRepository(Device::class)->findOneByName($deviceName);

    return $device;
  }

  /**
   * Получение устройства по наименованию устройства и событию
   *
   * @param $deviceName
   * @param $eventId
   *
   * @return mixed
   */
  public function getDeviceByNameEvent($deviceName, $eventId)
  {
    $device = $this->entityManager->getRepository(Device::class)->findOneBy(['name' => $deviceName, 'eventId' => $eventId]);

    return $device;
  }

  /**
   * Получение списка устройств
   *
   * @param array $orderBy
   *
   * @return array|object[]
   */
  public function getDevices($orderBy = ['eventId' => 'ASC', 'id' => 'ASC'])
  {
    $deviceList = $this->entityManager->getRepository(Device::class)->findBy([], $orderBy);

    return $deviceList;
  }

  /**
   * Получение списка устройств события
   *
   * @param $eventId
   * @param array $orderBy
   *
   * @return array|object[]
   */
  public function getDevicesByEvent($eventId, $orderBy = ['id' => 'ASC'])
  {
    $deviceList = $this->entityManager->getRepository(Device::class)->findBy(['eventId' => $eventId], $orderBy);

    return $deviceList;
  }

  /**
   * Создание/обновление устройства
   *
   * @param array $device   Массив свойств устройства
   * @param int   $eventId  Собитые, которому пренадлежит устройство
   *
   * @return string
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function addDevice($device, $eventId)
  {
    $status = 'update';
    // пытаемся получить объект устройства
    /**
     * @var Event $event
     */
    $event = $this->eventManager->getEvent($eventId);
    $item = $this->getDeviceByNameEvent($device['ID'], $event->getId());

    // проверяем, что объект есть
    if (null === $item) {
      // создаём новый
      $item = new Device();
      $status = 'new';
    }

    // заполняем оставшиеся поля
    $item->setName($device['ID']);
    $item->setEvent($this->eventManager->getEvent($eventId));
    if (isset($device['AllowGroups'])) {
      $item->setGroups($device['AllowGroups']);
    } else {
      $item->setGroups('');
    }

    $this->entityManager->persist($item);
    $this->entityManager->flush();

    return $status;
  }

  /**
   * Получение количества устройств на событии
   *
   * @param $eventId
   *
   * @return mixed
   */
  public function getDeviceCount($eventId)
  {
    $deviceCount = $this->entityManager->getRepository(Device::class)->count(['eventId' => $eventId]);

    return $deviceCount;
  }
}
