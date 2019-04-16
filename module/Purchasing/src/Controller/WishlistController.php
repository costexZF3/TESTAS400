<?php

namespace Purchasing\Controller;

//require_once './../../../../vendor/autoload.php';

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SM;

use PhpOffice\PhpSpreadsheet\IOFactory;

use Application\ObjectValue\PartNumber;


//use Box\Spout\Reader\ReaderFactory;
//use Box\Spout\Common\Type;


/* services injected from the FACTORY: WishListControllerFactory */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;


/* MODEL: forms classes */
use Purchasing\Form\FormAddItemWL as FormAddItemWL;
use Purchasing\Form\FormUpload; 
use Purchasing\Form\FormValidator;


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
 
    //loading fromfile
    public function uploadAction() 
    {
        // Create the form model.
        $form = new FormUpload( $this->queryManager );
               
        // Check if user has submitted the form.
        if($this->getRequest()->isPost()) {
            
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
            if($form->isValid()) {
                
                // Execute file filters.
                $data = $form->getData();
                
                $inputFileName = $data['file']['tmp_name'];
                
//                $sheetData = $this->readExcelFile( $inputFileName );

                $sheetData = [ 
                        1 =>['A'=>1, 'B'=>'1005930'],
                        2 =>['A'=>2, 'B'=>'AB54192'],
                        3 =>['A'=>3, 'B'=>'2770118'],
                        4 =>['A'=>4, 'B'=>'2672755'],                            
                        5 =>['A'=>5, 'B'=>'8N8221'],                            
                        6 =>['A'=>6, 'B'=>'8N8221122'],                            
                        7 =>['A'=>7, 'B'=>'FS12405555'],                            
                        8 =>['A'=>8, 'B'=>'VN8534'],                            
                    ];

                //result['valid'] : data ready for updating  and result['invalid'] : inconsistency data
                $result = $this->parseExcelFile( $sheetData );
                
                print_r($result['valid']);
                print_r($result['invalid']); exit;
                
                              
                // Redirect the user to another page.
                
                 $this->flashMessenger()->addSuccessMessage(
                     "The file with items to WISHLIST was uploaded perfectly : [".$data['file']['name']."]");
                return $this->redirect()->toRoute('wishlist', ['action'=>'index']);
            } else {
                 $form->get('file')->setMessages(['Oops!!! Invalid file format']); 
            }
    
        }  
        
        // Render the page.
        return new ViewModel([
                 'form' => $form
            ]);       
    }//END METHOD: 
    
    
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

            /* it's a MUST BEFORE validate */
            $form->setData( $data ); 
             
           /* valid if not exit in WISHLIST, THEN I can insert it inside de PRDWL */ 
           if ( $initial && $form->isValid() ) {                   
               //getting filtered data 
               $data = $form->getData();   

               $partnumber = $data['partnumber'];

               //NEWWWWWWW it was moved for a new method
               
               //it's determine if the part it's ready to insert in the wishlist
               $inStock = $this->existPartInventory( $partnumber );
               
               
               if (isset($inStock['noinventory'])) {
                    $form->get('partnumber')->setMessages(['Oops!!! It does not Exist in our Inventory.']);     
                   return new ViewModel(['form' => $form ]);
               }
               
               
               //NEWWWWW creating a new method
               
               $result = $this->findOutScenary( $partnumber, $inStock );
           
               if (empty( $result )) {
                  return $this->redirect()->toRoute('wishlist', ['action'=>'create']);  
               }
               return new ViewModel( $result );
             
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
        $data = $this->queryManager->runSql( $sqlStr );        
        if ($data !== null ) {
            
            for ( $i=0; $i<count($data); $i++) {                 
//              $result[ $data[$i]['CNMCOD'] ] = $data[$i]['CNMCOD']; 
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
    
        return $result;
    }//END: METHOD getInfo()
    
    
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
    }
    
    
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
    
    
    private function findOutScenary( $partnumber, $inStock  ) 
    {
        if ( $inStock['INMSTA'] ) {

            //get data from the partnumber 
            $data = $this->WLManager->getDataItem( $partnumber );       

            /* check if there was some error or the part does not exist in INMSTA */
             if ( !isset($data['error']) ) {
                 $data['user'] = str_replace('@costex.com', '', $this->getUser()->getEmail());  

                 /* creating FORM for scenario 2 */
                 $form = new FormAddItemWL( 'insert', $this->queryManager );
                 $form->setData( $data );

                 return ['form' => $form, 'renderAll'=> true ];

             } //not error getting info from IMSTA
        }// END: THE PART IS INSIDE INMSTA 
               
        // SCENARIO 3 CATER OR KOMAT
        if ( $inStock['CATER'] ) { //saving the PARTNUMBER into the session variable: part
            $this->updateSession($partnumber, 'CATER');                              
            return [];
            //return $this->redirect()->toRoute('wishlist', ['action'=>'create']);                       
        }         

        /* if the part number exist on CATER or KAMAT, then create part */
        if (  $inStock['KOMAT'] ) { //saving the PARTNUMBER into the session variable: part

            $this->updateSession($partnumber, 'KOMAT'); 
            return [];
            //return $this->redirect()->toRoute('wishlist', ['action'=>'create']);                       
        }
    }//END METHOD: findOutScenary()
    
    
    /**
     * 
     * @param array() $inStock
     * @return string it returns the table Name (IMNSTA, KOMAT, or CATER) where the part is.
     */
    private function whichTable( $inStock ) 
    {
      if ( $inStock['INMSTA'] == 1) {
          return 'INMSTA';
      } else if ( $inStock['CATER'] == 1) {
          return 'CATER';
      } return 'KOMAT';
    }
    
    /**
     * 
     * @param array() $sheetData | it is an array which contains the new records trying to insert to the WL
     * @return array()
     */
    private function parseExcelFile( $sheetData ) 
    {        
        $noValidParts = []; $validParts = []; $tmpCode = 1;
        
        $form = new FormValidator( $this->queryManager );
        
        foreach ($sheetData as $key => $row) {                       
           $partnumber = trim( $row['B']); echo $partnumber."<br>";                
            //it's determine if the part it's ready to insert in the wishlist
           $inStock = $this->existPartInventory( $partnumber );
           
           $data['partnumber'] =  $partnumber;         
           
           //updating form data for  
           $form->setData( $data );
           
            if ( !$form->isValid() ) {                 
                
                $errorCode = key($form->get('partnumber')->getMessages());             
                $noValidParts[$key]['B'] = $partnumber;                   
                $noValidParts[$key]['D'] = $errorCode;                                                   
                
            } else if (isset ($inStock['noinventory'])) {
                $noValidParts[$key]['B'] = $partnumber;                   
                $noValidParts[$key]['D'] = \Application\Validator\PartNumberValidator::INVALID_PARTNUMBER;
            } else { // the partnumber is ready for being inserted into WL
                $validParts[$key]['A'] = $tmpCode++;  
                $validParts[$key]['B'] = $partnumber;
                $validParts[$key]['D'] = $this->whichTable( $inStock ); //'C' it contains the name of the source table                
            }                                          
//               $result = $this->findOutScenary( $partnumber, $inStock );
           
               //if validate each part and recover its datas UFFFF 
//               if (empty( $result )) {
//                  return $this->redirect()->toRoute('wishlist', ['action'=>'create']);  
//               }
               
//               return new ViewModel( $result );
             
        }//endforeach
        
        print_r ( $noValidParts ); echo "<br>";
        print_r ( $validParts );
        exit();
        return $noValidParts;
    }
    
    private function readExcelFile( $inputFileName ) 
    {
        
        $inputFileType = 'Xls';
        $inputFileName = './data/upload/wishlist.xls';
        $reader = IOFactory::createReader( $inputFileType );
  
        $reader->setReadDataOnly( true );
        $spreadsheet = $reader->load($inputFileName);

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        var_dump($sheetData);
     
        exit;        
        return $sheetData;
    }
    
} //END: LostsaleController

