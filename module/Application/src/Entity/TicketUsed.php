<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Platform
 *
 * @ORM\Table(name="ticket_used")
 * @ORM\Entity
 */
class TicketUsed
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
   * @var integer
   *
   * @ORM\Column(name="ticket_id", type="integer", nullable=false)
   */
  private $ticketId;

  /**
   * @var string
   *
   * @ORM\Column(name="sub_event_id", type="string", nullable=true)
   */
  private $subEventId;

  /**
   * @var integer
   *
   * @ORM\Column(name="used", type="integer", nullable=true)
   */
  private $used;

  /**
   * @var integer
   *
   * @ORM\Column(name="used_date", type="integer", nullable=true)
   */
  private $usedDate;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Ticket", inversedBy="ticketUsed")
   * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id")
   */
  protected $ticket;

  /**
   * @return \Application\Entity\Ticket
   */
  public function getTicket()
  {
    return $this->ticket;
  }

  /**
   * @param \Application\Entity\Ticket $ticket
   */
  public function setTicket($ticket)
  {
    $this->ticket = $ticket;
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
   * @return int
   */
  public function getTicketId()
  {
    return $this->ticketId;
  }

  /**
   * @param int $ticketId
   */
  public function setTicketId($ticketId)
  {
    $this->ticketId = $ticketId;
  }

  /**
   * @return string
   */
  public function getSubEventId()
  {
    return $this->subEventId;
  }

  /**
   * @param string $subEventId
   */
  public function setSubEventId($subEventId)
  {
    $this->subEventId = $subEventId;
  }

  /**
   * @return int
   */
  public function getUsed()
  {
    return $this->used;
  }

  /**
   * @param int $used
   */
  public function setUsed($used)
  {
    $this->used = $used;
  }

  /**
   * @return int
   */
  public function getUsedDate()
  {
    return $this->usedDate;
  }

  /**
   * @param int $usedDate
   */
  public function setUsedDate($usedDate)
  {
    $this->usedDate = $usedDate;
  }

}