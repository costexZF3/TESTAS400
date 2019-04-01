<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* services injected from the FACTORY: WishListControllerFactory */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;
use \Application\Validator\PartNumberValidator;


/* MODEL: forms classes */
use Purchasing\Form\FormAddItemWL as FormAddItemWL;


class WishlistController extends AbstractActionController 
{    
     /**
      * VARIABLE QueryManager is an instance of the service
      * @var WishListManager 
      */
     private $WLManager = null; 

     /**
      * @var queryManager
      */
      private $queryManager; 
     
      /**
      * @param WishListManager $wlManager  | WishList service injected from the Factory
      * @param QueryManager $queryManager  | WishList service injected from the Factory
      * 
      */
      public function __construct( WishListManager $wlManager, QueryManager $queryManager ) 
      {           
         $this->WLManager = $wlManager;   
         $this->queryManager = $queryManager;
      }   
    
      /*
      * getting the logged user 
      */
     private function getUser()
     {
         $user = $this->currentUser();       
         //validating the user
         if ($user == null) {
             $this->getResponse()->setStatusCode( 404 );
             return;
         } 
         return $user;
     }//End: getUser()
      
     
   
      /**
       *  The IndexAction show the WishList 
       */
      public function indexAction() 
      {  
        $this->layout()->setTemplate('layout/layout_Grid');
         
        return new ViewModel(
            [                          
              'wldata'      => $this->WLManager->TableAsHtml(),                                      
            ]
         );
      }//END: indexAction method

          
     /* 
     * adding: Adding a new Item to WishList
     */
      public function addAction()
      {     
            /* deciding whether loading data (it'll happen just in case the part exist in INMSTA )
             *  -in other case we need to create a new one 
             *    - constrainst:
             *        -this PARTNUMBER: $idPartNumber will be in CATER OR KOMAT ...lostsales files
             */ 

            //scenario 1  
            $form = new FormAddItemWL( 'initial', $this->queryManager );

            if ($this->getRequest()->isPost()) {  

                /* getting data from the form*/
                $data = $this->params()->fromPost();
                
                //checking which type or instance of ForAddItemWL we MUST CREATE
                $initial = $data['submit']=='SUBMIT';
                
                
                // if not INITIAL THEN an ADD button was pressed
                if ( !$initial ) {
                    $form = new FormAddItemWL( 'insert', $this->queryManager );
                }
                
                /* updating form from data entered in the form */
                $form->setData( $data ); 

               /* valid if not exit in WISHLIST, THEN I can insert it inside de PRDWL */ 
               if ( $initial && $form->isValid() ) {                   
                   //getting filtered data 
                   $data = $form->getData();   
                                    
                   $partnumber = $data['partnumber'];
                   
                   //defining SCENARIO 
                   //try to loading data ( it must already exist in IMNSTA )            
                   $partInINMSTA = $this->getValidator('INMSTA');                
                   $partInCATER = $this->getValidator('CATER');                
                   $partInKOMAT = $this->getValidator('KOMAT');                

                   $existInvetory = 
                        $partInINMSTA->isValid( $partnumber ) ||
                        $partInCATER->isValid( $partnumber ) || $partInKOMAT->isValid( $partnumber ); 
                   
                   /*check if the part will be added to the WishList exist at: INMSTA, CATER, or KOMAT
                    * - in case the partnumber does not exist, then change the Error Message and 
                    */
                   if ( !$existInvetory ) {
                       //show invalid part and it can be processed. Modifying the Message Error
                       $form->get('partnumber')->setMessages(['Oops!!! It does not Exist in our Inventory.']);     
                       return new ViewModel(['form' => $form ]);
                   }
                   
                    
                   /* checking existence in the differents tables */
                   //check it the part is in INMSTA ??
                   if ( $partInINMSTA->isValid( $partnumber ) ) {
                       //get data from the partnumber 
                       $data = $this->WLManager->getDataItem( $partnumber );       
                       
                       /* check if there was some error or the part does not exist in INMSTA */
                        if ( !isset($data['error']) ) {
                            $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());  

                            /* creating FORM for scenario 2 */
                            $form = new FormAddItemWL( 'insert', $this->queryManager );
                            $form->setData( $data );

                            return new ViewModel(['form' => $form ]);
                            /* the part number is already valided at this point */                
                        } //not error getting info from IMSTA
                   }

               } elseif ( $form->isValid() ) {
                    /* get data filtered*/
                   $data = $form->getData();
//                   var_dump( $data ); exit;
                   $inserted = $this->WLManager->insert( $data, WishListManager::FROM_MANUAL );
                   
                   if ( $inserted ) {
                       //update flashmessenger INSERTION WAS OK     
                       $this->flashMessenger()->addSuccessMessage(
                                "The new part has been successfuly inserted CODE: [".$data['code']."]");
                
                   } else {
                       $this->flashMessenger()->addErrorMessage(
                                   'Oops!!! Could not be inserted the new part number in the Wish List.');
                       
                   }
                       
                   
                   return $this->redirect()->toRoute('wishlist', ['action'=>'index'] );
                   
               }

            }//END: IF isPost()  

             return new ViewModel([
                 'form' => $form,
                 '' => '',
                 '' => '',
             ]);

      }//END: METHOD
      
      
      
    /* ==================================== PRIVATE FUNCTIONS ====================================*/  
      /**
       * @param string $table
       */
      private function getValidator( $table ) 
      {     
           $options = [
                 'table' => $table,
                 'queryManager' => $this->queryManager                                  
            ];
           
          $partValid = new \Application\Validator\PartNumberValidator( $options );
          
          return $partValid;
      }
    
} //END: LostsaleController

