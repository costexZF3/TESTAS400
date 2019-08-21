<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;


use Purchasing\ValueObject\LostSale;
use Purchasing\Form\LostSaleForm;
use Purchasing\Form\FormLostsaleToWL;
use Purchasing\Service\WishListManager as WLM;

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
    
    private $WLManager; 
    
    /**------------- Class Methods -----------------*/ 
    
   /* constructor for claimsController. It will be injected 
    * with the entityManager with all Entities mapped 
    * by dependency injection 
    */
   public function __construct( $entityManager, Adapter $adapter, WLM $wishListManager ){
       //entitymanager
       $this->entityManager = $entityManager;       
       $this->conn = $adapter; 
       $this->WLManager = $wishListManager;
       
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
   
   /**
   * 
   * @return string | It returns the short form of the user
   */
  private function getUserS() 
  {
      $strUser = str_replace('@costex.com', '', $this->getUser()->getEmail());
      return strtoupper($strUser);
  }

      
   private function testPermission( string $permission){
       $user = self::getUser();       
        /*------ inherited --------  */  
       $accessT = $this->access( $permission, ['user'=> $user]);
       //var_dump($accessT); echo "";
       return $accessT;
   }
     
   /**
    * This method calls prepares data will be sent to WL across the 
    * Service Wishlist Manager
    * 
    * @param array $data
    * @return type
    */
   private function insertToWL( $data ) 
   {
      foreach ($data as $item ) {         
         $data1['code'] = $this->WLManager->nextIndex();
         $data1['user'] = trim($this->getUserS());
         $data1['partnumber'] = trim($item);         
         $data1['type'] = WLM::NEWVENDOR;        
         $data1['from'] = WLM::FROM_LOSTSALE;
         $data1['comment'] = 'FROM LOST SALE';
//         var_dump( $data1 ); echo"<br>";
         $inserted = $this->WLManager->insert($data1);
      }
      return $inserted != null; 
   }//End: method() insertToWL()
                  
                  
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
        $especial = ($this->access('special.access'))? 'true' : 'false';
                     
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
               if (isset($dataToWL['checkbox'])) {
                  //call the service WL and insert the WL
                  $inserted = $this->insertToWL( $dataToWL['checkbox'] );
                  
                  $arrayTostr = 'Inserted Items: ['.implode("]--[" , $dataToWL['checkbox']).']';
//                  var_dump($arrayTostr); exit;
                  $this->flashMessenger()->addErrorMessage('The following items have been inserted into the WishList successfully');
                  $this->flashMessenger()->addErrorMessage( $arrayTostr );
                  $this->redirect()->toRoute('wishlist'); 
                                   
               } else {
                  //active flash messengers
                  $this->flashMessenger()->addErrorMessage('Please, select at least one item that you want to send into the Wish List');
                  $this->redirect()->toRoute('lostsales');
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
            }//end if submit
            
            /* getting DATA from the FORM where times quotes was selected */        
            $data = $this->params()->fromPost();            

            $timesQuote = (int)$data['num-tq'];  //getting times quoted   
            /* getting: vendors assigned option: 1, 2, 3 */
            $vndAssignedOptionSelected = (int)$data['sel-vndassigned']; 
            /* if not vendor Assigned then decide with columns will be shown or not */
            $hideVnds = ( $vndAssignedOptionSelected==2 ) ? [8, 9,10]:[];
            
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

