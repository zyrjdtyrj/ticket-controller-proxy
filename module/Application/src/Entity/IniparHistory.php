<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class IniparHistory
 *
 * @package Application\Entity
 * @ORM\Table(name="ini_par_history")
 * @ORM\Entity
 */
class IniparHistory
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
   * @ORM\Column(name="user_id", type="integer", nullable=false)
   */
  private $user_id;

  /**
   * @var int
   * @ORM\Column(name="date", type="integer", nullable=false)
   */
  private $date;

  /**
   * @var int
   * @ORM\Column(name="par_id", type="integer", nullable=false)
   */
  private $par_id;

  /**
   * @var string
   * @ORM\Column(name="value", type="string", nullable=false)
   */
  private $value;

  /**
   * @var string
   * @ORM\Column(name="ip", type="string", nullable=false)
   */
  private $ip;

  /**
   * @ORM\ManyToOne(targetEntity="\Application\Entity\Inipar", inversedBy="history")
   * @ORM\JoinColumn(name="par_id", referencedColumnName="id")
   */
  protected $inipar;

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
   * @return int
   */
  public function getUserId()
  {
    return $this->user_id;
  }

  /**
   * @param int $user_id
   */
  public function setUserId($user_id)
  {
    $this->user_id = $user_id;
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
   * @return int
   */
  public function getParId()
  {
    return $this->par_id;
  }

  /**
   * @param int $par_id
   */
  public function setParId($par_id)
  {
    $this->par_id = $par_id;
  }

  /**
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @param string $value
   */
  public function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * @return mixed
   */
  public function getInipar()
  {
    return $this->inipar;
  }

  /**
   * @param mixed $inipar
   */
  public function setInipar($inipar)
  {
    $this->inipar = $inipar;
  }

}
