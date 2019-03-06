<?php

namespace Purchasing\Entity;

use Application\Service\QueryRecover as MyQueryRecover;


/**
 * Description of WishList (class Controller )
 * - This class is a wrapper. This encapsulates main operations through the WishList file 
 *   depend on some criteria 
 * 
 * @author mojeda
 */

class WishList {
    /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= [];
    
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['CODE', 'DATE', 'USER','PART NUMBER', 'DESCRIPTION', 'YEAR SALES','QTY QUOTED',
                              'TIMES QUOTED', 'OEM PRICE', 'LOCATION'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];    
    /*  
     * SERVICE: it's the SERVICE injected from WishListController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
     */
    private $dbService;     
    
    /* helpful attributes */
    private $sqlStr= '';      
    private $countItems = 0;
    private $tableAsHtml = '';
    
  
              
    /* constructor */
    public  function __construct( MyQueryRecover $dbService ) {
        /* injection adapter adapterection from WishListController*/
        
        $this->dbService = $dbService;
        $sqlStr = $this->getSqlStr();
        $this->dataSet = $this->dbService->runSql( $sqlStr );
              
    }//END:constructor 
    
      
   /*
    * this method return the SqlString generatered by the constructor
    */
    private function getSqlString():string{
       return $this->sqlStr;             
    }    
    
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
    
    public function CountItems(){
        return count($this->dataSet);
    }
     
 
    /* this function creates a <TR> element and assigned the CLASS, ID, and TITLE attributes for each <TD> element */
    private function rowToHTML( $row ):string{                
        
        $result ='<tr>';        
        foreach( $row as $item ){                                    
            $result .= '<td>'.$item.'</td>';
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
        
        array_push( $result, $row['PRWCOD'] );
        
        array_push( $result, $row['CRDATE'] );
        array_push( $result, $row['CRUSER'] );
        array_push( $result, $row['PRWPTN'] );
        array_push( $result, $row['IMDSC'] );
        array_push( $result, $row['IPYSLS'] );
        array_push( $result, $row['IPQQTE'] );
        array_push( $result, $row['IPTQTE'] );
        array_push( $result, number_format( $row['IMPRC'], 2 ));
        
        /* getting location from DVINVA */
        
        $strSql = "select DVBIN# from dvinva where UCASE(TRIM(dvpart))='". strtoupper(trim($row['PRWPTN'])).
                "' and dvlocn ='20' and dvonh# > 0";
        
        
        $dataSet = $this->dbService->runSql( $strSql );
        
//        print_r( $dataSet[0]['DVBIN#'] ); exit();
        
        $binLoc = $dataSet[0]['DVBIN#']?? 'Unkown';
        array_push( $result, $binLoc );
        
       
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
