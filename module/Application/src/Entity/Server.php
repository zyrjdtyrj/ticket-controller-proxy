<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Server
 *
 * @package Application\Entity
 * @ORM\Table(name="server")
 * @ORM\Entity
 */
class Server
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
   * @ORM\Column(name="name", type="string", nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="address", type="string", nullable=false)
   */
  private $address;

  /**
   * @ORM\OneToMany(targetEntity="\Application\Entity\Event", mappedBy="server")
   * @ORM\JoinColumn(name="id", referencedColumnName="server_id")
   */
  protected $event;

  public function __construct()
  {
    $this->event = new ArrayCollection();
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
   * @return string
   */
  public function getAddress()
  {
    return $this->address;
  }

  /**
   * @param string $address
   */
  public function setAddress($address)
  {
    $this->address = $address;
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
    $this->event->add($event);
  }
}
