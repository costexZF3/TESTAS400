<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* services injected from the FACTORY: WishListControllerFactory */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;
//use Application\Validator\PartNumberValidator;

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

    
    /**
     *  THIS METHOD USE THE WISHLIST MANAGER TO INSERT A LIST OF ITEMS
     * @param array $data | this method calls to the insert method of the WishList Manager 
     */  
    private function insert( $data ) 
    {
        $inserted = $this->WLManager->insert($data );
               
        if ( $inserted ) {
            //update flashmessenger INSERTION WAS OK     
            $this->flashMessenger()->addSuccessMessage(
                     "The new part has been successfuly inserted CODE: [".$data['code']."]");
            
            $this->session->part = null;
            $this->redirect()->toRoute('wishlist');

        } else {
            $this->flashMessenger()->addErrorMessage(
                        'Oops!!! Could not be inserted the new part number in the Wish List.');

        }        
    }//END METHOD: insert();   
    
    public function createAction() 
    {   
        //creating an instance of form to Map data
        $form = new FormAddItemWL( 'create', $this->queryManager );
        
        if ($this->getRequest()->isPost() ) {
            /* getting data from the form*/
            $data = $this->params()->fromPost();
            
            $form->setData( $data );            
            
            //all data are ok then they are ready for being inserted
            if ($form->isValid()) {           
               $data = $form->getData(); //retrieving filtered data
               
               //IT'S TIME TO UPDATE CATEGORY AND SUBCATEGORY FROM SESSION 
               $data['category'] = $this->session->category[$data['minor']];
               $data['subcategory'] = $this->session->subcategory[$data['minor']];
               $data['from'] = WishListManager::FROM_MANUAL;
              
               $this->insert( $data );
                              
            } else { //it was an error
                if ( $form->get('major')->getMessages() !== null ) {
                   $form->get('major')->setMessages(['Invalid code!']); 
                }
            }
            
        }//ENDIF: getting data by POST
        
        $partnumber = $this->session->part; 
                
        //RETRIEVING PARTNUMBER FROM ROUTE: 
//      $partnumber = $this->params()->fromRoute('id',-1);        
        if ($partnumber == null) {
            $this->redirect()->toRoute('wishlist');
        }
            
        //recover data from FILE (CATER or KOMAT). Manipulate it across session variables
        $data = $this->getInfo( $partnumber );               
        $minors = $this->getMinors();

        $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());
        $data['date'] = date('Y-m-d');
        $data['code'] = $this->queryManager->getMax('WHLCODE', 'PRDWL');

        //injecting all minor codes into the HTML SELECT ELEMENT
        $form->get('minor')->setValueOptions( $minors );        
        
        //updating the $form data before show it.
        $form->setData( $data );
        
        return new ViewModel(['form'=>$form]);
        
    }//END: METHOD create
    
    
    /* 
    * adding: Adding a new Item to WishList
    */
    public function addAction()
    {     
        //scenario 1  
        $form = new FormAddItemWL( 'initial', $this->queryManager );

        //checking it the request was by POST()
        if ($this->getRequest()->isPost()) {  

            /* getting data from the form*/
            $data = $this->params()->fromPost();

            //checking which type or instance of ForAddItemWL we MUST CREATE
            $initial = $data['submit']=='SUBMIT';

            
            // if not INITIAL THEN an ADD button was pressed
            if ( !$initial ) {                
                $form = new FormAddItemWL( 'insert', $this->queryManager );
            }

            /* updating form from data entered in the form 
             * BECAUSE we need to VALIDATE data BEFORE use them
             */
            $form->setData( $data ); 
             
           /* valid if not exit in WISHLIST, THEN I can insert it inside de PRDWL */ 
           if ( $initial && $form->isValid() ) {                   
               //getting filtered data 
               $data = $form->getData();   

               $partnumber = $data['partnumber'];

               //DETERMINING A SCENARIO 
               //attemp for loading data from files( it must already exist in IMNSTA, CATER or KOMAT )            
               $partInINMSTA = $this->getValidator('INMSTA');                
               $partInCATER = $this->getValidator('CATER');                
               $partInKOMAT = $this->getValidator('KOMAT');                

               /*checking if the PARTNUMBER is part of : INMSTA, CATER, or KOMAT
                * - in case the partnumber does not exist, then change the Error Message and 
                * show it.
                */
               $existInvetory = $partInINMSTA->isValid( $partnumber ) ||
                    $partInCATER->isValid( $partnumber ) || $partInKOMAT->isValid( $partnumber ); 


               if ( !$existInvetory ) {
                   //Modifying the Error MESSAGE: partnumber (form ELEMENT)
                   $form->get('partnumber')->setMessages(['Oops!!! It does not Exist in our Inventory.']);     

                   return new ViewModel(['form' => $form ]);
               }

               /* checking existence in the differents tables 
                * 
                * SCENARIO 2
                * check it the part is in INMSTA ??
                */
               if ( $partInINMSTA->isValid($partnumber) ) {

                   //get data from the partnumber 
                   $data = $this->WLManager->getDataItem( $partnumber );       

                   /* check if there was some error or the part does not exist in INMSTA */
                    if ( !isset($data['error']) ) {
                        $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());  

                        /* creating FORM for scenario 2 */
                        $form = new FormAddItemWL( 'insert', $this->queryManager );
                        $form->setData( $data );

                        return new ViewModel(['form' => $form, 'renderAll'=> true ]);

                    } //not error getting info from IMSTA
               }// END: THE PART IS INSIDE INMSTA 
               
               // SCENARIO 3 CATER OR KOMAT
               if ( $partInCATER->isValid( $partnumber ) ) {
                   //saving the PARTNUMBER into the session variable: part
                   $this->session->part = $partnumber;       
                   $this->session->table = 'CATER';
                   
                   return $this->redirect()->toRoute('wishlist', ['action'=>'create']);                       
               }         
               
               /* if the part number exist on CATER or KAMAT, then create part */
               if ( $partInKOMAT->isValid( $partnumber ) ) {
                   //saving the PARTNUMBER into the session variable: part
                   $this->session->part = $partnumber;       
                   $this->session->table = 'KOMAT';                   
                   
                   return $this->redirect()->toRoute('wishlist', ['action'=>'create']);                       
               }

           } elseif ( $form->isValid() ) {
                /* get data filtered*/
               $data = $form->getData();
               //updating the from field
               $data['from'] = WishListManager::FROM_MANUAL;
               
               //inserting THE DATA OF THE FORM INTO WISHLIST
               $this->insert( $data );           
           }//END ELSEIF
        }//END: IF isPost()  

        return new ViewModel([
             'form' => $form,            
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
               
//               $result[ $data[$i]['CNMCOD'] ] = $data[$i]['CNMCOD']; 
               $result[$data[$i]['CNMCOD']] = $data[$i]['CNMCOD']; 
               $category[$data[$i]['CNMCOD']] = $data[$i]['CNMCAT']; 
               $subcategory[$data[$i]['CNMCOD']] = $data[$i]['CNMSBC'];            
            }
        } 
        
        $this->session->category = $category;
        $this->session->subcategory = $subcategory;
        
        return $result;
    }
      /**
       * THIS METHOD RETRIEVE DESCRIPTION OF THE PARTNUMBER AND RETURN MAJOR, MINOR, CATEGORY AND SUBCATEGORY 
       * @param string $partnumber
       * @return array Description
       */
    private function getInfo( $partnumber ): array 
    {
        //retrieving CATER or KOTAM from the session 
        $table = $this->session->table;
                          
        $field = $table == 'CATER' ? 'CATPTN' : 'KOPTNO';
        $decriptionField = $table == 'CATER' ? 'CATDSC' : 'KODESC';
        $priceField = $table == 'CATER' ? 'CATPRC' : 'KOPRIC';
        
        $sqlStr = "SELECT * FROM ".$table." WHERE ".$field." = '".strtoupper( $partnumber )."'";
        $data = $this->queryManager->runSql( $sqlStr );
      
        if ($data !== null ) {
            $result['partnumber'] = $partnumber;
            $result['partnumberdesc'] = $data[0][$decriptionField];
            $result['price'] = round($data[0][$priceField], 2);
        }
//        var_dump($result);
        return $result;
    }//END: METHOD getInfo()
    
} //END: LostsaleController

