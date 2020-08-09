<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Controller\ApiController;
use Application\Controller\DeviceController;
use Application\Controller\EventController;
use Application\Controller\Factory\ApiControllerFactory;
use Application\Controller\Factory\DeviceControllerFactory;
use Application\Controller\Factory\EventControllerFactory;
use Application\Controller\Factory\ServerControllerFactory;
use Application\Controller\Factory\SettingsControllerFactory;
use Application\Controller\ServerController;
use Application\Controller\SettingsController;
use Application\Service\ApiManager;
use Application\Service\DeviceManager;
use Application\Service\EventManager;
use Application\Service\Factory\ApiManagerFactory;
use Application\Service\Factory\DeviceManagerFactory;
use Application\Service\Factory\EventManagerFactory;
use Application\Service\Factory\HistoryManagerFactory;
use Application\Service\Factory\IniparManagerFactory;
use Application\Service\Factory\LogManagerFactory;
use Application\Service\Factory\NavManagerFactory;
use Application\Service\Factory\RbacAssertionManagerFactory;
use Application\Service\Factory\ServerManagerFactory;
use Application\Service\Factory\TicketManagerFactory;
use Application\Service\HistoryManager;
use Application\Service\IniparManager;
use Application\Service\LogManager;
use Application\Service\NavManager;
use Application\Service\RbacAssertionManager;
use Application\Service\ServerManager;
use Application\Service\TicketManager;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
  'router' => [
    'routes' => [
      'home' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'stat' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/stat',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'stat',
          ],
        ],
      ],
      'metrika' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/metrika',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'metrika',
          ],
        ],
      ],
      'check' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/check',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'check',
          ],
        ],
      ],
      'application' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/application[/:action[/:id]]',
          'constraints' => [
            'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'      => '[0-9]+'
          ],
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'settings'  => [
        'type'    => Segment::class,
        'options' => [
          'route' => '/settings[/:action][/:param][/:eventId]',
          'constraints' => [
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'param' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'event' => '[0-9]*',
          ],
          'defaults'  => [
            'controller'  => Controller\SettingsController::class,
            'action'      => 'index',
          ],
        ],
      ],
      'event' => [
        'type'    => Segment::class,
        'options' => [
          'route'       => '/event[/:action][/:eventId]',
          'constraints' => [
            'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
            'eventId' => '[0-9]*',
          ],
          'defaults'    => [
            'controller'  => Controller\EventController::class,
            'action'      => 'index',
          ],
        ],
      ],
      'device'   => [
        'type'    => Segment::class,
        'options' => [
          'route'       => '/device[/:action][/:id]',
          'constrains'  => [
            'action'  => '[a-zA-Z][a-zA-Z0-9_-]',
            'id'      => '[0-9]*',
          ],
          'defaults'    => [
            'controller'  => Controller\DeviceController::class,
          ],
        ],
      ],
      'server'   => [
        'type'    => Segment::class,
        'options' => [
          'route'       => '/server[/:action][/:id]',
          'constrains'  => [
            'action'  => '[a-zA-Z][a-zA-Z0-9_-]',
            'id'      => '[0-9]*',
          ],
          'defaults'    => [
            'controller'  => Controller\ServerController::class,
            'action'      => 'index',
          ],
        ],
      ],
      'api' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/api/Tickets',
          'defaults' => [
            'controller'  => Controller\ApiController::class,
            'action'      => 'index',
          ],
        ],
      ],
      'apiTest' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/apitest[/:action]',
          'constrains'  => [
            'action'  => '[a-zA-Z][a-zA-Z0-9_-]',
          ],
          'defaults' => [
            'controller'  => Controller\ApiController::class,
            'action'      => 'test',
          ],
        ],
      ],
      'apiLog' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/apilog',
          'defaults' => [
            'controller'  => Controller\ApiController::class,
            'action'      => 'log',
          ],
        ],
      ],
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
      SettingsController::class         => SettingsControllerFactory::class,
      DeviceController::class           => DeviceControllerFactory::class,
      ApiController::class              => ApiControllerFactory::class,
      EventController::class            => EventControllerFactory::class,
      ServerController::class           => ServerControllerFactory::class,
    ],
  ],
  // The 'access_filter' key is used by the User module to restrict or permit
  // access to certain controller actions for unauthorized visitors.
  'access_filter' => [
    'options' => [
      // The access filter can work in 'restrictive' (recommended) or 'permissive'
      // mode. In restrictive mode all controller actions must be explicitly listed
      // under the 'access_filter' config key, and access is denied to any not listed
      // action for not logged in users. In permissive mode, if an action is not listed
      // under the 'access_filter' key, access to it is permitted to anyone (even for
      // not logged in users. Restrictive mode is more secure and recommended to use.
      'mode' => 'restrictive'
    ],
    'controllers' => [
      Controller\IndexController::class => [
        // Allow anyone to visit "index" and "about" actions
        ['actions' => ['index', 'stat', 'check'], 'allow' => '*'],
        // Allow authorized users to visit "settings" action
        ['actions' => ['profile', 'list', 'summary', 'view', 'set', 'metrika'], 'allow' => '@'],
      ],
      SettingsController::class => [
        ['actions' => ['cron'], 'allow' => '*'],
        ['actions' => ['index', 'edit', 'sync', 'synchronization'], 'allow' => '@'],
      ],
      DeviceController::class => [
        ['actions' => ['index', 'add', 'edit', 'delete', 'history'], 'allow' => '+device.view'],
      ],
      ApiController::class => [
        ['actions' => ['index', 'log'], 'allow' => '*'],
        ['actions' => ['test', 'exec'], 'allow' => '+api.test']
      ],
      EventController::class => [
        ['actions' => ['index'], 'allow' => '+event.view'],
        ['actions' => ['edit'], 'allow' => '+event.manage'],
      ],
      ServerController::class => [
        ['actions' => ['index', 'add', 'edit', 'delete'], 'allow' => '+server.manage']
      ],
    ]
  ],
  // This key stores configuration for RBAC manager.
  'rbac_manager' => [
    'assertions' => [Service\RbacAssertionManager::class],
  ],
  'service_manager' => [
    'factories' => [
      NavManager::class           => NavManagerFactory::class,
      RbacAssertionManager::class => RbacAssertionManagerFactory::class,
      IniparManager::class        => IniparManagerFactory::class,
      TicketManager::class        => TicketManagerFactory::class,
      HistoryManager::class       => HistoryManagerFactory::class,
      ApiManager::class           => ApiManagerFactory::class,
      LogManager::class           => LogManagerFactory::class,
      EventManager::class         => EventManagerFactory::class,
      DeviceManager::class        => DeviceManagerFactory::class,
      ServerManager::class        => ServerManagerFactory::class,
    ],
  ],
  'view_helpers' => [
    'factories' => [
      View\Helper\Menu::class         => View\Helper\Factory\MenuFactory::class,
      View\Helper\Breadcrumbs::class  => InvokableFactory::class,
    ],
    'aliases' => [
      'mainMenu'        => View\Helper\Menu::class,
      'pageBreadcrumbs' => View\Helper\Breadcrumbs::class,
    ],
  ],
  'view_manager' => [
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => [
      'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
      'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
      'error/404' => __DIR__ . '/../view/error/404.phtml',
      'error/index' => __DIR__ . '/../view/error/index.phtml',
    ],
    'template_path_stack' => [
      __DIR__ . '/../view',
    ],
    'strategies' => [
      'ViewJsonStrategy',
    ],
  ],
  // The following key allows to define custom styling for FlashMessenger view helper.
  'view_helper_config' => [
    'flashmessenger' => [
      'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert">×</button></button><ul><li>',
      'message_close_string' => '</li></ul></div>',
      'message_separator_string' => '</li><li>'
    ]
  ],
  'doctrine' => [
    'driver' => [
      __NAMESPACE__ . '_driver' => [
        'class' => AnnotationDriver::class,
        'cache' => 'array',
        'paths' => [__DIR__ . '/../src/Entity']
      ],
      'orm_default' => [
        'drivers' => [
          __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
        ]
      ]
    ]
  ],
];
