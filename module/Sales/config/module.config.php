<?php
namespace Sales;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [    
    'router' => [
        'routes' => [
            'sales' => [
                'type'    => Literal::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/sales',
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
                // Give access to "index" actions to everyone with +menu.purchasing  
                //+option.purchasing.claims 
                
                //Actions reponse to Roles associated to MENUS 
                ['actions' => ['index'],               'allow' => '+menu.sales'],  
            
                //Actions response to Roles associated to OPERATION
                // an user with Entry Level will be permissions to 
                ['actions' => ['watch'],               'allow' => '+sales.entry.level'],
                ['actions' => ['export','print',],     'allow' => '+sales.regular.level'],
                ['actions' => ['create', 'update', ],  'allow' => '+sales.high.level'],
                ['actions' => ['delete', ],           'allow' => '+sales.power.level'],
            ], //END: access_filter for ClaimsController          
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'sales' => __DIR__ . '/../view',
        ],
    ],
];
