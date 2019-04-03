<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* services injected from the FACTORY: WishListControllerFactory */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;
use Application\Validator\PartNumberValidator;

use Zend\Session\Container as SM;

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
       *
       * @var Zend\Session\Container
       */
      private $session;
     
      /**
      * @param WishListManager $wlManager  | WishList service injected from the Factory
      * @param QueryManager $queryManager  | WishList service injected from the Factory
      * 
      */
      public function __construct( WishListManager $wlManager, 
                                   QueryManager $queryManager,
                                   SM $sessionManager ) 
      {           
         $this->WLManager = $wlManager;   
         $this->queryManager = $queryManager;
         $this->session = $sessionManager;
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
         
        return new ViewModel(['wldata' => $this->WLManager->TableAsHtml()]);
      }//END: indexAction method

        
    public function createAction() 
    {   
        //creating an instance of form to Map data
        $form = new FormAddItemWL( 'create', $this->queryManager );
        
        if ($this->getRequest()->isPost() ) {
            /* getting data from the form*/
            $data = $this->params()->fromPost();
            
            $form->setData($data);
            
            //var_dump($data); exit;
            //all data are ok then they are ready for being inserted
//            if ($form->isValid()) {
            if (5==5) {
//               $data = $form->getData(); //retrieving filtered data
               $this->WLManager->insert($data, WishListManager::FROM_MANUAL );
               $this->session->part = null;
               $this->redirect()->toRoute('wishlist');               
            }
        }
        
        $partnumber = $this->session->part ?? null; 
        //var_dump($partnumber);
        
        //RETRIEVING PARTNUMBER FROM ROUTE: 
//        $partnumber = $this->params()->fromRoute('id',-1);        
        if ($partnumber==null) {
            $this->redirect()->toRoute('wishlist');
        }
                 
            
        //recover data from CATER
        $data = $this->getCATER( $partnumber );               
        $minors = $this->getMinors();

        $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());
        $data['date'] = date('Y-m-d');
        $data['code'] = $this->queryManager->getMax('WHLCODE', 'PRDWL');

        //injecting all minor codes into the HTML SELECT ELEMENT
        $form->get('minor')->setValueOptions($minors);

        $form->setData($data);
        
        return new ViewModel(['form'=>$form]);
        
    }//END: METHOD create
    
    
    /* 
    * adding: Adding a new Item to WishList
    */
    public function addAction()
    {     
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
               //attemp for loading data from files( it must already exist in IMNSTA, CATER or KOMAT )            
               $partInINMSTA = $this->getValidator('INMSTA');                
               $partInCATER = $this->getValidator('CATER');                
               $partInKOMAT = $this->getValidator('KOMAT');                

               /*checking if the part exist in: INMSTA, CATER, or KOMAT
                * - in case the partnumber does not exist, then change the Error Message and 
                * show it.
                */
               $existInvetory = 
                    $partInINMSTA->isValid( $partnumber ) ||
                    $partInCATER->isValid( $partnumber ) || $partInKOMAT->isValid( $partnumber ); 

               
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

                    } //not error getting info from IMSTA
               }// END: THE PART IS INSIDE INMSTA 
               
               if ( $partInCATER->isValid( $partnumber ) ) {
//                   $url1 = $this->url()->fromRoute('wishlist', ['action'=>'create', 'id'=>$partnumber]);
//                   return $this->redirect()->toUrl($url1);
                   $this->session->part = $partnumber;       
                  return $this->redirect()->toRoute('wishlist', ['action'=>'create']); 
//                  return $this->redirect()->toRoute('wishlist', ['action'=>'create', 'id'=>$partnumber]); 
               }
               
               /* if the part number exist on CATER or KAMAT, then create part */



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
      
      
      /* getting MINOR, AND CAT AND SUBCAT ASSOCIATED */
    private function getMinors() 
    {
        $sqlStr = "SELECT CNTDE1, CNMCOD, CNMCAT, CNMSBC FROM CNTRLM";
        $data = $this->queryManager->runSql($sqlStr);        
        if ($data !==null ) {
            
            for ( $i=0; $i<count($data); $i++) {  
               
               $result[ $data[$i]['CNMCOD'] ] = $data[$i]['CNMCOD']; 
//               $result[ $data[$i]['CNMCOD'] ] = $data[$i]['CNTDE1']; 
//               $result[ $data[$i]['CNMCOD'] ] = $data[$i]['CNMCAT'];
//               $result[ $data[$i]['CNMCOD'] ]= $data[$i]['CNMSBC']; 
           
            }
        } 
        
        return $result;
    }
      /**
       * THIS METHOD RETRIEVE DESCRIPTION OF THE PARTNUMBER AND RETURN MAJOR, MINOR, CATEGORY AND SUBCATEGORY 
       * @param string $partnumber
       * @return array Description
       */
    private function getCATER( $partnumber ): array 
    {
        $sqlStr = "SELECT * FROM CATER WHERE CATPTN = '".$partnumber."'";
        $data = $this->queryManager->runSql( $sqlStr );
        
        if ($data !==null ) {
            $result['partnumber'] = $partnumber;
            $result['partnumberdesc'] = $data[0]['CATDSC'];
            $result['price'] = $data[0]['CATPRC'];
        }
        
        return $result;
    }//END: METHOD getCATER()
    
} //END: LostsaleController

