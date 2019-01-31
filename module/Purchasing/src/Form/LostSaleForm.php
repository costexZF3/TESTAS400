<?php

namespace Purchasing\Form;


/**
 * Description of LostSaleForm
 *  - This CLASS LostSaleForm contains all the FORMS elements that will be shown to filter the information 
 *  on the VIEW associated to LostSales 
 * @author mojeda
 * 
 *  - for convention:
 *    - the form name: name-form : lostsale-form
 */

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\ArrayInput;
use Zend\Validator;

class LostSaleForm extends Form{
    
    private $inputFilter = null;
    
    public function __construct($name = null) {
        parent::__construct('lostsale-form');
        
        // Set POST method for this form
        $this->setAttribute('method', 'post');        
        
        $this->addElements();
        $this->addFilters();        
    }
    
    /* method used for adding FORM ELEMENTS
     * for convention:
     *  - name: (text)-> edText+name
     *          (numeric) -> edNum+name 
     *  */
    private function addElements() {
        //add a text Edit for : TIMES QUOTE 
        $this->add([
            'name'=>'edNumTimesQuote',
            'type'=>'numeric',                     
            'options' =>['label' => 'Times Quote:'],                     
        ]);        
        
        // Add the CSRF field
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ],
        ]);
        
         // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'timesqvalues',
            'options' => [
                'label' => 'Times Quote',
                'value_options' => [
                    1 => '10 +',
                    2 => '30 +',                    
                    3 => '50 +',                    
                    4 => '100 +',                    
                ]
            ],
        ]);
        
        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Report'
            ],
        ]);
        
    }//End: addElements
    
    /*
     *  adding Filters to  Elements
     */
    private function addFilters() {
       $inputFilter = new InputFilter();
       $this->setInputFilter($inputFilter);
    }
    
    
}
