<?php

namespace Purchasing\Form;


/**
 * Description of LostSaleForm
 *  - This CLASS LostSaleForm contains all the FORMS elements that will be shown to filter the information 
 *  on the VIEW associated to LostSales 
 * @author mojeda
 */

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class LostSaleForm extends Form{
    
    private $inputFilter = null;
    
    public function __construct($name = null) {
        parent::__construct( $name );
        
        $this->inputFilter = new InputFilter();
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
                     'options' =>['label' => 'Times Quote:'],s                     
                   ]
                );
        
    }//End: addElements
    
    private function addFilters() {
        
    }
    
    
}
