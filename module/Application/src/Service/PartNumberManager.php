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
    
    /**
     *
     * @var array | array of options 
     */
//    private $options= [];
       
     /* 
      * @var $queryManager queryManager
      */
    public  function __construct( queryManager $queryManager ) {        
        $this->queryManager = $queryManager;        
//        $this->options = $options;
    }// END: constructor 
   
   /*-------------------------------------- setters ---------------------------------------------*/   
   
  /**
   * This method retrieves the CTP REFERENCE of a given partnumber that exits
   * 
   * @param string $partnumber
   * @return string
   */  
   private function getCtpPartNumber( $partnumber )
   {
      $strSql = "SELECT * FROM CTPREFS WHERE UCASE(CRPTNO) = '". $partnumber."'";
      $dataSet = $this->queryManager->runSql( $strSql );


      if ( $dataSet!== null ) {
          $result = $dataSet[0]['CRCTPR'] ?? '';                  
      }

      return $result;
   }  
   
   /**
    * This method retrieves the count of references sold 
    * 
    * @param string $partnumber
    * @return integer
    */
   private function quantitySold( $partnumber )
   {
      $strSql = "SELECT * FROM INVPTYF WHERE trim(IPPART) ='". trim($partnumber)."'";
      $dataSet = $this->queryManager->runSql( $strSql );

      if ( $dataSet!== null ) {
          $result = $dataSet[0]['IPYSLS'] ?? '';                  
      }

      return $result;
   }   
            
   /**
    * 
    * @param array $record
    * @return PartNumber
    */
   private function populatePartNumber( $record ){          
      
     /* retrieving each FIELD VALUE */
//         foreach ( dataSet as $record ) {
            $data['id']              = trim($record['IMPTN']); //part number
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
            
            //retrieving  the CTP reference of the part
            $data['ctppartnumber'] = $this->getCtpPartNumber($data['id']);
            
            //retrieving the qty sold 
            $data['qtysold'] = $this->quantitySold( $data['id'] );
            
            /*  getting the last year Qty quoted
             *  IPPART
             */                       
           
            $strSql = "SELECT * FROM INVPTYF WHERE UCASE(IPPART) = '". $data['id']."'";
            $dataSet = $this->queryManager->runSql( $strSql );
            
          
            if ( $dataSet!==null ) {
                $tmpVnd = $dataSet[0]['IPVNUM'] ?? '';                
               
                $data['qtyquotedlastyear'] = $dataSet[0]['IPQQTE']?? 0;
                $data['vendor'] =  ( isset($tmpVnd) && $tmpVnd !=='') ? $dataSet[0]['IPVNUM'] : 'NA' ;           
                $data['onhand'] = ( isset($tmpVnd) && $tmpVnd !=='') ? $dataSet[0]['IPQONH']: 0;
                $data['onorder']= ( isset($tmpVnd) && $tmpVnd !=='') ? $dataSet[0]['IPQONO']: 0; //qty on order

                 //vendor of the part

                /* initial description for the vendor */
                $data['vendordesc'] = 'NA';
            
            
                if ( $data['vendor'] !='NA' ) { 
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
       
       return $data[0]['INDESC']??'NA';
    }//END: getCategoryDescByStr()

    /**
     * This METHOD retrieve all data from a given PartNumber if it exists inside INMSTA 
     * @param string $partNumberID  | This parameter is an string 
     * @return array()
     */
    public function getPartNumber( $partNumberID )
    {
        $options = [
            'table' => PartNumberValidator::TABLE_BY_DEFAULT ,
            'queryManager' => $this->queryManager, 
          ];
    
       $partNumberValidator = new PartNumberValidator( $options );       
       $isValid = $partNumberValidator->isValid( $partNumberID ); 
       
       //if the partnumber not exist then
       if (!$isValid ) { return null; }      
       
       /* Loading all from INMSTA */
       $strSql = "SELECT * FROM INMSTA WHERE TRIM(UCASE(IMPTN)) = '". strtoupper( trim( $partNumberID ))."'";
       
       $dataSet = $this->queryManager->runSql( $strSql ); 
       
       $result = $this->populatePartNumber( $dataSet[0] );  
       
       return $result;             
    } //END: getPartNumber 
    
} //End: class QueryRecover 
