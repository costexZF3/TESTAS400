<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\Db\Adapter\Adapter as MyAdapter;

/**
 * Description of QueryRecover 
 * - This class is a Service that let us get data from an AS400 TABLE
 * 
 *
 * @author mojeda
 */
class QueryRecover {    
    /*
     * dataSet: It saves the resultSet returned by runSql() method
     */    
    private $dataSet= null;    
    private $fields = [];     
    /* rows: its an array saving each tuple of the TABLE */
    private $rows = [];
    
    /* connection */ 
    private $adapter;    
    private $countItems = 0; //count items in DataSet         
   
    
     /* constructor */
    public  function __construct( MyAdapter $adapter ) {        
        $this->adapter = $adapter;
    }// END: constructor 
   
    
    /*
     * runSql: run the sqlStr 
     */
    public function  runSql( $sqlStr ) {
        try {
          $resultSet = $this->adapter->query( $sqlStr, MyAdapter::QUERY_MODE_EXECUTE )->toArray();   
           
          $this->dataSet = $resultSet;
          
          return $this->dataSet;
          
        } catch ( Exception $e ) {
           echo "Caught exception: ", $e->getMessage(), ""; 
        }   
      
    } /* END: runSql */
    
    /*
     *   checking if dataSet was modified: 
     */
    private function dataSetReady() {
        return ($this->dataSet!=null)??false;
    }
    
    /* ---------------------------- getters -------------------------------*/    
    
    /* return COUNT ITEMS in the Result Set */
    public function CountItems(){
        return count($this->dataSet);
    }    
    
    /* return DATASET RESULT */
    public function getDataSet() {        
        return $this->dataSet;
    }
    
     /*
    * this method all fields in the fields array
    */
    public function getFields(){
      $this->fields = array_keys( $this->dataSet );  
      return $this->fields;
    }
  
   /* ----------------------   setters  --------------------------- */    
     
  
} //End: class QueryRecover 
