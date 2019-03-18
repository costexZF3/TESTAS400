<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* SERVICES TO BE USED*/
use Application\Service\QueryRecover as queryManager;
use Application\Service\PartNumberManager;
use Application\ObjectValue\PartNumber;

use Purchasing\Form\WishListNewItemForm;

use Purchasing\Service\WishListManager;


class WishListController extends AbstractActionController {
     /*
     * Service QueryRecover
     */
    private $queryManager;
    
    private $partNumberManager;
     
    /**------------- Class Methods -----------------*/ 
    
   /* 
    * @var queryRecover queryManager
    * @var $partNumberManager PartNumberManager
    */
   public function __construct( queryManager $queryRecover, PartNumberManager $partNumberManager ) {   
      $this->queryManager= $queryRecover;      
      $this->partNumberManager = $partNumberManager;      
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
   
   /* this method returns the max code that will be insert in the WL */
   private function getMax( $code, $table ) {
      $strSql = "SELECT MAX(".$code.") as MAXCOD FROM ".$table;
        
      $data = $this->queryManager->runSql( $strSql );
      
      $result = $data[0]['MAXCOD']++; 
      
      return $result+1 ;
   }//END: getMax
   
   private function getWishListItem( $partNumberID ) {
      /*
       * @var $parNumberObj \Application\ObjectValue\PartNumber
       */
      $partNumberObj = ( $this->partNumberManager->getPartNumber( $partNumberID ) )?? NULL;

      if ( $partNumberObj != NULL ) { 
         $data['code'] = $this->getMax('PRWCOD', 'PRDWL');
//         $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());
         $data['user'] = $this->getUser()->getEmail();

         $data['date'] = date('Y-m-d');
         /* - if the partNumber exist NOT NULL then return it back
          *   other case it returns UNKNOW string*/

         $data['partnumber'] = $partNumberObj->getId(); 

         $data['partnumberdesc']= $partNumberObj->getDescription();
         
         $data['vendor'] = $partNumberObj->getVendor();
         $data['vendordesc'] = $partNumberObj->getVendorDescription(); 
         
         $data['tqLastYear'] = $partNumberObj->getQtyQuotedLastYear();
         
         $data['comment'] = '';
         $data['type'] = '';
                  
         return $data;
      }
      
      // return 'unknow' if the PartNumber not exits in the DATABASE 
      return $data['unknow'] = true;    
      
   } //END: getWishListItem()
   
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction() {            
      //create instance of the form
      $data = [];      
      $renderAllForm = false;
      //check if user has submitted the form

      if ($this->getRequest()->isPost()) {
            
            /* getting DATA from the FORM where times quotes was selected */        
            $data = $this->params()->fromPost();    
            
            if ($data['submit']=='SUBMIT') { //CHECKING IF ITS THE FIRT PARTNUMBER
                $form = new WishListNewItemForm('initial', $this->queryManager ) ;
                $form->setData($data);
                
                if ($form->isValid()) { //validating PartNumber
                   // retrieving all data associated to this partnumber 
                   $dataItem = $this->getWishListItem( $data['partnumber'] ); 
                   /* creating the form instance */
                   $form = new WishListNewItemForm( 'entered', $this->queryManager ) ;
                   $form->setData( $dataItem );                    
                   $renderAllForm = true;
                }//END: isValid() checking 
            }//END: IF WITH SUBMIT 
            
            else {  //IT's trying to insert data to wishlits
                
               $form = new WishListNewItemForm('entered', $this->queryManager ) ;
               //fill the form with all data 
               $form->setData($data);
                
                $renderAllForm = true;
               //check valid data
                if ($form->isValid()) {
                   //getting back all filtered data 
                   
                   $data = $form->getData(); 
                   //INSERT DATA TO WISHLIST
                   echo "VALUE OF COMMENT---".$data['comment'];
                   print_r($data);
                }
               
            }//END: IF ELSE (TRUE: ADD TO WISHLIST
           
        } else {
           $form = new WishListNewItemForm( 'initial', $this->queryManager ) ;
           $renderAllForm = false;
        } //END: if post()
      
      
      $WLManager = new WishListManager( $this->queryManager, $this->partNumberManager );              
      $this->layout()->setTemplate('layout/layout_Grid');
      return new ViewModel([                          
//                   'wishlist' => $WLManager,    //rendering on the phtml
                     'wishlist' => $WLManager->TableAsHtml(),
                     'formNewItemWL' => $form,
                     'renderAll'  => $renderAllForm
            ]);
   }//END: indexAction method
    
   
   /* 
    * adding: Adding a new Item to WishList
    */
   public function addAction() {
       
   }
    
} //END: LostsaleController

