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
     * this function return an array with the items of menu
     * will be shown  
     */
    private function addOptionsToMenu(string $menuPermission )
    {
        //getting the Instance of urlHelper which let us create links on our page (on the menus)
        // using VALID ROUTES definided on the module.config.php file
        $url = $this->urlHelper;
        //load the options associate to the user with the $menuPermission 
        //doit....
        
        $options= []; //init $optionss 
        //------- checking permission Acces: +menu.purchasing ----------
        if ($this->rbacManager->isGranted(null, $menuPermission)) {
            
            //the option will be render if user has permission associated to the 
            // claims (action),  
            if ($this->rbacManager->isGranted(null, 'option.purchasing.claims')) {
                $options[] = [  
                                'id' => 'claims',
                             'label' => 'Claims',
                              'link' => $url('claims',['action'=>'index'])
                         ];
            }//ENDIF: granted +option.purchasing.claims 
            
            if ($this->rbacManager->isGranted(null, 'option.purchasing.productdevelopments')) {   
            /* Option: Product Developments */
            $options[] = [
                        'id' => 'productDevelopment',
                        'label' => 'Product Developments',
                        'link' => $url('pagebuilding')
                    ];
            }//END IF: option.purchasing.productdevelopments 
            
            /* Menu: Purchasing
             * - Option: supplies
             */ 
            $options[] = [
                        'id' => 'supplies',
                        'label' => 'Supplies',
                        'link' => $url('pagebuilding')
                    ];
            
                            
            /* Menu: Purchasing
             * - Option:  
             *     Comments, New Supplies/Others 
             */
            
            $options[] = [
                        'id' => 'comments',
                        'label' => 'Comments, New Supplies/Others',
                        'link' => $url('pagebuilding')
                    ];
            
            /* a divider between options */ 
            $options[] = [ 'id' => '-'];
            
            
            /* Menu: Purchasing
             * - Option:  
             *     Sales Back Orders 
             */
                 
            $options[] = [
                       'id' => 'salesbackorders',
                       'label' => 'Sales Backorders',
                       'link' => $url('pagebuilding')
                   ];
                
            /* Menu: Purchasing
             * - Option:  
             *  Follow Backorders 
             */ 
                   
            $options[] = [
                        'id' => 'followbackorders',
                        'label' => 'Follow Backorders',
                        'link' => $url('pagebuilding')
                    ];
            
            /* a divider between options */ 
            $options[] = [ 'id' => '-'];
            
            /* Menu: Purchasing
             * - Option:   
             *   Part/Vendor Comments 
             */        
            $options[] = [
                      'id' => 'partvendorcomments',
                      'label' => 'Part/Vendor Comments',
                      'link' => $url('pagebuilding')
                  ];
            
            /* Menu: Purchasing
             * - Option: 
             *     Suspended Parts
             */        
            $options[] = [
                        'id' => 'suspendedparts',
                        'label' => 'Suspended Parts',
                        'link' => $url('pagebuilding')
                    ];

            $options[] = [ 'id' => '-'];
            
            /* Menu: Purchasing
             * - Option: 
             *      Email Vendors */        
            $options[] = [
                        'id' => 'emailvendors',
                        'label' => 'Email Vendors',
                        'link' => $url('pagebuilding')
                    ];

           /* Menu: Purchasing
             * - Option:     
             *       Purchasing Quote 
             */        
            $options[] = [
                        'id' => 'partvendorcomments',
                        'label' => 'Purchasing Quote',
                        'link' => $url('pagebuilding')
                    ];
            
            /* Menu: Purchasing
             * - Option:  
             *      Vendors Price List
             */        
            $options[] = [
                        'id' => 'vendorspricelist',
                        'label' => 'Vendors Price List',
                        'link' => $url('pagebuilding')
                    ];

            /* Menu: Purchasing
             *  - Option: 
             *      Print labels (Vendors)
             */        
            $options[] = [
                        'id' => 'printlabels',
                        'label' => 'Print Labels(Vendors)',
                        'link' => $url('pagebuilding')
                    ];

            /* a divider between options */ 
            $options[] = [ 'id' => '-'];
            
            /** Menu: Purchasing
             *   - Option:  
             *      Change Pur. Agent / Person in charge
             */        
            $options[] = [
                        'id' => 'changeagentpersonincharge',
                        'label' => 'Change Pur. Agent/Person in charge',
                        'link' => $url('pagebuilding')
                    ];
             
            /* Menu: Purchasing
             * - Option: 
             *       Upload OEM Pictures 
             */        
            $options[] = [
                        'id' => 'uploadoempictures',
                        'label' => 'Upload OEM Pictures',
                        'link' => $url('pagebuilding')
                    ];
            
            /* Menu: Purchasing
             * - Option:  
             *      Reports 
             */        
            $options[] = [
                        'id' => 'reports',
                        'label' => 'Reports',
                        'link' => $url('pagebuilding')
                    ];
                   
        } //ENDIF:  access() to $permission 
        
        return $options;
    } //END METHOD: addMenuOptions(+permission)
    
     private function getMainMenuPermissions(){
        return [
                    'management' =>[
                        'permission'=>'menu.management',
                                'id' =>'management',
                             'label' =>'Management'
                        ],
                    
                    'marketing' =>[
                        'permission'=>'menu.marketing',
                                'id' =>'marketing',
                             'label' =>'Marketing'
                          ],                     
                    'mis' =>[
                        'permission'=>'menu.mis',
                                'id' =>'mis',
                             'label' =>'MIS'
                        ],
                     
                    'purchasing' =>[
                        'permission'=>'menu.purchasing',
                                'id' =>'purchasing',
                             'label' =>'Purchasing'
                        ],
                     
                    'quality' =>[
                        'permission'=>'menu.quality',
                                'id' =>'quality',
                             'label' =>'Quality'
                          ],
                    
                    'manufacturing' =>[
                        'permission'=>'menu.manufacturing',
                                'id' =>'manufacturing',
                             'label' =>'Manufacturing'
                       ],                    
                    'sales' =>[
                        'permission'=>'menu.sales',
                                'id' =>'sales',
                             'label' =>'Sales'
                        ],                    
                    'receiving' =>[
                        'permission'=>'menu.receiving',
                                'id' =>'receiving',
                             'label' =>'Receiving'
                        ],                    
                    'warehourse' =>[
                        'permission'=>'menu.warehourse',
                                'id' =>'warehourse',
                             'label' =>'Warehourse'
                        ],                    
                    'maintenance' =>[
                        'permission'=>'menu.maintenance',
                                'id' =>'maintenance',
                             'label' =>'Maintenance'
                        ],
        ];
     }//END: function getMainMenu()     
     
    /*
        * This method returns menu items depending on whether user has logged in or not.
        *  
        *  Menu: Home will be shown always, so you don't need to verify access
        *  indexes:
        *      - id   : it identifies each one of the menu items
        *      - label: This NAME matches with how users will watch the menu option  on the UI
        *      - link : It identifies the ROUTE (defined on the module.config.php ) 
        *              when the user click it on.
    */
        
    public function getMenuItems() 
    {
        $url = $this->urlHelper;
        //This variable ARRAY $items[] will contain all menu items that will be shown 
        $items = [];
               
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
            } 
        else {            
        /**
         * CREATE DYNAMIC MENUS WITH THE OPTIONS GIVEN OR ASSIGNED EACH MENU 
         * Management, Marketing, MIS, Purchasing, Quality Control, Manufacturing, Sales Shipping
         *  Receiving, Warehouse, maintenance 
         */       
            
           // Determine which items must be displayed in Purchasing
           $mainMenu = ["management"=>0,    "marketing"=>0, "mis"=>0, "purchasing"=>0, "purchasing"=>0,
                      "quality"=>0,"manufacturing"=>0,"sales"=>0,"receiving"=>0,"warehourse"=>0,"maintenance"=>0];
            
        /* 
         * GETTING THE MENUS AND THE PERMISSIONS ASSOCIATED TO THEM 
         *  - getMainMenuPermissions(): it get back an array with
         *  - the menu and the permissions associated to them
         */
           
           $mainMenuPermissions = $this->getMainMenuPermissions();            
           
           //getting back the options associated to $menu['MODULE_NAME'] 
           $menuOptions = $this->addOptionsToMenu($mainMenuPermissions['purchasing']['permission']);
           
           //checking COUNT of ITEMS(options) will be shown on the Menu: PURCHASING FOR EXAMPLE
           $countOptionsMenu['purchasing'] =  count($menuOptions)??0;
           
           
            //----- THE LINES BELOW MUTS BE DYNAMICALLY IMPLEMENTED ----
            //************ RENDERING PURCHASING dropDownItems WITH ALL OPTIONS *******************
            //checking whether purchasing's menu-items is different of 0 
           
            if ($countOptionsMenu['purchasing']!=0) {
                $items[] = [
                    'id' => 'purchasing', 
                    'label' => $mainMenuPermissions['purchasing']['label'], //'Puchasing',
                    'dropdown' => $menuOptions
                ];
            } 
            
             /*             
             *  Determine WHAT items(OPTIONS) must be displayed in Admin dropDownList   
             */            
            
            // ARRAY WITH ITEMS FOR ADMIN USERS
            $adminMenuOptions = [];
            
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


