<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Журнал использования API
 *
 * @ORM\Table(name="log")
 * @ORM\Entity
 */
class Log
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
   * @ORM\Column(name="date", type="integer", nullable=false)
   */
  private $date;

  /**
   * @var string
   *
   * @ORM\Column(name="device", type="string", nullable=false)
   */
  private $device;

  /**
   * @var string
   *
   * @ORM\Column(name="ip", type="string", nullable=false)
   */
  private $ip;

  /**
   * @var string
   *
   * @ORM\Column(name="method", type="string", nullable=false)
   */
  private $method;

  /**
   * @var string
   *
   * @ORM\Column(name="params", type="string", nullable=false)
   */
  private $params;

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
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @param int $date
   */
  public function setDate($date)
  {
    $this->date = $date;
  }

  /**
   * @return string
   */
  public function getDevice()
  {
    return $this->device;
  }

  /**
   * @param string $device
   */
  public function setDevice($device)
  {
    $this->device = $device;
  }

  /**
   * @return string
   */
  public function getIp()
  {
    return $this->ip;
  }

  /**
   * @param string $ip
   */
  public function setIp($ip)
  {
    $this->ip = $ip;
  }

  /**
   * @return string
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * @param string $method
   */
  public function setMethod($method)
  {
    $this->method = $method;
  }

  /**
   * @return string
   */
  public function getParams()
  {
    return $this->params;
  }

  /**
   * @param string $params
   */
  public function setParams($params)
  {
    $this->params = $params;
  }
}
