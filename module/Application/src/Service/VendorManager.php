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
use Application\ObjectValue\Vendor as Vendor;

//use Application\Validator\PartNumberValidator;

/**
 * Description of PartRecover 
 * - This class is a Service that let us get data from an AS400 TABLE
 * 
 * @author mojeda
 */
class VendorManager {         
    /*
     * @var $queryManager queryManager
     */   
    private $queryManager;
    
    /**
     * Instance of Vendor ( Object )
     * 
     * @var Vendor
     */
    private $vendor;
    
    public  function __construct( queryManager $queryManager ) {        
        $this->queryManager = $queryManager;        
    }// END: constructor 
    
    
    /**
     * 
     * @return \Vendor 
     */
    public function getVendor() 
    {
        return $this->vendor;
    }
    
    /**
     * 
     * @param string $vendorNum
     * @return array()
     */
    private function loadVendor( string $vendorNum )
    {        
        try {
            $strSql = "SELECT VMVNUM, VMNAME, DIGITS(VM#POY) PS, DIGITS(VM#POL) PA, VMVTYP, VMADD1, VMADD2, 
                       VMADD3, VMYTDP, VMPLYR FROM vnmas WHERE VMVNUM = '".trim($vendorNum)."'  "; 
  
            $dataSet = $this->queryManager->runSql( $strSql );             
            
            return $dataSet[0];
            
        } catch (Exception $ex) {
            echo "Caught exception: ", $ex->getMessage(), ""; 
        }        
    } // end loadVendor()
    

    /**
     * This METHOD receive the $vendor (number) to looking for its data
     * 
     * @param string $vendor
     */
    public function setVendor( $vendor ) {
        //loading All data
        $vendorArray = !empty($vendor) ? $this->loadVendor( $vendor ) : [];        
        
        $this->vendor = count($vendorArray)>0 ? $this->populateVendor( $vendorArray ) : null;       
    }

    private function recoverName( $value )
    {
        try {
            $strSql = "SELECT CNTRLL.CNT03 PA, TRIM(CSUSER.USNAME) NAME FROM CNTRLL INNER JOIN CSUSER 
          ON CNT03 = DIGITS(USPURC) WHERE CNT01 = '216' AND      
          USPTY9 <> 'R' AND USPURC <> 0 and CNT03='".$value."'";
            
            $dataSet = $this->queryManager->runSql( $strSql );             
                     
            return ( $dataSet[0]['NAME'] ) ?? 'no assigned';
            
        } catch ( Exception $ex ) {
            echo "Caught exception: ", $ex->getMessage(), ""; 
        }
    }//End recoverName()
    
    /**
     * - it receives an array with data and create a vendor
     * 
     * @param array() $record
     * @return Vendor
     */
    private function populateVendor( $record )
    { 
        $data['name'] = $record["VMNAME"];
        $data['number'] = $record["VMVNUM"];
        
        //getting purchasing agent and product specialist' names
        
        $data['ps'] = $this->recoverName( $record["PS"] );
        $data['pa'] = $this->recoverName( $record["PA"] );
        
        $data['type'] = $record["VMVTYP"];
//        $tmpAdd = [];
//        array_push($tmpAdd, $record["VMADD1"], $record["VMADD2"], $record["VMADD3"]);
//        $data['address'] =  $tmpAdd;
        $data['yearsales'] = $record["VMYTDP"]; 
        $data['lastyear']= $record["VMPLYR"];   
                 
        $vendorObj = new Vendor( $data );
        return $vendorObj;   
   }//END: populatePartNumber method
   
   /**
    * This method return a Purchasing Agent Name
    * 
    * @return string
    */
    public function getPA() {
       if ($this->vendor == null) {return '';}
       
       return $this->vendor->getPurchasingAgent();
    }
    
    /**
     * This method returns the Product Specialist associated to a Vendor
     * 
     * @return string
     */
    public function getPS() {
       if ($this->vendor == null) {return '';}
       
       return $this->vendor->getProductSpecialist();
    }    
      
    
} //End: class QueryRecover 
