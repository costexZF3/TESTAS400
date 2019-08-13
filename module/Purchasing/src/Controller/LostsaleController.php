<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;


use Purchasing\ValueObject\LostSale;
use Purchasing\Form\LostSaleForm;
use Purchasing\Form\FormLostsaleToWL;

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
        
        $msg = 'The shown data are based on the following criteria: TimesQuote: +100, Vendors Assigned: YES ';            
           //Initicial Value for TimesQuote 
        $timesQuote = 100; 
    
        //-- checking if the logged user has SPECIAL ACCESS: he/she would be an Prod. Specialist
        $especial = ($this->access('special.access'))?'true':'false';
                     
        $changeData = true;        
        $vndAssignedOptionSelected = 1;
        $hideVnds = [];

      //create LostSaleForm
        $form = new LostSaleForm();
        $formWL = new FormLostsaleToWL();

        //check if user has submitted the form

        if ($this->getRequest()->isPost()) {
            //checking if the button MOVE TO WL was pressed
            $dataToWL = $this->params()->fromPost();
            if ( isset($dataToWL['submitWL'])) {
               //looking for items select
               if (isset($dataToWL['checkall'])) {
                  //call the service WL and insert the WL
                  echo "inserting into WL....."; exit;
               } else {
                  //active flash messengers
                  $this->flashMessenger()->addErrorMessage('Please, select at least one item thatn you want to send into the Wish List');
                 
                   /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
                  $LostSale = new LostSale( $this->conn, $timesQuote, $vndAssignedOptionSelected );
                  $tableHTML = $LostSale->getGridAsHtml();     
                  $countItems = $LostSale->getCountItems();

                  $this->layout()->setTemplate('layout/layout_Grid');
                  return new ViewModel([
                                       'form' => $form,         //HTML ELEMENTS: to render on the filter seccions
                                       'formto' => $formWL,
                                'tableHeader' => $tableHTML,                                                                    
                                       'user' => $user,
                              'specialAccess' => $especial,
                                 'countItems' => $countItems,
                                'timesquoted' => $timesQuote,
                              'columnsToHide' => $hideVnds
                       ]);
               }
            }
            
            /* getting DATA from the FORM where times quotes was selected */        
            $data = $this->params()->fromPost();            

            $timesQuote = (int)$data['num-tq'];  //getting times quoted   
            /* getting: vendors assigned option: 1, 2, 3 */
            $vndAssignedOptionSelected = (int)$data['sel-vndassigned']; 
            /* if not vendor Assigned then decide with columns will be shown or not */
            $hideVnds = ( $vndAssignedOptionSelected==2 )?[8, 9,10]:[];
            
            /* assigning value seleted to the ListBox */
            $form->get('sel-vndassigned')->setValue( $vndAssignedOptionSelected );
           
            $changeData =  ($data['oldtimesquoted']!= $timesQuote)?? false;
            
            if ($changeData) {
                $msg = 'Criteria for times Quote has chanced. Parts  with more than '.$timesQuote.' times quote are being shown';
            }    
        } 

        
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
       $LostSale = new LostSale( $this->conn, $timesQuote, $vndAssignedOptionSelected );
       $tableHTML = $LostSale->getGridAsHtml();     
       $countItems = $LostSale->getCountItems();
       
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([
                            'form' => $form,         //HTML ELEMENTS: to render on the filter seccions
                            'formto' => $formWL,
                     'tableHeader' => $tableHTML,                                                                    
                            'user' => $user,
                   'specialAccess' => $especial,
                      'countItems' => $countItems,
                     'timesquoted' => $timesQuote,
                   'columnsToHide' => $hideVnds
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

