<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * / getCategoryDescByStr ( string param ): string /
 *    - this returns the category description of a PartNumber ( the param is the CATEGORY AS ABBREVIATION ) 
 * 
 */

namespace Application\Service;


use Application\Service\QueryRecover as queryManager;
use Application\ObjectValue\PartNumber;

/**
 * Description of PartRecover 
 * - This class is a Service that let us get data from an AS400 TABLE
 * 
 * @author mojeda
 */
class PartNumberManager {         
    /*
     * @var $queryManager queryManager
     */   
    private $queryManager;
       
     /* 
      * @var $queryManager queryManager
      */
    public  function __construct( queryManager $queryManager ) {        
        $this->queryManager = $queryManager;        
    }// END: constructor 
   
   /*-------------------------------------- setters ---------------------------------------------*/   
    
   /* this method retrives or populate the OBJECT */
   private function populatePartNumber( $record ): PartNumber {          
      
     /* retrieving each FIELD VALUE */
//         foreach ( dataSet as $record ) {
            $data['id']              = $record['IMPTN']; //part number
            $data['description']     = $record['IMDSC']; //part number description            
            $data['major']           = $record['IMPC1']; // major product code
            $data['minor']           = $record['IMPC2']; // minor product code
            $data['unitCost']        = $record['IMCST']; // unit cost
            $data['listPrice']       = $record['IMPRC']; // OEM PRICE - LIST PRICE
            $data['sellUnitMeasure'] = $record['IMUMS']; //  SELL UNIT/MEASURE 
            $data['countryOriginal'] = $record['IMCNT']; //  COUNTRY ORIGINAL
            $data['model']           = $record['IMMOD']; //MODEL
            $data['length']          = $record['IMLENG'];// part length
            $data['width']           = $record['IMWIDT']; // part width
            $data['deep']            = $record['IMDPTH']; //part deep
            $data['volumen']         = $record['IMVOLU']; //volumen of the part
            $data['category']        = $record['IMCATA']; //category of the part
            $data['subCategory']     = $record['IMSBCA']; //subcategory of the part
                      
            /* getting the last year Qty quoted*/
            $strSql = "SELECT IPVNUM, IPQQTE, IPQONH, IPQONO FROM INVPTYF WHERE UCASE(IPPART) = '". trim(strtoupper($data['id']))."'";
            $dataSet = $this->queryManager->runSql( $strSql );
            $data['qtyquotedlastyear'] = $dataSet[0]['IPQQTE']?? 0;
            $data['vendor'] = $dataSet[0]['IPVNUM']?? 'NO ASSIGNED';           
            $data['onhand'] = $dataSet[0]['IPQONH']?? 0;
            $data['onorder']= $dataSet[0]['IPQONO']?? 0; //qty on order
            
             //vendor of the part
            
            /* initial description for the vendor */
            $data['vendordesc'] = 'no assigned ';
            
            if ($data['vendor'] != 'NO ASSIGNED' ) { 
               $strSql = "SELECT VMNAME FROM vnmas WHERE VMVNUM = '".$data['vendor']."'";
               
               $vendorName = $this->queryManager->runSql( $strSql );
               $data['vendordesc'] = $vendorName[0]['VMNAME'];
            } 
          
         /* creating and PartNumber */
          $partNumber = new PartNumber( $data );
          
         return $partNumber;   
   }//END: populatePartNumber method
    
   /*---------------------------------------- getters ----------------------------------------------*/
    
    /* this method retrieves the measurements of a PartNumber Object */
    public function getMeasurements( PartNumber $partNumber ) {
       if ($partNumber == null) {return null;}
       
       return ['length'=>$partNumber->getLength(),
               'width'=>$partNumber->getWidth(),
               'deep'=> $partNumber->getDeep() 
       ];
    }
     
    /*
     * returns MAJOR AND MINOR CODE FROM a PartNumberID
     */ 
    public function getMajorMinor( $partNumberId ) {
              
       $strSql = "SELECT DVMJPC, DVMNPC FROM dvinva WHERE DVPART = '".strtoupper( trim( $partNumberId ) )."'"; 
       $dataSet = $this->queryManager->runSql( $strSql );
       
       /* validating data */
       $Major = ($dataSet[0]['DVMJPC'])??'N/A';
       $Minor = ($dataSet[0]['DVMNPC'])??'N/A';
       
       $data = ['major'=>$Major, 
                'minor'=>$Minor
               ];
                  
       return $data;
    }
    
     /* 
      * this method returns the category description associated PartNumber 
      * $category: it's an abbreviation of category
      */    
    public function getCategoryDescByStr(  $param ): string {
       /* checks if $param is instance of PARTNUMBER CLASS */
                    
       $category = (is_a( $param, 'Application\ObjectValue\PartNumber')) ? $param->getCategory(): $param;
       
       $validCategory = ( $category!=='') ? true : false;
       
       if (!$validCategory) { return 'unknow';}
       
              
       $strSql = "SELECT INDESC FROM INMCAT where INCATA = '".strtoupper( trim( $category ) )."'"; 
       $data = $this->queryManager->runSql( $strSql );
       
       return $data[0]['INDESC'];
    }//END: getCategoryDescByStr()

    public function getPartNumber( $partNumberID ): PartNumber {
       
       $strSql = "SELECT * FROM INMSTA WHERE TRIM(UCASE(IMPTN)) = '". strtoupper( trim( $partNumberID ))."'";
        
       
       try {
         $dataSet = $this->queryManager->runSql( $strSql );        
        
         $ExistPartNumber = $dataSet[0]['IMPTN']!== null ? true:false; 
         
         //if exist the part Number then recover all its data
         if ( $ExistPartNumber ) {
            $result = $this->populatePartNumber( $dataSet[0] );
         }
       }catch ( Exception $e ){
         echo 'Caught exception: '.$e->getMessage(),"\n"; 
       }
       finally {
          return $result;
       }
       
    } //END: getPartNumber 
    
} //End: class QueryRecover 
