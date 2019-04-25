<?php

namespace Purchasing\Service;

use Application\Service\QueryManager as queryManager;
use Application\Service\PartNumberManager;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


/**
 * - Description of WishListManager
 *    - This class is a wrapper. This encapsulates main operations through the WishList file 
 *      depend on some criteria
 *    - methods privates
 *       -   
 * 
 * @author mojeda
 */

class WishListManager 
{
   /* reason type */
   const NEWVENDOR = 'newvendor';  
   const NEWPART =  'newpart';  
   
   /* status in the wishlist for an item */
   const STATUS_OPEN     = '1'; // initial status
   const STATUS_REJECTED = '2'; // rejected may be this part won't be develpmented anymore
   const STATUS_APROVED  = '3'; // the part was moved to achanges to aproved   
   const STATUS_HOLD     = '4'; // the part is ready (it's APPROVED) but it won't be moved to development yet
   const STATUS_CLOSED   = '5'; // the parts won't be analysed with hurry
   
   const FROM_LOSTSALE = '1';
   const FROM_VENDORLIST = '2';
   const FROM_MANUAL = '3';
   const FROM_EXCEL = '4';
   
  
   const FIELDS = ['WHLCODE', 'WHLUSER', 'WHLPARTN', 'WHLSTATUS', 'WHLSTATUSU',  'WHLREASONT', 'WHLFROM',
                   'WHLCOMMENT'
                  ];
   
   const TABLE_WISHLIST = 'PRDWL';
   const TABLE_WISLISTADD = 'PRDWLADD';
    
   protected $reasontype = [ self::NEWVENDOR => "New Vendor", self::NEWPART => "New Part"];
   
   protected $status = [ self::STATUS_OPEN      => "OPEN",                         
                         self::STATUS_REJECTED  => "REJECTED",                         
                         self::STATUS_APROVED   => "APROVED",
                         self::STATUS_HOLD      => "HOLD",
                         self::STATUS_CLOSED    => "CLOSE", 
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
    private $columnHeaders = ['From','Code','Date', 'User','Part Number', 'Description', 'Status','Year Sales',
                              'Qty Quoted','Times Quoted', 'OEM Price', 'Loc20-STK', 'Model', 'Category',
                              'SubCat', 'Major','Minor', 'ACTION'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];    
    private $rawTable = [];    
    
    /**  
     * SERVICE: it's the SERVICE injected from WishListController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
     * @var queryManager
     */
    private $queryManager;  
    
    /**
     * service to retrieve PartNumber details
     * @var Application\Service\PartNumberManager
     */
    private $partNumberManager;
    
    /* helpful attributes */        
    private $countItems = 0;
    private $tableAsHtml = '';
    
    /**
     * @param  queryManager $queryManager  
     * @param  PartNumberManager $PNManager
     */
    public  function __construct( $queryManager, $PNManager ) 
    {
        /* injection adapter adapterection from WishListController*/
        
        $this->queryManager = $queryManager;
        $this->partNumberManager = $PNManager;
        
        
        $this->refreshWishList();           
    }//END:constructor 
        
    private function refreshWishList()
    {
       $strSql =  $this->getSqlStr();         
       $this->dataSet = $this->queryManager->runSql( $strSql );       
       
       $this->countItems = count( $this->dataSet ); 
    }
    
    /*populate all data */
    private function populateDataMatriz() 
    {
      /* 
       * getting data dinamically
       * Pushing on the first ROW the Header of each column
       */        
            
      foreach ($this->dataSet as $row) { 
         /* gettin row */
         $rowAsArray = $this->rowToArray( $row );
         /* each row pushing to the rows (body to render)*/
         array_push( $this->rawTable, $rowAsArray );                
         
      }//end: foreach         
      
    }//END METHOD: populateDataMatriz() method...
    
   
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
    
    /**
     * @return string  |  It returns a STRING that will be used to execute the SQL query.
     */
    private function getSqlStr():String 
    {          
       $sqlStr = "SELECT * FROM ( SELECT  IMPTN, IMDSC, IMPC1,IMPC2,IMCATA,IMSBCA,IMMOD, IMPRC     
                  FROM WHLINMSTAJ UNION                                                                     
                  SELECT  WHLPARTN, WHLADDDESC, WHLADDMAJO, WHLADDMINO, WHLADDCATE, WHLADDSUBC, WHLADDMODE, WHLADDPRIC                       
                  FROM WHLADDINMJ ) y                                               
                  INNER JOIN PRDWL on y.IMPTN = PRDWL.WHLPARTN                       
                  LEFT JOIN invptyf on y.IMPTN = invptyf.IPPART ORDER BY PRDWL.WHLCODE ASC ";
       
       return $sqlStr;  
    }//END: getSqlString()
    
    /*
     * Return date as String (one year before)  
     */
    private function dateOneYearBefore()
    {
        $year = date('y')-1; 
        $month = date('m'); 
        $day= date('d');
        return $year."".$month."".$day;
    }
    
    
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
        $dataSet['WHLUSER'] = strtoupper($data['user']);
        $dataSet['WHLPARTN'] = strtoupper($data['partnumber']);
        $dataSet['WHLSTATUS'] = self::STATUS_OPEN;
        $dataSet['WHLSTATUSU'] = strtoupper($data['user']);
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
 
    
    /**
     * - This function creates a <TR> element and assigned the CLASS, ID, 
     *    and TITLE attributes for each <TD> element
     * 
     * @param array $row
     * @return string
     */
    private function rowToHTML( $row ):string
    {   
        $result ='<tr>';  
        $col = 0;
        $className = '';
        
        /* Exclude or discard the following COLUMNS to use the CLASS description 
         * first coluns is 0
         */
        
        $columns = [6, 7, 8, 10, 11];
        foreach( $row as $item ) {              
           if (!in_array( $col, $columns ) ) {
              $className = "description";
           } else if ( $col === 10 ) { $className = "money";}
           else {$className = '';}
              
            $result .= '<td class="'.$className.'">'.$item.'</td>';
          $col++;  
        }//endforeach 
        
        $result .= '</tr>';        
        return $result;
    }//END METHOD: rowToHTML()
    
    /**
     *  RETURNS: all rows of the table will be returned as an ARRAY 
     * @return array
     */ 
     
    public function getRows() {
        return ($this->rows)?? null;
    }
    
    
    private function rowToArray( $row ) {
          
        $result = [];
        $partNumberInWL = trim($row['WHLPARTN']);
        $fromValue = $this->from[$row['WHLFROM']];
        $statusP = $this->status[$row['WHLSTATUS']];
        
        array_push( $result, $fromValue );   // index: 0 : code
        array_push( $result, $row['WHLCODE'] );         //index: 1 WL No
        array_push( $result, $row['WHLDATE'] );  // index: 2 DATE
        array_push( $result, $row['WHLUSER'] );  // index: 3 - USER IN CHARGE
        array_push( $result, $partNumberInWL ); // index: 4 - PART NUMBER IN WL
        array_push( $result, $row['IMDSC'] );   // index: 5 - description
        
        //NEW COLUMS AND REQUIREMENTS 
        array_push( $result, $statusP );   // index: 6 - description
        
        array_push( $result, $row['IPYSLS'] );  // index: 7 - year sales
        array_push( $result, $row['IPQQTE'] );  // index: 8 - qty quoted
        array_push( $result, $row['IPTQTE'] );  // index: 9 - times quoted
        array_push( $result, number_format( $row['IMPRC'], 2 )); //index: 10 - OEM PRICE
        
        /*  getting location from DVINVA  where the part has STOCK in  ( DVBIN#: if you need the bin location) 
         *  - location: 20
         *  - dvonh#: (qty on hand) > 0  ( hay alguna on hand ) */        
        $strSql = "SELECT DVONH# FROM DVINVA WHERE UCASE(TRIM(DVPART))='". strtoupper( $partNumberInWL ).
                "' and dvlocn ='20' and DVONH# > 0";
               
        $dataSet = $this->queryManager->runSql( $strSql );
        
        array_push( $result, $dataSet[0]['DVONH#']?? '0' ); // index: 10 location
        
        /* adding MODEL */
        array_push( $result, trim($row['IMMOD']) != '' ? $row['IMMOD']:'N/A' ); //index: 11 model
        
        //passing an OBJECT
//         $partNumberOBJ = $this->PartNumberManager->getPartNumber( $partNumberInWL );
//         $CatDescription =  $this->PartNumberManager->getCategoryDescByStr( $partNumberOBJ );
         
        /* ADDING CATEGORY DESCRIPTION BEST CASE 5.3 S*/
        $cat = $row['IMCATA'];         
        $CatDescription =  $this->partNumberManager->getCategoryDescByStr( $cat );           
        array_push( $result, $CatDescription ); // index: 12 - Category Description 
         
         /* SUB-CATEGORY */
         array_push( $result, $row['IMSBCA'] ); //index: 13 - Subcategory
         
         /* mayor and minor */
         //$mayorMinor = $this->partNumberManager->getMajorMinor( $partNumberInWL );
         array_push( $result, $row['IMPC1'] ); // index; 14 - Major code
         array_push( $result, $row['IMPC2'] ); // index: 15 - Minor code 
       
         /* inserting  link to the other options */
        $strLink = '" comment-'.$row["WHLCOMMENT"].'number-'.$row['WHLCODE'].'"'; 
        
        $url = '<a href='.'login'.' class="btn btn-default btn-rounded" data-toggle="modal" data-target="#modalLoginAvatar">♣</a>';
         
         array_push( $result, $url  ); // index: 15 - Minor code 
         
        return $result;
    }
    
    
    /* builts the TBODY part */
    private function getBodyTable():string {
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
          $tableBody.= $this->rowToHTML( $currentRow ); 

        $iteration++;
      }//end: foreach  
      
      return $tableBody;
    }//END: getBodyTable() method
    
    /*
     * function: dataToHtml() 
     * -this return all processed data as a HTML file. This will be rendered by the view
     */    
    public function TableAsHtml(){
        //checking if the method: runSql() was invoked before...
             
        if (!$this->dataSetReady()) { return '';}      
        
        /* ------------ creating table with all data from dataSet -----------------------*/
        $tableHeader = '<table class="table_ctp table_filtered display">';
        $tableHeader.='<thead><tr>';  
        
         /*********** generating each column label dynamically *****************/
         foreach ($this->columnHeaders as $field) {           
            $tableHeader.='<th class="description">'.$field.'</th>';    
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
                $tableBody.= $this->rowToHTML( $currentRow ); 
                
              $iteration++;
            }//end: foreach    

            $tableFooter = '<tfoot class="description"><tr>';

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

            $data['vendor'] = $partNumberObj->getVendor();
            $data['vendordesc'] = $partNumberObj->getVendorDescription(); 

            $data['tqLastYear'] = $partNumberObj->getQtyQuotedLastYear();

            $data['comment'] = '';
            $data['type'] = '';

            return $data;
         }

         // return 'error' if the PartNumber not exits in the DATABASE 
         return $data['error'] = $partNumberObj['error'];    

      } //END: getWishListItem()
      
      
      
      /**************************************************** EXCEL MANANGER *********************************/
      
        /**
     * 
     * @param type $spread | spreadsheet generated 
     * @param type $cell | Cell will be hightlighter
     * @param type $bold | True or False if it will be BOLD
     * @param type $color | color
     */
    private function highLighter( $spread, $cell, $bold=true, $color="" ) 
    {        
        $styleOptions = ['font'=>['bold'=> $bold, 'color'=>['rgb'=> $color]]];        
        
        $spread->getActiveSheet()->getStyle($cell)->applyFromArray( $styleOptions );         
    }     
    
    /**
     * - This Method() adds headers to the sheet of the EXCEL FILE
     * 
     * @param Spreadsheet $sheet
     * @param array() $options
     */
    private function createSheetHeaders($sheet)
    {     
        
        $options = [ '1'=>['cell'=>'A1', 'desc' =>'COD', 'dimension'=>-1], //-1: dimension by default 
                          '2'=>['cell'=>'B1', 'desc' =>'PART NUMBER', 'dimension'=>26],
                          '3'=>['cell'=>'C1', 'desc' =>'ERRORS', 'dimension'=>26],
            ];
        
        $sheet->getActiveSheet()->freezePane('A2'); //freezing TOP COLUMN
        
        foreach ($options as $key => $value)    {
            $sheet->getActiveSheet()->setCellValue($value['cell'], $value['desc']);
            
            $this->highLighter($sheet, $value['cell']);
            if ($value['dimension'] != -1 ) {
                $sheet->getActiveSheet()->getColumnDimensionByColumn( $key )->setWidth($value['dimension']);                 
            }
        }        
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
     *  -the sheet created has as name: INCONS_<actualdate>
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
    
     /* 
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
