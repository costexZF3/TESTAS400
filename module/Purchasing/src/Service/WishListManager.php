<?php

    namespace Purchasing\Service;

    use Application\Service\QueryManager as queryManager;
    use Application\Service\PartNumberManager as PNManager;
    use Application\Service\VendorManager as VndManager; 

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * - Description of WishListManager
 *    - This class is a wrapper. This encapsulates main operations through the WishList file 
 *      depend on some criteria
 *    - methods privates
 *      
 * @author mojeda
 */

class WishListManager 
{
   const USER_BY_DEFAULT = 'NA';
   
   /* reason type */    
   const NEWPART =  '1';  
   const NEWVENDOR = '2'; 
   
   
   /* status in the wishlist for an item */
    const STATUS_OPEN             = '1';  // initial status   (the item is added to the WL)
    const STATUS_DOCUMENTATION    = '2';  // purchasing.documentator  ( ready for documenting by Maikol etc )
    const STATUS_TO_DEVELOP       = '3';  // this part number is ready to release it  into a new Product Development  Project
    const STATUS_CLOSE_BY_DEV     = '4';  // this part is in development
    const STATUS_REOPEN           = '5';  // this part can be analyzed again
    const STATUS_REJECTED         = '6';  // this part won't be developing any more
 
   const FROM_LOSTSALE   = '1';
   const FROM_VENDORLIST = '2';
   const FROM_MANUAL     = '3';
   const FROM_EXCEL      = '4';
   
  
   const FIELDS = ['WHLCODE', 'WHLUSER', 'WHLPARTN', 'WHLSTATUS', 'WHLSTATUSU',  'WHLREASONT', 'WHLFROM',
                   'WHLCOMMENT'
                  ];
   
   const TABLE_WISHLIST   = 'PRDWL';
   const TABLE_WISLISTADD = 'PRDWLADD';
   
   //state transition table
   const STATE_TRANSITION_TABLE = [
        ['current_state' => '1', 'next_state' => '2'], 
        ['current_state' => '1', 'next_state' => '6'], 
        ['current_state' => '1', 'next_state' => '1'], 
        ['current_state' => '2', 'next_state' => '3'], 
        ['current_state' => '2', 'next_state' => '2'], 
        ['current_state' => '2', 'next_state' => '6'], 
        ['current_state' => '3', 'next_state' => '4'], 
        ['current_state' => '3', 'next_state' => '5'], 
        ['current_state' => '3', 'next_state' => '3'], 
        ['current_state' => '4', 'next_state' => '5'], 
        ['current_state' => '4', 'next_state' => '4'], 
        ['current_state' => '5', 'next_state' => '2'], 
        ['current_state' => '5', 'next_state' => '6'],                
        ['current_state' => '5', 'next_state' => '5'],                
        ['current_state' => '6', 'next_state' => '5'],                 
        ['current_state' => '6', 'next_state' => '6'],                 
   ];
    
   protected $reasontype = [ self::NEWVENDOR => "NEW VENDOR", self::NEWPART => "NEW PART"];
   
   protected $status = [ 
        self::STATUS_OPEN          => "OPEN",                         
        self::STATUS_DOCUMENTATION => "DOCUMENTATION",
        self::STATUS_TO_DEVELOP    => "TO DEVELOP",                         
        self::STATUS_REJECTED      => "REJECTED",                                 
        self::STATUS_REOPEN        => "RE-OPEN",
        self::STATUS_CLOSE_BY_DEV  => "MOVED TO PROJ", 
   ];
  
   protected $from = [ self::FROM_LOSTSALE    => "LS", 
                       self::FROM_VENDORLIST  => "VNDL", 
                       self::FROM_MANUAL      => "MN", 
                       self::FROM_EXCEL       => "EXCEL"
                     ];
   
   
   /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= [];
    
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['', 'From','ID','Date', 'User','Part Number', 'Description','Status', 'Assigned', 'Vendor',
                    'PA', 'PS',  'Year Sales',
                              'Qty Quot','TimesQ', 'OEM Price', 'Loc20', 'Model', 'Category',
                              'SubCat', 'Major','Minor'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];    
    private $rawTable = [];    
    private $jsonResponse;
    
    /**
     *
     * @var \queryManager 
     */
    private $queryManager;  
    
    /**
     * Service to retrieve PartNumber details
     * 
     * @var PNManager
     */
    private $partNumberManager;
    
   /**  
     * SERVICE: it's the SERVICE injected from WishListController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
    * 
     * @var VndManager
     */    
    
    private $vendorManager;
    
    /* helpful attributes */        
    private $countItems = 0;
    private $tableAsHtml = '';
   
    public  function __construct( $queryManager, $PNManager, $vendorManager ) 
    {
        /* injection adapter adapterection from WishListController*/        
        $this->queryManager = $queryManager;
        $this->partNumberManager = $PNManager;
        $this->vendorManager = $vendorManager;        
        
        $this->refreshWishList();           
    }//END:constructor 
    
    
    /**
     * This method returns a boolean value indicating whether 
     * the new status can be reached from the current one
     * 
     * @param char(1) $currentState
     * @param char $newStatus
     * @return boolean
     */
    public function changeStatus( $currentState, $newStatus )
    {        
        $tmp = [];
        foreach (self::STATE_TRANSITION_TABLE as $row) {
           if ($row['next_state'] == $newStatus) {
                  array_push( $tmp, $row['current_state']); 
           } 
        }

        return in_array( $currentState, $tmp) ;  
    }//End Method: changeStatus()
    
    
    public function validVendor( $vendorNumber )
    {
      return $this->vendorManager->validVendor( $vendorNumber);      
    }
    
    /**
     * 
     * @param char $reason
     * @return String
     */
    public function getReasonAsString( $reason )
    {
       return $this->reasontype[$reason] ?? $this->reasontype['1'];
    }
    
    /**
     * This method uses the service VendorManager and recover the information of a given vendor
     * 
     * @param string $vndNumber
     */
    public function getVendorInfo( $vndNumber )
    {
       $VNDMANAGER = $this->vendorManager;           
       return  $VNDMANAGER->getVendor( $vndNumber );       
    }
    
    /**
     * This method updates an item taking its code into account 
     * 
     * @param array() $data
     */
   private function updateByCode( $data )
   {       
      $SET['WHLSTATUS'] = $data['status'] ?? self::STATUS_OPEN;        
      $SET['WHLSTATUSU'] = $data['name'] ?? self::USER_BY_DEFAULT;        
      $SET['WHLCOMMENT'] = isset($data['comment']) && trim($data['comment'] !='') ? $data['comment'] : '';        

    //   if (trim($SET['WHLCOMMENT']) == '') { unset($SET['WHLCOMMENT']);}
      if ($SET['WHLSTATUSU'] == self::USER_BY_DEFAULT) { unset($SET['WHLSTATUSU']);}
      if ($SET['WHLSTATUS'] == self::STATUS_OPEN) { unset($SET['WHLSTATUS']);}
      
      $WHERE['WHLCODE'] = $data['WHLCODE'];

      $this->queryManager->update( self::TABLE_WISHLIST, $SET, $WHERE);
   }//END updateByCode() method
    
    
    /**
     * This method() update one item or multiples in the WISHLIST 
     * 
     * @param array() $data
     * @param boolean $multiple
     * @return boolean
     */
    public function update( $data, $multiple=false) 
    {
        //checking if the updating is multiple or not
        if (!$multiple) {
            $this->updateByCode ( $data );
            return;
        }
        
        //updating multiple records
        $records = $data['records']; //sent from the session variable
        
        //new values to modify 
        $newStatus = $data['status'];
        $userToAssign = $data['name'];
        
        //verifying if the user needs be changed or updated 
        $changeUser = ($userToAssign != self::USER_BY_DEFAULT);
        $changeStatus = ($newStatus != self::USER_BY_DEFAULT);
        
        //to assign a new user, the actual status must be 2 (approved), 3 (hold)  
        //for each item in $records
        foreach ( $records as $idItem ) {
           // getting data from WL 
           $data = $this->getDataFromWL( $idItem );
           $actualUserAssigned = $data['WHLSTATUSU'];
           $actualStatus = $data['WHLSTATUS'];
           
           //checking new user will be assigned to list of parts
           $data['name'] = $changeUser && ( $actualUserAssigned !== $userToAssign ) ? $userToAssign : $actualUserAssigned;           
           
           //checking status
           $data['status'] = $changeStatus && $this->changeStatus($actualStatus, $newStatus) ? $newStatus : $actualStatus; 
           $data['WHLCODE']= $idItem; 
           $this->updateByCode($data);
        }
    }//end: update() method
    
        
    
    //DEFINING A METHOD OF CLASS
    /**
     *  This method is used for returning the STRING associated to the status ID.
     * 
     * @param \Zend\Db\Sql\Ddl\Column\Char $idStatus
     * @return string
     * @throws \Exception
     */
    public function getStatus( $idStatus ) 
    {
        if (!in_array( $idStatus,['1','2','3','4','5', '6'])) {
                throw new \Exception('Status Id no valid.');
        }    
       
        return $this->status[$idStatus];        
    }//END method getStatus()
    
    /**
     * It returns $data[] depending on $scenario 
     * 
     * @param array() $row
     * @param string $scenario
     */     
    public function parseData( $row )
    {
        $data['partnumber'] = $row['WHLPARTN'] ?? $row['partnumber'];
        $data['status'] = $row['WHLSTATUS']?? $row['status'];
        $data['comment'] = $row['WHLCOMMENT']?? $row['comment'];
        $data['name'] = $row['WHLSTATUSU']?? $row['name'];
        
        return $data;
    }//END parseDate()
    
    
    /**
     * This method returns the data associated to the code of the PARTNUMBER inside of WL 
     * 
     * @param string $code
     * @return array()
     */
    public function getDataFromWL( $code )
    {
        $sqlStr = 'SELECT * FROM PRDWL';// WHERE WHLPARTN = '.$partnumber;
        $data = $this->queryManager->runSql( $sqlStr )[--$code];
       
        return $data;
    }


    /**
     * -This method generates the strSql taking into account the userName and its Role
     *  in the WL
     * 
     * @param string $userName
     */
    private function refreshWishList( string $userName = '')
    {
       $strSql =  $this->getSqlStr( $userName );  
       
       $this->dataSet = $this->queryManager->runSql( $strSql );       
       
       $this->countItems = count( $this->dataSet ); 
       
    }//END. refreshWishList() 
    
    /**
     *  Populate all data       
     * getting data dynamically
     * Pushing on the first ROW the Header of each column      
     */
    private function populateDataMatriz() 
    {      
        foreach ($this->dataSet as $row) { 
            /* gettin row */
            $rowAsArray = $this->rowToArray( $row );

            /* each row pushing to the rows (body to render)*/
            array_push( $this->rawTable, $rowAsArray );                

        }//end: foreach         
      
    }//END METHOD: populateDataMatriz() method...
    
   /**
    *  -This method retrieves all active users in the AS400 that are PA and PS
    */ 
   public function usersPAAS400()
   {
       $strSql =  "SELECT CNTRLL.CNT03 PA, TRIM(CSUSER.USNAME) FULLNAME, TRIM(CSUSER.USUSER) USER 
              FROM CNTRLL INNER JOIN CSUSER ON CNT03 = DIGITS(USPURC) WHERE CNT01 = '216' AND USPTY9 <> 'R' AND USPURC <> 0";
       
       $dataSet = $this->queryManager->runSql( $strSql );       
       
       return $dataSet;        
   }//END: usersPAAS400


   /* returns all fields defined for the table */
    public function getFieldNames() 
    {
       return $this->columnHeaders;   
    }
    
    /* this method returns the RawTable. this can be used to render */
    public function getTableAsMatriz() 
    {
       return $this->rawTable;
    }
    
    //this method regenerates the WL 
    public function renewWL( string $userName )
    {
       $this->refreshWishList( $userName ); 
    }
    
    /**
     * -This method() returns the items in the WL associated to an userName
     *  - if userName='' the method returns all items in the WL
     * 
     * @return string  |  It returns a STRING that will be used to execute the SQL query.
     */
    private function getSqlStr( string $userName = '' )
    {  
        $strRenew = ''; 
        if ( $userName == 'DOCUMENTATOR' ) {       
           $strRenew = " WHERE UCASE(PRDWL.WHLSTATUS)= '". self::STATUS_DOCUMENTATION."'";
        } else if ($userName !='') {
             $strRenew = "WHERE UCASE(WHLSTATUSU)= '".strtoupper($userName)."'  AND PRDWL.WHLSTATUS<> '". self::STATUS_CLOSE_BY_DEV."'";       
        }
           
        $sqlStr = "SELECT * FROM ( SELECT  IMPTN, IMDSC, IMPC1,IMPC2,IMCATA,IMSBCA,IMMOD, IMPRC     
                  FROM WHLINMSTAJ UNION                                                                     
                  SELECT  WHLPARTN, WHLADDDESC, WHLADDMAJO, WHLADDMINO, WHLADDCATE, WHLADDSUBC, WHLADDMODE, WHLADDPRIC                       
                  FROM WHLADDINMJ ) y                                               
                  INNER JOIN PRDWL on y.IMPTN = PRDWL.WHLPARTN                       
                  LEFT JOIN invptyf on y.IMPTN = invptyf.IPPART ".$strRenew." ORDER BY PRDWL.WHLCODE ASC ";
       
       return $sqlStr;  
    }//END: getSqlString()
    
    /**
     *  -Returns date as String (one year before the actual date )
     *   
     * @return string
     */
    private function dateOneYearBefore()
    {
        $year = date('y')-1; 
        $month = date('m'); 
        $day= date('d');
        return $year."".$month."".$day;
    }
    
    /**
     *  The method() checks if the dataSet is ready
     * 
     * @return boolean
     */
    private function dataSetReady()
    {
        return ($this->dataSet != null) ?? false;
    }
        
    /*
     * populateHTML : this method populate the table using the resultSet 
     * value returned by the function runSql()
     */
    public function getDataSet()
    {              
       return $this->dataSet;
    } 
    
    public function countItems() 
    {
        return count( $this->dataSet );
    }
    
    /**
     * - This method insert data into the PRDWL ( file: WISHLIST )
     * 
     * @param array() $data | An associative array with all needed data inside a WL row.
     * @return object | it returns null is could not insert the field  
     */ 
   public function insert( $data ) 
   {   
        // INSERTING DATA: THE PART EXIST INSIDE INMSTA
        $dataSet['WHLCODE'] = $data['code'];
        $dataSet['WHLUSER'] = trim(strtoupper($data['user'])); 
        $dataSet['WHLPARTN'] = trim(strtoupper($data['partnumber']));
        $dataSet['WHLSTATUS'] = self::STATUS_OPEN;
        $dataSet['WHLSTATUSU'] = 'N/A';
        $dataSet['WHLREASONT'] = $data['type'];        
        $dataSet['WHLFROM']  =  $data['from'];
        $dataSet['WHLCOMMENT'] = $data['comment']??'';

        // inserting in TABLE WL: PRDWL the set of data 
        $DB = $this->queryManager->insert( self::TABLE_WISHLIST , $dataSet );
       
        //checking if the origin of the PARTNUMBER is: KOMAT or CATER  
        if (isset($data['minor']) ) {             
           $dataSet2['WHLADDCODE'] = $data['code'];            
           $dataSet2['WHLADDMODE'] = strtoupper($data['model']);
           
           $dataSet2['WHLADDMAJO'] = $data['major']; 
           $dataSet2['WHLADDMINO'] = $data['minor']; 
           
           $dataSet2['WHLADDCATE'] = $data['category']; 
           $dataSet2['WHLADDSUBC'] = $data['subcategory']; 
           
           $dataSet2['WHLADDDESC'] = $data['partnumberdesc']; 
           
           $dataSet2['WHLADDPRIC'] = round( floatval($data['price']), 2); 
           $this->dataSet = $this->queryManager->insert( self::TABLE_WISLISTADD , $dataSet2 );
        }
       
       return ($this->dataSet || $DB) ?? null;             
    } //END: AddItem method
 
    
    private function getClassCSSforStatus( $item )
    {
        $strStatus = $this->status;    
        switch ($item){
            case $strStatus[self::STATUS_REJECTED]: { $className="statusRejected description"; break;}
            case $strStatus[self::STATUS_OPEN]: { $className="statusOpen description"; break;}                   
            case $strStatus[self::STATUS_DOCUMENTATION] : { $className="statusDocumentation description"; break;}                   
            case $strStatus[self::STATUS_REOPEN] : { $className="statusReOpen description"; break;}                   
            case $strStatus[self::STATUS_TO_DEVELOP] : { $className="statusDevelop description"; break;}                   
            case $strStatus[self::STATUS_CLOSE_BY_DEV] : { $className="statusClosebyDev description"; break;}                   
        }  
        
        return $className;
    }
    
    /**
     * - This method() converts the elements of an array into a <TR> element 
     *   and assigned the CLASS, ID, and TITLE attributes for each <TD> element
     *   - It receives an array and returns the each Item as a <TD> element inside a <TR>
     * 
     * @param array $row
     * @return string
     */
    private function rowArrayToHTML( $row ):string
    {   
        $result ='<tr><td><label class= "container-check"> <input type="checkbox" name= "checkedrow[]" id="checked'.$row[1].'" value="'.$row[1].'"><span class="checkmark"></span></label></td>';  
      
        $col = 1; $className = '';        
        
        /* COLUMNS with the CLASS description-CSS- (first coluns is 0 ) */        
        $columns = [  2, 12, 13, 14, 16 ];
        foreach( $row as $item ) {
            //CHANGING THE ICON TO THE FROM COLUMN: (EXCEL FILE, ...)
            
            if ($col==1) {                             
                $iconToExcel = '<i class="fa fa-file-excel-o fa-1x"  aria-hidden="true" text="From Excel file" ></i>';
                $iconToManual = '<i class="fa fa-keyboard-o fa-1x"  aria-hidden="true" text="One by One" ></i>';
                $item = ($item=='EXCEL') ? $iconToExcel : $iconToManual;
            }
            if (in_array( $col, $columns ) ) {
                $className = "number";
            } else if ( $col === 7 ) { 
                $className = $this->getClassCSSforStatus( $item );
                          
            } else if ( $col === 15 ) { 
                $className = "money";
                $item ='$ '.$item;
            } else if ( $col === 18 ) { $className = "description";}
            else {$className = '';}              
            
            $result .= '<td class="'.$className.'">'.$item.'</td>';
          $col++;  
        }//endforeach 
        
        $result .= '</tr>';        
        return $result;
    }//END METHOD: rowArrayToHTML()
    
    /**
     *  RETURNS: all rows of the table will be returned as an ARRAY 
     * @return array()
     */      
    public function getRows() {
      return $this->rows;
//      return ($this->rows)?? null;
    }
    
    public function jsonResponse()
    {
        return $this->jsonResponse; 
    }
    /**
     *  This method converts $row to an Array
     * @param array() $row | All fields ( columns ) will be mapped into an ARRAY   
     * @return array
     */
   private function rowToArray( $row ) 
   { 
      $result = [];
      $toJson = [];

      $partNumberInWL = trim($row['WHLPARTN']); 
      $toJson += ['partnumber' => $partNumberInWL ];

      $fromValue = $this->from[$row['WHLFROM']];      //MANUAL, EXCEL, LOSTSALES
      $toJson += ['from' => $fromValue ];

      $statusP = $this->status[$row['WHLSTATUS']];
      $toJson += ['status' => $statusP];


      array_push( $result, $fromValue );   // index: 0 : code
      array_push( $result, $row['WHLCODE'] );         //index: 1 WL No
      $toJson += ['code' => $row['WHLCODE'] ];

      array_push( $result, $row['WHLDATE'] );  // index: 2 DATE
      $toJson += ['date' => $row['WHLDATE'] ];

      array_push( $result, $row['WHLUSER'] );  // index: 3 - USER 
      $toJson += ['usercreated' => $row['WHLUSER'] ];

      $strUpdate = '/ctpsystem/public/wishlist/update/'.$row['WHLCODE'];//$partNumberInWL;        
//        
      $url = '<a href='.$strUpdate.' class="partnumber">'.$partNumberInWL.'</a>';

      array_push( $result, $url ); // index: 4 - PART NUMBER IN WL       

      array_push( $result, $row['IMDSC'] );   // index: 5 - description
      $toJson += ['description' => $row['IMDSC']];

      //NEW COLUMS AND REQUIREMENTS 
      array_push( $result, $statusP );   // index: 6 - STATUS

      array_push( $result, $row['WHLSTATUSU'] );   // index: 7 - USER IN CHARGE


      // index: 8 - VENDOR NUMBER        
      $vendorNum = $row['IPVNUM'];                
      
      array_push( $result,  $vendorNum);   
      $toJson += ['vendor' => $vendorNum];

      // index: 9 - getting PA
     //its needed for retrieving the informations about PA
     
      array_push( $result,  $this->vendorManager->getPA( $vendorNum ));   
      $toJson += ['pa' => $this->vendorManager->getPA( $vendorNum )];

      // index: 10 - getting PS
      array_push( $result,  $this->vendorManager->getPS());   
      $toJson += ['ps' => $this->vendorManager->getPS()];

       // index: 11 - year sales
      array_push( $result, $row['IPYSLS'] ); 
      $toJson += ['yearsales' => $row['IPYSLS']];

      // index: 12 - qty quoted
      array_push( $result, $row['IPQQTE'] );  
      $toJson += ['qtyquoted' => $row['IPQQTE']];

      // index: 13 - times quoted
      array_push( $result, $row['IPTQTE'] );  
      $toJson += ['timesquoted' => $row['IPTQTE']];

      //index: 14 - OEM PRICE
      array_push( $result, number_format( $row['IMPRC'], 2 )); 
      $toJson += ['oemprice' => $row['IMPRC']];

      /*  getting location from DVINVA  where the part has STOCK in  ( DVBIN#: if you need the bin location) 
       *  - location: 20
       *  - dvonh#: (qty on hand) > 0  ( hay alguna on hand ) */        
      $strSql = "SELECT DVONH# FROM DVINVA WHERE UCASE(TRIM(DVPART))='". strtoupper( $partNumberInWL ).
              "' and dvlocn ='20' and DVONH# > 0";

      $dataSet = $this->queryManager->runSql( $strSql );

      //index: 15 - location
      $inLoc20 = $dataSet[0]['DVONH#']?? '0';
      array_push( $result,$inLoc20 ); 
      $toJson += ['inloc20' => $inLoc20];

      /* adding MODEL */
      $model = trim($row['IMMOD']) != '' ? $row['IMMOD']:'N/A';
      array_push( $result,$model  ); //index: 11 model
      $toJson += ['model' => $inLoc20];

      /* ADDING CATEGORY DESCRIPTION BEST CASE 5.3 S*/
      $cat = $row['IMCATA'];         
      $CatDescription =  $this->partNumberManager->getCategoryDescByStr( $cat );           
      array_push( $result, $CatDescription ); // index: 12 - Category Description 
      $toJson += ['categoria' => $CatDescription];

       /* SUB-CATEGORY */
      array_push( $result, $row['IMSBCA'] ); //index: 13 - Subcategory
      $toJson += ['subcategoria' => $row['IMSBCA']];

       /* mayor and minor */
       //$mayorMinor = $this->partNumberManager->getMajorMinor( $partNumberInWL );
       array_push( $result, $row['IMPC1'] ); // index; 14 - Major code
       $toJson += ['major' => $row['IMPC1']];

       array_push( $result, $row['IMPC2'] ); // index: 15 - Minor code 
       $toJson += ['minor' => $row['IMPC2']];

      //creating rows as JSON
      $this->jsonResponse = json_encode( $toJson ); 
           
      //var_dump($this->jsonResponse()); exit;
      return $result;
    }
    
    
    /**
     *  Returns the WL as HTML
     * 
     * @return string
     */ 
     
    private function getBodyTable(){
      $iteration = 0; 
      $tableBody = '<tbody>';        
            
      /************* dynamic body **********************/        
      foreach ($this->dataSet as $row) { 
          /* gettin row */
          $rowAsArray = $this->RowToArray( $row );
          /* each row pushing to the rows (body to render)*/
          array_push( $this->rows, $rowAsArray );  
                   
          $currentRow = $this->rows[$iteration];
            
          /* conver row to HTML: $row  */
          $tableBody.= $this->rowArrayToHTML( $currentRow ); 

        $iteration++;
      }//end: foreach  
      
      return $tableBody;
    }//END: getBodyTable() method
    
    /**
     * - function: dataToHtml() 
     * - this return all data processed as a HTML file. 
     * - this is recovered by the the WishlistController then
     *   it'll be sent as parameter for being rendered by the view associated
     * 
     * @return string
     */
         
    public function TableAsHtml(){
        //checking if the method: runSql() was invoked before...
             
        if (!$this->dataSetReady()) { return '';}      
        
        /* ------------ creating table with all data from dataSet -----------------------*/
        $tableHeader = '<table class="table_ctp table_filtered display">';
        $tableHeader.='<thead><tr>';  
        
         /*********** generating each column label dynamically *****************/
         foreach ($this->columnHeaders as $field) {           
            $tableHeader.='<th>'.$field.'</th>';    
         }

        /* concatening  header */
        $tableHeader .= '</tr></thead>';

            
        /*********** adding tbody element ***************/      
        $iteration = 0;       

        //  $tableBody = $this->getBodyTable();                         
        $tableBody = '<tbody>';        
            
        /************* dynamic body **********************/        
        foreach ($this->dataSet as $row) { 
            /* gettin row */
            $rowAsArray = $this->RowToArray( $row );
            /* each row pushing to the rows (body to render)*/
            array_push( $this->rows, $rowAsArray );                

            $currentRow = $this->rows[$iteration];

            /* conver row to HTML: $row  */
            $tableBody.= $this->rowArrayToHTML( $currentRow ); 

          $iteration++;
        }//end: foreach    

        $tableFooter = '<tfoot><tr>';

        /*********** generating each column label dynamically *****************/
        foreach ($this->columnHeaders as $field) {           
            $tableFooter.='<td>'.$field.'</td>';
        }

        $tableFooter.='</tr></tfoot>';       
              
        $this->tableAsHtml = $tableHeader.$tableBody.$tableFooter;               
       
        return  $this->tableAsHtml;
    }/* END: getGridAsHtml()*/    
    
    public function nextIndex() 
    {
        return $this->queryManager->getMax('WHLCODE', 'PRDWL')?? 1;
    }
    
    /**
     * 
     * @param string $partNumberID | The Part Number will be used to retrieve data from INMSTA  
     * @return array() | RETURN AN ASSOCIATIVE ARRAY WITH THE PART 
     */
    public function getDataItem( $partNumberID ) 
    {
        $partNumberObj =  $this->partNumberManager->getPartNumber( $partNumberID );
        
        if ( $partNumberObj !== null ) {         
            $data['code'] = $this->nextIndex();//$this->queryManager->getMax('WHLCODE', 'PRDWL');
            $data['date'] = date('Y-m-d');
            /* - if the partNumber exist NOT NULL then return it back
             *   other case it returns UNKNOW string*/

            $data['partnumber'] = $partNumberObj->getId(); 

            $data['partnumberdesc']= $partNumberObj->getDescription();

            $data['vendor']      = $partNumberObj->getVendor();
            $data['vendordesc']  = $partNumberObj->getVendorDescription(); 

            $data['tqLastYear']  = $partNumberObj->getQtyQuotedLastYear();
            $data['minor']       =  $partNumberObj->getMinor();
            $data['oemprice']    = number_format($partNumberObj->getListPrice(), 2);
            $data['ctppartnumber'] = $partNumberObj->getCTPRefs();
            $data['qtysold'] = $partNumberObj->getQuantitySold();
           
            $data['comment'] = '';
            $data['type'] = ''; //

            return $data;
         }

         // return 'error' if the PartNumber not exits in the DATABASE 
         return $data['error'] = $partNumberObj['error'];    

      } //END: getWishListItem()
      
      
      
    /************************************ EXCEL MANANGER *********************************/
      
    /**
     * - This method highLight an specific CELL regarding its style and color
     * 
     * @param Spreadsheet $spread | spreadsheet generated 
     * @param string $cell | Cell will be hightlighter
     * @param boolean $bold | True or False if it will be BOLD
     * @param string $color | color
     */
    private function highLighter( $spread, $cell, $bold=true, $color="" ) 
    {        
        $styleOptions = [
            'font' => [
                'bold'  => $bold, 
                'color' =>[
                    'rgb' => $color
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '50E0F5F5'
                ]
            ] 
        ];        
        
        $spread->getActiveSheet()->getStyle($cell)->applyFromArray( $styleOptions );
       
    }//END highLighter() method
    
    /**
     * This method insert a comment in a cell
     * 
     * @param string $cell
     * @param string $comment
     */
    private function InsertComment( $sheet, $cell, $comment='', $bold=false)
    {        
        $sheet->getActiveSheet()->getComment($cell)->getText()->createTextRun( $comment );
    }
    
    /**
     * - This Method() adds headers to the sheet of the EXCEL FILE
     * 
     * @param Spreadsheet $sheet     * 
     */
    private function createSheetHeaders($sheet)
    {   
        $options = [ 
                '1'=>['cell'=>'A1', 'desc' =>'COD', 'dimension'=>-1], //-1: dimension by default 
                '2'=>['cell'=>'B1', 'desc' =>'PART NUMBER', 'dimension'=>26],
                '3'=>['cell'=>'C1', 'desc' =>'ERRORS', 'dimension'=>26],
            ];
        
        $sheet->getActiveSheet()->freezePane('A2'); //freezing TOP ROW
        
        
//        $this->InsertComment($sheet, 'C1', 'REFERENCES: '); 
        
        foreach ($options as $key => $value)    {
            $sheet->getActiveSheet()->setCellValue($value['cell'], $value['desc']);            
         
            if ($value['dimension'] != -1 ) {
                $sheet->getActiveSheet()->getColumnDimensionByColumn( $key )->setWidth($value['dimension']);                 
            }
        } 
        
        $this->highLighter($sheet, 'A1:C1');
        
    }//END createSheetHeaders Method
    
    /**
     * This method update a cell of a SpreadSheet with the value passed
     * 
     * @param Spreadsheet $sheet | it's an instance of PHPOffice\Spreadsheet component
     * @param type $cell         | it's the cell that will be updated ex: C2 
     * @param type $value        | it's the value will be assigned to CELL passed as param
     */
    private function fillCellSheet( $sheet, $cell, $value ) 
    {
        $sheet->getActiveSheet()->setCellValue( $cell, $value );
        
        $cellTmp = strtoupper(substr($cell, 0, 1));        
        if ( $cellTmp == 'C') {
          $styleError = ['font'=>['bold'=> true, 'color'=>['rgb'=>'b21703']]];  
          $sheet->getActiveSheet()->getStyle( $cell )->applyFromArray( $styleError ); 
        }  
        
    }//END: fillCellSheet() method   
    
    
    /**
     * This Method creates an instance of Spreadsheet class
     * 
     * @param string $sheetName
     * @return object
     */
    private function createSpreadSheet( $sheetName ) {
        $inputFileType = 'Xls';  
        
        $actualDate = date('Y-m-d');      
        
        //creating a new Spreadsheet()
        $spreadsheet = new Spreadsheet();
        $writer = IOFactory::createWriter( $spreadsheet,  $inputFileType );          
                
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle($sheetName.'_'.$actualDate); 
        
        $result = ['sheet' => $spreadsheet, 'writer' => $writer ];
        return $result;
    }
    
    /**
     * -The method inserts INCONSISTENCIES into a new Excel File  
     *   - the sheet created has as name: INCONS_<actualdate>
     * 
     * @param array() $inconsistence | List of Inconsistencies
     */
    public function writeErrorsToExcel( $inconsistence ) 
    {   
        $row = 2;  
        
        $excelOBJ = $this->createSpreadSheet('INCONS');
        $spreadsheet = $excelOBJ['sheet'];
        $writer = $excelOBJ['writer'];
        
        //creating HEADERS  ( creatin )
        $this->createSheetHeaders( $spreadsheet );
        
        foreach ( $inconsistence as  $value)        {            
            $this->fillCellSheet( $spreadsheet, 'A'.$row, $value['code'] );
            $this->fillCellSheet( $spreadsheet, 'B'.$row, $value['partnumber'] );
            $this->fillCellSheet( $spreadsheet, 'C'.$row, $value['error'] );
            $row++;
        }
        
        try {
            // $urlInc = './data/upload/wishlist_inc.xls';
            $urlInc = 'public/data/wishlist_inc.xls';
            $writer->save( $urlInc );           
        } catch (Zend_Exception $error ) {
            echo "Caught exception: trying to saving the wishlist inconsistencies". get_class($error)."\n";
            echo "Message: ". $error->getMessage()."\n";            
        }        
        
    }//END METHOD updateErrorsInXls
    
    /**
     * Auxiliar method. 
     * 
     * -invoke from readExcelFile()
     * @param array() $sheetData
     * @return boolean
     */
    private function validEXCELHeader( $sheetData ) 
    {
        return $sheetData[1]['A'] == 'COD' &&  
               $sheetData[1]['B'] == 'PART NUMBER' && 
               $sheetData[1]['C'] == 'MINOR';
    }

    /**
     * 
     * @param type $sheetData
     * @return type
     */
    private function removeHeader( &$sheetData )
    {
        unset( $sheetData[1] );
        return $sheetData;
    }
    
    /** 
    * @param string $inputFileName | route of the file XLS (excel file) with the WL
    * @return array() | it returns an array with the excel file as array
    */
    public function readExcelFile( $inputFileName ) 
    {   //reading file      
        $inputFileType = 'Xls';        
        $reader = IOFactory::createReader( $inputFileType );  
        $reader->setReadDataOnly( true );        
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        // validating EXCEL FILE
        $validHeader = $this->validEXCELHeader( $sheetData );
        
        if ( !$validHeader ) {
            throw new \Exception('The excel file header is not valid. Check the documentation about it.');
        }        
        $sheetDataFilter = $this->removeHeader( $sheetData );
        
        return $sheetDataFilter;
    }
            
    
}//END: WishList class()
