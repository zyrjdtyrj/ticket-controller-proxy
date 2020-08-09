<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Уровень доступа в группу
 *
 * @ORM\Table(name="event_group_allow")
 * @ORM\Entity
 * @package Application\Entity
 */
class EventGroupAllow
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
   * @var int
   *
   * @ORM\Column(name="event_group_id", type="integer", nullable=false)
   */
  private $eventGroupId;

  /**
   * @var string
   *
   * @ORM\Column(name="allow", type="string", nullable=false)
   */
  private $allow;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\EventGroup", inversedBy="eventGroupAllow")
   * @ORM\JoinColumn(name="event_group_id", referencedColumnName="id")
   */
  protected $eventGroup;

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
   * @return int
   */
  public function getEventGroupId()
  {
    return $this->eventGroupId;
  }

  /**
   * @param int $eventGroupId
   */
  public function setEventGroupId($eventGroupId)
  {
    $this->eventGroupId = $eventGroupId;
  }

  /**
   * @return string
   */
  public function getAllow()
  {
    return $this->allow;
  }

  /**
   * @param string $allow
   */
  public function setAllow($allow)
  {
    $this->allow = $allow;
  }

  /**
   * @return mixed
   */
  public function getEventGroup()
  {
    return $this->eventGroup;
  }

  /**
   * @param mixed $eventGroup
   */
  public function setEventGroup($eventGroup)
  {
    $this->eventGroup = $eventGroup;
  }
}