<?php

namespace Application\Service;

use User\Service\RbacManager;
use Zend\View\Helper\Url;
use Zend\Authentication\AuthenticationService;

/**
 * This service is responsible for determining which items should be in the main menu.
 * The items may be different depending on whether the user is authenticated or not.
 */
class NavManager
{
  /**
   * Auth service.
   * @var AuthenticationService
   */
  private $authService;

  /**
   * Url view helper.
   * @var Url
   */
  private $urlHelper;

  /**
   * RBAC manager.
   * @var RbacManager $rbacManager
   */
  private $rbacManager;

  /**
   * NavManager constructor.
   *
   * @param $authService
   * @param $urlHelper
   * @param $rbacManager
   */
  public function __construct($authService, $urlHelper, $rbacManager)
  {
    $this->authService = $authService;
    $this->urlHelper = $urlHelper;
    $this->rbacManager = $rbacManager;
  }

  /**
   * This method returns menu items depending on whether user has logged in or not.
   * @TODO: переписать весь этот метод, чтобы меню бралось из БД. С учётом доступа к пункту меню, сортировки и расположения.
   */
  public function getMenuItems()
  {
    /**
     * @var Url $url
     */
    $url = $this->urlHelper;
    $items = [];

    $items[] = [
      'id' => 'home',
      'label' => 'Главная',
      'link' => $url('home')
    ];

    // Display "Login" menu item for not authorized user only. On the other hand,
    // display "Admin" and "Logout" menu items only for authorized users.
    if (!$this->authService->hasIdentity()) {
      $items[] = [
        'id' => 'login',
        'label' => 'Вход',
        'link' => $url('login'),
        'float' => 'right'
      ];
    } else {
      if ($this->rbacManager->isGranted(null, 'metrika.view')) {
        $items[] = [
          'id'    => 'metrika',
          'label' => 'Метрики',
          'link'  => $url('metrika'),
        ];
      }

      if ($this->rbacManager->isGranted(null, 'event.view')) {
        $items[] = [
          'id'    => 'event',
          'label' => 'События',
          'link'  => $url('event')
        ];
      }

      if ($this->rbacManager->isGranted(null, 'device.view')) {
        $items[] = [
          'id'    => 'device',
          'label' => 'Устройства',
          'link'  => $url('device', ['action' => 'index']),
        ];
      }

      if ($this->rbacManager->isGranted(null, 'server.manage')) {
        $items[] = [
          'id' => 'server',
          'label' => 'Серверы',
          'link' => $url('server')
        ];
      }

      if ($this->rbacManager->isGranted(null, 'settings.manage')) {
        $items[] = [
          'id' => 'settings',
          'label' => 'Настройки',
          'link' => $url('settings')
        ];
      }

      if ($this->rbacManager->isGranted(null, 'api.test')) {
        $items[] = [
          'id'        => 'api',
          'label'     => 'API',
          'dropdown'  => [
            [
              'id'    => 'apitest',
              'label' => 'Тестирование',
              'link'  => $url('apiTest'),
            ],
            [
              'id'    => 'apilog',
              'label' => 'Журнал',
              'link'  => $url('apiLog'),
            ]
          ]
        ];
      }

      // Determine which items must be displayed in Admin dropdown.
      $adminDropdownItems = [];

      /*
      if ($this->rbacManager->isGranted(null, 'settings.manage')) {
        $adminDropdownItems[] = [
          'id' => 'settings',
          'label' => 'Настройки',
          'link' => $url('settings')
        ];
      }
      */

      if ($this->rbacManager->isGranted(null, 'user.manage')) {
        $adminDropdownItems[] = [
          'id' => 'users',
          'label' => 'Пользователи',
          'link' => $url('users')
        ];

        /*
        $adminDropdownItems[] = [
          'id' => 'online',
          'label' => 'В сети',
          'link' => $url('online')
        ];
        */
      }

      if ($this->rbacManager->isGranted(null, 'permission.manage')) {
        $adminDropdownItems[] = [
          'id'    => 'permissions',
          'label' => 'Разрешения',
          'link'  => $url('permissions')
        ];
      }

      if ($this->rbacManager->isGranted(null, 'role.manage')) {
        $adminDropdownItems[] = [
          'id'    => 'roles',
          'label' => 'Роли',
          'link'  => $url('roles')
        ];
      }

      if (count($adminDropdownItems) != 0) {
        $items[] = [
          'id'        => 'admin',
          'label'     => 'Администрирование',
          'dropdown'  => $adminDropdownItems
        ];
      }

      $items[] = [
        'id' => 'logout',
        'label' => $this->authService->getIdentity(),
        'float' => 'right',
        'dropdown' => [
          [
            'id' => 'profile',
            'label' => 'Профиль',
            'link' => $url('application', ['action' => 'profile'])
          ],
          [
            'id' => 'logout',
            'label' => 'Выход',
            'link' => $url('logout')
          ],
        ]
      ];
    }

    return $items;
  }
}


