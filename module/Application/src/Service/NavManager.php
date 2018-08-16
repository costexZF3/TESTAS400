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
     * Constructs the service.
     */
    public function __construct($authService, $urlHelper, $rbacManager) 
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->rbacManager = $rbacManager;
    }
    
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
            
            /*
             ********************************************************************
             *  Determine which items must be displayed in Purchasing           *
             * ******************************************************************
             */            
            
            $purchasingDropdownItems = [];
            //call method for creating all menus dinamically
            
            if ($this->rbacManager->isGranted(null, 'menu.purchasing')) {
                $purchasingDropdownItems[] = [
                            'id' => 'claims',
                            'label' => 'Claims',
                            'link' => $url('claims',['action'=>'index'])
                        ];
              
                /* Menu Product Developments */
                $purchasingDropdownItems[] = [
                            'id' => 'productDevelopment',
                            'label' => 'Product Developments',
                            'link' => $url('pagebuilding')
                        ];
                /* Menu supplies */ 
                $purchasingDropdownItems[] = [
                            'id' => 'supplies',
                            'label' => 'Supplies',
                            'link' => $url('pagebuilding')
                        ];
                
                /* Comments, New Supplies/Others */        
                $purchasingDropdownItems[] = [
                            'id' => 'comments',
                            'label' => 'Comments, New Supplies/Others',
                            'link' => $url('pagebuilding')
                        ];
                 /* Sales Back Orders */        
                $purchasingDropdownItems[] = [
                            'id' => 'salesbackorders',
                            'label' => 'Sales Backorders',
                            'link' => $url('pagebuilding')
                        ];
                
                  /* Follow Backorders */        
                $purchasingDropdownItems[] = [
                            'id' => 'followbackorders',
                            'label' => 'Follow Backorders',
                            'link' => $url('pagebuilding')
                        ];
                
                  /* Part/Vendor Comments */        
                $purchasingDropdownItems[] = [
                            'id' => 'partvendorcomments',
                            'label' => 'Part/Vendor Comments',
                            'link' => $url('pagebuilding')
                        ];
                
                  /* Purchasing Quote */        
                $purchasingDropdownItems[] = [
                            'id' => 'partvendorcomments',
                            'label' => 'Purchasing Quote',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Suspended Parts*/        
                $purchasingDropdownItems[] = [
                            'id' => 'suspendedparts',
                            'label' => 'Suspended Parts',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Upload OEM Pictures */        
                $purchasingDropdownItems[] = [
                            'id' => 'uploadoempictures',
                            'label' => 'Upload OEM Pictures',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Email Vendors */        
                $purchasingDropdownItems[] = [
                            'id' => 'emailvendors',
                            'label' => 'Email Vendors',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Vendors Price List */        
                $purchasingDropdownItems[] = [
                            'id' => 'vendorspricelist',
                            'label' => 'Vendors Price List',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Print labels (Vendors)*/        
                $purchasingDropdownItems[] = [
                            'id' => 'printlabels',
                            'label' => 'Print Labels(Vendors)',
                            'link' => $url('pagebuilding')
                        ];
                
                 /* Change Pur. Agent / Person in charge  */        
                $purchasingDropdownItems[] = [
                            'id' => 'changeagentpersonincharge',
                            'label' => 'Change Pur. Agent/Person in charge',
                            'link' => $url('pagebuilding')
                        ];
                 /* Reports */        
                $purchasingDropdownItems[] = [
                            'id' => 'reports',
                            'label' => 'Reports',
                            'link' => $url('pagebuilding')
                        ];
                
                
            }//end: rbacManager->isGrandted(null, 'purchasing.menu'), CHECKS IF THE LOGIN USER HAS THE purchasin.menu PERMISSION
            
            
            //************ RENDERING PURCHASING dropDownItems WITH ALL OPTIONS *******************
            //checking whether purchasing's menu-items is different of 0 
            if (count($purchasingDropdownItems)!=0) {
                $items[] = [
                    'id' => 'purchasing', 
                    'label' => 'Puchasing',
                    'dropdown' => $purchasingDropdownItems
                ];
            }
            
             /*
             ********************************************************************
             *  Determine which items must be displayed in Admin dropDownList   *
             * ******************************************************************
             */            
            
            // ARRAY WITH ITEMS FOR ADMIN USERS
            $adminDropdownItems = [];
            
            if ($this->rbacManager->isGranted(null, 'user.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'users',
                            'label' => 'Manage Users',
                            'link' => $url('users')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'permissions',
                            'label' => 'Manage Permissions',
                            'link' => $url('permissions')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'role.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'roles',
                            'label' => 'Manage Roles',
                            'link' => $url('roles')
                        ];
            }
            //checking whether Admin's menu-items is different of 0 
            if (count($adminDropdownItems)!=0) {
                $items[] = [
                    'id' => 'admin',
                    'label' => 'Admin',
                    'dropdown' => $adminDropdownItems
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


