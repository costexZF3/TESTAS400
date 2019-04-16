<?php

namespace Purchasing\Form;


/**
 * Description of WishListNewItemForm
 * - This CLASS WishListItemForm contains all the FORMS elements that will be
 *   entered by keyboard for the user
 *  
 * @author mojeda
 *  *  - for convention:
 *    - the form name: nameForm : WishListNewItemForm
 */

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

use Application\Service\QueryManager as queryManager;


class FormUpload extends Form 
{
   /**
    * @var queryManager
    */
   
    private $queryManager; 
    
    /**
     * 
     * @param string $scenario  | it defines if the user is looking for ADD or SUBMIT data 
     * @param queryManager $queryManager | service manager with connection to out database
     */
        
    public function __construct( $queryManager ) 
    {   
        $this->queryManager = $queryManager;
       
        // Defined form name 
        parent::__construct('form-validate-loading');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
        
        // Set binary content encoding.
        $this->setAttribute('enctype', 'multipart/form-data');
        
        /* method for add items to the form */
        $this->addElement();
        $this->addInputFilters();
        $this->addCommonElements();
              
    }//END CONSTRUCTOR    
    

    /**
     * this method add the commons elements to each scenario 
     * @param string $submitValue | value
     */
    private function addCommonElements( $submitValue = "UPLOAD" ) 
    {
        // Add the CSRF field
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => 
                    ['timeout' => 600]
            ],
        ]);
        
        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => ['value' => $submitValue],
        ]);
    }
    
    private function addElement() 
    {            
        /* part number */       
        $this->add([
            'name'=>'file',    
            'type'=>'file',             
            'attributes' => [          //array of attributes                       
                'id'=>'file',                                                                                
            ],
            'options' =>['label' => 'Excel File'],                     
        ]);
    }
      
    /*
     *  This method creates input filters (used for form filtering/validation ).
     */
   private function addInputFilters() 
   { 
        // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter( $inputFilter );
        
      //IMPORTANT!!!!
      //For file uploads, validators are executed before filters. This behaviour is inverse to the usual behaviour  
          
      // Add VALIDATORS AND FILTERS rules for the "file" field.	
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'file',  // Element's name.
            'required' => true,    // Whether the field is required.
            'validators' => [
                ['name' => 'FileUploadFile'], //validator 1
                ['name' => 'FileMimeType',                        
                 'options' => [                            
                    'mimeType' => [
                               'application/vnd.ms-excel', 'application/CDFV2', 'text/plain', 'txt/html',
                               'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                               'application/xls','application/x-xls'
                    ]    
                 ]
                ],
                ['name'    => 'FileExists'], //validator 2
                ['name'    => 'FileSize', //validator 3
                    'options' => [
                        'max' => 2048000,                            
                    ]
                ],
            ],
            /* FILTERS APPLIED */
            'filters'  => [                    
                ['name' => 'FileRenameUpload', //filter 1
                    'options' => [  
                        'target' => './data/upload',
                        'useUploadName' => true,
                        'useUploadExtension' => true,
                        'overwrite' => true,
                        'randomize' => false
                    ]
                ] //end: FileRenameUpload Filter
            ],   
        ]); 
        
       
   } //END: addFilters method()
  
      
}//END CLASS
