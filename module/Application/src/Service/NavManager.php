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
         
        if ($this->rbacManager->isGranted(null, $menuPermission)) {
            //echo "true";
            switch ($menuPermission) { 
                /**** ADMIN MENU ****/
                case 'menu.admin' : 
                    if ($this->rbacManager->isGranted(null, 'manage.user')) {
                        $options[] = $this->setOptions('users', 'Manage Users', 'users','');
                    }            
                    if ($this->rbacManager->isGranted(null, 'manage.permission')) {
                        $options[] = $this->setOptions('permissions', 'Manage Permissions', 'permissions','');                       
                    }            
                    if ($this->rbacManager->isGranted(null, 'manage.role')) {
                        $options[] = $this->setOptions('roles', 'Manage Roles', 'roles','');                       
                    }
                break; //end case: 'menu.admin'                
                
                /**** MANAGEMENT MENU ****/
                case 'menu.management':                                  
                       
                        // 'link' => $url('management',['action'=>'index'])
                        $options[] = $this->setOptions('management', 'Test', 'management','');            
                    
                        $options[] = $this->setOptions('management1', 'Option 1', 'pagebuilding','');
                        $options[] = $this->setOptions('management2', 'Option 2', 'pagebuilding','');
                        
                       // $options[] = $this->setDivider($options);                         
                        
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
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.claims')) {                   
                        $options[] = $this->setOptions('claims', 'Claims', 'claims','');
                       // $options[] = $this->setDivider($options); 
                    }//end if: granted +option.purchasing.claims 

                    /* Options: Product Developments */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.productdevelopments')) {                                             
                         
                        $options[] = $this->setOptions('productdevelopment', 'Product Developments', 'pagebuilding','');
                    }//end if: option.purchasing.productdevelopments 
                    
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.pd.wishlist')) {                                             
                         $options[] = $this->setOptions('pdwishlist', 'Product Dev. Wish List', 'pagebuilding','');
                         $options[] = $this->setOptions('lostsales', 'Lost Sales', 'lostsales','');
                    }//end if: productdevelopments  wish list
                    
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.pd.personincharge')) {                                             
                         $options[] = $this->setOptions('pdpersonincharge', 'Product Dev. Person In Charge', 'pagebuilding','');
                    }//end if: productdevelopments  wish list
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.pd.reports')) {                                             
                         $options[] = $this->setOptions('pdreports', 'Product Dev. Reports', 'pagebuilding','');
                    }//end if: productdevelopments  wish list

                    /* Options: Supplies */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.supplies')) {
                        $options[] = $this->setDivider($options); 

                        $options[] = $this->setOptions('supplies', 'Supplies', 'pagebuilding','');
                        $options[] = $this->setOptions('comments', 'Comments, New Supplies/Others', 'pagebuilding','');
                       
                    }//end if: option.purchasing.supplies    
                    
                    /* Options: Sales Backorders */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.backorders')) {                        
                        //$options[] = $this->setDivider($options); 

                        $options[] = $this->setOptions('salesbackorders', 'Sales Backorders', 'pagebuilding','');                                                 
                        $options[] = $this->setOptions('followbackorders', 'Follow Backorders', 'pagebuilding',''); 
                    }//end if: BACKORDERS 

                    /* Options: Vendors */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.vendors')) {
                       // $options[] = $this->setDivider($options);  
                        
                        $options[] = $this->setOptions('partvendorcomments', 'Part/Vendor Comments', 'pagebuilding','');                           
                        $options[] = $this->setOptions('emailvendors', 'Email Vendors', 'pagebuilding','');                        
                        $options[] = $this->setOptions('vendorspricelist', 'Vendors Price List', 'pagebuilding','');                                               
                        $options[] = $this->setOptions('printlabels', 'Print Labels(Vendors)', 'pagebuilding','');
                        
                    }//END IF: VENDORS

                    /* Options: parts */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.parts')) {                        
                      //  $options[] = $this->setDivider($options); 
                       
                        $options[] = $this->setOptions('suspendedparts', 'Suspended Parts', 'pagebuilding','');
                        $options[] = $this->setOptions('partvendorcomments', 'Purchasing Quote', 'pagebuilding','');
                        
                    }//end if: PARTS

                    /* Options: AGENTS */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.agents')) {                        
                      //  $options[] = $this->setDivider($options);                                                  
                        $options[] = $this->setOptions('changeagentpersonincharge', 'Change Pur. Agent/Person in charge', 'pagebuilding','');                          
                    }//end if: AGENTS                         

                    /* Options: UPLOAD OEM PICS */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.uploadoem')) {                        
                        //$options[] = $this->setDivider($options); 
                        $options[] = $this->setOptions('uploadoempictures', 'Upload OEM Pictures', 'pagebuilding','');
                    }//end if: UPLOAD OEM PICS

                    /* Options: REPORTS */
                    if ($this->rbacManager->isGranted(null, 'purchasing.option.reports')) {                        
                       // $options[] = $this->setDivider($options);                         
                        $options[] = $this->setOptions('reports', 'Reports', 'pagebuilding','');                                
                    }//end if: REPORTS
                    
                break;/*---------------------      END CASE:  menu.purchasing ----------------------*/
                
                /**** QUALITY MENU ****/
                case 'menu.quality' :
                break; //END CASE: menu.qualiaty 
                           
                /**** SALES MENU ****/
                case 'menu.sales' :                    
                        // 'link' => $url('sales',['action'=>'index'])
                        $options[] = $this->setOptions('sales', 'Test aaaa', 'sales','');            
                    
                        $options[] = $this->setOptions('sales1', 'Option 1', 'pagebuilding','');
                        $options[] = $this->setOptions('sales2', 'Option 2', 'pagebuilding','');
                        
                       // $options[] = $this->setDivider($options);                         
                        
                        $options[] = $this->setOptions('sales3', 'Option 3', 'pagebuilding','');
                        $options[] = $this->setOptions('sales4', 'Option 4', 'pagebuilding','');
                break; //END CASE: 'menu.sales' 
            
                /**** RECEIVING MENU ****/
                case 'menu.receiving' : 
                    // 'link' => $url('sales',['action'=>'index'])
                        $options[] = $this->setOptions('rec1', 'Test rrrr', 'receiving','');            
                    
                        $options[] = $this->setOptions('rec2', 'Receiving 1', 'pagebuilding','');
                        $options[] = $this->setOptions('rec3', 'Receiving 2', 'pagebuilding','');
                        
                       // $options[] = $this->setDivider($options);                         
                        
                        $options[] = $this->setOptions('rec4', 'Receiving 3', 'pagebuilding','');
                        $options[] = $this->setOptions('rec5', 'Receiving 4', 'pagebuilding','');
                break; //END CASE:  'menu.receiving'
            
                /**** WHAREHOUSE MENU MANUFACTORING ****/
                case 'menu.warehouse' : 
                    
                    $countOptions = 0; 
                    if ($this->rbacManager->isGranted(null, 'menu.warehouse')) {
                       // echo "access granted: MENU WAREHOUSE"."<br>";
                    }
                    
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.picking')) {
                        $options[] = $this->setOptions('warehouse1', 'Picking Process', 'pagebuilding','');
                        $countOptions++;
                    }
                    
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.assembly')) {
                        $options[] = $this->setOptions('warehouse2', 'Assembly Process', 'pagebuilding','');
                        $countOptions++; 
                    }
                    
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.seal')) {
                        $options[] = $this->setOptions('warehouse3', 'Seal Process', 'pagebuilding','');
                        $countOptions++;
                    }
                        
                    
                    $options[] = $this->setDivider($options);                         
                        
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.missing.parts')) {
                        $options[] = $this->setOptions('warehouse4', 'Report Missing Parts', 'pagebuilding','');
                    }
                    
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.gasket')) {
                        $options[] = $this->setOptions('warehouse5', 'Gasket Process', 'pagebuilding','');
                    }
                    
                    if ($this->rbacManager->isGranted(null, 'warehouse.option.kits.info')) {
                        $options[] = $this->setOptions('warehouse6', 'Kits Info', 'pagebuilding','');
                    }    
                        
                   // $options[] = $this->setDivider($options);  
                    
                    $options[] = $this->setOptions('warehouse7', 'Picked up Process (Productivity)', 'pagebuilding','');
                    $options[] = $this->setOptions('warehouse8', 'Assembled', 'pagebuilding','');
                    $options[] = $this->setOptions('warehouse9', 'Kits Already Sealed Reports', 'pagebuilding','');
                    $options[] = $this->setOptions('warehouse10', 'Gaskets Already Sealed Reports', 'pagebuilding','');
                        
                        
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
                'warehouse' =>[
                    'permission'=>'menu.warehouse',
                            'id' =>'warehouse',
                         'label' =>'Warehouse Manufacturing'
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
     * This method add a list of Options($options) to a Menu with
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
     *
     */
    private function setDivider( $options ){
       return  (count($options)!=0) ? $this->setOptions('divider','','',''):null;
    } //END method: setDivider()
    
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
*  - $items = []; //its a associative array with all Menu and its options 
*  Menu: Home will be shown always, so you don't need to verify access indexes:
*      - id   : it identifies each one of the menu items
*      - label: This NAME matches with how users will watch the menu option  on the UI
*      - link : It identifies the ROUTE (defined on the module.config.php ) 
*              when the user click it on.
 *   
*/
      
    public function getMenuItems() {
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
        else {  //CREATE A DYNAMIC MENUS WITH THE OPTIONS GIVEN OR ASSIGNED TO EACH MENU          
          
           /* 
            * - getMainMenuPermissions(): 
            *   it returns an array with the menu and its permission associated by the Menu Role, 
            *   which will be tested against the RBAC           
            */
           
           $mainMenuPermissions = $this->getMainMenuPermissions();            
           //$mainMenu1 = ["management", "marketing", "mis", "purchasing", "quality",
           //             "manufacturing", "sales","receiving","warehourse","maintenance"]; 
                      
                
          /*
           *  Getting params
           *   
           *   - Label: It's a string that idenfifies ONE item of the Main Menu 
           *   - $menuOptions : It receives the items of menu will be rendered
           *  - Id, Label and $menuOptions are passed to the method:
           *    addOptionToMenuDropDown to be added to Menu Items            
          */
           
            //----- Menus will be added -----
            $mainMenu = [
                            "management", //"marketing", "mis", 
                            "purchasing", //"quality","manufacturing", 
                            "sales",
                            "receiving",
                            "warehouse", //"maintenance"
                            "admin",                            
                        ];
            
            // rendering the menus dynamically  
            foreach ($mainMenu as $moduleName) {
                //getting the permission associated to menu :(+menu.purchasing, +menu.admin etc)
                //echo $mainMenuPermissions[$moduleName]['permission']."<br>"; 
                $menuOptions = $this->addOptionsToMenu($mainMenuPermissions[$moduleName]['permission']);  
                 // $id (it's like a name of object
                $id = $mainMenuPermissions[$moduleName]['id'];           
                $label = $mainMenuPermissions[$moduleName]['label'];           
                $items[] = $this->setOptionsToMenu( $id, $label, $menuOptions );
            }
            
            /*
             *  Adding About Menu
             */
            $items[] = $this->setOptions('about', 'About', 'about','');              
             
            /*
             *  Adding Sing in Menu  
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
    }// END method: getMenuItems()
}


