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
        
        /*
         * Load the options associated to the user with the permission to the MENU ($menuPermission) 
         * - if $menuPermission = 'menu.purchasing' then 
         * - $options [] return all the options which the user has permission
         */   
        $options= []; //init $options
                
       // echo"ENTER---". $menuPermission;   
        if ($this->rbacManager->isGranted(null, $menuPermission)) {
            switch ($menuPermission) {                
                /**** MANAGEMENT MENU ****/
                case 'menu.management': 
                                 
                        $options[] = [
                                        'id' => 'management',
                                        'label' => 'Test',
                                        'link' => $url('management',['action'=>'index'])
                                    ];
                        $options[] = [
                                        'id' => 'management1',
                                        'label' => 'Option 2',
                                        'link' => $url('pagebuilding')
                                    ];

                        /* a divider between options */ 
                            $options[] = [ 'id' => '-'];

                        $options[] = [
                                        'id' => 'management2',
                                        'label' => 'Option 3',
                                        'link' => $url('pagebuilding')
                                    ];  
                        $options[] = [
                                        'id' => 'management3',
                                        'label' => 'Option 4',
                                        'link' => $url('pagebuilding')
                                    ];
                break; //END CASE: 'menu.management'
            
                /**** MARKETING MENU ****/
                case 'menu.marketing' : //doing all about marketing Menu
                break; //END CASE: 'menu.maketing'
                
                /**** MIS MENU ****/
                case 'menu.mis' : //doing all about mis Menu
                break;/*-------------      END CASE:  menu.mis ----------------------*/
            
                /**** PURCHASING MENU ****/
                case 'menu.purchasing' : 
                    /* Options: CLAIMS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.claims')) {
                        $options[] = [  
                                        'id' => 'claims',
                                     'label' => 'Claims',
                                      'link' => $url('claims',['action'=>'index'])
                                 ];
                    }//ENDIF: granted +option.purchasing.claims 

                    /* Options: Product Developments */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.productdevelopments')) {                      
                        $options[] = [
                                    'id' => 'productdevelopment',
                                    'label' => 'Product Developments',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: option.purchasing.productdevelopments 

                    /* Options: Supplies */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.supplies')) {
                        $options[] = [
                                    'id' => 'supplies',
                                    'label' => 'Supplies',
                                    'link' => $url('pagebuilding')
                                ];
                        $options[] = [
                                    'id' => 'comments',
                                    'label' => 'Comments, New Supplies/Others',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: option.purchasing.supplies    

                    
                    /* Options: Sales Backorders */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.backorders')) {                        
                        $options[] = [ 'id' => '-']; /* a divider between options */ 

                        $options[] = [
                                   'id' => 'salesbackorders',
                                   'label' => 'Sales Backorders',
                                   'link' => $url('pagebuilding')
                               ];
                        
                        $options[] = [
                                    'id' => 'followbackorders',
                                    'label' => 'Follow Backorders',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: BACKORDERS 

                    /* Options: Vendors */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.vendors')) { 
                        $options[] = [ 'id' => '-']; /* a divider between options */
                          
                        $options[] = [
                                  'id' => 'partvendorcomments',
                                  'label' => 'Part/Vendor Comments',
                                  'link' => $url('pagebuilding')
                              ];
                        
                        $options[] = [
                                    'id' => 'emailvendors',
                                    'label' => 'Email Vendors',
                                    'link' => $url('pagebuilding')
                                ];   
                        
                        $options[] = [
                                'id' => 'vendorspricelist',
                                'label' => 'Vendors Price List',
                                'link' => $url('pagebuilding')
                            ];
                        
                        $options[] = [
                                    'id' => 'printlabels',
                                    'label' => 'Print Labels(Vendors)',
                                    'link' => $url('pagebuilding')
                                ];
                        
                    }//END IF: VENDORS

                    /* Options: parts */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.parts')) {
                        $options[] = [ 'id' => '-']; /* a divider between options */
                        
                        $options[] = [
                                        'id' => 'suspendedparts',
                                        'label' => 'Suspended Parts',
                                        'link' => $url('pagebuilding')
                                    ];  
                        $options[] = [
                                    'id' => 'partvendorcomments',
                                    'label' => 'Purchasing Quote',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: PARTS

                    //* Options: AGENTS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.agents')) { 
                        $options[] = [ 'id' => '-']; /* a divider between options */ 
                        $options[] = [
                                    'id' => 'changeagentpersonincharge',
                                    'label' => 'Change Pur. Agent/Person in charge',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: AGENTS 
                        

                    ///* Options: UPLOAD OEM PICS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.uploadoem')) { 
                        $options[] = [ 'id' => '-']; /* a divider between options */ 
                        $options[] = [
                                    'id' => 'uploadoempictures',
                                    'label' => 'Upload OEM Pictures',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: UPLOAD OEM PICS

                    ///* Options: REPORTS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.reports')) { 
                        $options[] = [
                                    'id' => 'reports',
                                    'label' => 'Reports',
                                    'link' => $url('pagebuilding')
                                ];
                    }//END IF: REPORTS
                    
                break;/*-------------      END CASE:  menu.purchasing ----------------------*/
                
                /**** QUALITY MENU ****/
                case 'menu.quality' :
                break; //END CASE: menu.qualiaty 
            
                /**** MANUFACTURING MENU ****/
                case 'menu.manufacturing' : 
                break; //END CASE: 'menu.manufacturing'
                
                /**** SALES MENU ****/
                case 'menu.sales' :
                     $options[] = [
                                        'id' => 'test',
                                        'label' => 'Test',
                                        'link' => $url('management',['action'=>'index'])
                                    ];
                        $options[] = [
                                        'id' => 'option2',
                                        'label' => 'Option 2',
                                        'link' => $url('pagebuilding')
                                    ];

                        /* a divider between options */ 
                            $options[] = [ 'id' => '-'];

                        $options[] = [
                                        'id' => 'option3',
                                        'label' => 'Option 3',
                                        'link' => $url('pagebuilding')
                                    ];  
                        $options[] = [
                                        'id' => 'option4',
                                        'label' => 'Option 4',
                                        'link' => $url('pagebuilding')
                                    ];
                break; //END CASE: 'menu.sales' 
            
                /**** RECEIVING MENU ****/
                case 'menu.receiving' : 
                break; //END CASE:  'menu.receiving'
            
                /**** WHAREHOUSE MENU ****/
                case 'menu.wharehouse' : 
                break; //END CASE: 'menu.wharehouse'
            
                /**** MAINTENANCE MENU ****/
                case 'menu.maintenance' : 
                break; //END CASE:   'menu.maintenance'      

            } // END SWITCH 
    }//End IF 
        
        return $options;
    } //END METHOD: addMenuOptions(+permission)
    
     private function getMainMenuPermissions(){
        return [//0
                'management' =>[
                    'permission'=>'menu.management',
                            'id' =>'management',
                         'label' =>'Management'
                    ],
                //1    
                'marketing' =>[
                    'permission'=>'menu.marketing',
                            'id' =>'marketing',
                         'label' =>'Marketing'
                      ],
                //2      
                'mis' =>[
                    'permission'=>'menu.mis',
                            'id' =>'mis',
                         'label' =>'MIS'
                    ],
                //3    
                'purchasing' =>[
                    'permission'=>'menu.purchasing',
                            'id' =>'purchasing',
                         'label' =>'Purchasing'
                    ],
                //4
                'quality' =>[
                    'permission'=>'menu.quality',
                            'id' =>'quality',
                         'label' =>'Quality'
                      ],
                //5
                'manufacturing' =>[
                    'permission'=>'menu.manufacturing',
                            'id' =>'manufacturing',
                         'label' =>'Manufacturing'
                   ],
                //6   
                'sales' =>[
                    'permission'=>'menu.sales',
                            'id' =>'sales',
                         'label' =>'Sales'
                    ],                    
                //7
                'receiving' =>[
                    'permission'=>'menu.receiving',
                            'id' =>'receiving',
                         'label' =>'Receiving'
                    ],
                //8
                'warehourse' =>[
                    'permission'=>'menu.warehourse',
                            'id' =>'warehourse',
                         'label' =>'Warehourse'
                    ],                    
                //9
                'maintenance' =>[
                    'permission'=>'menu.maintenance',
                            'id' =>'maintenance',
                         'label' =>'Maintenance'
                    ],
        ];
     }//END: function getMainMenu()     
    
    /*
     * This method add a list of Options to a Menu with
     *    -id: $id 
     * -label: $label
     */
    private function setOptionsToMenu( $id, $label, $options ){
        if (count($options)!=0){
            return [
                        'id' => $id,
                     'label' => $label, 
                  'dropdown' => $options
            ];
        }
        
        return [];
        
    } //ENDIF: function AddOptionsToMenuDropDown() 
      
    private function setOptions($id, $label, $route ){  
        $url = $this->urlHelper;
        return [
                'id' => $id,
                'label' => $label,
                'link' => $url($route),
              ];                
    }//END: setOptions    
   
/*
* This method returns menu items depending on whether user has logged in or not.
*  
*  Menu: Home will be shown always, so you don't need to verify access indexes:
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
       
        $mainMenu = ["management", "marketing", "mis", "purchasing", "quality",
                     "manufacturing", "sales","receiving","warehourse","maintenance"];
        /* 
         * GETTING THE MENUS AND THE PERMISSIONS ASSOCIATED TO THEM 
            *  - getMainMenuPermissions(): it get back an array with
            *  - the menu and the permissions associated to them. 
            *  -$mainMenuPermissions[] : it contains each module  
            *    and the permission associated to it (this permission associate
            *    to a Menu Role : (example:  +menu.purchasing  
         */
           
           $mainMenuPermissions = $this->getMainMenuPermissions();            
           
           /* getting back the options associated to $menu['MODULE_NAME'] 
             checking COUNT of ITEMS(options) will be shown on the Menu: PURCHASING FOR EXAMPLE
              - $mainMenuPermission['management']['permission']] : it containts the permission menu
            */   
           
            $menuOptions = $this->addOptionsToMenu($mainMenuPermissions['management']['permission']);          
           
          /*
           *  get Id, Label and $menuOptions and pass them to the method:
           *  addOptionToMenuDropDown             
          */
           $id = $mainMenuPermissions['management']['id'];           
           $label = $mainMenuPermissions['management']['label'];           
           $items[] = $this->setOptionsToMenu( $id, $label, $menuOptions );
           
           // get Id, Label and $menuOptions and pass them to the method:
           // addOptionToMenuDropDown 
           $menuOptions = $this->addOptionsToMenu($mainMenuPermissions['purchasing']['permission']);
           $id = $mainMenuPermissions['purchasing']['id'];
           $label = $mainMenuPermissions['purchasing']['label'];           
           $items[] = $this->setOptionsToMenu( $id, $label, $menuOptions );
           
           
           $menuOptions = $this->addOptionsToMenu($mainMenuPermissions['sales']['permission']);
           $id = $mainMenuPermissions['sales']['id'];
           $label = $mainMenuPermissions['sales']['label'];           
           $items[] = $this->setOptionsToMenu( $id, $label, $menuOptions );
           
           /*             
            *  Determine WHAT items(OPTIONS) must be displayed in Admin dropDownList   
            */            
            
            // OPTIONS FOR ADMIN MENU: ARRAY WITH ITEMS FOR ADMIN USERS
            // $adminMenuOptions = $this->setOptions($id, $label, $route, $permission);
            $adminMenuOptions =[];
            if ($this->rbacManager->isGranted(null, 'user.manage')) {
                $adminMenuOptions[] = $this->setOptions('users', 'Manage Users', 'users');
            }            
            if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                $adminMenuOptions[] = $this->setOptions('permissions', 'Manage Permissions', 'permissions');                       
            }            
            if ($this->rbacManager->isGranted(null, 'role.manage')) {
                $adminMenuOptions[] = $this->setOptions('roles', 'Manage Roles', 'roles');                       
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
             *  Adding About Menu
             */
            $items[] = $this->setOptions('about', 'About', 'about');              
                        
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


