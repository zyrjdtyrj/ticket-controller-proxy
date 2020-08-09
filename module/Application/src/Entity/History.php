<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * История
 *
 * @ORM\Table(name="history")
 * @ORM\Entity
 */
class History
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
   * @ORM\Column(name="date", type="integer", nullable=false)
   */
  private $date;

  /**
   * @ORM\Column(name="ticket_id", type="integer", nullable=false)
   */
  private $ticketId;

  /**
   * @ORM\Column(name="device", type="string", nullable=true)
   */
  private $device;

  /**
   * @ORM\Column(name="status", type="string", nullable=false)
   */
  private $status;

  /**
   * @ORM\Column(name="ip", type="string", nullable=true)
   */
  private $ip;

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @param mixed $date
   */
  public function setDate($date)
  {
    $this->date = $date;
  }

  /**
   * @return string
   */
  public function getTicketId()
  {
    return $this->ticketId;
  }

  /**
   * @param string $ticketId
   */
  public function setTicketId($ticketId)
  {
    $this->ticketId = $ticketId;
  }

  /**
   * @return mixed
   */
  public function getDevice()
  {
    return $this->device;
  }

  /**
   * @param mixed $device
   */
  public function setDevice($device)
  {
    $this->device = $device;
  }

  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * @param string $status
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }

  /**
   * @return mixed
   */
  public function getIp()
  {
    return $this->ip;
  }

  /**
   * @param mixed $ip
   */
  public function setIp($ip)
  {
    $this->ip = $ip;
  }

}
