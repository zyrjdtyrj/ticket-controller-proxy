<?php

namespace Application\Service;

use Application\Service\IniparManager;
use Application\Service\ServerManager;

class ApiManager
{
  /**
   * @var IniparManager
   */
  private $iniParManager;

  /**
   * @var ServerManager
   */
  private $serverManager;

  /**
   * ApiManager constructor.
   *
   * @param IniparManager $iniParManager
   * @param ServerManager $serverManager
   */
  public function __construct($iniParManager, $serverManager)
  {
    $this->iniParManager  = $iniParManager;
    $this->serverManager  = $serverManager;
  }

  public function exec($method, $params = [], $strict = true)
  {
    $server   = $this->serverManager->getServerAddress($this->iniParManager->get('server'));

    $params['Action'] = $method;

    if (isset($params['Device']))
      $deviceName = $params['Device'];
    else
      $deviceName = '';
    $params['Device'] = $deviceName .'.'. $this->iniParManager->get('proxyId', 'PROXY');

    if (isset($params['Time']))
      $time = $params['Time'];
    else
      $time = date('c');
    $params['Time']   = $time;

    $queryParams = [];
    foreach ($params as $paramKey => $paramVal) {
      if ('Post' !== $paramKey)
        $queryParams[] = $paramKey .'='. urlencode($paramVal);
    }

    $ch = curl_init($server . '?'. implode('&', $queryParams));
    curl_setopt($ch,CURLOPT_TIMEOUT, 10); // задаём максимальное время для выполнения
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (isset($params['Post'])) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params['Post']));
    }

    $request = curl_exec($ch);

    if ('CheckStatus' === $method) {
      // узнаём параметры подключения
      $speedUpload    = curl_getinfo($ch, CURLINFO_SPEED_UPLOAD); // скорость закачки
      $speedDownload  = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD); // скорость загрузки
    }

    curl_close($ch);

    if (false === $request) {
      // перевод режима работы в режим offline
      if (true === $strict) { // если стоит строгая проверка
        if ('offline' !== $this->iniParManager->get('proxyMode')) {
          $this->iniParManager->set('proxyMode', 'offline');

          // фиксируем время перевода в режим OFFLINE
          $this->iniParManager->set('offlineModeTime', time());
        }
      }

      return [
        'Status'  => 'Error',
        'Message' => 'Сервер билетов offline',
      ];
    } else {
      $request = json_decode($request, true);

      if (isset($speedUpload) && isset($speedDownload)) {
        $request['speed_upload']    = $speedUpload;
        $request['speed_download']  = $speedDownload;
      } else {
        $request['speed_upload']    = 0;
        $request['speed_download']  = 0;
      }

      // возвращаем ассоциативный массив, полученный из json
      return $request;
    }
  }
}