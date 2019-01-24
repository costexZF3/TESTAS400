<?php
namespace Purchasing\ValueObject;

use Zend\Db\Adapter\Adapter as MyAdapter;
use Zend\Db\Sql\Sql;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LostSale
 * -This class is a wrapper. This encapsulates main operatios through the LostSale file 
 *  depend on some criterias 
 * 
 * @author mojeda
 */
class LostSale {
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['Part Number', 'Description', 'Description 2','Description 3', 
                              'Qty Quote', 'Times Quote', 'Sales Last12', 'VND No', 'Vendor Name','Pur. Agent', 
                              'Caterpillar (P/L)', 'Wish List', 'Dev.Proj', 'Dev.Status', 'Loc.20', 'OEM VND', 'Major', 
                              'Category', 'Minor', 'Description'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];
    /*  
     * $conn: it's the adapter injected from LostSaleController 
     * $db: it's and instance from  Zend\Db\Sql\Sql;
     * sqlStr: it contains the Sql STRING that will be excecuted  
     */
    private $conn; 
    private $db;
    
    private $sqlStr= '';  
    private $timesQuote=0;
    private $tableAsHtml = '';
    
    /* constructor */
    public  function __construct(MyAdapter $adaptConn, $timesQuote = 5 ) {
        /* injection adapter connection from LostSaleController*/
        $this->conn = $adaptConn;
        $this->timesQuote = $timesQuote;
        $this->db = new Sql($this->conn);
        $this->sqlStr = $this->getSqlStr();
    }
    
    /*
     * this method return the SqlString generatered by the constructor
     */
    private function getSqlString():string{
       return $this->sqlStr;             
    }    
    
    /*
     * getSqlStr: It returns and STRING tha will be used to execute the SQL query.
     */
    private function getSqlStr():String{
       /* getting date of one year before as string */    
       $fromDate = $this->dateOneYearBefore();
       
      /* create a model class with hand  */ 
       $sqlStr = "with z as (SELECT WRKPTN,qt,t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 TQ from "
                . "(SELECT WRKPTN,(WRK001+ WRK002+ WRK003+ WRK004+ WRK005+ WRK006+WRK007+WRK008+ WRK009+ "
                . "WRK010+ WRK011+ WRK012+ WRK013) QT,"
                . "CASE WRK001 WHEN 0 THEN 0 ELSE 1 END T1,"
                . "CASE WRK002 WHEN 0 THEN 0 ELSE 1 END T2,"
                . "CASE WRK003 WHEN 0 THEN 0 ELSE 1 END T3,"
                . "CASE WRK004 WHEN 0 THEN 0 ELSE 1 END T4,"
                . "CASE WRK005 WHEN 0 THEN 0 ELSE 1 END T5,"
                . "CASE WRK006 WHEN 0 THEN 0 ELSE 1 END T6,"
                . "CASE WRK007 WHEN 0 THEN 0 ELSE 1 END T7,"
                . "CASE WRK008 WHEN 0 THEN 0 ELSE 1 END T8,"
                . "CASE WRK009 WHEN 0 THEN 0 ELSE 1 END T9,"
                . "CASE WRK010 WHEN 0 THEN 0 ELSE 1 END T10,"
                . "CASE WRK011 WHEN 0 THEN 0 ELSE 1 END T11,"
                . "CASE WRK012 WHEN 0 THEN 0 ELSE 1 END T12,"
                . "CASE WRK013 WHEN 0 THEN 0 ELSE 1 END T13 FROM ddtwrk) a where "
                . "t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 >=".$this->timesQuote.") "
                . "select imptn, imdsc, imds2, imds3, (select zoned(sum(odrsq),10,0) from horddt where odrcd = 1 and"
                . " SUBSTR(DIGITS(ODDATE),5 ,2) || SUBSTR(DIGITS(ODDATE),1 ,2) || SUBSTR(DIGITS(ODDATE),3 ,2) >= ".$fromDate." and"
                . " odptn = imptn and odcu# not in (4384,4385, 4381) and"
                . " odlcn in ('01', '07', '05', '02') group by odptn) qtysold, x.onhand, x.onorder, coalesce(x.vendor, '') "
                . "vendor,impc2, (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+"
                . "INMSTA.IMQT06+INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) TQUOTE,"
                . "imprc,invptyf.iptqte Timesq,coalesce((select 'X' from dvinva where dvlocn='20' and"
                . " dvpart=imptn and dvonh#>0),' ') F20,(case when x.vendor = '261339' or x.vendor='060106' or"
                . " x.vendor='262369' or x.vendor = '262673' or x.vendor='261903' or x.vendor='150064'"
                . " then 'X' else '' end) Foem, zoned(coalesce((select count(distinct qdcuno) from qtedtld "
                . "where qdptno=imptn and qdyear||qdmth||qdday >= '".$fromDate."'),0),5,0) Ncus, impc1, imcata, "
               . "(select mindes from mincodes where mincod = inmsta.impc2) mindsc from inmsta left join "
               . "(select dvpart, sum(dvonh#) onhand, sum(dvono#) onorder, max(dvprmg) vendor from dvinva "
               . "where dvlocn in ('01', '05', '07') and trim(dvprmg) <> '' and dvonh# <= 0 and dvono# <= 0 "
               . "group by dvpart) x on inmsta.imptn = x.dvpart inner join invptyf on inmsta.imptn = invptyf.ippart "
               . "where substr(ucase(trim(imdsc)),1,3) <> 'USE' and imsts <> 'D' and impc1 = '01' and"
               . " (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+INMSTA.IMQT06+"
               . "INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) > 0 and"
               . " imptn not in (select dvpart from dvinva where dvlocn in ('01', '05', '07') and"
               . " (dvprmg = '' or dvonh# > 0 or dvono# > 0)) and invptyf.iptqte >= ".$this->timesQuote." union "
               . "select z.wrkptn imptn, coalesce(catdsc,coalesce(kodesc,'N/A')) imdsc, coalesce(imds2, 'N/A') imds2, "
               . "coalesce(imds3, 'N/A') imds3, 0 qtysold, 0 onhand, 0 onorder, '' vendor, impc2, qt tquote, "
               . "coalesce(catprc,coalesce(kopric,0)) imprc, z.TQ Timesq, '' F20, '' Foem, 0 Ncus, impc1, imcata, "
               . "'TEST' mindsc from z left join cater on z.wrkptn = catptn left join inmsta on z.wrkptn = inmsta.imptn"
               . " left join komat on z.wrkptn = koptno where imsts <> 'D' and z.wrkptn not in "
               . "(select dvpart from dvinva where dvlocn in ('01', '05', '07'))";    
       
        return $sqlStr;  
    }
    
    /*
     * Return date as String (one year before) 
     */
    private function dateOneYearBefore(){
        $year = date('y')-1; 
        $month = date('m'); 
        $day= date('d');
        return $year."".$month."".$day;
    }
    
    /*
     * runSql: run the query associated with the sqlStr 
     */
    private function  runSql(){
        try
        {
          $resultSet = $this->conn->query( $this->sqlStr, MyAdapter::QUERY_MODE_EXECUTE );   
        }
        catch (Exception $e){
           echo "Caught exception: ", $e->getMessage(), ""; 
        }        
        return $resultSet;
    }
    
    /*
     * populateHTML : this method populate the table using the resultSet 
     * value returned by the function runSql()
     */
    public function populateHtml(){
       $resultSet = self::runSql();
       // 
       return $resultSet;
    }
    
    /*
     * function: convertDataToHtml() 
     * -this return all processed data as a HTML file. This will be rendered by the view
     */
    
    public function convertDateToHtml(){
        
    }
            
    
}
