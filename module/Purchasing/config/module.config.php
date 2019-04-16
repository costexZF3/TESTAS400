<?php
namespace Purchasing;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Session;

return [   
    'router' => [
        'routes' => [
            'claims' => [
                'type'    => Literal::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/claims',
                    'defaults' => [
                        'controller'    => Controller\ClaimsController::class,
                        'action'        => 'index',
                    ],
                ],                
            ],//end: claims route
            /*********************************  LOST SALE ROUTESSS **********************************************/
            'lostsales' => [
                'type'    => Literal::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/lostsale',
                    'defaults' => [
                        'controller'    => Controller\LostsaleController::class,
                        'action'        => 'index',
                    ],
                ],                
            ],//end: lostsale route
            
                        
            'wishlist' => [
                'type'    => Segment::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/wishlist[/:action[/:id]]',
                    'constraints' => [
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\WishlistController::class,
                        'action'        => 'index',
                    ],
                ],
            ],//end: wishlist route
        ],
    ],   
    /* REGISTERING CONTROLES */
    'controllers' => [
        'factories' => [
            Controller\ClaimsController::class => Controller\Factory\ClaimsControllerFactory::class,
            Controller\LostsaleController::class => Controller\Factory\LostsaleControllerFactory::class,
            Controller\WishlistController::class => Controller\Factory\WishlistControllerFactory::class
            
        ],
    ],
    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [      
        'controllers' => [
            Controller\ClaimsController::class => [
                    //Allowing routes access depending on the type of permission assigned to loggin user
                    // Give access to "index" actions to everyone with +menu.purchasing  
                    //+option.purchasing.claims 

                    //Actions reponse to Roles associated to MENUS 
                    ['actions' => ['index'],               'allow' => '+menu.purchasing'],  //every body logged in with this permission
                    ['actions' => ['claims'],              'allow' => '+option.purchasing.claims'],                 

                    //Actions response to Roles associated to OPERATION
                    // an user with Entry Level will be permissions to 
                    ['actions' => ['watch'],               'allow' => '+purchasing.entry.level'],
                    ['actions' => ['export','print',],     'allow' => '+purchasing.regular.level'],
                    ['actions' => ['create', 'update', ],  'allow' => '+purchasing.high.level'],
                    ['actions' => ['delete', ],           'allow' => '+purchasing.power.level'],
                ], //END: access_filter for ClaimsController       

            Controller\LostsaleController::class => [
                //Allowing routes access depending on the type of permission assigned to loggin user
                // Give access to "index" actions to everyone with +menu.purchasing  
                //+option.purchasing.claims 

                //Actions reponse to Roles associated to MENUS 
                ['actions' => ['index'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['lostsales'],      'allow' => '+option.purchasing.claims'],                 

            ], //END: access_filter for LostSaleController           

            Controller\WishlistController::class => [
                //Allowing routes access depending on the type of permission assigned to loggin user
                // Give access to "index" actions to everyone with +menu.purchasing  
                //+option.purchasing.claims 

                //Actions reponse to Roles associated to MENUS 
                ['actions' => ['index'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['add'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['create'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['update'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['delete'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['insert'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['upload'],          'allow' => '+menu.purchasing'],  //every body logged in with this permission
                ['actions' => ['wishlist'],       'allow' => '+menu.purchasing'],                 

            ], //END: access_filter for LostSaleController    
            
        ],
    ], //END: ACCESS FILTERS
    
    'service_manager' => [
        'factories' => [
            Service\WishListManager::class    => Service\Factory\WishListManagerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'purchasing' => __DIR__ . '/../view',
        ],
    ],
];
