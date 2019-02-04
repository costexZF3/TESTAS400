<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;


use Purchasing\ValueObject\LostSale;
use Purchasing\Form\LostSaleForm;

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
   public function indexAction() {
        //getting the loggin user object
        $user = self::getUser(); 
    
     //-- checking if the logged user has SPECIAL ACCESS: he/she would be an Prod. Specialist
        $especial = ($this->access('special.access'))?'TRUE':'FALSE';
       
        $changeData = true;

      //create LostSaleForm
        $form = new LostSaleForm();

        //check if user has submitted the form

        if ($this->getRequest()->isPost()) {
            //fill in the form with POST data        
            $data = $this->params()->fromPost();            
//            
//            $form->setData($data);
//            
//            if ($form->isValid()) {
//                $data = $form->getData(); //getting back data filter and validated 
//                echo "valid data";  
//            }
            $timesQuote = (int)$data['num-tq'];  //getting times quoted   
            $changeData =  ($data['oldtimesquoted']!= $timesQuote)?? false;
            
            if ($changeData) {
                $msg = 'Criteria for times Quote has chanced. Parts  with more than '.$timesQuote.' times quote are being shown';                      
               
            }
        } else {
           $msg = 'The shown data are based on the following criteria: TimesQuote: +100, Vendors Assigned: YES ';            
           //Initicial Value for TimesQuote 
           $timesQuote = 100;  
           
        }
         
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
       $LostSale = new LostSale( $this->conn, $timesQuote );
       $tableHTML = $LostSale->getGridAsHtml();
       $countItems = $LostSale->getCountItems();
       $timesQuote = $LostSale->getTimesQuoted(); 
       
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([
                            'form' => $form,         //HTML ELEMENTS: to render on the filter seccions
                     'tableHeader' => $tableHTML,                                                                    
                            'user' => $user,
                   'specialAccess' => $especial,
                      'countItems' => $countItems,
                     'timesquoted' => $timesQuote,
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

