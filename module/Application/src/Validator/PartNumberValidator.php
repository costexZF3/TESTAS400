<?php
namespace Application\Validator;

use Zend\Validator\AbstractValidator;
use Application\Service\QueryRecover as QueryManager;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PartNumberValidator
 *
 * @author mojeda
 */
class PartNumberValidator extends AbstractValidator {
  const PARTNUMBER_MIN_LENGTH = 4; 
  const PARTNUMBER_MAX_LENGTH = 19; 
  
  const TABLE_BY_DEFAULT = 'INMSTA';
  const TABLE_STOCK  = 'DVINVA';  
  const TABLE_INVPTYF  = 'INVPTYF';  
  
  const TABLE_WISHLIST = 'PRDWL';
  
  // Available validator options.
  protected $options = [
    'table' => self::TABLE_BY_DEFAULT ,
    'queryManager' => null, 
    'notInTable' => false
  ];
    
  // Validation failure message IDs.
  const NOT_SCALAR  = 'notScalar';
  const INVALID_PARTNUMBER  = 'invalidPartNumber';
  const INVALID_PARTNUMBER_IN_TABLE = 'invalidPartNumberInTable';
  const INVALID_PARTNUMBER_LENGTH = 'invalidPartNumberLength';
  const INVALID_PARTNUMBER_ALREADY_EXIST = 'invalidPartNumberExist';
    
  // Validation failure messages.
  protected $messageTemplates = [
    self::NOT_SCALAR  => "The part number must be a scalar value",
    self::INVALID_PARTNUMBER_LENGTH => "Invalid Part Number: Length Must be between 4-19 characters", 
    self::INVALID_PARTNUMBER => "it does NOT EXIT in our INVENTORY",
    self::INVALID_PARTNUMBER_IN_TABLE => "The part number can not be found in the table",
    self::INVALID_PARTNUMBER_ALREADY_EXIST => "This PART NUMBER already exist",
          
  ];
    
  // Constructor.
  public function __construct($options = null) 
  {
    // Set filter options (if provided).
    if(is_array($options)) {
            
      if(isset($options['table'])) {
            $this->setTable($options['table']);
        }
      if(isset($options['queryManager'])) {
            $this->options['queryManager']= $options['queryManager'];
        }  
      if(isset($options['notInTable'])) {
            $this->options['notInTable']= $options['notInTable'];
        }   
    }
        
      // Call the parent class constructor.
      parent::__construct( $options );
  }
    
  // Sets table Name
  public function setTable( $table ) {
    // Check input argument.
   
    if($table!=self::TABLE_BY_DEFAULT && $table!= self::TABLE_STOCK &&
       $table!=self::TABLE_WISHLIST && $table!= self::TABLE_INVPTYF
            ) {            
      throw new \Exception('Invalid table argument passed.');
    }
     
    $this->options['table'] = $table;
  }
    
  // Validates a part number.
  public function isValid( $value ) {
      if(!is_scalar($value)) {
        $this->error( self::NOT_SCALAR );
        return false; // Part Number must be a scalar.
      }

      // Convert the value to string.
      $value = strtoupper( $value );

      // Retrieve the Table Name      
      $table = $this->options['table'];

      // Determine the correct length of the part number,
      // depending on the lenght of the PART NUMBER.            
      if(strlen($value) > self::PARTNUMBER_MAX_LENGTH &&
        strlen($value) < self::PARTNUMBER_MIN_LENGTH )   {
           $this->error( self::INVALID_PARTNUMBER_LENGTH ); 
           return false;
      }

      // select the correct Attibute of partNumber

      switch ( $table ) {      
        case self::TABLE_INVPTYF :  $fieldName = 'IPPART'; break;  //INVPTYF
        case self::TABLE_STOCK :  $fieldName = 'DVPART'; break;    //DVINVA
        case self::TABLE_WISHLIST :  $fieldName = 'PRWPTN'; break; //PRDWL

        default  :  
           $fieldName = 'IMPTN';  //INMSTA      
      }

      // LOOKING FOR THE PART EXIST IN THE TABLE
      // retrieving the queryManager
      $queryManager = $this->options['queryManager'];
      $strSql = "SELECT IMPTN FROM ".self::TABLE_BY_DEFAULT." WHERE UCASE(IMPTN) = '".$value."'"; 

      $data = $queryManager->runSql( $strSql ); 
      
      $isValid = isset( $data[0]['IMPTN'] ) ? true : false;
       
      // If there was an error, set error message.
      if( !$isValid ) {
        $this->error(self::INVALID_PARTNUMBER);                
      } else if (isset($this->options['notInTable'])) {
           $strSql = "SELECT ".$fieldName." FROM ".$table." WHERE ".$fieldName." = '".$value."'"; 
           $data = $queryManager->runSql( $strSql );       
           $isValid = !isset( $data[0][$fieldName] ) ? true : false;       
           if (!$isValid ) {              
              $this->error(self::INVALID_PARTNUMBER_ALREADY_EXIST);
           }
        }
              

      // Return validation result.
     return $isValid;
   }//END: isValid() method 
} //END: class



