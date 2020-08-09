<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Platform
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 */
class Event
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\GeneratedValue
   * @ORM\Id
   */
  private $id;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=256, nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="date_begin", type="integer")
   */
  private $dateBegin;

  /**
   * @var string
   *
   * @ORM\Column(name="date_end", type="integer")
   */
  private $dateEnd;

  /**
   * @var string
   *
   * @ORM\Column(name="sync_time", type="integer", nullable=false)
   */
  private $syncTime;

  /**
   * @var int
   *
   * @ORM\Column(name="event_id", type="integer", nullable=false)
   */
  private $eventId;

  /**
   * @var string
   *
   * @ORM\Column(name="server_id", type="integer", nullable=false)
   */
  private $serverId;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\EventGroup", mappedBy="event")
   * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
   */
  protected $groups;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\Device", mappedBy="event")
   * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
   */
  protected $devices;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\Ticket", mappedBy="event")
   * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
   */
  protected $tickets;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Server", inversedBy="servers")
   * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
   */
  protected $server;

  public function __construct()
  {
    $this->groups   = new ArrayCollection();
    $this->devices  = new ArrayCollection();
    $this->tickets  = new ArrayCollection();
  }

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getDateBegin()
  {
    return $this->dateBegin;
  }

  public function setDateBegin($dateBegin)
  {
    $this->dateBegin = $dateBegin;
  }

  public function getDateEnd()
  {
    return $this->dateEnd;
  }

  public function setDateEnd($dateEnd)
  {
    $this->dateEnd = $dateEnd;
  }

  /**
   * @return string
   */
  public function getSyncTime()
  {
    return $this->syncTime;
  }

  /**
   * @param string $syncTime
   */
  public function setSyncTime($syncTime)
  {
    $this->syncTime = $syncTime;
  }

  /**
   * @return int
   */
  public function getEventId()
  {
    return $this->eventId;
  }

  /**
   * @param int $eventId
   */
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }

  /**
   * @return int
   */
  public function getServerId()
  {
    return $this->serverId;
  }

  /**
   * @param string $serverId
   */
  public function setServerId($serverId)
  {
    $this->serverId = $serverId;
  }

  /**
   * @return mixed
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * @param mixed $group
   */
  public function setGroups($group)
  {
    $this->groups->add($group);
  }

  /**
   * Удаление группы
   *
   * @param $key
   */
  public function delGroups($key)
  {
    $this->groups->remove($key);
  }

  /**
   * Очистка всех групп
   */
  public function delAllGroups()
  {
    $this->groups->clear();
  }

  /**
   * @return mixed
   */
  public function getDevices()
  {
    return $this->devices;
  }

  /**
   * @param mixed $devices
   */
  public function setDevices($devices)
  {
    $this->devices->add($devices);
  }

  public function delDevices($key)
  {
    $this->devices->remove($key);
  }

  public function delAllDevices()
  {
    $this->devices->clear();
  }

  /**
   * @return mixed
   */
  public function getTickets()
  {
    return $this->tickets;
  }

  /**
   * @return mixed
   */
  public function getServer()
  {
    return $this->server;
  }

  /**
   * @param mixed $server
   */
  public function setServer($server)
  {
    $this->server = $server;
  }
}