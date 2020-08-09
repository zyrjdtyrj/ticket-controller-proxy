<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Device
 *
 * @ORM\Table(name="device")
 * @ORM\Entity
 */
class Device
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   * @var int
   *
   * @ORM\Column(name="event_id", type="integer", nullable=false)
   */
  private $eventId;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="groups", type="string", nullable=true)
   */
  private $groups;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Event", inversedBy="devices")
   * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
   */
  protected $event;

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return int
   */
  public function getEventId()
  {
    return $this->eventId;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @param int $eventId
   */
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @param string $groups
   */
  public function setGroups($groups)
  {
    $this->groups = $groups;
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
