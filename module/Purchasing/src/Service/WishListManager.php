<?php

namespace Purchasing\Service;

use Application\Service\QueryRecover as queryManager;
use Application\Service\PartNumberManager;

use Application\ObjectValue\PartNumber;


/**
 * Description of WishList (class Controller )
 * - This class is a wrapper. This encapsulates main operations through the WishList file 
 *   depend on some criteria 
 * 
 * @author mojeda
 */

class WishListManager {
    /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= [];
    
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['Code','WL No.','Date', 'User','Part Number', 'Description', 'Year Sales','Qty Quoted',
                              'Times Quoted', 'OEM Price', 'Location', 'Model', 'Category', 'SubCat', 'Major','Minor'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];    
    /*  
     * SERVICE: it's the SERVICE injected from WishListController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
     * @var $queryManager  queryManager
     */
    private $queryManager;  
    
    /*
     * service to retrieve PartNumber details
     * @var Application\Service\PartNumberManager
     */
    private $PartNumberManager;
    
    /* helpful attributes */        
    private $countItems = 0;
    private $tableAsHtml = '';
     
              
    /* constructor */
    /*
     * @var $dService queryManager 
     * @var $PNManager PartNumberManager
     */
    public  function __construct( $queryManager, $PNManager) {
        /* injection adapter adapterection from WishListController*/
        
        $this->queryManager = $queryManager;
        $this->PartNumberManager = $PNManager;
        
        $strSql =  $this->getSqlStr(); 
        $this->dataSet = $this->queryManager->runSql( $strSql );
        $this->countItems = count( $this->dataSet );      
    }//END:constructor 
    
    /*
     * getSqlStr: It returns and STRING tha will be used to execute the SQL query.
    */
    private function getSqlStr():String {
//     $fields =' PRDWL.PRWCOD PRDWL.CRDATE PRDWL.CRUSER PRDWL.PRWPTN INMSTA.IMDSC INMSTA.IMPRC INVPTYF.IPYSLS INVPTYF.IPQQTE INVPTYF.IPTQTE ' ;
             
       $sqlStr = "SELECT * FROM PRDWL INNER JOIN INMSTA "
                . "ON TRIM(UCASE(PRDWL.PRWPTN)) = TRIM(UCASE(INMSTA.IMPTN))"
                . "LEFT JOIN INVPTYF ON TRIM(UCASE(INVPTYF.IPPART)) = TRIM(UCASE(PRDWL.PRWPTN)) "
                . "ORDER BY PRDWL.CRDATE DESC";
       
        return $sqlStr;  
    }//END: getSqlString()
    
    /*
     * Return date as String (one year before) 
     */
    private function dateOneYearBefore(){
        $year = date('y')-1; 
        $month = date('m'); 
        $day= date('d');
        return $year."".$month."".$day;
    }
    
    
    private function dataSetReady(){
        return ($this->dataSet!=null)??false;
    }
        
    /*
     * populateHTML : this method populate the table using the resultSet 
     * value returned by the function runSql()
     */
    public function getDataSet(){              
       return $this->dataSet;
    } 
    
    public function CountItems() {
        return count( $this->dataSet );
    }
     
 
    /* this function creates a <TR> element and assigned the CLASS, ID, and TITLE attributes for each <TD> element */
    private function rowToHTML( $row ):string{                
        
        $result ='<tr>';  
        $col = 0;
        $className = '';
        
        /*exclude or discard the following COLUMNS to use the CLASS description */
        $columns = [6, 7, 8, 9];
        foreach( $row as $item ){              
           if (!in_array( $col, $columns ) ) {
              $className = "description";
           } else if ( $col === 9 ) { $className = "money";}
           else {$className = '';}
              
            $result .= '<td class="'.$className.'">'.$item.'</td>';
          $col++;  
        }        
        $result .= '</tr>';        
        return $result;
    }
    
    /* 
     * RETURNS: all rows of the table will be returned as an ARRAY 
     */
    public function getRows() {
        return ($this->rows)?? NULL;
    }
    
    
    
    private function RowToArray( $row ) {
        $result = [];
        $partNumberInWL = trim($row['PRWPTN']);
        
        array_push( $result, '@' );
        array_push( $result, $row['PRWCOD'] );        
        array_push( $result, $row['CRDATE'] );
        array_push( $result, $row['CRUSER'] );
        array_push( $result, $partNumberInWL );
        array_push( $result, $row['IMDSC'] );
        array_push( $result, $row['IPYSLS'] );
        array_push( $result, $row['IPQQTE'] );
        array_push( $result, $row['IPTQTE'] );
        array_push( $result, number_format( $row['IMPRC'], 2 ));
        
        /*  getting location from DVINVA  */        
        $strSql = "select DVBIN# from dvinva where UCASE(TRIM(dvpart))='". strtoupper( $partNumberInWL ).
                "' and dvlocn ='20' and dvonh# > 0";
               
        $dataSet = $this->queryManager->runSql( $strSql );
        array_push( $result, $dataSet[0]['DVBIN#']?? 'N/A' );
        
        /* adding MODEL */
        array_push( $result, trim($row['IMMOD'])!=''? $row['IMMOD']:'N/A' );
        
        //passing an OBJECT
//         $partNumberOBJ = $this->PartNumberManager->getPartNumber( $partNumberInWL );
//         $CatDescription =  $this->PartNumberManager->getCategoryDescByStr( $partNumberOBJ );
         
        /* ADDING CATEGORY DESCRIPTION BEST CASE 5.3 S*/
         $cat = $row['IMCATA'];
         $CatDescription =  $this->PartNumberManager->getCategoryDescByStr( $cat );       
         array_push( $result, $CatDescription );
         
         /* SUB-CATEGORY */
         array_push( $result, $row['IMSBCA'] );
         
         /* mayor and minor */
         $mayorMinor = $this->PartNumberManager->getMajorMinor( $partNumberInWL );
         array_push( $result, $mayorMinor['major'] );
         array_push( $result, $mayorMinor['minor'] );
         
        return $result;
    }
    
    
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

            $iteration = 0;
            /*********** adding tbody element ***************/       
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

            $tableFooter = '<tfoot><tr>';

            /*********** generating each column label dynamically *****************/
            foreach ($this->columnHeaders as $field) {           
                $tableFooter.='<td>'.$field.'</td>';
            }

            $tableFooter.='</tr></tfoot>';       
              
        $this->tableAsHtml = $tableHeader.$tableBody.$tableFooter;               
        
        return  $this->tableAsHtml;
    }/* END: getGridAsHtml()*/    
    
            
    
}//END: WishList class()
