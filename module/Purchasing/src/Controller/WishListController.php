<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* SERVICES TO BE USED*/
use Application\Service\QueryRecover as queryManager;
use Application\Service\PartNumberManager;
use Purchasing\Service\WishListManager;
use Application\ObjectValue\PartNumber;
use Purchasing\Form\WishListNewItemForm;


class WishListController extends AbstractActionController 
{
     /*
     * Service QueryRecover
     */
    private $queryManager;
    
    private $partNumberManager;
     
    /**------------- Class Methods -----------------*/ 
    
   /**
    * @param queryManager $queryRecover            - Service for running SQL
    * @param PartNumberManager $partNumberManager  - Service for recovering       
   */
   public function __construct( queryManager $queryRecover, PartNumberManager $partNumberManager ) 
   {   
      $this->queryManager= $queryRecover;      
      $this->partNumberManager = $partNumberManager;      
   }   
  
   
    /*
    * getting the logged user 
    */
   private function getUser()
   {
       $user = $this->currentUser();       
       //validating the user
       if ($user == null) {
           $this->getResponse()->setStatusCode(404);
           return;
       } 
       return $user;
   }//End: getUser()
      
   private function getWishListItem( $partNumberID ) 
   {
      /*
       * @var $parNumberObj \Application\ObjectValue\PartNumber
       */
      $partNumberObj =  $this->partNumberManager->getPartNumber( $partNumberID ) ?? null;

      if ( $partNumberObj !== null ) {         
         $data['code'] = $this->queryManager->getMax('WHLCODE', 'PRDWL');
         $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());
//       $data['user'] = $this->getUser()->getEmail();

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
   public function indexAction() 
   {            
       //create instance of the form
      $data = [];      
      $renderAllForm = false;
      $form = new WishListNewItemForm('initial', $this->queryManager ) ;
      
      //check if user has submitted the form

      if ($this->getRequest()->isPost()) {            
         /* getting DATA from the FORM where times quotes was selected */        
         $data = $this->params()->fromPost();  
         $form->setData( $data );

         if ($data['submit'] == 'SUBMIT') { //CHECKING IF ITS THE FIRT PARTNUMBER             

             if ( $form->isValid() ) { //validating PartNumber
                // retrieving all data associated to this partnumber 
                $dataItem = $this->getWishListItem( $data['partnumber'] ); 
                /* creating the form instance */
                $form = new WishListNewItemForm( 'entered', $this->queryManager ) ;
                $form->setData( $dataItem );                    
                $renderAllForm = true;
                $this->flashMessenger()->addSuccessMessage(
                             'Data of the part number are being recovered successfully.');
             } //END: isValid() checking 
             else {
               $this->flashMessenger()->addErrorMessage(
                             'Oops!!! Data of the part number COULD NOT BE loaded to update the Wish List. Check errors'); 
             }
         } else { // this occurr if data are ready for being inserted to wishlits ( ADD BUTTON )

            //creating an Object NewItem to wishlist with all fields associated 
            $form = new WishListNewItemForm( 'entered', $this->queryManager ) ;

            //fill the form with all data 
            $form->setData( $data );

            $renderAllForm = true;

            //checking if all data in the form are validated 
            if ($form->isValid()) {                  

                //getting back all filtered data  from the FORM Object                   
                $data = $form->getData(); 

                //INSERT DATA TO WISHLIST                   
                $WLManager = new WishListManager( $this->queryManager, $this->partNumberManager );
                $inserted = $WLManager->insert( $data );
                
                if ( $inserted ) {
                    $this->flashMessenger()->addSuccessMessage(
                             "The new part has been inserted into the Wish List: (".$data['code'].")");
                    return $this->redirect()->toRoute('wishlist', 
                                                       ['action'=>'index']);                          
                }//endif: inserted??  
            } else { //inserting validated
                   $this->flashMessenger()->addErrorMessage(
                                'Oops!!! Could not insert the new part number in the Wish List.');
            }
         }//endelse: SUBMITTED CHECK          
      } //endif: CHECK IF POST ()
      
      $WLManager = new WishListManager( $this->queryManager, $this->partNumberManager );
      
      $this->layout()->setTemplate('layout/layout_Grid');
      return new ViewModel([                          
                     'wishlist'      => $WLManager->TableAsHtml(),
                     'formNewItemWL' => $form,
                     'renderAll'     => $renderAllForm
            ]);
   }//END: indexAction method
    
   
   /* 
    * adding: Adding a new Item to WishList
    */
   public function addAction() 
   {
          
       
   }
    
} //END: LostsaleController

