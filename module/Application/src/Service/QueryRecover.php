<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\Db\Adapter\Adapter as MyAdapter;
use Zend\Db\Sql\Sql;


/**
 * Description of QueryRecover 
 * - This class is a Service that let us get data from an AS400 TABLE
 * 
 *
 * @author mojeda
 */
class QueryRecover 
{    
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
    public  function __construct( MyAdapter $adapter ) 
    {        
        $this->adapter = $adapter;
    }// END: constructor 
   
    
   /**
    *  -this method returns the max code that will be insert in the WL 
    * @param int $code
    * @param string $table
    * @return int
    */
     
    public function getMax( $code, $table ) 
    {
      $strSql = "SELECT MAX(".$code.") as MAXCOD FROM ".$table;
        
      $data = $this->runSql( $strSql );
      
      $result = $data[0]['MAXCOD']++; 
      
      return $result+1 ;
    }//END: getMax
    
   /*
   * runSql: run the sqlStr 
   */
    public function  runSql( $sqlStr ) 
    {
        try {
          $resultSet = $this->adapter->query( $sqlStr, MyAdapter::QUERY_MODE_EXECUTE )->toArray();   
          var_dump( $resultSet ); exit; 
          
          $this->dataSet = $resultSet;          
          return $this->dataSet;
          
        } catch ( Exception $e ) {
           echo "Caught exception: ", $e->getMessage(), ""; 
        }   
      
    } /* END: runSql */
    
   
    /**
    * @param string $table | Name of table where will occur the insertion
    * @param array() $data   | Its an associated array mapping the data : ['field1'=>value1, 'field2'=>value2]
    */
    public function  insert( $table, $data ) 
    {            
      try {
         $sql = new Sql( $this->adapter );
         $insert = $sql->insert();
         $insert->into( $table );
         $insert->values( $data );
        
         $statement = $sql->prepareStatementForSqlObject( $insert );   
         $result = $statement->execute();   
         
         $this->dataSet = $result;
            
         return $this->dataSet;
          
      } catch ( Exception $e ) {
           echo "Caught exception: ", $e->getMessage(), ""; 
      } 
     
    } /* END: insert */
    
   /*
   *   checking if dataSet was modified: 
   */
   private function dataSetReady() 
   {
      return ( $this->dataSet!=null )?? false;
   }
    
   /* ---------------------------- getters -------------------------------*/    
    
   /* return COUNT ITEMS in the Result Set */
   public function countItems()
   {
        return count( $this->dataSet );
    }    
    
    /* return DATASET RESULT */
   public function getDataSet() 
   {        
      return $this->dataSet;
   }
    
   /*
    * this method all fields in the fields array
    */
    public function getFields()
    {
      $this->fields = array_keys( $this->dataSet );  
      return $this->fields;
    }
  
   /* ----------------------   setters  --------------------------- */    
     
  
} //End: class QueryRecover 
