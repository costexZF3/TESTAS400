<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ClaimsController extends AbstractActionController
{
   /**------------- Class Attributes -----------------*/ 
   /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
   
    
    /**------------- Class Methods -----------------*/ 
    
   // constructor for claimsController. It will be injected with the entityManager
   // with all Entities mapped
   public function __construct( $entityManager ){
       $this->entityManager = $entityManager;
   }   
   
   //getting the logged user 
   private function getUser(){
       $user = $this->currentUser();       
       //validating the user
       if ($user==null) {
           $this->getResponse()->setStatusCode(404);
           return;
       } 
       return $user;
   }//End: getUser()
      
   private function testPermission( string $permission){
       $user = self::getUser();       
        //------ inherited --------     
       $accessT = $this->access( $permission, ['user'=> $user]);
       return $accessT;
   }
   
   /** 
    * -$permission : it has the name of the permission will be test to the user     
    *  -$menuList: List of Items will be shown as Menu 
    *  -$newItem : It's the menu item might be added depending on the $user has the $permission
    */ 
   protected function createMenu($permission, $menuList, $newItem )
   {   // 
       $user = self::getUser(); 
       //init $rest with $menuList  
       $res = $menuList;
                  
       if (self::testPermission($permission)) {      
           $res = array_merge($menuList, $newItem);  
           
           //echo "$permission : true". "<br>";
       }
       else{
          //echo "$permission : false <br>";            
       }       
       //var_dump($res); echo "<br>";
       return $res;
   }//End: createMenu 
   
   
             
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction(){              
       //getting the loggin user object
       $user = self::getUser();
        /** 
         * the Menu could be loading automaticaly with the following format
         * [menu][permissionassociate]            
        **/
        // ---- Entry Level checking permissions ------
        $newItem = ['watch'=> 'Watch Reports',
                    'view' => 'View Profile'
        ];
        $menuList = self::createMenu('module.watch.document', [], $newItem );
            
       // ---- Regular Level checking permissions ----
        $newItem = ['export'=>'Export to Excel',
                    'print' =>'Print Document' 
        ];
        
        $menuList = self::createMenu('module.export.document',  $menuList, $newItem );
        
        //---- High level checking permissions
        $newItem = ['create'=>'Create Projects',
                    'update' =>'Update Documents'
        ];
        $menuList = self::createMenu('module.create.document', $menuList, $newItem );
        
        //---- High level checking permissions
        $newItem = ['delete'=>'Delete Project', ];
        $menuList = self::createMenu('module.delete.document',  $menuList, $newItem );
        
        //var_dump($menuList); exit;    
        
        /**------------- Creating the ViewModel will be rendered on the view Index.phtml -----------------
         *      return to the View the params needed for rendering on the index.phtml
         *      -$menuList : List of Menu Items will be show on the menu 
         *      - $user : User that has been logged in
         */ 
       
        return new ViewModel([
                'menuClaims'=> $menuList,
                'user' => $user,            
            ]);
    }//END: indexAction method
    
    //watch route 
    public function watchAction(){
        $flashText = 'Welcome to Watch Route';        
        $user = self::getUser();       
        
        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage( $flashText );
        
        return new ViewModel(['text'=>$flashText]);
        
    }//End: watchAction() method
    
} //END: ClaimsController
