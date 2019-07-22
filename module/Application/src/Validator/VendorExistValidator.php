<?php
namespace Application\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Description of VendorExistValidator
 * 
 * - This VALIDATOR checks whether a given vendor is valid()
 *   ** a vendor is valid whether VMVTYP IN (I,Y) IN THE FILE: CNTRLL PARAMETER: 416 **  by default
 * - You can also check if the part exist in others table
 *   example: I want to know if the part is assigned to a partnumber in INMSTA...THEN you can use this...
 *
 * @author mojeda
 */

class VendorExistValidator extends AbstractValidator {
   const VENDOR_MIN_LENGTH = 2; 
   const VENDOR_MAX_LENGTH = 6; 
  
   /**
    * The below CONSTANS are defining the WHOLE UNIVERSE of the Tables where you are able to find out 
    * about a VENDORNUMBER is valid or not taking into account whether you want to know how make it a valid
    * vendor
    */
   const TABLE_BY_DEFAULT = 'VNMAS';
   const TABLE_VENDORS = 'VNMAS#';
   const TABLE_CNTRLL  = 'CNTRLL'; 
   const TABLE_INMSTA ='INMSTA'; 
   const TABLE_DVINVA ='DVINVA'; 
   const TABLE_INVPTYF ='INVPTYF'; 
  
  
   /**
    *  $options[]: it has the settings options
    *  - queryManager: it's the DB instance
    *  - notintable: it  means (true) that you maybe want to insert a new vendor in the table 
    *    then VALIDATOR returns true if the NEW VENDOR is not in the table. This case you could insert it.
    *    - (false):  its a DEFAULT value. It returns a Valid Vendor if the Vendor exist in the 
    *    VENDORS TABLE (VNMAS). You ONLY need to know if the Vendor is Valid (exist) 
    * 
    */  
   protected $options = [  
     'table' => self::TABLE_BY_DEFAULT, 
     'queryManager' => null,  
     'notintable' => false,      //(false): Valid if it Exists, (true): Valid if it not Exist     
     'checkspecialvendor' => true, // a true value means to find out about the passed Vendor inside 
                               //the CNTRLL FILE parameter: 416 (special vendors)
   ];

   // Validation failure message IDs.
   const NOT_SCALAR  = 'notScalar';
   const INVALID_VENDOR  = 'invalidVendor';
   const INVALID_VENDOR_IN_TABLE = 'invalidVendorInTable';
   const INVALID_VENDOR_LENGTH = 'invalidVendorLength';
   const INVALID_VENDOR_ALREADY_EXIST = 'invalidVendorAlreadyExist';  

   // Validation failure messages.
   protected $messageTemplates = [
     self::NOT_SCALAR  => "The Vendor MUST be a scalar",
     self::INVALID_VENDOR=> "This vendor does NOT EXIT in our RECORDS",    
     self::INVALID_VENDOR_LENGTH => "Invalid Vendor: it length Must be at least 6 numbers",        
     self::INVALID_VENDOR_ALREADY_EXIST => "This PART NUMBER already exist",
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
       if(isset($options['specialvendor'])) {
             $this->options['specialvendor']= $options['specialvendor'];
         }  
     }

       // Call the parent class constructor.
       parent::__construct( $options );
   }//END: CONSTRUCTOR

   // Sets the table Name where we looking for about the entered vendor 
   public function setTable( $table ) 
   {     
      // Check input argument.
      if( $table!=self::TABLE_BY_DEFAULT && $table!= self::TABLE_DVINVA &&
          $table!=self::TABLE_INMSTA && $table!= self::TABLE_INVPTYF &&
          $table!=self::TABLE_CATERPILLAR
              ) {            
        throw new \Exception('Invalid table argument passed.');
      }     
      $this->options['table'] = $table;
   }// END: setTable()

   /**
    * This method return if the VENDORNUMBER ixist in the table $table 
    * @param string $table
    * @param string $vendornumber
    * @return bool true: Exist in the table
    */
   private function existPartInTable( $table, $fieldName, $vendornumber )
   { 
      $vendorTable = ($table === self::TABLE_BY_DEFAULT ) || ($table === self::TABLE_VENDORS); 
      $WHERE1 = $vendorTable ? " AND VMVTYP IN('I', 'Y') " : "";
        
      $asString = in_array($table, [self::TABLE_DVINVA, self::TABLE_INMSTA, self::TABLE_INVPTYF]);
      $vendorFormat = $asString ? "'".$vendornumber."'" : $vendornumber;
              
      $queryManager = $this->options['queryManager'];
      $strSql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$vendorFormat." ".$WHERE1; 
      
//      var_dump($strSql); exit;
      $data = $queryManager->runSql( $strSql );       
      $isValid = isset( $data[0][$fieldName] ) ? true : false;           
      return $isValid;  
   }
   
   /**
    * This method returns the Key Field inside the $table (pass by parameter) which will
    * be used for matching the searching inside the $table
    * 
    * @param string $table
    * @return string
    */
   private function getFieldNameBySearching( $table )
   { 
      switch ( $table ) {      
        case self::TABLE_VENDORS    :  $fieldName = 'VMVNUM'; break;    //VNMAS#
        case self::TABLE_INVPTYF    :  $fieldName = 'IPVNUM'; break;  //INVPTYF            
        case self::TABLE_INMSTA     :  $fieldName = 'IMVN#'; break; //INMSTA
        case self::TABLE_DVINVA     :  $fieldName = 'DVMFP#'; break; //DVINVA

        default  :  
           $fieldName = 'VMVNUM';  //FILE: VNMAS      
      }

      return $fieldName;
   }//END: getFieldNameBySearching()

   //return true if the $vendornumber is a special vendor 
   // (checking in the CNTRLL FILE
   private function isSpecial( $vendornumber )
   {
      $queryManager = $this->options['queryManager'];
      
      $strSql = "SELECT CNTDE1 FROM ".self::TABLE_CNTRLL." WHERE CNT01 ='416' AND CNTDE1 ='".$vendornumber."'"; 
      $data = $queryManager->runSql( $strSql );       
      $isValid = isset( $data[0]['CNTDE1'] ) ? true : false;           
      
      return $isValid;
   }

   // Validates a part number.
   public function isValid( $vendornumber ) 
   {
      if(!is_scalar( $vendornumber )) {
         $this->error( self::NOT_SCALAR );
         return false; // Vendor Number must be a scalar.
      }

      // Retrieve the Table Name      
      $table = $this->options['table'];

      // Determine the correct length of the part number,
      // depending on the lenght of the PART NUMBER.            
      if(strlen($vendornumber) < self::VENDOR_MIN_LENGTH &&
         strlen($vendornumber) > self::VENDOR_MAX_LENGTH)   {
           $this->error( self::INVALID_VENDOR_LENGTH ); 
           return false;
      }

      // select the correct Attibute of partNumber
      $fieldName = $this->getFieldNameBySearching( $table );


      // LOOKING FOR THE PART EXIST IN THE TABLE      
      $isValid = $this->existPartInTable($table, $fieldName, $vendornumber);
      
      //check if the $vendornumber is a special vendor
      $isSpecialVendor = $this->isSpecial( $vendornumber );
      
      //CHECKING A VALID VENDOR AND 
      $isValid &= !$isSpecialVendor;
                    
      //check if the user wants to know if the vendor is a apecial one
      if ( $this->options['checkspecialvendor'] && $isSpecialVendor ) {         
         return $isValid = true;
      }
         
//         echo " valid?? ".$isValid."<br>";

      //checking assertions
      $notInTable = $this->options['notintable'];
      
      //like a XOR function 
      if ($isValid && $notInTable === true) {
         $isValid = false;
         $this->error( self::INVALID_VENDOR_ALREADY_EXIST );             
      } elseif ($isValid && $notInTable === false) {
         $isValid = true;                               
      }elseif( !$isValid && $notInTable === true) {              
        $isValid = true;  
        $this->error( self::INVALID_VENDOR );                            
      } elseif( !$isValid && $notInTable === false) {              
        $isValid = false;  
        $this->error(self::INVALID_VENDOR);
      }

//          echo $table." -- isValid: ".$isValid."--- notintable: ".$this->options['notintable'];
      return $isValid;
   }
      
}//END: isValid() method 
 




