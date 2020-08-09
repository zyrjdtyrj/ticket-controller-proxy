<?php

namespace Application\Service;

use Application\Entity\Event;
use Application\Entity\EventGroup;
use Application\Entity\EventGroupAllow;
use Application\Entity\EventGroupPlace;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use User\Service\PermissionManager;
use User\Service\RoleManager;

class EventManager
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var PermissionManager
   */
  private $permissionManager;

  /**
   * @var RoleManager
   */
  private $roleManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * @var ServerManager
   */
  private $serverManager;

  /**
   * EventManager constructor.
   *
   * @param EntityManager     $entityManager
   * @param PermissionManager $permissionManager
   * @param RoleManager       $roleManager
   * @param IniparManager     $iniParManager
   * @param ServerManager     $serverManager
   */
  public function __construct($entityManager, $permissionManager, $roleManager, $iniParManager, $serverManager)
  {
    $this->entityManager      = $entityManager;
    $this->permissionManager  = $permissionManager;
    $this->roleManager        = $roleManager;
    $this->iniParManager      = $iniParManager;
    $this->serverManager      = $serverManager;
  }

  /**
   * Получение события по идентификатору
   *
   * @param $eventId
   *
   * @return object|null
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function getEvent($eventId)
  {
    $event = $this->entityManager->getRepository(Event::class)->findOneBy(['eventId' => $eventId, 'serverId' => $this->iniParManager->get('server')]);

    return $event;
  }

  /**
   * Получение списка событий в виде объектов
   *
   * @param array $orderBy
   *
   * @return array|object[]
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function getEvents($orderBy = ['id' => 'ASC'])
  {
    $eventList = $this->entityManager->getRepository(Event::class)->findBy(['serverId' => $this->iniParManager->get('server')], $orderBy);

    return $eventList;
  }

  /**
   * Получение списка событий в форме сокращённого массива
   *
   * @param array $orderBy
   *
   * @return array
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function getEventsShortArray($orderBy = ['id' => 'ASC'])
  {
    $eventArray = [];

    $eventList = $this->getEvents($orderBy);
    /**
     * @var Event $event
     */
    foreach ($eventList as $event) {
      $eventArray[$event->getEventId()] = $event->getName();
    }

    return $eventArray;
  }

  /**
   * Создание/обновление события
   *
   * @param $event
   *
   * @return string
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function addEvent($event)
  {
    // идентификатор текущего сервера
    $serverId = $this->iniParManager->get('server');

    // объект СЕРВЕР
    $serverItem = $this->serverManager->getServer($serverId);

    // пытаемся загрузить объект события
    $eventItem = $this->getEvent($event['ID']);

    // проверяем, что объект есть
    if (null === $eventItem) {
      // создаём новый
      $status = 'new';
      $eventItem = new Event();
      $eventItem->setEventId($event['ID']);
      $eventItem->setServer($serverItem);

      // создаём новое разрешение для нового объекта
      try {
        $permission = $this->permissionManager->addPermission([
          'name' => 'event_' . $event['ID'] . '_'. $serverId,
          'description' => $event['Name'],
        ]);

        // добавляем Администратору право на управление новыми событиями
        $role = $this->roleManager->getRoleByName('Administrator');
        $this->roleManager->addRolePermission($role, $permission);
      } catch (\Exception $e) {

      }
    } else {
      // обновляем старый
      $status = 'update';
    }

    if (isset($event['DateBegin'])) $eventItem->setDateBegin($event['DateBegin']);
    if (isset($event['DateEnd'])) $eventItem->setDateEnd($event['DateEnd']);
    $eventItem->setName($event['Name']);
    $eventItem->setSyncTime(time());

    $this->entityManager->persist($eventItem);

    // обновляем группы (если есть они)
    // @todo: по хорошему надо конечно переписать это, чтоб группы не удалялись... а дописывались те, которых нет в БД. И удалялись из БД, тех которых нет в результате.
    if (isset($event['Groups'])) {

      // загружаем новые группы
      $groups = $event['Groups'];
      foreach ($groups as $groupPar) {
        $groupItem = $this->entityManager->getRepository(EventGroup::class)->findOneBy(['groupId' => $groupPar['ID'], 'eventId' => $eventItem->getId()]);

        if (null === $groupItem) {
          $groupItem = new EventGroup();
          $groupItem->setGroupId($groupPar['ID']);
          $groupItem->setEvent($eventItem);
        }

        $groupItem->setName($groupPar['Name']);
        $groupItem->setColor($groupPar['Color']);

        $this->entityManager->persist($groupItem);

        // дописываем группы доступа к группе (если есть)
        if (isset($groupPar['Allow']) && 0 < count($groupPar['Allow'])) {
          foreach ($groupPar['Allow'] as $allow) {
            $groupAllowItem = $this->entityManager->getRepository(EventGroupAllow::class)->findOneBy(['eventGroupId' => $groupItem->getId(), 'allow' => $allow]);

            if (null == $groupAllowItem) {
              $groupAllowItem = new EventGroupAllow();
              $groupAllowItem->setAllow($allow);
              $groupAllowItem->setEventGroup($groupItem);

              $this->entityManager->persist($groupAllowItem);
            }
          }
        }

        // дописываем места рассадки группы (если есть)
        if (isset($groupPar['Places']) && 0 < count($groupPar['Places'])) {
          foreach ($groupPar['Places'] as $place) {
            $groupPlaceItem = $this->entityManager->getRepository(EventGroupPlace::class)->findOneBy(['eventGroupId' => $groupItem->getId(), 's' => $place['s'], 'r' => $place['r'], 'f' => $place['from'], 't' => $place['to']]);

            if (null == $groupPlaceItem) {
              $groupPlaceItem = new EventGroupPlace();
              $groupPlaceItem->setEventGroup($groupItem);
              $groupPlaceItem->setS($place['s']);
              $groupPlaceItem->setR($place['r']);
              $groupPlaceItem->setF($place['from']);
              $groupPlaceItem->setT($place['to']);

              $this->entityManager->persist($groupPlaceItem);
            }
          }
        }
      }
    }

    $this->entityManager->flush();

    return $status;
  }

  /**
   * Получение истинного (внутреннего) идентификатора события
   *
   * @param $eventId
   *
   * @return int
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function getTrueEventId($eventId)
  {
    $serverId = $this->iniParManager->get('server');

    /**
     * @var Event $event
     */
    $event = $this->entityManager->getRepository(Event::class)->findOneBy(['serverId' => $serverId, 'eventId' => $eventId]);

    return $event->getId();
  }
}
