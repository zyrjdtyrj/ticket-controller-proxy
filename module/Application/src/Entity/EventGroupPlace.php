<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Места рассадки группы
 *
 * @ORM\Table(name="event_group_place")
 * @ORM\Entity
 * @package Application\Entity
 */
class EventGroupPlace
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
   * @var int
   *
   * @ORM\Column(name="s", type="integer", nullable=false)
   */
  private $s;

  /**
   * @var int
   *
   * @ORM\Column(name="r", type="integer", nullable=false)
   */
  private $r;

  /**
   * @var int
   *
   * @ORM\Column(name="f", type="integer", nullable=false)
   */
  private $f;

  /**
   * @var int
   *
   * @ORM\Column(name="t", type="integer", nullable=false)
   */
  private $t;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\EventGroup", inversedBy="eventGroupPlace")
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
   * @return int
   */
  public function getS()
  {
    return $this->s;
  }

  /**
   * @param int $s
   */
  public function setS($s)
  {
    $this->s = $s;
  }

  /**
   * @return int
   */
  public function getR()
  {
    return $this->r;
  }

  /**
   * @param int $r
   */
  public function setR($r)
  {
    $this->r = $r;
  }

  /**
   * @return int
   */
  public function getF()
  {
    return $this->f;
  }

  /**
   * @param int $f
   */
  public function setF($f)
  {
    $this->f = $f;
  }

  /**
   * @return int
   */
  public function getT()
  {
    return $this->t;
  }

  /**
   * @param int $t
   */
  public function setT($t)
  {
    $this->t = $t;
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