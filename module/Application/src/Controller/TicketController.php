<?php

namespace Application\Controller;

use Application\Service\IniparManager;
use Application\Service\TicketManager;
use Doctrine\ORM\EntityManager;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;

class TicketController extends AbstractActionController
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * @var RbacManager
   */
  private $rbacManager;

  /**
   * @var TicketManager
   */
  private $ticketManager;

  /**
   * TicketController constructor.
   *
   * @param $entityManager
   * @param $iniParManager
   * @param $rbacManager
   * @param $ticketManager
   */
  public function __construct($entityManager, $iniParManager, $rbacManager, $ticketManager)
  {
    $this->entityManager  = $entityManager;
    $this->iniParManager  = $iniParManager;
    $this->rbacManager    = $rbacManager;
    $this->ticketManager  = $ticketManager;
  }

  public function indexAction()
  {

  }
}
