<?php

namespace Application\Service;

use Application\Entity\Server;
use Doctrine\ORM\EntityManager;

class ServerManager
{
  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * ServerManager constructor.
   *
   * @param EntityManager $entityManager
   */
  public function __construct($entityManager)
  {
    $this->entityManager = $entityManager;
  }

  /**
   * Получение списка серверов
   *
   * @return array|object[]
   */
  public function getList()
  {
    return $this->entityManager->getRepository(Server::class)->findAll();
  }

  /**
   *  Получение короткого списка серверов
   *
   * @return array
   */
  public function getShortList()
  {
    $servers = [];

    $serverList = $this->getList();

    /**
     * @var Server $server
     */
    foreach ($serverList as $server) {
      $servers[$server->getId()] = [
        'name'    => $server->getName(),
        'address' => $server->getAddress(),
      ];
    }

    return $servers;
  }

  /**
   * Получение адреса сервера по его идентификатору
   *
   * @param $serverId
   *
   * @return string
   * @throws \Exception
   */
  public function getServerAddress($serverId) {
    /**
     * @var Server $server
     */
    $server = $this->getServer($serverId);

    return $server->getAddress();
  }

  /**
   * Получение объекта сущьности СЕРВЕР
   *
   * @param $serverId
   *
   * @return Server
   * @throws \Exception
   */
  public function getServer($serverId) {
    if (null === $serverId)
      throw new \Exception('serverId не может быть пустым');

    /**
     * @var Server $server
     */
    $server = $this->entityManager->getRepository(Server::class)->findOneById($serverId);

    if (null === $server)
      throw new \Exception('Сервер не найден');

    return $server;
  }

  /**
   * Добавление сервера
   *
   * @param $server
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function addServer($server)
  {
    $item = new Server();
    $item->setName($server['name']);
    $item->setAddress($server['address']);

    $this->entityManager->persist($item);
    $this->entityManager->flush();

    return true;
  }

  /**
   * Редактирование сервера
   *
   * @param $server
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function editServer($server)
  {
    $item = $this->getServer($server['id']);
    $item->setName($server['name']);
    $item->setAddress($server['address']);

    $this->entityManager->persist($item);
    $this->entityManager->flush();

    return true;
  }

  /**
   * Удаление сервера
   *
   * @param $server
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function deleteServer($server)
  {
    $this->entityManager->remove($server);
    $this->entityManager->flush();

    return true;
  }
}
