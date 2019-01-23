<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
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
    */
   public function __construct( $entityManager, Adapter $adapter ){
       //entitymanager
       $this->entityManager = $entityManager;
       
       $this->conn = $adapter; // by dependency injection
      // $this->db = new Sql($this->conn);
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
       $timesQuote = "100";       
       /*
        * LostSale: It's an Object that will be the core of LostSales
        */
       $LostSale = new LostSale($this->conn, $timesQuote);
       $resultSet = $LostSale->populateHtml(); 
       
       $this->layout()->setTemplate('layout/layoutLostSale');
      //  $this->layout()->setTemplate('layout/layout');
       return new ViewModel([
                    'lostsalelist' => $resultSet,                                            
                           'user'  => $user,
                   'specialAccess' => $especial,
            ]);
    }//END: indexAction method
} //END: LostsaleController
