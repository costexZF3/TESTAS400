<?php
namespace Management;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [    
    'router' => [
        'routes' => [
            'management' => [
                'type'    => Literal::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/management',
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
                ['actions' => ['index'],               'allow' => '+menu.management'],  
            
                //Actions response to Roles associated to OPERATION
                // an user with Entry Level will be permissions to 
                ['actions' => ['watch'],               'allow' => '+management.entry.level'],
                ['actions' => ['export','print',],     'allow' => '+management.regular.level'],
                ['actions' => ['create', 'update', ],  'allow' => '+management.high.level'],
                ['actions' => ['delete', ],           'allow' => '+management.power.level'],
            ], //END: access_filter for ClaimsController          
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'management' => __DIR__ . '/../view',
        ],
    ],
];
