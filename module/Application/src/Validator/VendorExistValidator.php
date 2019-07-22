<?php
namespace Application\Validator;

use Zend\Validator\AbstractValidator;


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
  const TABLE_CATERPILLAR  = 'CATER';  // Parts list of CATERPILLAR 
  const TABLE_KOMATSUT  = 'KOMAT';  //tale with list of komatsut parts
  
  const TABLE_MAJOR  = 'MAJCODES'; // table with all majors code   
  const TABLE_MINOR  = 'CNTRLM'; // table with minor   
  
  const TABLE_WISHLIST = 'PRDWL'; // wishlist table
  
  // Available validator options.
  protected $options = [
    'table' => self::TABLE_BY_DEFAULT ,
    'queryManager' => null, 
    'notintable' => false,
    'lostsale' => false     //it let know to the validator whether it need to 
    //find out about the part inside the lostsales files
  ];
    
  // Validation failure message IDs.
  const NOT_SCALAR  = 'notScalar';
  const INVALID_PARTNUMBER  = 'invalidPartNumber';
  const INVALID_PARTNUMBER_IN_TABLE = 'invalidPartNumberInTable';
  const INVALID_PARTNUMBER_LENGTH = 'invalidPartNumberLength';
  const INVALID_PARTNUMBER_ALREADY_EXIST = 'invalidPartNumberExist';
  const INVALID_PARTNUMBER_IN_LOSTSALE = 'invalidpartnumberinlostsale';
  const INVALID_MINOR_CODE = 'invalidminorcode';
            
  // Validation failure messages.
  protected $messageTemplates = [
    self::NOT_SCALAR  => "The part number must be a scalar partnumber",
    self::INVALID_PARTNUMBER_LENGTH => "Invalid Part Number: Length Must be between 4-19 characters", 
    self::INVALID_PARTNUMBER => "it does NOT EXIT in our INVENTORY",
    self::INVALID_PARTNUMBER_IN_LOSTSALE => "NOT EXIT in our INVENTORY neither Lostsales Files",
    self::INVALID_PARTNUMBER_IN_TABLE => "The part number can not be found in the table",
    self::INVALID_PARTNUMBER_ALREADY_EXIST => "This PART NUMBER already exist",
    self::INVALID_MINOR_CODE => "Invalid Minor Code",
          
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
      if(isset($options['notintable'])) {
            $this->options['notintable']= $options['notintable'];
        }            
    }
        
      // Call the parent class constructor.
      parent::__construct( $options );
  }
    
  // Sets table Name
  public function setTable( $table ) {
    // Check input argument.
   
    if($table!=self::TABLE_BY_DEFAULT && $table!= self::TABLE_STOCK &&
       $table!=self::TABLE_WISHLIST && $table!= self::TABLE_INVPTYF &&
       $table!=self::TABLE_CATERPILLAR && $table!= self::TABLE_KOMATSUT && 
       $table!=self::TABLE_MAJOR  && $table!= self::TABLE_MINOR
            ) {            
      throw new \Exception('Invalid table argument passed.');
    }
     
    $this->options['table'] = $table;
  }
  
  /**
   * 
   * @param string $table
   * @param string $partnumber
   * @return bool true: Exist in the table
   */
   
    private function existPartInTable( $table, $fieldName, $partnumber )
    {   
        $queryManager = $this->options['queryManager'];
        $strSql = "SELECT * FROM ".$table." WHERE ".$fieldName." = '".$partnumber."'"; 
        $data = $queryManager->runSql( $strSql );       
        $isValid = isset( $data[0][$fieldName] ) ? true : false;           
        return $isValid;  
    }
  
  // Validates a part number.
    public function isValid( $partnumber ) 
    {
        if(!is_scalar( $partnumber )) {
            $this->error( self::NOT_SCALAR );
            return false; // Part Number must be a scalar.
          }

          // Retrieve the Table Name      
          $table = $this->options['table'];

          // Determine the correct length of the part number,
          // depending on the lenght of the PART NUMBER.            
          if(strlen($partnumber) > self::PARTNUMBER_MAX_LENGTH &&
             strlen($partnumber) < self::PARTNUMBER_MIN_LENGTH )   {
               $this->error( self::INVALID_PARTNUMBER_LENGTH ); 
               return false;
          }

          // select the correct Attibute of partNumber

          switch ( $table ) {      
            case self::TABLE_INVPTYF :  $fieldName = 'IPPART'; break;  //INVPTYF
            case self::TABLE_STOCK :  $fieldName = 'DVPART'; break;    //DVINVA
            case self::TABLE_WISHLIST :  $fieldName = 'WHLPARTN'; break; //PRDWL
            case self::TABLE_CATERPILLAR :  $fieldName = 'CATPTN'; break; //CATER
            case self::TABLE_KOMATSUT :  $fieldName = 'KOPTNO'; break; //KOMAT
            case self::TABLE_MAJOR :  $fieldName = 'MAJCOD'; break; //MAJCODES
            case self::TABLE_MINOR :  $fieldName = 'CNMCOD'; break; //MAJCODES

            default  :  
               $fieldName = 'IMPTN';  //INMSTA      
          }

          // LOOKING FOR THE PART EXIST IN THE TABLE
          // retrieving the queryManager

          $isValid = $this->existPartInTable($table, $fieldName, $partnumber);

          //
//          echo " valid?? ".$isValid."<br>";

          if ($isValid && $this->options['notintable']=== true) {
             $isValid = false;
             $this->error(self::INVALID_PARTNUMBER_ALREADY_EXIST);             
          } elseif ($isValid && $this->options['notintable'] === false) {
             $isValid = true;                               
          }elseif( !$isValid && $this->options['notintable'] === true) {              
            $isValid = true;  
            $this->error(self::INVALID_PARTNUMBER);                            
          } elseif( !$isValid && $this->options['notintable']=== false) {              
            $isValid = false;  
            $this->error(self::INVALID_PARTNUMBER);
          }
          
//          echo $table." -- isValid: ".$isValid."--- notintable: ".$this->options['notintable'];
          return $isValid;
    }
      
}//END: isValid() method 
 




