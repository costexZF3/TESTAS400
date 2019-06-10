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

class FormWishList extends Form 
{
    /**
     * @param string $scenario  | it defines if the user is looking for ADD or SUBMIT data 
     * @param queryManager $queryManager | service manager with connection to out database
     */        
    public function __construct() 
    {                 
               
        // Defined form name 
        parent::__construct('form-wishlist');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
        $this->setAttribute('action', 'wishlist/update');  
        
        /* method for add items to the form */
        $this->selectElements();
              
    }//END CONSTRUCTOR
    
    /**
     * 
     * @param string $scenario
     */
    private function  selectElements() 
    {
        $this->addCommonElements(); //CSFR (CROSS SIDE FORGERY REQUEST)
                       
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
 
  
    
}//END CLASS
