<?php

    namespace Purchasing\Service;

    use Application\Service\QueryManager as queryManager;
    use Application\Service\PartNumberManager;
    use Application\Service\VendorManager;

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * - Description of ProductDevManager
 *          
 * @author mojeda
 */

class ProductDevManager 
{
   const USER_BY_DEFAULT = 'NA'; //
   
   /* reason type */    
   const NEWPART =  '1';  //
   const NEWVENDOR = '2'; 
   
   const FINALIZED = 'F';
   const IN_PROCESS = 'I';
   
   /* status of parts inside a project */
    const STATUS_ENTERED                          = 'E';  // initial status   (the item is added to the WL)
    const STATUS_APPROVED_QC                      = 'A';  // approved 
    const STATUS_APPROVED_WITH_ADVICE             = 'AA';  // purchasing.documentator  ( ready for documenting by Maikol etc )
    const STATUS_ANALYSIS_OF_SAMPLES_QC           = 'AS';  // ANALYSIS OF SAMPLES_QC 
    const STATUS_CLOSED_WO_NEGOTIATION            = 'CD';  // ANALYSIS OF SAMPLES_QC 
    const STATUS_CLOSED_APPROVED                  = 'CL';  // CLOSE WO NEGOTIATION 
    const STATUS_CLOSED_APPROVED_WO_NEGOTIATION   = 'CN';  // CLOSE APPROVED WO NEGOTIATION 
    const STATUS_CLOSED_SUCCESSFULLY              = 'CS';  // CLOSE SUCCESSFULLY 
    const STATUS_DOCUMENTATION_FINALIZED          = 'DF';  // DOCUMENTATION FINALIZED
    const STATUS_DOCUMENTATION_IN_PROCESS         = 'DF';  // DOCUMENTATION IN PROCCESS
    const STATUS_NEGOTIATION_SUPPLIER             = 'NS';  // NEGOTION WITH SUPPLIER
    const STATUS_PENDING_FROM_SUPPLIER            = 'PS';  // PENDING FROM SUPPLIER
    const STATUS_QUOTING                          = 'Q';  // QUOTING    
    const STATUS_REJECTED_QC                      = 'R';  // REJECTED  
    const STATUS_RECEIVING_FIRST_PRODUCTION       = 'RP';  // RECEIVING OF FIRST PRODUCTION  
    const STATUS_SAMPLE_ALREADY_SENT              = 'SS';  // SAMPLE ALREADY SENT  
    const STATUS_TECHNICAL_DOCUMENTATION          = 'TD';  // TECHNICAL DOCUMENTATION   
    
    
   const FROM_LOSTSALE   = '1';
   const FROM_VENDORLIST = '2';
   const FROM_MANUAL     = '3';
   const FROM_EXCEL      = '4';
     
   const FIELDS = ['WHLCODE', 'WHLUSER', 'WHLPARTN', 'WHLSTATUS', 'WHLSTATUSU',  'WHLREASONT', 'WHLFROM',
                   'WHLCOMMENT'
                  ];
   
   const TABLE_PRDHEADER   = 'PRDVLH';
   const TABLE_PRDDETAIL   = 'PRDVLD';
   const TABLE_PRDCOMMENTSH = 'PRDCMH';
   const TABLE_PRDCOMMENTSD = 'PRDCMD';
   
   //state transition table
   const STATE_TRANSITION_TABLE = [
        ['current_state' => '1', 'next_state' => '2'], 
        ['current_state' => '1', 'next_state' => '6'], 
        ['current_state' => '1', 'next_state' => '1'], 
        ['current_state' => '2', 'next_state' => '3'], 
        ['current_state' => '2', 'next_state' => '2'], 
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
        self::FINALIZED => "FINALIZED",
        self::IN_PROCESS =>"IN PROCESS"    
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
     * SERVICE: it's the SERVICE injected from WishListController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
     * @var queryManager
     */
    private $queryManager;  
    
    /**
     * Service to retrieve PartNumber details
     * 
     * @var Application\Service\PartNumberManager
     */
    private $partNumberManager;
    
    /**
     *
     * @var Application\Service\VendorManager 
     */
    
    private $vendorManager;
    
    /* helpful attributes */        
    private $countItems = 0;
    private $tableAsHtml = '';
    
    private $entityManager;
   /**
    * 
    * @param type $queryManager
    * @param type $PNManager
    * @param type $vendorManager
    */
    public  function __construct( $entityManager, $queryManager, $PNManager, $vendorManager ) 
    {  
       $this->entityManager = $entityManager;
       $this->queryManager = $queryManager;
       $this->partNumberManager = $PNManager;
       $this->vendorManager = $vendorManager;  
       // $this->refreshWishList();           
    }//END:constructor 
    
    
   /**
    * THIS METHOD CHECKS IF a project can 
    * @param strim $partnumber
    * @return array
    */ 
   public function canCreateProject( $data )
   {
      $partnumber = trim($data['partnumber']);
      $newvendor = $data['newvendorname'];
      $currentVendor = $data['currentvendor'];
      
      // using the ORM (DOCTRINE) FOR RECOVERING THE Project ENTITY         
      $Part = $this->entityManager->getRepository(\Purchasing\Entity\PartsDetail::class)
                         ->findBy([
                              'partnumber' => $partnumber,
                              'vendor' => $newvendor,                             
                           ] );
                   
      //checking the STATUS OF THIS PART
     
      if ($Part!=null ) {         
          
         $status = $Part[0]->getStatus();
         $validStatus = [self::STATUS_CLOSED_SUCCESSFULLY,
                         self::STATUS_CLOSED_APPROVED_WO_NEGOTIATION, 
                         self::STATUS_CLOSED_WO_NEGOTIATION,
                         self::STATUS_CLOSED_APPROVED,
             ];
         
         $statusClose = in_array($status, $validStatus); 
         
         //EXIST AND ITS NOT CLOSE
         if ( !$statusClose ) { 
            $result['partdata'] = $Part[0];
            $result['message'] = "The partnumber exist in an OPEN PROJECT with CODE: [".$Part[0]->getCode()."]";
            
            return $result;
         } 
      }      
           
      $result['partdata']= null;   
      $result['message']= 'INSERT';   
      
      return $result;
     
   }//END: canCreateProject
   
    /**
     * This method returns a boolean value indicating whether 
     * the new status can be assigned to the part
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
      $SET['WHLSTATUS']  = $data['status']  ?? self::STATUS_OPEN;        
      $SET['WHLSTATUSU'] = $data['name']    ?? self::USER_BY_DEFAULT;        
      $SET['WHLCOMMENT'] = $data['comment'] ?? '';        

      if ($SET['WHLCOMMENT'] == '') { unset($SET['WHLCOMMENT']);}
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
     * This method returns the data associated to the Project Number inside the PRDVLH
     * 
     * @param string $code
     * @return array()
     */
    public function getDataFromDevelopment( $code )
    {
        $sqlStr = 'SELECT * FROM PRDVLH INNER JOIN PRDVLD';// WHERE WHLPARTN = '.$partnumber;
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
           $strRenew = " where UCASE(PRDWL.WHLSTATUS)= '". self::STATUS_DOCUMENTATION."'";
    } else if ($userName !='') {
         $strRenew = "where UCASE(WHLSTATUSU)= '".strtoupper($userName)."'";         
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
        
            
    //************************** INSERTING METHODS FOR PRODUCT DEVELOPMENT ***************************************
    
    private function insertSentence( $table, $data )
    {
        
      $DB = $this->queryManager->insert( $table , $data );
      
      return $DB !== null;
    }
    
    private function insertInPRDHeader( $data )
    {       
      $dataSet['PRHCOD'] = $data['projectcode'];
      $dataSet['PRNAME'] = trim(strtoupper($data['projectname']));
      $dataSet['PRINFO'] = trim(strtoupper($data['projectdescription']));
      $dataSet['PRSTAT'] = self::IN_PROCESS;
      $dataSet['CRUSER'] = $data['currentuser']; //user logged        
      $dataSet['MOUSER']  = $data['currentuser'];  //user logged
      $dataSet['PRPECH']  = $data['assignedto'];  //user in charge: by default it's taken from WL      
      
      // inserting in TABLE PRDVLH: PRODUCT DEVELOPMENT HEADER
      return $this->insertSentence(self::TABLE_PRDHEADER, $dataSet);
    }//END: insertInPRDHeader

    /**
     *  this method inserts into PRDVLD: PRODUCTDEVELPMENT DETAIL 
     * @param type $data
     * @return boolean
     */
    private function insertInPRDDetail( $data )
    {
      $newPart =  trim($data['reasontype'])=='NEWPART';
            
      $dataSet['PRHCOD'] = $data['projectcode'];
      
      //updating with data from the WL
      $dataSet['PRDPTN'] = trim(strtoupper($data['partnumber']));
      $dataSet['PRDCTP'] = trim(strtoupper($data['ctppartnumber']));
      $dataSet['CRUSER'] = $data['currentuser'] ?? ''; //user logged        
      $dataSet['MOUSER'] = $data['currentuser'] ?? '';  //user logged
      $dataSet['PRDUSR'] = $data['assignedto'] ?? '';  //user in charge: by default it's taken from WL   
      $dataSet['PRDSTS'] = $data['status'] ?? self::STATUS_ENTERED; //Status of the PART IN PRODUCT DEVELOPMENT CHARACTER (2)
    //   $dataSet['PRDPTS'] = '' //Status of the PART IN PRODUCT DEVELOPMENT CHARACTER (2)
    //   $dataSet['PRDERD'] = ''; //
    //   $dataSet['PRDPDA'] = ''; //
                  
//    $dataSet['PRDQTY'] = '';
//    $dataSet['PRDMFR'] = $data[''];
      
//    $dataSet['PRDCOS'] = $data['']; //Unit Cost Current Supplier
//    $dataSet['PRDCOS'] = $data[''])); //Unit Cost Current Supplier
//    $dataSet['PRDCON'] = $data['']; //Unit Cost New Supplier
     
      $dataSet['VMVNUM'] = $data['newvendorname']; //VENDOR NUMBER      
        
      $dataSet['PRDNEW']  = $newPart ? 1 : 0;  //NEW ITEM FLAG; it depends on the New Status of the Reason Type from WL
      $dataSet['PRDMPC']  = $data['minorcode'] ?? '';  //MINOR PRODUCT CODE
      $dataSet['PRDSQTY']  = intval( $data['qtysold']) ?? 0;  //MINOR PRODUCT CODE
      
      //checking if it comes from WL then retrieve some data
      if ($data['fromwl']) {
         $dataSet['PRWLDA'] = $data['creationdate']; 
         $dataSet['PRWLFL']  = 1;  //CHECKING IS THE PART IS IN THE WL
      } else {
         $dataSet['PRWLFL']  = 0;
      }      
       
      // inserting in TABLE PRDVLH: PRODUCT DEVELOPMENT HEADER
      return $this->insertSentence(self::TABLE_PRDDETAIL, $dataSet);
    }
    
    /* 
        ! ************************************************ PRODUCT DEVELOPMENT ***************************************************************
    * /

    /**
     * ! insertInPRDCommentHeader()
     * 
     *  This METHOD INSERT DATA INTO THE THE TABLE PRDCMH (COMMENTS LOG)
     * 
     * @param array $data 
     * @return object 
     */
    private function insertInPRDCommentHeader( $data )
    {       
        $dataSet['PRDCCO'] = $data['codecomment'];       
        $dataSet['PRHCOD'] = $data['projectcode'];                   //product development Code
        $dataSet['PRDPTN'] = trim(strtoupper($data['partnumber']));  //partnumber

        $comment =  isset($data['fromwl']) ? "COMMENTS FROM WL(REG-WL [".$data['wlcode']."]": $data['comments'];

        
        //READY TO INSERT INTO THE COMMMENT DETAIL
        $dataSet['PRDCTX'] = $data['comments'];
        $dataSet['PRDCDC'] = 1;

        $commentsDetailsInserted = $this->insertSentence(self::TABLE_PRDCOMMENTSD, $dataSet);  

        //unsetting this fields 
        unset($dataSet['PRDCTX']); 
        unset($dataSet['PRDCDC']); 

        //comment subject
        $dataSet['PRDCSU'] = strtoupper( $comment );
        $dataSet['USUSER'] = strtoupper( $data['assignedto'] );

        // inserting in TABLE PRDVLH: PRODUCT DEVELOPMENT HEADER
        return $this->insertSentence(self::TABLE_PRDCOMMENTSH, $dataSet) && $commentsDetailsInserted;                   
    }//END: 
    
    /**
     *  ! insert() 
     * 
     * This method insert data into the PRODUCT DEVELOPMENT FILES 
     * 
     *   FILES: 
     *   - PRDVLH : Product Develop Header
     *   - PRDVLD : Product Develop Detail
     *   - PRDCMH : Product Develop. comments header
     *   - PRDCMD : Product Develop. comments detail
     * 
     * @param array() $data | An associative array with all needed data needed for updating all FILES
     * @return boolean | it returns true if the project was created
     */ 

   public function insert( $data ) 
   {   
      //INSERTING PRDVLH ( PRODUCT DEVELOPMENT HEADER )
      $result['PRDVLH']= $this->insertInPRDHeader( $data );            
      
      //INSERTING INTO PRDVLD ( PRODUCT DEVELOPMENT DETAIL)
      $result['PRDVLD']= $this->insertInPRDDetail( $data ); 
      
      //INSERTING INTO PRDCMD ( PRODUCT DEVELOPMENT DETAIL)

      //getting the INDEX (code) for the COMMENT
      //this will be used  for the both below process
      $data['codecomment'] = $this->nextIndex(self::TABLE_PRDCOMMENTSH, 'PRDCCO');

      //inserting to the COMMMENT HEARDER and DETAIL AT THE SAME TIME
      $result['PRDCMH']= $this->insertInPRDCommentHeader( $data );            

      $allInserted = ($result['PRDVLH'] != null) && ($result['PRDVLD'] != null) && ($result['PRDCMH'] != null);
      
      return $allInserted ?? null;                              
    } //END: insert() method() 
 
   /*
    ! *************************** ENDING UP PRODUCT DEVELOPMENT METHODS *************************************************
    */     
   
   
   
    /**
     *  RETURNS: all rows of the table will be returned as an ARRAY 
     * @return array()
     */      
    public function getRows() {
      return $this->rows;
    }
  
    /**
     * This method returns the next index where you can insert a new record 
     * inside the PRDVLH file
     * 
     * @return INTEGER
     */
    public function nextIndex( $table=self::TABLE_PRDHEADER, $field= 'PRHCOD' ) 
    {          
        return $this->queryManager->getMax($field, $table) ?? -1;           
    }//END: nextIndex
    
    /**
     * 
     * @param string $partNumberID | The Part Number will be used to retrieve data from INMSTA  
     * @return array() | RETURN AN ASSOCIATIVE ARRAY WITH THE PART 
     */
    public function getDataItem( $partNumberID ) 
    {
        $partNumberObj =  $this->partNumberManager->getPartNumber( $partNumberID );
        
        if ( $partNumberObj !== null ) {         
            $data['code'] = $this->nextIndex();
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

      } //END: 
      
    

}//END: ProductDevManager class()
