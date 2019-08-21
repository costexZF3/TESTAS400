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

use Application\Validator\PartNumberValidator;
use Application\Service\QueryManager as queryManager; 


class FormLostsaleToWL extends Form 
{
   
        
    public function __construct() 
    {          
        // Defined form name 
        parent::__construct('form-to-wl');
      
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
      $this->addCommonElements();           
    }//END: selectElements

    /**
     * this method add the commons elements to each scenario 
     * @param string $submitValue | value
     */
    private function addCommonElements () 
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
            'name' => 'submitWL',
             'options' => [
               'label'   => 'WL',
               'class'    => 'fas fa-file-export',
              ],
            'attributes' => [
                'value' => 'WL', 
                'title' => "Insert in the Wish List",
//                'icon'  => '<i class="fas fa-file-export">', 
                'id'    => 'towlbutton'
            ],
        ]);
    }
    
     
}//END CLASS
