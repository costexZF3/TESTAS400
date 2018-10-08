<?php
namespace Warehouse;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [    
    'router' => [
        'routes' => [
            'warehouse' => [
                'type'    => Literal::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/warehouse',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
     // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [      
        'controllers' => [
         Controller\IndexController::class => [
                //Allowing routes access depending on the type of permission assigned to loggin user
                // Give access to "index" actions to everyone with +menu.warehouse  
                               
                //Actions reponse to Roles associated to MENUS 
                ['actions' => ['index'],               'allow' => '+menu.warehouse'],  
            
                //Actions response to Roles associated to OPERATION
                // an user with Entry Level will be permissions to 
                ['actions' => ['watch'],               'allow' => '+warehouse.entry.level'],
                ['actions' => ['export','print',],     'allow' => '+warehouse.regular.level'],
                ['actions' => ['create', 'update', ],  'allow' => '+warehouse.high.level'],
                ['actions' => ['delete', ],           'allow' => '+warehouse.power.level'],
            ], //END: access_filter for ClaimsController          
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'warehouse' => __DIR__ . '/../view',
        ],
    ],
];
