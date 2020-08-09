<?php

namespace Application\Controller;

use Application\Form\ServerForm;
use Application\Service\IniparManager;
use Application\Service\ServerManager;
use User\Service\RbacManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ServerController extends AbstractActionController
{
  /**
   * @var ServerManager
   */
  private $serverManager;

  /**
   * @var RbacManager
   */
  private $rbacManager;

  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * ServerController constructor.
   *
   * @param ServerManager $serverManager
   * @param RbacManager   $rbacManager
   * @param IniparManager $iniParManager
   */
  public function __construct($serverManager, $rbacManager, $iniParManager)
  {
    $this->serverManager  = $serverManager;
    $this->rbacManager    = $rbacManager;
    $this->iniParManager  = $iniParManager;
  }

  /**
   * Заглавная страница управления списокм серверов
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Exception
   */
  public function indexAction()
  {
    if (!$this->rbacManager->isGranted(null, 'server.manage'))
      return $this->redirect()->toRoute('server', ['action' => 'index']);

    // текущий сервер
    $serverId = $this->iniParManager->get('server');

    // список серверов
    $serverList = $this->serverManager->getList();

    return new ViewModel([
      'serverList'  => $serverList,
      'serverId'    => $serverId,
    ]);
  }

  /**
   * Добавление нового сервера
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function addAction()
  {
    if (!$this->rbacManager->isGranted(null, 'server.manage'))
      return $this->redirect()->toRoute('server', ['action' => 'index']);

    $form = new ServerForm('create', $this->entityManager, null);

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      $form->setData($data);

      if ($form->isValid()) {
        $this->serverManager->addServer($data);

        $this->flashMessenger()->addSuccessMessage('Новый сервер добавлен');

        return $this->redirect()->toRoute('server', ['action' => 'index']);
      }
    }

    return new ViewModel([
      'form'  => $form,
    ]);
  }

  /**
   * Редактирование сервера
   *
   * @return \Zend\Http\Response|ViewModel
   * @throws \Exception
   */
  public function editAction()
  {
    if (!$this->rbacManager->isGranted(null, 'server.manage'))
      return $this->redirect()->toRoute('server', ['action' => 'index']);

    $serverId = $this->params()->fromRoute('id', -1);
    if (1 > $serverId) {
      $this->flashMessenger()->addErrorMessage('Такого сервера не существует');
      return $this->redirect()->toRoute('server', ['action' => 'index']);
    }

    $serverItem = $this->serverManager->getServer($serverId);
    if (null === $serverItem) {
      $this->flashMessenger()->addErrorMessage('такого сервера не существует');
      return $this->redirect()->toRoute('server', ['action' => 'index']);
    }

    $form = new ServerForm('edit', $this->entityManager, $serverItem);

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      $form->setData($data);

      if ($form->isValid()) {
        $data['id'] = $serverId;
        $this->serverManager->editServer($data);

        $this->flashMessenger()->addSuccessMessage('Сервер обновлён');

        $this->redirect()->toRoute('server', ['action' => 'index']);
      }
    }

    return new ViewModel([
      'form'  => $form,
    ]);
  }

  /**
   * Удаление сервера
   *
   * @return \Zend\Http\Response
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function deleteAction()
  {
    if (!$this->rbacManager->isGranted(null, 'server.manage'))
      return $this->redirect()->toRoute('server', ['action' => 'index']);

    $serverId = $this->params()->fromRoute('id', -1);
    if (1 > $serverId) {
      $this->flashMessenger()->addErrorMessage('Такого сервера не существует');
      return $this->redirect()->toRoute('server', ['action' => 'index']);
    }

    // проверка, что удаляется не текущий сервер
    if ($serverId != $this->iniParManager->get('server')) {
      $serverItem = $this->serverManager->getServer($serverId);
      if (null === $serverItem) {
        $this->flashMessenger()->addErrorMessage('Такого сервера не существует');
        return $this->redirect()->toRoute('server', ['action' => 'index']);
      }

      $this->serverManager->deleteServer($serverItem);
    } else {
      $this->flashMessenger()->addErrorMessage('Нельзя удалять текущий сервер!');
    }

    return $this->redirect()->toRoute('server', ['action' => 'index']);
  }
}
