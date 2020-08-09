<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Группы доступа
 *
 * @ORM\Table(name="event_group")
 * @ORM\Entity
 */
class EventGroup
{
  /**
   * @var int
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Id
   */
  private $id;

  /**
   * @var string
   * @ORM\Column(name="group_id", type="string", nullable=false)
   */
  private $groupId;

  /**
   * @var string
   * @ORM\Column(name="name", type="string", nullable=true)
   */
  private $name;

  /**
   * @var int
   * @ORM\Column(name="event_id", type="integer", nullable=false)
   */
  private $eventId;

  /**
   * @var string
   * @ORM\Column(name="color", type="string", nullable=false)
   */
  private $color;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Event", inversedBy="groups")
   * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
   */
  protected $event;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\EventGroupAllow", mappedBy="eventGroup")
   * @ORM\JoinColumn(name="id", referencedColumnName="event_group_id")
   */
  protected $allow;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\EventGroupPlace", mappedBy="eventGroup")
   * @ORM\JoinColumn(name="id", referencedColumnName="event_group_id")
   */
  protected $place;


  public function __construct()
  {
    $this->allow = new ArrayCollection();
    $this->place = new ArrayCollection();
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getGroupId()
  {
    return $this->groupId;
  }

  /**
   * @param string $groupId
   */
  public function setGroupId($groupId)
  {
    $this->groupId = $groupId;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
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
   * @return string
   */
  public function getColor()
  {
    return $this->color;
  }

  /**
   * @param string $color
   */
  public function setColor($color)
  {
    $this->color = $color;
  }

  /**
   * @return Event
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

  /**
   * @return mixed
   */
  public function getAllow()
  {
    return $this->allow;
  }

  /**
   * @param mixed $allow
   */
  public function setAllow($allow)
  {
    $this->allow->add($allow);
  }

  /**
   * @return mixed
   */
  public function getPlace()
  {
    return $this->place;
  }

  /**
   * @param mixed $place
   */
  public function setPlace($place)
  {
    $this->place->add($place);
  }
}
