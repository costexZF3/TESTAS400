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
                /**** ADMIN MENU ****/
                case 'menu.admin' : 
                    if ($this->rbacManager->isGranted(null, 'user.manage')) {
                        $options[] = $this->setOptions('users', 'Manage Users', 'users','');
                    }            
                    if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                        $options[] = $this->setOptions('permissions', 'Manage Permissions', 'permissions','');                       
                    }            
                    if ($this->rbacManager->isGranted(null, 'role.manage')) {
                        $options[] = $this->setOptions('roles', 'Manage Roles', 'roles','');                       
                    }
                break; //end case: 'menu.admin'  
                
                
                /**** MANAGEMENT MENU ****/
                case 'menu.management':                                  
                       
                        // 'link' => $url('management',['action'=>'index'])
                        $options[] = $this->setOptions('management', 'Test', 'management','');            
                    
                        $options[] = $this->setOptions('management1', 'Option 1', 'pagebuilding','');
                        $options[] = $this->setOptions('management2', 'Option 2', 'pagebuilding','');
                        
                        $options[] = $this->setDivider($options);                         
                        
                        $options[] = $this->setOptions('management3', 'Option 3', 'pagebuilding','');
                        $options[] = $this->setOptions('management4', 'Option 4', 'pagebuilding','');
                break; //END CASE: 'menu.management'
            
                /**** MARKETING MENU ****/
                case 'menu.marketing' : //doing all about marketing Menu
                break; //--------------------------  END CASE: 'menu.maketing'-------------------
                
                /****************************** MIS MENU ******************************/
                case 'menu.mis' : //doing all about mis Menu
                break;/*--------------------------   END CASE:  menu.mis  ----------------------*/
            
                /************************** PURCHASING MENU ******************************/
                case 'menu.purchasing' : 
                    /* Options: CLAIMS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.claims')) {                   
                        $options[] = $this->setOptions('claims', 'Claims', 'claims','');
                    }//end if: granted +option.purchasing.claims 

                    /* Options: Product Developments */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.productdevelopments')) {                                             
                         $options[] = $this->setOptions('productdevelopment', 'Product Developments', 'pagebuilding','');
                    }//end if: option.purchasing.productdevelopments 

                    /* Options: Supplies */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.supplies')) {
                        $options[] = $this->setDivider($options); 

                        $options[] = $this->setOptions('supplies', 'Supplies', 'pagebuilding','');
                        $options[] = $this->setOptions('comments', 'Comments, New Supplies/Others', 'pagebuilding','');
                       
                    }//end if: option.purchasing.supplies    
                    
                    /* Options: Sales Backorders */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.backorders')) {                        
                        $options[] = $this->setDivider($options); 

                        $options[] = $this->setOptions('salesbackorders', 'Sales Backorders', 'pagebuilding','');                                                 
                        $options[] = $this->setOptions('followbackorders', 'Follow Backorders', 'pagebuilding',''); 
                    }//end if: BACKORDERS 

                    /* Options: Vendors */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.vendors')) {
                        $options[] = $this->setDivider($options);  
                        
                        $options[] = $this->setOptions('partvendorcomments', 'Part/Vendor Comments', 'pagebuilding','');                           
                        $options[] = $this->setOptions('emailvendors', 'Email Vendors', 'pagebuilding','');                        
                        $options[] = $this->setOptions('vendorspricelist', 'Vendors Price List', 'pagebuilding','');                                               
                        $options[] = $this->setOptions('printlabels', 'Print Labels(Vendors)', 'pagebuilding','');
                        
                    }//END IF: VENDORS

                    /* Options: parts */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.parts')) {                        
                        $options[] = $this->setDivider($options); 
                       
                        $options[] = $this->setOptions('suspendedparts', 'Suspended Parts', 'pagebuilding','');
                        $options[] = $this->setOptions('partvendorcomments', 'Purchasing Quote', 'pagebuilding','');
                        
                    }//end if: PARTS

                    /* Options: AGENTS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.agents')) {                        
                        $options[] = $this->setDivider($options);                                                  
                        $options[] = $this->setOptions('changeagentpersonincharge', 'Change Pur. Agent/Person in charge', 'pagebuilding','');                          
                    }//end if: AGENTS                         

                    /* Options: UPLOAD OEM PICS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.uploadoem')) {                        
                        $options[] = $this->setDivider($options); 
                        $options[] = $this->setOptions('uploadoempictures', 'Upload OEM Pictures', 'pagebuilding','');
                    }//end if: UPLOAD OEM PICS

                    /* Options: REPORTS */
                    if ($this->rbacManager->isGranted(null, 'option.purchasing.reports')) {                        
                        $options[] = $this->setDivider($options);                         
                        $options[] = $this->setOptions('reports', 'Reports', 'pagebuilding','');                                
                    }//end if: REPORTS
                    
                break;/*---------------------      END CASE:  menu.purchasing ----------------------*/
                
                /**** QUALITY MENU ****/
                case 'menu.quality' :
                break; //END CASE: menu.qualiaty 
            
                /**** MANUFACTURING MENU ****/
                case 'menu.manufacturing' : 
                break; //END CASE: 'menu.manufacturing'
                
                /**** SALES MENU ****/
                case 'menu.sales' :                    
                        // 'link' => $url('sales',['action'=>'index'])
                        $options[] = $this->setOptions('sales', 'Test', 'sales','');            
                    
                        $options[] = $this->setOptions('sales1', 'Option 1', 'pagebuilding','');
                        $options[] = $this->setOptions('sales2', 'Option 2', 'pagebuilding','');
                        
                        $options[] = $this->setDivider($options);                         
                        
                        $options[] = $this->setOptions('sales3', 'Option 3', 'pagebuilding','');
                        $options[] = $this->setOptions('sales4', 'Option 4', 'pagebuilding','');
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
            'admin' =>[
                    'permission'=>'menu.admin',
                            'id' =>'admin',
                         'label' =>'Admin'
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
     
    /*
     * render a divider between options 
     */
    private function setDivider( $options ){
       return  (count($options)!=0) ? $this->setOptions('divider','','',''):null;
    }//END METHOD: setDivider()
    
    private function setOptions(string $id, string $label,  string $route, string $floatItem ){  
        $url = $this->urlHelper;
        /*
         * there are two options 
         */       
        //it's a divider 
        if ($id ==='divider'){             
            return [ 'id' => '-'];
        }
        
        $result = [
                'id' => $id,
                'label' => $label,
                'link' => $url($route),
              ]; 
        
        if ( $floatItem!='' ){
            $result = $result + ['float'=>$floatItem];
        }
        
        return $result;               
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
        
        //Home Menu: setOptions($id, $label, $route, $floating)
        $items[]= $this->setOptions('home', 'Home', 'home', '');
               
        /* 
         * Display "Login" menu item for not authorized user only. On the other hand,
         * display "Admin" and "Logout" menu items only for authorized users.               
         */
        if (!$this->authService->hasIdentity()) {             
            $items[]= $this->setOptions('login', 'Sign in', 'login', 'right');            
        } 
        else {            
            /**
             * CREATE DYNAMIC MENUS WITH THE OPTIONS GIVEN OR ASSIGNED EACH MENU 
             * Management, Marketing, MIS, Purchasing, Quality Control, Manufacturing, Sales Shipping
             *  Receiving, Warehouse, maintenance 
             */       
       
            
           /* 
            * GETTING THE MENUS AND THE PERMISSIONS ASSOCIATED TO THEM 
            *  - getMainMenuPermissions(): 
            *       it returns an array with the menu and its permissions associated which will be tested
            *  -$mainMenuPermissions[] : it contains each module and the permission associated to it 
            *    (this permission associate to a Menu Role : (example:  +menu.purchasing) 
            */
           
           $mainMenuPermissions = $this->getMainMenuPermissions(); 
           
           //$mainMenu1 = ["management", "marketing", "mis", "purchasing", "quality",
           //             "manufacturing", "sales","receiving","warehourse","maintenance"]; 
                      
                
          /*
           *  Getting params
           *   -Id : id (it's like a name of object, 
           *   - Label: It's a string that idenfifies ONE item of the Main Menu 
           *   - $menuOptions : It receives the items of menu will be rendered
           *  - Id, Label and pass them to the method:
           *  addOptionToMenuDropDown             
          */
           
            $mainMenu = [
                            "management", //"marketing", "mis", 
                            "purchasing", //"quality","manufacturing", 
                            "sales",//"receiving","warehourse","maintenance"
                            "admin",
                        ]; 
            foreach ($mainMenu as $moduleName) {
                $menuOptions = $this->addOptionsToMenu($mainMenuPermissions[$moduleName]['permission']);  
                $id = $mainMenuPermissions[$moduleName]['id'];           
                $label = $mainMenuPermissions[$moduleName]['label'];           
                $items[] = $this->setOptionsToMenu( $id, $label, $menuOptions );
            }
            
            /*
             *  Adding About Menu
             */
            $items[] = $this->setOptions('about', 'About', 'about','');              
             
            /*
             *  Adding Sing in, Settings and Logout options floating to the right
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


