<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Platform
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity
 */
class Ticket
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\Id
   */
  private $id;

  /**
   * @ORM\Column(name="event_id", type="integer", nullable=false)
   */
  private $eventId;

  /**
   * @var string
   *
   * @ORM\Column(name="fio", type="string", nullable=true)
   */
  private $fio;

  /**
   * @var string
   *
   * @ORM\Column(name="phone", type="string", nullable=true)
   */
  private $phone;

  /**
   * @var string
   *
   * @ORM\Column(name="city", type="string", nullable=true)
   */
  private $city;

  /**
   * @var string
   *
   * @ORM\Column(name="unumber", type="string", nullable=true)
   */
  private $unumber;

  /**
   * @var int
   *
   * @ORM\Column(name="onumber", type="integer", nullable=true)
   */
  private $onumber;

  /**
   * @var string
   *
   * @ORM\Column(name="type", type="string", nullable=true)
   */
  private $type;

  /**
   * @var string
   *
   * @ORM\Column(name="place", type="string", nullable=false)
   */
  private $place;

  /**
   * @var int
   *
   * @ORM\Column(name="sync_time", type="integer", nullable=true)
   */
  private $syncTime;

  /**
   * @var string
   * @ORM\Column(name="ostatus", type="string", nullable=true)
   */
  private $ostatus;

  /**
   * @var string
   * @ORM\Column(name="groups", type="string", nullable=true)
   */
  private $groups;

  /**
   * @var string
   * @ORM\Column(name="status", type="string", nullable=false)
   */
  private $status;

  /**
   * @var int
   * @ORM\Column(name="modified_time", type="integer", nullable=true)
   */
  private $modified_time;

  /**
   * @var string
   * @ORM\Column(name="log", type="text", nullable=true)
   */
  private $log;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Event", inversedBy="tickets")
   * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
   */
  protected $event;

  public function getId()
  {
    return str_pad($this->id, 8, 0, STR_PAD_LEFT);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getFio()
  {
    return $this->fio;
  }

  public function setFio($fio)
  {
    $this->fio = $fio;
  }

  /**
   * @return string
   */
  public function getPhone()
  {
    return $this->phone;
  }

  /**
   * @param string $phone
   */
  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  /**
   * @return string
   */
  public function getCity()
  {
    return $this->city;
  }

  /**
   * @param string $city
   */
  public function setCity($city)
  {
    $this->city = $city;
  }

  /**
   * @return int
   */
  public function getOnumber()
  {
    return $this->onumber;
  }

  /**
   * @param int $onumber
   */
  public function setOnumber($onumber)
  {
    $this->onumber = $onumber;
  }

  /**
   * @return mixed
   */
  public function getEventId()
  {
    return $this->eventId;
  }

  /**
   * @param mixed $eventId
   */
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }

  /**
   * @return string
   */
  public function getPlace()
  {
    return $this->place;
  }


  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getUnumber()
  {
    return $this->unumber;
  }

  /**
   * @param string $place
   */
  public function setPlace($place)
  {
    $this->place = $place;
  }

  /**
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * @param string $unumber
   */
  public function setUnumber($unumber)
  {
    $this->unumber = $unumber;
  }

  /**
   * @return int
   */
  public function getSyncTime()
  {
    return $this->syncTime;
  }

  /**
   * @param int $syncTime
   */
  public function setSyncTime($syncTime)
  {
    $this->syncTime = $syncTime;
  }

  public function getOstatus()
  {
    return $this->ostatus;
  }

  public function setOstatus($ostatus)
  {
    $this->$ostatus = $ostatus;
  }

  public function getGroups()
  {
    return $this->groups;
  }

  public function setGroups($groups)
  {
    $this->groups = $groups;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  public function getModifiedTime()
  {
    return $this->modified_time;
  }

  public function setModifiedTime($modifiedTime)
  {
    $this->modified_time = $modifiedTime;
  }

  /**
   * @return string
   */
  public function getLog()
  {
    return $this->log;
  }

  /**
   * @param string $log
   */
  public function setLog($log)
  {
    $this->log = $log;
  }

  /**
   * @return mixed
   */
  public function getEvent()
  {
    return $this->event;
  }

  /**
   * @param mixed $event
   */
  public function setEvent($event)
  {
    $this->event = $event;
  }

}
