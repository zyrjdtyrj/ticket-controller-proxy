<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Статистика
 *
 * @package Application\Entity
 * @ORM\Table(name="stat")
 * @ORM\Entity
 */
class Stat
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
   * @var float
   * @ORM\Column(name="request_count", type="float", nullable=true)
   */
  private $requestCount;

  /**
   * @var float
   * @ORM\Column(name="speed_upload", type="float", nullable=true)
   */
  private $speedUpload;

  /**
   * @var float
   * @ORM\Column(name="speed_download", type="float", nullable=true)
   */
  private $speedDownload;

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
   * @return float
   */
  public function getRequestCount()
  {
    return $this->requestCount;
  }

  /**
   * @param float $requestCount
   */
  public function setRequestCount($requestCount)
  {
    $this->requestCount = $requestCount;
  }

  /**
   * @return float
   */
  public function getSpeedUpload()
  {
    return $this->speedUpload;
  }

  /**
   * @param float $speedUpload
   */
  public function setSpeedUpload($speedUpload)
  {
    $this->speedUpload = $speedUpload;
  }

  /**
   * @return float
   */
  public function getSpeedDownload()
  {
    return $this->speedDownload;
  }

  /**
   * @param float $speedDownload
   */
  public function setSpeedDownload($speedDownload)
  {
    $this->speedDownload = $speedDownload;
  }
}
