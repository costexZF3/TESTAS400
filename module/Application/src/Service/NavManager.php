<?php
namespace Application\Service;

/**
 * This service is responsible for determining which items should be in the main menu.
 * The items may be different depending on whether the user is authenticated or not.
 */
class NavManager
{
    /**
     * Auth service.
     * @var Zend\Authentication\Authentication
     */
    private $authService;
    
    /**
     * Url view helper.
     * @var Zend\View\Helper\Url
     */
    private $urlHelper;
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;
    
    
    /**
     * Entity manager 
     * @var \DoctrineModule\Service\Authentication\AdapterFactory; 
     */    
    private $entityManager;
    
    /**
     * Constructs the service.
     */
    public function __construct($authService, $urlHelper, $rbacManager, $entityManager) 
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->rbacManager = $rbacManager;
        //add the options access for this user
         $this->entityManager = $entityManager;
    }
    
    /**
     * this function return an array with the items will be shown 
     */
    private function addMenuOptions(string $menuPermission )
    {
        $url = $this->urlHelper;
        //load the options associate to the user with the $menuPermission 
        //doit....
        
        $options= []; //init $optionss       
        if ($this->rbacManager->isGranted(null, $menuPermission)) {
            $options[] = [  
                         'id' => 'claims',
                         'label' => 'Claims',
                         'link' => $url('claims',['action'=>'index'])
                     ];
              
            /* Menu Product Developments */
            $options[] = [
                        'id' => 'productDevelopment',
                        'label' => 'Product Developments',
                        'link' => $url('pagebuilding')
                    ];
            /* Menu supplies */ 
            $options[] = [
                        'id' => 'supplies',
                        'label' => 'Supplies',
                        'link' => $url('pagebuilding')
                    ];
                
            /* Comments, New Supplies/Others */        
            $options[] = [
                        'id' => 'comments',
                        'label' => 'Comments, New Supplies/Others',
                        'link' => $url('pagebuilding')
                    ];
            /* Sales Back Orders */        
            $options[] = [
                       'id' => 'salesbackorders',
                       'label' => 'Sales Backorders',
                       'link' => $url('pagebuilding')
                   ];
                
                  /* Follow Backorders */        
            $options[] = [
                        'id' => 'followbackorders',
                        'label' => 'Follow Backorders',
                        'link' => $url('pagebuilding')
                    ];
                
            /* Part/Vendor Comments */        
            $options[] = [
                      'id' => 'partvendorcomments',
                      'label' => 'Part/Vendor Comments',
                      'link' => $url('pagebuilding')
                  ];
                
                  /* Purchasing Quote */        
            $options[] = [
                        'id' => 'partvendorcomments',
                        'label' => 'Purchasing Quote',
                        'link' => $url('pagebuilding')
                    ];

             /* Suspended Parts*/        
            $options[] = [
                        'id' => 'suspendedparts',
                        'label' => 'Suspended Parts',
                        'link' => $url('pagebuilding')
                    ];

             /* Upload OEM Pictures */        
            $options[] = [
                        'id' => 'uploadoempictures',
                        'label' => 'Upload OEM Pictures',
                        'link' => $url('pagebuilding')
                    ];

             /* Email Vendors */        
            $options[] = [
                        'id' => 'emailvendors',
                        'label' => 'Email Vendors',
                        'link' => $url('pagebuilding')
                    ];

             /* Vendors Price List */        
            $options[] = [
                        'id' => 'vendorspricelist',
                        'label' => 'Vendors Price List',
                        'link' => $url('pagebuilding')
                    ];

             /* Print labels (Vendors)*/        
            $options[] = [
                        'id' => 'printlabels',
                        'label' => 'Print Labels(Vendors)',
                        'link' => $url('pagebuilding')
                    ];

             /* Change Pur. Agent / Person in charge  */        
            $options[] = [
                        'id' => 'changeagentpersonincharge',
                        'label' => 'Change Pur. Agent/Person in charge',
                        'link' => $url('pagebuilding')
                    ];
             /* Reports */        
            $options[] = [
                        'id' => 'reports',
                        'label' => 'Reports',
                        'link' => $url('pagebuilding')
                    ];
                   
        } //end if access() to $permission 
        
        return $options;
    } //End: method addMenuOptions
    
    
    /**
     * This method returns menu items depending on whether user has logged in or not.
     */
    public function getMenuItems() 
    {
        $url = $this->urlHelper;
        //This variable ARRAY $items[] will contain all menu items that will be shown ******
        $items = [];
        
        /*
         *  
         *  Menu: Home will be shown always, so you don't need to verify access
         *  indexs:
         *      - id   : it identifies each one of the menu items
         *      - label: This NAME matches with how users will watch the menu option  on the UI
         *      - link : It identifies the route when the user click it on.
        */
        
        //Home Menu
        $items[] = [
            'id' => 'home',
            'label' => 'Home',
            'link'  => $url('home')
        ];        
               
        // Display "Login" menu item for not authorized user only. On the other hand,
        // display "Admin" and "Logout" menu items only for authorized users.       
        
        if (!$this->authService->hasIdentity()) {
            $items[] = [
                'id' => 'login',
                'label' => 'Sign in',
                'link'  => $url('login'),
                'float' => 'right'
            ];
        } else {
            
            /**
             * CREATE DYNAMIC MENUS WITH THE OPTIONS GIVEN OR ASSIGNED EACH MENU 
             * Management, Marketing, MIS, Purchasing, Quality Control, Manufacturing, Sales Shipping
             *  Recesiving, Warehouse, maintenance 
             */       
                        
            // Determine which items must be displayed in Purchasing           *
           $purchasingMenuOptions = $this->addMenuOptions('menu.purchasing');
           
            //----- THE LINES BELOW MUTS BE DYNAMICALLY IMPLEMENTED ----
            //************ RENDERING PURCHASING dropDownItems WITH ALL OPTIONS *******************
            //checking whether purchasing's menu-items is different of 0 
            if (count($purchasingMenuOptions)!=0) {
                $items[] = [
                    'id' => 'purchasing', 
                    'label' => 'Puchasing',
                    'dropdown' => $purchasingMenuOptions
                ];
            }  
            
             /*
             ****************************************************************************
             *  Determine WHAT items(OPTIONS) must be displayed in Admin dropDownList   *
             * **************************************************************************
             */            
            
            // ARRAY WITH ITEMS FOR ADMIN USERS
            $adminMenuOptions = [];
            $has =  $this->rbacManager->isGranted(null, 'user.manage');
            var_dump($has);
            if ($this->rbacManager->isGranted(null, 'user.manage')) {                
                $adminMenuOptions[] = [
                            'id' => 'users',
                            'label' => 'Manage Users',
                            'link' => $url('users')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                $adminMenuOptions[] = [
                            'id' => 'permissions',
                            'label' => 'Manage Permissions',
                            'link' => $url('permissions')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'role.manage')) {
                $adminMenuOptions[] = [
                            'id' => 'roles',
                            'label' => 'Manage Roles',
                            'link' => $url('roles')
                        ];
            }
            //checking whether Admin's menu-items is different of 0 
            if (count($adminMenuOptions)!=0) {
                $items[] = [
                    'id' => 'admin',
                    'label' => 'Admin',
                    'dropdown' => $adminMenuOptions
                ];
            }
            
            /*
             *  ABOUT MENU: it would be the lastest menu
             *  It does not need nothing special and neither checks if the user has permissions
             */
            $items[] = [
            'id' => 'about',
            'label' => 'About',
            'link'  => $url('about')
            ];
            
            /*
             * 
             */
            $items[] = [
                'id' => 'logout',
                'label' => $this->authService->getIdentity(),
                'float' => 'right',
                'dropdown' => [
                    [
                        'id' => 'settings',
                        'label' => 'Settings',
                        'link' => $url('application', ['action'=>'settings'])
                    ],
                    [
                        'id' => 'logout',
                        'label' => 'Sign out',
                        'link' => $url('logout')
                    ],
                ]
            ];
        }
        
        return $items;
    }
}


