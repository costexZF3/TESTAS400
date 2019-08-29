<?php
namespace Purchasing;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Session;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
            
            'createdevprod' => [
                'type'    => Segment::class,
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/createdevprod',
                    'defaults' => [
                        'controller'    => Controller\WishlistController::class,
                        'action'        => 'createdevprod',
                    ],
                ],                
            ],//end: createdevprod
            
            // ProductDevController 
            'productdev' => [
                'type'    => Segment::class,
                 'options' => [
                    // Change this to something specific to your module
                    'route'    => '/productdev[/:action[/:id]]',
                    'constraints' => [
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                             'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ProductdevController::class,
                        'action'        => 'index',
                    ],
                  ],                
            ],//end: newproject in ProductDevController
                        
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
            Controller\WishlistController::class => Controller\Factory\WishlistControllerFactory::class,
            Controller\ProductdevController::class => Controller\Factory\ProductDevControllerFactory::class            
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
                //+purchasing.wl.owner
 
                //Actions reponse to Roles associated to MENUS //             
                ['actions' => ['lostsales', 'index'],   'allow' => '+purchasing.wl.owner'], 

            ], //END: access_filter for LostSaleController           

            //defining access to the WishlistController accions 
            Controller\WishlistController::class => [
                /* Allowing routes access depending on the type of permission assigned to loggin user
                 Give access to "index" actions to everyone with +menu.purchasing +option.purchasing.claims */

                //ACCESS TO ACCTIONS ASSOCIATED WITH MENUS               
                ['actions' => ['index', 'update', 'updatemultiple', 'add', 'create', 'upload', 'createdevprod'], 'allow' => '+purchasing.option.pd.wishlist' ],
                
                /* ACCESS TO ACTIONS ASSOCIATED TO ROLES  */
                /* +purchasing.wl.owner 
                 * +purchasing.ps   
                 * +purchasing.pa
                 * +purchasing.wl.documentator
                 */
                 
                ['actions' => ['add', 'create','update', 'updatemultiple', 'upload','createdevprod'],   'allow' => '+purchasing.wl.owner'],                
                                     

            ], //END: access_filter for WishListController 
            
            //defining Access to ProductDevController
            Controller\ProductdevController::class => [             
                //ACCESS TO ACCTIONS ASSOCIATED WITH MENUS                            
                ['actions' => ['index','addproject', 'viewproject'], 'allow' => '+purchasing.option.pd.wishlist' ],
                ['actions' => ['index','addproject', 'viewproject'], 'allow' => '+purchasing.pa' ],
                ['actions' => ['index','addproject', 'viewproject'], 'allow' => '+purchasing.ps' ],
                ['actions' => ['index','addproject', 'viewproject'], 'allow' => '+purchasing.wl.owner' ],
                                
            ], //END: access_filter for LostSaleController 
        ],
    ], //END: ACCESS FILTERS
    
    //Registeing the services here
    'service_manager' => [
        'factories' => [
            Service\WishListManager::class      => Service\Factory\WishListManagerFactory::class,
            Service\ProductDevManager::class    => Service\Factory\ProductDevManagerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'purchasing' => __DIR__ . '/../view',
        ],
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
