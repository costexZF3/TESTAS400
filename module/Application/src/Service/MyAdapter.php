<?php 
 namespace Application\Service;
 
 use Zend\Db\Adapter\Adapter;
 
 class MyAdapter
 {    
    
     private $conn;
     
     public function __construct( Adapter $adapter )
     {
         $this->conn = $adapter;
     }
     
     public function getAdapter(){
         return $this->conn;
     }
     
 }
