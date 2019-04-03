<?php

namespace Purchasing\Service;

use Application\Service\QueryManager as queryManager;
use Application\Service\PartNumberManager;
use Application\ObjectValue\PartNumber;


/**
 * Description of WishList (class Controller )
 * - This class is a wrapper. This encapsulates main operations through the WishList file 
 *   depend on some criteria 
 * 
 * @author mojeda
 */

class WishListManager 
{
   /* reason type */
   const NEWVENDOR = 'newvendor';  
   const NEWPART =  'newpart';  
   
   /* status in the wishlist for an item */
   const STATUS_OPEN = '1';
   const STATUS_CLOSE = '2';
   const STATUS_CLOSEBYDEVELOPMENT = '3';
   
   const FROM_MANUAL ='manual';
   const FROM_LOSTSALE ='lostsale';
   const FROM_EXCEL_FILE = 'excel';
   
  
   const FIELDS = ['WHLCODE', 'WHLUSER', 'WHLPARTN', 'WHLSTATUS', 'WHLSTATUSU',  'WHLREASONT', 'WHLFROM',
                   'WHLCOMMENT'
                  ];
   
   const TABLE_NAME = 'PRDWL';
    
   protected $reasontype = [ self::NEWVENDOR => "New Vendor", self::NEWPART => "New Part"];
   protected $from = [ self::FROM_MANUAL => "1", self::FROM_LOSTSALE => "2", self::FROM_EXCEL_FILE => "3"];
   
   
   /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= [];
    
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['Info','Code','Date', 'User','Part Number', 'Description', 'Year Sales',
                              'Qty Quoted','Times Quoted', 'OEM Price', 'Loc20-STK', 'Model', 'Category',
                              'SubCat', 'Major','Minor'];
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
     * @return STRING : It returns and STRING that will be used to execute the SQL query.
     */
    private function getSqlStr():String 
    {        
       $sqlStr = "SELECT * FROM PRDWL LEFT JOIN INMSTA "
                . "ON TRIM(UCASE(PRDWL.WHLPARTN)) = TRIM(UCASE(INMSTA.IMPTN))"
                . "LEFT JOIN INVPTYF ON TRIM(UCASE(INVPTYF.IPPART)) = TRIM(UCASE(PRDWL.WHLPARTN)) "
                . "ORDER BY PRDWL.WHLCODE ASC";
       
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
     * @param array() $data | An associative array with all needed data inside a WL row.
     * @return object | it returns null is could not insert the field 
     */ 
    public function insert( $data, $from ) 
    {              
        $dataSet['WHLCODE'] = $data['code'];
        $dataSet['WHLUSER'] = strtoupper($data['user']);
        $dataSet['WHLPARTN'] = strtoupper($data['partnumber']);
        $dataSet['WHLSTATUS'] = self::STATUS_OPEN;
        $dataSet['WHLSTATUSU'] = strtoupper($data['user']);
        $dataSet['WHLREASONT'] = $data['type'];        
        $dataSet['WHLFROM'] =  $this->from[$from];
        $dataSet['WHLCOMMENT'] = $data['comment'];
       
        // inserting in TABLE WL: PRDWL the set of data 
        $this->dataSet = $this->queryManager->insert( self::TABLE_NAME , $dataSet );
       
       return $this->dataSet ?? null;             
    } //END: AddItem method
 
    
    /**
     * This function creates a <TR> element and assigned the CLASS, ID, 
     * and TITLE attributes for each <TD> element
     * @param array $row
     * @return string
     */
    private function rowToHTML( $row ):string
    {   
        $result ='<tr>';  
        $col = 0;
        $className = '';
        
        /*exclude or discard the following COLUMNS to use the CLASS description */
        $columns = [6, 7, 8, 9, 10];
        foreach( $row as $item ) {              
           if (!in_array( $col, $columns ) ) {
              $className = "description";
           } else if ( $col === 9 ) { $className = "money";}
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
    
    
    private function RowToArray( $row ) {
        $result = [];
        $partNumberInWL = trim($row['WHLPARTN']);
        
        array_push( $result, '@' );   // index: 0 : code
        array_push( $result, $row['WHLCODE'] );         //index: 1 WL No
        array_push( $result, $row['WHLDATE'] );  // index: 2 DATE
        array_push( $result, $row['WHLUSER'] );  // index: 3 - USER IN CHARGE
        array_push( $result, $partNumberInWL ); // index: 4 - PART NUMBER IN WL
        array_push( $result, $row['IMDSC'] );   // index: 5 - description
        array_push( $result, $row['IPYSLS'] );  // index: 6 - year sales
        array_push( $result, $row['IPQQTE'] );  // index: 7 - qty quoted
        array_push( $result, $row['IPTQTE'] );  // index: 8 - times quoted
        array_push( $result, number_format( $row['IMPRC'], 2 )); //index: 9 - OEM PRICE
        
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
         $mayorMinor = $this->partNumberManager->getMajorMinor( $partNumberInWL );
         array_push( $result, $mayorMinor['major'] ); // index; 14 - Major code
         array_push( $result, $mayorMinor['minor'] ); // index: 15 - Minor code 
         
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
        
        /*
         * ------------ creating table with all data from dataSet ----------------------------
         * 
        */
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
    
    
    /**
     * 
     * @param string $partNumberID | The Part Number will be used to retrieve data from INMSTA  
     * @return array() | RETURN AN ASSOCIATIVE ARRAY WITH THE PART 
     */
    public function getDataItem( $partNumberID ) 
    {
        $partNumberObj =  $this->partNumberManager->getPartNumber( $partNumberID );
        
        if ( $partNumberObj !== null ) {         
            $data['code'] = $this->queryManager->getMax('WHLCODE', 'PRDWL');
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
            
    
}//END: WishList class()
