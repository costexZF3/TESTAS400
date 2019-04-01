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


use Application\Service\QueryManager as queryManager;
use Application\ObjectValue\PartNumber;
use Application\Validator\PartNumberValidator;

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
    
   /**
    * 
    * @param array $record
    * @return PartNumber
    */
   private function populatePartNumber( $record ){          
      
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
            
            /*  getting the last year Qty quoted
             *  IPPART
             */
            
            
            $strSql = "SELECT * FROM INVPTYF WHERE UCASE(IPPART) = '". trim($data['id'])."'";
            $dataSet = $this->queryManager->runSql( $strSql );
            
          
            if ($dataSet!==null) {
                $data['qtyquotedlastyear'] = $dataSet[0]['IPQQTE']?? 0;
                $data['vendor'] = trim($dataSet[0]['IPVNUM']) !=='' ? $dataSet[0]['IPVNUM'] : 'NA' ;           
                $data['onhand'] = $dataSet[0]['IPQONH']?? 0;
                $data['onorder']= $dataSet[0]['IPQONO']?? 0; //qty on order

                 //vendor of the part

                /* initial description for the vendor */
                $data['vendordesc'] = 'NA';
            
            
          
                if ( strlen($data['vendor']) > 2 ) { 
                   $strSql = "SELECT * FROM VNMAS WHERE VMVNUM = ".$data['vendor']."";

                   $vendorName = $this->queryManager->runSql( $strSql );
                   $data['vendordesc'] = $vendorName[0]['VMNAME']??'';

                } else {
                   $data['vendor'] ='NA';
                } 
            }
         /* creating a PartNumber Object */
          $partNumberObj = new PartNumber( $data );
          
         return $partNumberObj;   
   }//END: populatePartNumber method
    
   /*---------------------------------------- getters ----------------------------------------------*/
    
   /* this method retrieves the measurements of a PartNumber Object  */
   /**
    * 
    * @param PartNumber $partNumber
    * @return array | it returns an associative array with all dimension of the part
    */
    public function getMeasurements( PartNumber $partNumber ) {
       if ($partNumber == null) {return null;}
       
       return ['length' => $partNumber->getLength(),
               'width' => $partNumber->getWidth(),
               'deep' => $partNumber->getDeep() 
       ];
    }//END METHOD: getMeasurements()
     
    /**      
     * @param string $partNumberId | the parameter is an STRING 
     * @return array() | returns an ARRAY with major and minor code of the part 
     */
      
    public function getMajorMinor( $partNumberId ) 
    {
              
       $strSql = "SELECT IMPC1, IMPC2 FROM INMSTA WHERE IMPTN = '".strtoupper( trim( $partNumberId ) )."'"; 
       $dataSet = $this->queryManager->runSql( $strSql );
       
       /* validating data */
       
       $major = ($dataSet[0]['IMPC1']) ?? 'N/A';
       $minor = ($dataSet[0]['IMPC2']) ?? 'N/A';
       
       $data = [
                'major' => $major, 
                'minor' => $minor
               ];
                  
       return $data;
    }//END METHOD: getMajorMinor() 
    
     /**
      * @param STRING $param |it returns the description of the category associated with
      *                       PartNumber    
      */    
    public function getCategoryDescByStr(  $param ): string {
       /* checks if $param is instance of PARTNUMBER CLASS */
                    
       $category = is_a( $param, 'Application\ObjectValue\PartNumber') ? $param->getCategory(): $param;
       
       $validCategory = ( $category !== '') ? true : false;
       
       if ( !$validCategory ) { return 'unknow';}
         
       $strSql = "SELECT INDESC FROM INMCAT where INCATA = '".strtoupper( trim( $category ) )."'"; 
       $data = $this->queryManager->runSql( $strSql );
       
       return $data[0]['INDESC'];
    }//END: getCategoryDescByStr()

    /**
     * This METHOD retrieve all data from a given PartNumber if it exists inside INMSTA 
     * @param string $partNumberID  | This parameter is an string 
     * @return array()
     */
    public function getPartNumber( $partNumberID )
    {
       /* Loading all from INMSTA */
       $strSql = "SELECT * FROM INMSTA WHERE TRIM(UCASE(IMPTN)) = '". strtoupper( trim( $partNumberID ))."'";
       
       try {
           $dataSet = $this->queryManager->runSql( $strSql ); 
           $ExistPartNumber = ($dataSet[0]['IMPTN'] !== null) ? true : false; 
         
         //if exist the part Number then recover all its data
         if ( $ExistPartNumber ) {
           $result = $this->populatePartNumber( $dataSet[0] ) ?? null;
         }
       } catch ( Exception $e ) {
         echo 'Caught exception: '.$e->getMessage(),"\n"; 
       }
       
       return $result;             
    } //END: getPartNumber 
    
} //End: class QueryRecover 
