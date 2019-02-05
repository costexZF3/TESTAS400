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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\ArrayInput;
use Zend\Validator;

class LostSaleForm extends Form{
    
    private $inputFilter = null;
    
    public function __construct($name = null) {
        parent::__construct('lostsale-form');
        
        // Set POST method for this form
        $this->setAttribute('method', 'post');  
        // (Optionally) set action for this form
        //$this->setAttribute('action', '/lostsale');
        
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
            'name'=>'num-tq',         //Field name
            'type'=>'number',        //element type
            'value' => 100,
            'attributes' =>[          //array of attributes
                   'id'=>'tqId',
                   'min' => "10",
                   'max' => "150",
                   'required' => true, 
            ],
            'options' =>[                   //array of options
                'label' => 'Times Quote'   //Text Label
            ],                     
        ]);    
       
         // Add "status" field : tqvalues 
        $this->add([            
            'type'  => 'select',
            'name' => 'sel-vndassigned',
            'options' => [
                'label' => 'Vendors Assigned',
                'value_options' => [
                    1 => 'YES',
                    2 => 'NO',                                                         
                    3 => 'BOTH',                                                         
                ],
            ],
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
       
       $inputFilter->add(
        [
            'name'=>'num-tq',         //Field name                        
            'filter'   => [      //array with filters
                ['name' => 'ToInt'],    
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
                ['name' => 'StripNewlines'],
            ],
            
//            'validators' => [
//                   // ['name'=>'GreaterThan', 'options'=>['min'=>0]],
//                    ['name'=>'NotEmpty'],
//                    ['name'=>'Between', 'options' => ['min'=>10, 'max'=>150]],                    
//            ],            
       ]);
       }
    
    
}//END CLASS
