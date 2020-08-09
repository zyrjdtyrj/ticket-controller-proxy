<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Platform
 *
 * @ORM\Table(name="ini_par")
 * @ORM\Entity
 */
class Inipar
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
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=128, nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="value", type="string", length=250, nullable=false)
   */
  private $value;

  /**
   * @var string
   *
   * @ORM\Column(name="user", type="string", length=256, nullable=true)
   */
  private $user;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\IniparHistory", mappedBy="inipar")
   * @ORM\JoinColumn(name="id", referencedColumnName="par_id")
   */
  protected $history;

  public function __construct()
  {
    $this->history = new ArrayCollection();
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

  public function getValue()
  {
    return $this->value;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param string $user
   */
  public function setUser($user)
  {
    $this->user = $user;
  }

  /**
   * @return mixed
   */
  public function getHistory()
  {
    return $this->history;
  }

  /**
   * @param mixed $history
   */
  public function setHistory($history)
  {
    $this->history->add($history);
  }

}
