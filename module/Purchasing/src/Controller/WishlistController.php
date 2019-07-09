<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SM;

/* services injected from the FACTORY: WishListControllerFactory */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;


/* MODEL: forms classes */
use Purchasing\Form\FormAddItemWL as FormAddItemWL;
use Purchasing\Form\FormUpload; 
use Purchasing\Form\FormUpdate; 
use Purchasing\Form\FormUpdateMultiple; 
use Purchasing\Form\FormValidator;
use Purchasing\Form\FormWishList;


class WishlistController extends AbstractActionController 
{    
     /**
      * VARIABLE QueryManager is an instance of the service
      * @var WishListManager 
      */
     private $wlManager = null; 

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
         $this->wlManager = $wlManager;   
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
     
   /************************************ for updating items in WL ******************************  
    
    /**
     *  This method save on the session variable an list of data
     * 
     * @param array() $data
     */
    private function saveIntoSession( $data )
    {
        $this->session->data = $data;
    }
    
    /**
     *  This method compares $data, with data in the session and returns if was some 
     *  differences between the new data and the save them into the session
     * 
     * @param array() $data
     * @return type
     */    
    private function changedData( $data )
    {                
        $result = array_diff( $data, $this->session->data );
        return  $result;
    }


    /**
     * This method returns the ViewModel(updatemultiple.phtml)
     *  - this returns an instance of the form 
     * 
     * @return ViewModel    
     */
    public function updatemultipleAction() 
    {       
        $form = new FormUpdateMultiple( $this->wlManager );
        
        //checking if was clicked 
        if( $this->getRequest()->isPost() ) {
            //retrieving user and status new $data['user], $data['status']
            $data = $this->params()->fromPost();
            
            //if not want update nothing redirect to whislit()
            $continue = $data['status'] != 'NA' ||  $data['name'] != 'NA';
            
            if ( !$continue ) {
                $this->flashMessenger()->addErrorMessage('Oopss!!!. It seems you did not SELECTED a user neither a new status.');
                return $this->redirect()->toRoute('wishlist');
            }
            //retrieving the code of parts (rows) selected which were saved into 
            // the session->data (session variable)
            $data['records'] = $this->session->data;
              
            $this->wlManager->update( $data, true );
            
            $mes = implode(', ',$data['records'] );
            $this->flashMessenger()->addInfoMessage('The parts were updated successfully : ['.$mes.']');
            return $this->redirect()->toRoute('wishlist');
        }
        
        return new ViewModel(
        [
            'form' => $form,
        ]);        
    }//END updatemultipleAction()
    
 
    /**
     * This method returns a string with the logged in user's role
     * 
     * @return string
     */
    private function getRoleInWl() 
    {
        $isWLO = $this->access('purchasing.wl.owner') ? 'WLO' : null;    
        $isPS = $this->access('purchasing.ps') ? 'PS' : null;    
        $isPA = $this->access('purchasing.pa') ? 'PA' : null;    
        $isWLDoc = $this->access('purchasing.wl.documentator') ? 'WLDOC' : null;    
        
        return $isWLO ?? $isPS ?? $isPA ?? $isWLDoc ?? null;
    }// END: getRoleINWl()
    
    /**
     * This method (Action) update status and comment of one part inside the WL
     * -used in the FormUpdate
     */
    public function updateAction()
    {   
        $WL = $this->wlManager;
    
        $userRole = $this->getRoleInWL();
        
        $isHighLevel = $userRole == 'WLO';
        
        $scenario = $userRole;
              
        //checking if the data are coming from FormUpdate
        if( $this->getRequest()->isPost() ) {            
            $raw = $this->params()->fromPost(); 
            $form = new FormUpdate( $scenario, $this->wlManager );
            $form->setData($raw);
                    
            $dat = $form->isValid()? $form->getData():null;
            
            //checking multiple selection for updating assigned users and status
            $multipleUpdate = isset($raw['checkedrow']);
            
            //checking if the parts need to be updated in a massive way
            if ($multipleUpdate) {
                $this->saveIntoSession( $raw['checkedrow'] );
                return $this->redirect()->toRoute('wishlist', ['action' => 'updatemultiple']);                
            } else if (!isset($raw['partnumber'])){               
               $this->flashMessenger()->addErrorMessage('Oopss!!!. It seems you did not check at least one item.');
               return $this->redirect()->toRoute('wishlist');
            }
           
            //in case the user is updating one by one
            $rawData = $WL->parseData( $raw );
            
            $status = $rawData['status'];
                       
            $canChangeStatus = $WL->changeStatus($this->session->InitialStatus, $status);
           
            //checking if the new status can be acepted
            if ( !$canChangeStatus ) {
               $form->setData($dat);
                $status = $this->session->InitialStatus;
               
                $form->get('status')->setMessages(['Invalid State. See the graph and select the a right state.']);
                return new ViewModel(
                        [ 'form' => $form, 'status' => $status ]);
            }
            
            //***************************** checkinggg if I can change de STATUS ***********
            $data = $this->changedData( $rawData );
            $needUpdate = $data !=[] && $data['name']!= 'NOCHANGE';
              
            if ( $needUpdate ) {
                $data['WHLCODE'] = $this->session->id;
                //using de SERVICE for update by Code
                $WL->update( $data );                
                
                $this->flashMessenger()->addInfoMessage('The part number '.$rawData["partnumber"].' with COD:['. $this->session->id. '] was updated ');
                
            } else {
                $this->flashMessenger()->addErrorMessage("Oopss!! You did not change any data of the selected partnumber.");               
            }
            
            return $this->redirect()->toRoute('wishlist');
            
        } else {
            //getting the 'id' param, for checking this later
            $id = (string)$this->params()->fromRoute('id','');            
            $this->session->id = $id;
            
            //If the user Log in is not a Pa or PS then
            if ($id==='' && !$isHighLevel) {
                $this->getResponse()->setStatusCode( 404 );
                return;
            }
            
            // creating Form template
            $form = new FormUpdate( $scenario, $WL );
                
            // getting information from the part
            // Retrieving the row with id received 
            $row = $WL->getDataFromWL( $id );
            $data = $WL->parseData( $row );     
            $status = $data['status'];            
            $data['name'] = trim($data['name']);
            //saving initial status
            $this->session->InitialStatus = $status;
                       
            //updating data to show on the form will be updated
            $form->setData( $data );  
            
            $assignedUser = trim($data['name']);
            
            $form->get('name')->setValue( $assignedUser );
                        
            $this->saveIntoSession( $data ); //updating session for compated data and not update if there is no change
                
        } //end of the else
       
        return new ViewModel(
      [
          'form' => $form,
          'status' => $status
      ]);
 
    }//END: updateAction()
    
    private function createButtonsOnLayout()
    {        
        $buttonADD = [
            'label' => 'new item',
            'title' => 'add item',
            'class' => 'boxed-btn-layout btn-rounded',
            'font-icon' => 'fa fa-plus fa-1x',
            'url' => [                          
                'route'=>'wishlist', 
                'action'=>['action'=>'add'],                            
            ],
        ];
        
        $buttonEXCEL = [
            'label' => 'import',
            'title' => 'import itesms from excel',
            'class' => 'boxed-btn-layout btn-rounded',
            'font-icon' => 'fa fa-file-excel-o fa-1x',
            'url' => [                          
                'route'=>'wishlist', 
                'action'=>['action'=>'upload'],                            
            ],
        ];            
        
        $buttonList = [];
        array_push($buttonList, $buttonADD);
        array_push($buttonList, $buttonEXCEL);
        
        return $buttonList;
    }
    
    /**
     *  The IndexAction show the WishList taking into account
     *  the logged in user's access permissions
     * 
     *  -The users with the permissions: (they are the only users with access to this module)
     *    -purchasing.wl.owner, -purchasing.ps, purchasing.pa, purchasing.wl.documentator
     */
    public function indexAction() 
    {   
        $user= $this->getUser();
        $isDocumentator = $this->access('purchasing.wl.documentator');
      
        
        $form = new FormWishList();
        
        $this->layout()->setTemplate('layout/layout_Grid');
        unset($this->layout()->buttons);  
        $this->layout()->form = null;
        
        $isWLOwner = $this->access('purchasing.wl.owner'); 
        
        if ($isWLOwner) {            
            $this->layout()->buttons = $this->createButtonsOnLayout();             
        } else {
            //getting user for loading only its items assigned           
            $userN = ($isDocumentator == false) ? $this->getUserS(): 'DOCUMENTATOR';
            $this->wlManager->renewWL( $userN );                   
        }
        
        //this checks if there was generated some insconsistency trying to import from
        // an excel file some new items to the WishList
        $linkInc = $this->session->inconsistency ?? false;
               
        // renew WL with the logged in user's assigned parts
        
        //$re = $this->wlManager->jsonResponse();
        
        return new ViewModel(
            [
                'wldata' => $this->wlManager->TableAsHtml(),
                'urlInc' => $linkInc, //$urlinc  
                'isWLOwner' => $isWLOwner,
                'documentator' => $isDocumentator,
                'user' => $user,
                'form' => $form
            ]);
    }//END: indexAction method
    
 
    /**
     * THIS METHOD IS AND ACTION upload defined in the module.config.php
     * @return ViewModel
     */
    public function uploadAction() 
    {
        $form = new FormUpload( $this->queryManager );
       
        if( $this->getRequest()->isPost() ) {
            
            // Make certain to merge the files info!
            $request = $this->getRequest();
            
            /* you should remember three things: 1) merge $_POST and $_FILES super-global arrays 
               before you pass them to the form's setData() method; 2) use isValid() form's method 
                to check uploaded files for correctness (run validators); 3) use getData() form's method 
                to run file filters. */            
            $data = array_merge_recursive(
                $request->getPost()->toArray(),  //getting data from Post of Elements
                $request->getFiles()->toArray()  //getting data from Files Elements (like: name of the file, etc )
            );
            
            // Pass data to form.
            $form->setData($data);
            
            // Execute file validators. INVERSE TO THE NORMAL PROCESS 
            if ($form->isValid()) {                                
                $data = $form->getData();
                
                $inputFileName = $data['file']['tmp_name']; //recovering from the route defined
                
                //reading from file
                $sheetData = $this->wlManager->readExcelFile( $inputFileName );
                
                //result['valid'] : data ready for updating  and result['invalid'] : inconsistency data
                if (!empty( $sheetData )) {
                    $result = $this->parseExcelFile( $sheetData );
                } 
                
                $existPartsToInsert = !empty( $result['valid'] ); // are there some Parts ready for being inserted?
                $existInc = !empty($result['novalid']); // are there inconsistencies???
                
                if ( $existPartsToInsert ) {
                    $inserted = $this->insertValid( $result['valid'] );
                    
                    if ( $inserted ) {                        
                        $this->flashMessenger()->addInfoMessage("The PARTS from file: [".$data['file']['name']."] were uploaded.[ ".count($result['valid'])." ] item(s) was(were) uploaded.");
                    } else {
                        $this->flashMessenger()->addErrorMessage("Oopss!! Error trying uploading files.");
                    }
                }
                
                /* CREATE INCONSISTENCY EXCEL  */
                if ( $existInc ) {
                    $this->wlManager->writeErrorsToExcel( $result['novalid'] );
                    $this->session->inconsistency = true;
                    
                    $urlInc = './data/upload/wishlist_inc.xls';  
                    $message = !$existPartsToInsert ? 'No PARTS were uploaded' : '';
                    $this->flashMessenger()->addErrorMessage("Oopss!!! [".count($result['novalid'])."] inconsistencies were found. ". $message);                            
                }
               
                $options = ['urlinc'=> $urlInc];
                return $this->redirect()->toRoute('wishlist', ['action'=>'index'], $options, $options);          
                                
            } else {
                 $form->get('file')->setMessages(['Oops!!! Invalid file format']); 
            }//ENDIF isValid the File???
    
        }//ENDIF checking isPost()  
        
        // Render the page.
        return new ViewModel([
                 'form' => $form
            ]);       
    }//END METHOD: 
    
    
    public function createAction() 
    {   
        $this->session->fromExcel = false;
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
       //$table = $this->session->table;
             
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
            $data = $this->params()->fromPost();

            //checking which type or instance of ForAddItemWL we MUST CREATE
            $initial = $data['submit']=='SUBMIT';
            
            // if not INITIAL THEN an ADD button was pressed
            if ( !$initial ) {                
                $form = new FormAddItemWL( 'insert', $this->queryManager );
            }

            /* it's a MUST BEFORE validating */
            $form->setData( $data ); 
             
           /* valid if not exit in WISHLIST, THEN I can insert it inside de PRDWL */ 
           if ( $initial && $form->isValid() ) {                   
               //getting filtered data 
               $data = $form->getData();   

               $partnumber = $data['partnumber'];

               //it's determine if the part it's ready to insert in the wishlist
               $inStock = $this->existPartInventory( $partnumber );               
               
               if (isset($inStock['noinventory'])) {
                    $form->get('partnumber')->setMessages(['Oops!!! It does not Exist in our Inventory.']);     
                   return new ViewModel(['form' => $form ]);
               }
                             
               $result = $this->findOutScenario( $partnumber, $inStock );
           
               if (empty( $result )) {
                  return $this->redirect()->toRoute('wishlist', ['action'=>'create']);  
               }
               return new ViewModel( $result );
             
           } elseif ( $form->isValid() ) {
                /* get data filtered*/
               $data = $form->getData();              
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
      
      
    /** 
     * This method return Minors and CATEGORY AND SUBCATEGORY ASSOCIATED TO THE MINOR
     * @return array() | Create sessions variables: category[] and subcategory[]  
     */
    private function getMinors() 
    {
        $sqlStr = "SELECT CNTDE1, CNMCOD, CNMCAT, CNMSBC FROM CNTRLM";
        $data = $this->queryManager->runSql( $sqlStr );        
        if ($data !== null ) {            
            for ( $i=0; $i<count($data); $i++) {                               
               $result[$data[$i]['CNMCOD']] = $data[$i]['CNMCOD'];      //MINOR
               $category[$data[$i]['CNMCOD']] = $data[$i]['CNMCAT'];    //CATEGORY
               $subcategory[$data[$i]['CNMCOD']] = $data[$i]['CNMSBC']; //SUBCATEGORY       
            }//end for
            
            $this->session->minors = $result;
            $this->session->category = $category;
            $this->session->subcategory = $subcategory;        
        }//end if        
        
        return $result;
    }//end: getMinors() method
    
      /**
       * This method retrieves information (PartNumber, Description, Price, Major
       * about parts in CATER or KOMAT
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
            $result['major'] = ($table == 'CATER') ? '01':'03';
        }
    
        return $result;
    }//END: METHOD getInfo()
    
    
    /**
     * The method returns an array showing whether partnumber exist, and within
     * which table, in other case (it does not exist) return in the index: 'noinventory' == true
     * 
     * @param string $partnumber | Part number which we need to find out 
     * @return array()
     */
    private function existPartInventory ( $partnumber ) 
    {
        $partInINMSTA = $this->getValidator('INMSTA');                
        $partInCATER = $this->getValidator('CATER');                
        $partInKOMAT = $this->getValidator('KOMAT');                

        /*checking if the PARTNUMBER is part of : INMSTA, CATER, or KOMAT
         * - in case the partnumber does not exist, then change the Error Message and 
         * show it.
         */
        $result['INMSTA'] = $partInINMSTA->isValid( $partnumber ); 
        $result['CATER'] = $partInCATER->isValid( $partnumber ); 
        $result['KOMAT'] = $partInKOMAT->isValid( $partnumber ); 
        
        $existInvetory = $result['INMSTA'] || $result['CATER'] || $result['KOMAT'];

        if ( !$existInvetory ) {
            $result['noinventory'] = true; 
            return $result;            
        }        
        
        return $result;
    }//END METHOD: existPartInventory()
    
    
    /**
     *  This Method update the PartNumber and table in the session
     * @param string $partnumber
     * @param string $table
     */
    private function updateSession( $partnumber, $table ) 
    {
        $this->session->part = $partnumber;       
        $this->session->table = $table;        
    }
        
    /**
    *  THIS METHOD USE THE WISHLIST MANAGER TO INSERT A LIST OF ITEMS
    * @param array $data | this method calls to the insert method of the WishList Manager 
    */  
    private function insert( $data ) 
    {
        $inserted = $this->wlManager->insert( $data );
        
        
        if (!$this->session->fromExcel) {
            if ( $inserted ) {
                //update flashmessenger INSERTION WAS OK     
                $this->flashMessenger()->addSuccessMessage(
                         "The new part has been successfuly inserted CODE: [".$data['code']."]");

                $this->session->part = null;
                $this->redirect()->toRoute('wishlist');

            } else {
                $this->flashMessenger()
                    ->addErrorMessage(
                            'Oops!!! Could not be inserted the new part number in the Wish List.');
            } 
        } else {
          $this->session->countUploaded++;  
        }
        
        return $inserted === true;
    }//END METHOD: insert();  
    
    /**
     * 
     * @return string | It returns the short form of the user
     */
    private function getUserS() 
    {
        $strUser = str_replace('@costex.com', '', $this->getUser()->getEmail());
        return $strUser;
    }
    
    private function findOutScenario( $partnumber, $inStock  ) 
    {
        if ( $inStock['INMSTA'] ) {

            //get data from the partnumber 
            $data = $this->wlManager->getDataItem( $partnumber );       

            /* check if there was some error or the part does not exist in INMSTA */
             if ( !isset($data['error']) ) {
                 $data['user'] = $this->getUserS(); //str_replace('@costex.com', '', $this->getUser()->getEmail());  

                 /* creating FORM for scenario 2 */
                 $form = new FormAddItemWL( 'insert', $this->queryManager );
                 $form->setData( $data );

                 return ['form' => $form, 'renderAll'=> true ];

             } //not error getting info from IMSTA
        }// END: THE PART IS INSIDE INMSTA 
               
        // SCENARIO 3 CATER OR KOMAT.  
        // Updating the PartNumber and Table names in the session for recovering their values later
        if ( $inStock['CATER'] ) { 
            $this->updateSession($partnumber, 'CATER');                              
            return [];                                  
        }         

        if (  $inStock['KOMAT'] ) {
            $this->updateSession($partnumber, 'KOMAT'); 
            return [];                                 
        }
    }//END METHOD: findOutScenario()
    
    
    /**
     *  It returns the name of the table (IMNSTA, KOMAT, or CATER) where the part belongs
     * 
     * @param array() $inStock
     * @return string 
     */
    private function whatTable( $inStock ) 
    {
        if ( $inStock['INMSTA'] == 1) { 
            return 'INMSTA';
        } else if ( $inStock['CATER'] == 1) {
            return 'CATER';
        }

        return 'KOMAT';
    }
      
    /**
     * This method removes from the VALID PARTS all with a no valid MINOR CODE
     *  - the parts inside KOMAT and CATER will be checked
     * 
     * @param array() $validParts
     * @param array() $noValidParts
     * @return array()  
     */
    private function removeInconsByMinor( $validParts, $noValidParts) 
    {
        // checking if the minors[] are loaded within the sessionManager
        $minors = (!$this->session->minors ) ? $this->getMinors() :  $this->session->minors;
        
        //check inconsistencies by MINORS CODE
        foreach ( $validParts as $key => $row ) {            
            $table = $row['table'];
            $minor = strtoupper($row['minor'])??''; //if defined then assign it
            
            //checking if the minor code is valid
            if ( in_array( $table, ['CATER', 'KOMAT'])) {              
                //check the minor code 
                 
                if (!in_array( $minor, $minors )){  // no valid MINORS
                    $validParts[$key]['error'] = 'INVALID MINOR';
                    
                    //inserting into $noValidParts[]
                    array_push($noValidParts, $validParts[$key]);                 
                    
                    // removing from $validParts[]
                    unset($validParts[$key]); 
                } else { //update other properties
                    $validParts[$key]['category'] = $this->session->category[$minor];
                    $validParts[$key]['subcategory'] = $this->session->subcategory[$minor];
                }
            }                  
        }//end foreach        
        
        $result['valid'] = $validParts;
        $result['novalid'] = $noValidParts;
        return $result;
    }// END removeInconsByMinor()
  
    /**
     * -This method() create a NoValid Parts (inconsistency) and return it
     * 
     * @param int $cod            | Code of the part with inconsistency
     * @param string $partnumber  | Part Number to insert
     * @param string $error       | Error code will be inserted
     * @return array() 
     */
    private function updateNoValid( $cod, $partnumber, $error ) {
        
        $noValidParts['code'] =  $cod;
        $noValidParts['partnumber']= $partnumber;
        $noValidParts['error']= $error;
        
        return $noValidParts;
    }
    
    /**  
     * It returns the array['valid', 'novalid']
     *     
     * @param array() $sheetData   | it is an array which contains the new records trying to insert to the WL
     * @return array()             
     */
    private function parseExcelFile( $sheetData ) 
    {        
        $noValidParts = []; $validParts = [];  
        
        $form = new FormValidator( $this->queryManager );
        
        foreach ($sheetData as $key => $row ) {                       
           $partnumber = trim( $row['B']); 
            //it's determine if the part it's ready to insert in the wishlist
           $inStock = $this->existPartInventory( $partnumber );
           
           $data['partnumber'] =  $partnumber;         
           
           //updating form data for  
           $form->setData( $data );
           
           //checking the part number does not exist within the WL (PRDWL). 
           //It will be truth whether the part already exist
           if ( !$form->isValid() ) {
                $errorCode = key($form->get('partnumber')->getMessages());             
                $noValidParts[$key] = $this->updateNoValid($row['A'], $partnumber, $errorCode);               
                                
            } else if (isset ($inStock['noinventory'])) {   //checking that the PART be a VALID PART (it be withing INMSTA, CATER, KOMAT)
                $errorCode = \Application\Validator\PartNumberValidator::INVALID_PARTNUMBER;
                $noValidParts[$key] = $this->updateNoValid($row['A'], $partnumber, $errorCode);              
                
            } else { // the partnumber is ready for being inserted into WL
                $validParts[$key]['code'] = $row['A'];  
                $validParts[$key]['partnumber'] = $partnumber;
                $validParts[$key]['minor'] = $row['C'];  //this MUST BE  a valid MINOR CODE
                $validParts[$key]['table'] = $this->whatTable( $inStock ); //'C' it contains the name of the source table 
            }
        }//endforeach
                     
        $parsedlist = $this->removeInconsByMinor( $validParts, $noValidParts);
        
        return $parsedlist;
    }//END METHOD: parseExcelFile()
        
    /**
     * This method insert parts with no INCONS
     * @param array() $listValid  | List of valid parts will be inserted in the WL
     */
    private function insertValid( $listValid ) 
    {               
        $this->session->countUploaded = 0; 
        $caterKomat = false;
        
        $data =[];
        
       //loading data of each part number depending on where they comes from.
        foreach ( $listValid as $key => $row ) {            
            
            $data['code'] = $this->wlManager->nextIndex();
            $data['user'] = $this->getUserS();
            $data['partnumber'] = $listValid[$key]['partnumber'];            
            $data['type'] = '1';   //new item default
            $data['from'] = WishListManager::FROM_EXCEL;   //new item default
            
            //if key minor is defined then get it
            if ( !empty($row['minor'])) {
              $data['minor'] = $row['minor'];   
              $data['category'] = $row['category'];   
              $data['subcategory'] = $row['subcategory'];                   
            }
            
            
            switch ($row['table']) {
               case 'IMNSTA' : $this->updateSession($data['partnumber'], 'IMNSTA'); break;                 
               case 'CATER' : $this->updateSession($data['partnumber'], 'CATER');
                            $data['major'] = '01';
                            $caterKomat = true;
                break;
               case 'KOMAT' : $this->updateSession($data['partnumber'], 'KOMAT'); 
                            $data['major'] = '03';
                            $caterKomat = true;
                break;
            }
            //update description and price, so that's why the (updateSession() calls)
                        
            if ($caterKomat) {
                $properties = $this->getInfo($data['partnumber']);
                
                $data['partnumberdesc'] = $properties['partnumberdesc'];
                $data['price'] = $properties['price'];
            }
            
            $this->session->fromExcel = true;
                                
           $inserted = $this->insert( $data );                        
           
           if ( !$inserted ) {
                throw new \Exception($data['partnumber'].' could not be inserted into the wish list.');
           }
        }//END FOR 
        
        return $this->session->countUploaded == count( $listValid );  
    }//END insertValid() into WL
    
    /**********************************************  EXCEL FILE MANAGER ***************************/
    
    
} //END: LostsaleController

