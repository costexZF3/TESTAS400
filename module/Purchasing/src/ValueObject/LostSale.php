<?php
namespace Purchasing\ValueObject;

use Zend\Db\Adapter\Adapter as MyAdapter;

/**
 * Description of LostSale
 * - This class is a wrapper. This encapsulates main operations through the LostSale file 
 *   depend on some criterias 
 * 
 * @author mojeda
 */
class LostSale {
    /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= null;
    
    /*
     * array with all COLUMN LABELS that will be rendered
     */
    private $columnHeaders = ['Part Number', 'Description', 'Description 2','Description 3', 'Qty Qte', 'Times Qte','Custs. Quote',
                              'Sales Last12', 'VND No', 'VND Name','P. Agent', 'List Price', 'WL', 
                              'Dev.Proj', 'Dev.Status', 'Loc.20', 'OEM VND', 'Major', 'Category', 'Min', 'Desc'];
    /*
     * rows: this array saves all <tr> elements generated running sql query..
     */
    private $rows = [];
    /*  
     * $adapter: it's the adapter injected from LostSaleController      
     * sqlStr: it contains the Sql STRING that will be excecuted  
     */
    private $adapter;     
    
    /* helpful attributes */
    private $sqlStr= '';      
    private $countItems = 0;
    private $tableAsHtml = '';
    
    /* filters */
    private $vendorAssigned = true;
    private $timesQuote= 0;
    private $both = false;
            
    /* constructor */
    public  function __construct( MyAdapter $adapter, $timesQuote = 100, $vndAssignedOptionSelected = 1 ) {
        /* injection adapter adapterection from LostSaleController*/
        $this->adapter = $adapter;
        $this->timesQuote = $timesQuote;
        $this->vendorAssigned = ($vndAssignedOptionSelected==1 )?? false;  
        $this->both = ( $vndAssignedOptionSelected==3 )?? false;
        
        $this->sqlStr = $this->getSqlStr();
        $this->runSql();
    }//constructor 
    
    /*
     * getTimesQuoted()
    */
    
    public function getTimesQuoted() {
        return $this->timesQuote;
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
    private function getSqlStr():String {
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
    
    /*
     * runSql: run the query associated with the sqlStr 
     */
    private function  runSql() {
        try
        {
          $resultSet = $this->adapter->query( $this->sqlStr, MyAdapter::QUERY_MODE_EXECUTE );   
        }
        catch (Exception $e){
           echo "Caught exception: ", $e->getMessage(), ""; 
        }      
        
        $this->dataSet = $resultSet;
        return $resultSet;
    } /* END: runSql */
    
    
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
    
    public function getCountItems(){
        return $this->countItems;
    }
    
    private function getClassNameForTQ( Int $timesQuotes ){
       $className="tq5plus";
       
       /* clasifying all rows of the table according the times quotes: */
        if ( $timesQuotes >= 100 ){
            $className="tq100plus";  
        } else if ( $timesQuotes >= 50 ){
            $className="tq50plus";
        } else if ( $timesQuotes >= 30 ){
            $className="tq30plus";
        } else if ( $timesQuotes >= 10 ){
            $className="tq10plus";
        }        
        return $className;
    }/* END: getClassnameForTQ */
    
    private function convertPAId( $PaID ) {
        $Pa_id ="".$PaID;
        $PaidLen = strlen( $Pa_id );
        $hasCeros = ($PaidLen < 3 )? 3 - $PaidLen : 0;

        if  ($hasCeros!=0) { 
            if ( $hasCeros ===1 ){
                  $Pa_id="0".$Pa_id;
            } else {
                 $Pa_id="00".$Pa_id;
            }                                      
        } //end if   
        
        return $Pa_id;
    } //END: convertPAid() : convert purchasing Agent Id 
    
    /* getting Vendor Name, Purchasing Agent Name */
    private function getVendorData( $vendorNumber ) { 
       if (!$this->vendorAssigned and ($vendorNumber=="" || $vendorNumber=="000000")) { 
          $vendorData =['name'=>'--', 'pagent' =>'--' ]; 
          return $vendorData;           
       }
       /* getting the Purchasing Agent's ID */ 
       $strSql = "SELECT VMNAME, VM#POY AS VENDN FROM VNMAS WHERE VMVNUM = ".$vendorNumber;
       try
        {
            $resultSet = $this->adapter->query( $strSql, MyAdapter::QUERY_MODE_EXECUTE )->toArray();   
            $resultAsArray = $resultSet;

            $PaID = $resultAsArray[0]['VENDN'];

            /* $PaID: we need to convert this to a String with 3 characters
             * for use it as parameter on the Control Table CNTRLL, parameter: '216':
             * which it's used for Purchasing Agents 
             */

            $PAgentToStr = $this->convertPAId($PaID);
            $strSqlCNTRLL = " SELECT CNTDE1 FROM CNTRLL WHERE CNT01 ='216' AND CNT03 = '".$PAgentToStr."'";
            $RS_PAgentName = $this->adapter->query( $strSqlCNTRLL, MyAdapter::QUERY_MODE_EXECUTE )->toArray();

            //$resultAsArray1 = $RS_PAgentName; 
            $vendorData['name'] = $resultAsArray[0]['VMNAME'];  /* vendor name */ 
            $vendorData['pagent'] = $RS_PAgentName[0]['CNTDE1'];  /* purchasing Agent's Name */
        }
        catch (Exception $e){
           echo "Caught exception: ", $e->getMessage(), ""; 
        }
        
        return $vendorData;
    }//End: getVendorData()
    
    //getWishListValue(): it returns It this part exist in the wish list      
    private function getWishListValue( $PartNumber ) {  
       /* getting the Purchasing Agent's ID */ 
       $strSql = "SELECT 'X' WL  From PRDWL WHERE WHLPARTN = '".$PartNumber."'";
       try
        {
          $resultSet = $this->adapter->query( $strSql, MyAdapter::QUERY_MODE_EXECUTE )->toArray(); 
          //$resultSet = $resultSet->toArray();
          /* $isInWL: this returns X if the PartNumber is in the WishList File: PRDWL1 */
          $isInWL = ($resultSet[0]['WL']) ?? '--';  // if it's in WL return X else returns --      
        }
        catch (Exception $e){
           echo "Caught exception: ", $e->getMessage(), ""; 
        }         
        return $isInWL;
    }//end: getWishListValue()

    
    /* Product Development */
    private function getProdDev( $PartNumber ) {         
       $ProdDevData =['isdev'=>'',
                     'status' =>''   
                    ]; 
       
       $strSql = "SELECT prhcod, prdsts FROM PRDVLD4 where prdptn = '".$PartNumber."'";
       try
        {
         $resultSet = $this->adapter->query( $strSql, MyAdapter::QUERY_MODE_EXECUTE )->toArray();         

         $ProdDevData['isdev'] = ($resultSet[0]['PRHCOD'])?? "--";
         $ProdDevData['status'] = ($resultSet[0]['PRDSTS'])?? "--";
        }
        catch (Exception $e){
           echo "Caught exception: ", $e->getMessage(), ""; 
        }
        
        return $ProdDevData; 
    }//End: product development 
    
    /* it returns the resultset from a sqlString passed as parameter */
    private function getResultSet( $strSql ) {
       return $this->adapter->query( $strSql,  MyAdapter::QUERY_MODE_EXECUTE )->toArray();              
    }
    
    /* getCatDescription(): it convert abbrevations of the category to its description 
     */
    private function getCatDescription( $catAbbreviation ) {
        if (!isset( $catAbbreviation )) {
            return 'N/A';
        }
        
        $strSql = "SELECT INDESC FROM INMCAT where INCATA = '".strtoupper( $catAbbreviation )."'";        

        $resultSet = $this->getResultSet( $strSql );
        
        return  ($resultSet[0]['INDESC'])?? "--";       
    }//End: getCatDescription
    
    /**
     * creating rows 
     * 
     * @param type $item
     * @param type $iteration
     * @return array
     */
    private function getRowArray( $item, $iteration )
    {
      
        $tq = "tq".$iteration;

        /**
         * -it returns the Purchasing Agent's name associated with a vendor. 
         * @var $vendorData array 
         */
        $vendorData = $this->getVendorData( $item->VENDOR );

        /* taking Wish List values */
        $wishList = $this->getWishListValue( trim($item->IMPTN) );

        /*Looking for Product Development Data */
        $proDevData = $this->getProdDev( trim( $item->IMPTN ) );
        
        $catDescription = $this->getCatDescription( $item->IMCATA );


        $record = [ 'Part Number'       => ['value'=> $item->IMPTN,        'class'=>"partnumber", 'id'=>''],
                    'Description'       => ['value'=> $item->IMDSC,       'class'=>"description", 'id'=>''],
                    'Description 2'     => ['value'=> $item->IMDS2,       'class'=>"description", 'id'=>''],
                    'Description 3'     => ['value'=> $item->IMDS3,       'class'=>"description", 'id'=>''],
                    'Qty Quote'         => ['value'=>($item->TQUOTE)??0,  'class'=>'', 'id'=>''],
                    'Times Quote'       => ['value'=>($item->TIMESQ)??0,  'class'=>"timesq", 'id'=> $tq],
                    'Custs. Quote'      => ['value'=>($item->NCUS)??0,    'class'=>'', 'id'=>''],           
                    'Sales Last12'      => ['value'=>($item->QTYSOLD)??0, 'class'=>'', 'id'=>'', 'title'=>'Qty Sold Last 12 Month'],            
                    'VND No'            => ['value'=>($item->VENDOR)??'N/A', 'class'=>'description', 'id'=>'', 'title'=>'Vendor Number'], 

                    'Vendor Name'       => ['value'=> $vendorData['name'],   'class'=>"description", 'id'=>''], 
                    'Pur. Agent'        => ['value' => $vendorData['pagent'], 'class'=>"description", 'id'=>''],             
                    'List Price'        => ['value'=> number_format($item->IMPRC,2)?? 0, 'class'=>"money", 'id'=>''],            
                    'Wish List'         => ['value'=> $wishList,               'class' => "", 'id'=>''],
                    'Dev.Proj'          => ['value'=> $proDevData['isdev']??'N/A',          'class' => "", 'id'=>'', 'title'=>'Cod. Dev. Proj'],
                    'Dev.Status'        => ['value'=> $proDevData['status']??'N/A',         'class' => "", 'id'=>'', 'title'=>'Prod. Dev. Status'],
                    'Loc.20'            => ['value'=> ($item->F20)?? 0,   'class'=>'', 'id'=>''],
                    'OEM VND'           => ['value'=> ($item->FOEM)??0,   'class'=>'', 'id'=>'', 'title'=>'X->OEM Vendor'],
                    'Major'             => ['value'=> ($item->IMPC1)??0,  'class'=>'', 'id'=>'', 'title'=>'Mayor Code'],
                    'Category'          => ['value'=> $catDescription,    'class'=>'description', 'id'=>'', 'title'=>'Category'],
                    'Minor'             => ['value'=> ($item->IMPC2)??0,  'class'=>'description', 'id'=>'', 'title'=>'Minor'],
                    'Minor Description' => ['value'=> ($item->mindsc)??'N/A', 'class'=>"description", 'id'=>'', 'title'=>'Minor'],                     
          ];            

        return $record;
    } //END: function getRow()
    
    /**
     *  - This function creates a <TR> element and assigned the CLASS, ID, 
     *    and TITLE attributes for each <TD> element
     * 
     * @param array $row
     * @param string $classNameTQ
     * @return string
     */
    private function rowToHTML( $row, $classNameTQ )
    {
        //$result ='<tr class="'.$classNameTQ.'">';
        
        $trClass= ($classNameTQ!=='')? ' class="'.$classNameTQ.'">' : '>';
        $result ='<tr'.$trClass;
        
        foreach( $row as $item ){
            /*retriving the classes and ids attribute values*/  
            $classAttr = $item['class']??'';
            $idAttr = $item['id']??'';
            $value = $item['value']??''; 
            $title = $item['title']??'';
            
            $result .= '<td class= "'.$classAttr.'" id="'.$idAttr.'" title = "'.$title.'">'.$value.'</td>';
        }
        
        $result .= '</tr>';        
        return $result;
    }//END. rowToHTML()
    
    /**
     * - This return all processed data as a HTML file. 
     *   This will be rendered by the view
     */    
    public function getGridAsHtml()
    {
        //checking if the method: runSql() was invoked before...
             
        if (!$this->dataSetReady()) { return '';}      
              

        $iteration = 1;
        /*********** adding tbody element ***************/       
        $tableBody = '<tbody>';        

        /************* dynamic body **********************/        
        foreach ($this->dataSet as $item) { 
            //checking is there is vendorAssigned
            if ($this->vendorAssigned and (trim($item->VENDOR)==="" || trim($item->VENDOR)==="000000" )){ continue; }
            if (!$this->both ) {
              if (!$this->vendorAssigned and (trim($item->VENDOR)!='' and trim($item->VENDOR)!="000000")) { continue;}                  
            }

            /*
             *  retriving the className added to each <tr> element 
             * this classify each row attending THE TIMES QUOTES: tq10plus, tq100plus, etc
             */
            $className = $this->getClassNameForTQ( $item->TIMESQ );

            /* 
             * Retriving a ROW... 
             */
            $row = $this->getRowArray( $item, $iteration );

            /* conver row to HTML: $row  */
            $tableBody.= $this->rowToHTML( $row, $className ); 

            $iteration++;

        }//end: foreach    

         $tableBody.= '</tbody>';
        
              
        $this->tableAsHtml = $tableBody;              
        $this->countItems = --$iteration;
        
        return  $this->tableAsHtml;
    }/* END: getGridAsHtml()*/    
    
            
    
}//END: LostSale class()
