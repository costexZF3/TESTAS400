<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;


use Purchasing\ValueObject\LostSale;


class LostsaleController extends AbstractActionController
{
   /**------------- Class Attributes -----------------*/ 
   /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /* DB2 connection */    
    private $conn; //it's the adapter
    //private $db;
    
    /**------------- Class Methods -----------------*/ 
    
   /* constructor for claimsController. It will be injected 
    * with the entityManager with all Entities mapped 
    * by dependency injection 
    */
   public function __construct( $entityManager, Adapter $adapter ){
       //entitymanager
       $this->entityManager = $entityManager;       
       $this->conn = $adapter;      
   }   
   
   /*
    * getting the logged user 
    */
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
        /*------ inherited --------  */  
       $accessT = $this->access( $permission, ['user'=> $user]);
       //var_dump($accessT); echo "";
       return $accessT;
   }
                   
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction(){ 

    $this->flashMessenger()->addInfoMessage('The shown data are based on the following criteria: TimesQuote: +100, Vendors Assigned: YES ');
         
    //getting the loggin user object
    $user = self::getUser();  
              
        //-- checking if the logged user has SPECIAL ACCESS: he/she would be an Prod. Specialist
       $especial = ($this->access('special.access'))?'TRUE':'FALSE';
       
       //Initicial Value for TimesQuote 
       $timesQuote = "50"; 
       
       /* LostSale: It's an Object that will be the core of LostSales  */
       $LostSale = new LostSale($this->conn, $timesQuote);
       
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */
       $resultSet = $LostSale->getDataSet(); 
       $tableHTML= $LostSale->getGridAsHtml();
       $countItems = $LostSale->getCountItems();
       
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([
                     'tableHeader' => $tableHTML,
                    'lostsalelist' => $resultSet,                                            
                            'user' => $user,
                   'specialAccess' => $especial,
                      'countItems' => $countItems,
                   
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

