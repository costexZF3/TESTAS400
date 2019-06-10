<?php

namespace Purchasing\Form;


/**
 * UPDATING CLASS - THIS CLASS IS AN ABSTRACTION OF FORM THAT WILL BE USED AS TEMPLATE FOR CREATING
 * FORM ELEMENTS FOR UPDATING ELEMENTS DEPENDING ON THE LOGGED IN USER'S ACCESS (PERMISSIONS)
 * 
 *   
 * @author mojeda
 *   - for convention:
 *   - the name of the form will be: FormName (using camelCase) : FormUpdate
 */

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

use Application\Validator\PartNumberValidator;
use Application\Service\QueryManager as queryManager;
use Purchasing\Service\WishListManager as WLM;

class FormUpdateMultiple extends Form 
{     
    private $WLManager;
    
    public function __construct( $WLM ) 
    {                
        
        $this->WLManager = $WLM;
       
        // Defined form name 
        parent::__construct('form-update-multiple');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
        
        /* method for add items to the form */
        $this->selectElements();
              
    }//END CONSTRUCTOR
    
    /**
     * 
     * @param string $scenario
     */
    private function  selectElements() 
    {
        $status = [ 
            'NA' =>'NA',
            WLM::STATUS_OPEN => $this->WLManager->getStatus( WLM::STATUS_OPEN ),
            WLM::STATUS_DOCUMENTATION => $this->WLManager->getStatus( WLM::STATUS_DOCUMENTATION ),
            WLM::STATUS_TO_DEVELOP => $this->WLManager->getStatus( WLM::STATUS_TO_DEVELOP ),                                                                                           
            WLM::STATUS_CLOSE_BY_DEV => $this->WLManager->getStatus( WLM::STATUS_CLOSE_BY_DEV ),                                                                                           
            WLM::STATUS_REOPEN => $this->WLManager->getStatus( WLM::STATUS_REOPEN ),                                                                                           
            WLM::STATUS_REJECTED => $this->WLManager->getStatus( WLM::STATUS_REJECTED ),                                                                                           
        ];

        $users = [
                'NA'        => 'NA',
                'CTOBON'    => 'CTOBON',
                'ALOPEZ'    => 'ALOPEZ',
                'ALORENZO'  => 'ALORENZO',
                'CMONTILVA' => 'CMONTILVA',
            ];
        
        $this->addElementSC1( $status, $users ); 
        $this->addCommonElements(); //CSFR (CROSS SIDE FORGERY REQUEST)
//        $this->addInputFiltersSC1();  
       
    }//END: selectElements

    /**
     * This method add the commons elements to each scenario 
     *  ( SUBMIT BUTTON AND CSRF : CROSS SITE REQUEST FORGERY )
     *  @param string $submitValue | value
     */
    private function addCommonElements ( $submitValue = "UPDATE" ) 
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
    }// addCommonElements method
    
    private function addElementSC1( $status, $users ) 
    {  
        // User in charge
        $this->add([            
            'type'  => 'select',
            'name' => 'user',
            'options' => [
                'label' => 'USER',
                'value_options' => $users                
            ],
        ]);
                       
             
        // status: $status is an array with the status allowed to see by the user in charge.   
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'STATUS',
                'value_options' => $status
            ],
        ]);
    }
    
    /*
     *  This method creates input filters (used for form filtering/validation ).
     */
   private function addInputFiltersSC1() 
   { 
       // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter( $inputFilter );
      
        $inputFilter->add([
            'name'     => 'partnumber',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],                                                            
                ['name' => 'StripTags'],                                                            
                ['name' => 'StripNewlines'],  
                ['name' => 'StringToUpper'],
            ], 
            'validators' => [ 
                //validator: 1           
                ['name'    => 'StringLength',
                    'options' => ['min' => 5,'max' => 19],
                ],
            ]
        ]); 
        
        //------------------------- validating only the entered data -----------------------
        
        /* adding filters by comments */
        $inputFilter->add([
            'name'     => 'comment',
            'required' => false,
            'filters'  => [
                ['name' => 'StringTrim',
                      'options' => ['charlist' => "&'@?*%#$",]  //character to remove from comment                            
                ],                                                            
                ['name' => 'StripTags'],                                                            
            ], //END: FILTERS
            'validators' => [           
                ['name'    => 'StringLength', 'options' => ['min' => 0,'max' => 255]],                          
            ], //END: VALIDATORS KEY 
        ]);
   } //END: addFilters method()
    
   
    /*
     *  This method creates input filters (used for form filtering/validation ).
     */
   private function addInputFiltersSC2() 
   {    
           
    } //END: addFilters method()
  
    
}//END CLASS
