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
use Application\Service\QueryRecover as queryManager;


class WishListNewItemForm extends Form {
    /**
     *
     * @var array 
     */ 
    private $dataForm;
    
    /**
     * @var string
     */
    private $scenario;
    
    /**
     * @var queryManager
     */
   
    private $queryManager; 
    
    /**
     * 
     * @param string $scenario  | it defines if the user is looking for ADD or SUBMIT data 
     * @param queryManager $queryManager | service manager with connection to out database
     */
        
    public function __construct( $scenario, queryManager $queryManager ) 
    {      
           
        $this->scenario = $scenario;
        $this->queryManager = $queryManager;
     
        // Defined form name 
        parent::__construct('wl-newitem-form');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
         // ( Optionally ) set action for this form
         //$this->setAttribute('action', '/newItem');

         /* method for add items to the form */
        $this->addElements();
        $this->addInputFilters();        
    }//END CONSTRUCTOR
    
    
    private function addElements() 
    {
       /* only create partnumber for show and submit button */        
       
       if ($this->scenario == 'initial') {
          $textSubmit = 'SUBMIT';
          
         /* part number */       
            $this->add([
                'name'=>'partnumber',    
                'type'=>'text',                              
                'attributes' => [          //array of attributes                       
                       'id'=>'partnumber',
                       'minlength' => "6",
                       'maxlength' => "19",                   
                       'required' => true, 
                       'placeholder' => 'must be created',
                       'text' => 'Part Number MUST BE created first'
                ],
                'options' =>[              
                    'label' => 'PART NUMBER'
                ],                     
            ]);
          
       } else {
            $textSubmit = 'ADD';
            
            $this->add([
                'name'=>'code',    
                'type'=>'text',        
//                'value' =>$this->dataForm['code'], 
                'attributes' =>[      
                       'class' =>'form-control',
                       'id'       => 'code',                                          
                       'readonly' => true,
                ],
                'options' =>[           
                    'label' => 'CODE'   
                ],                     
            ]);

            //created by user XYZ 
            $this->add([
                'name'=>'user',    //Field name
                'type'=>'text',        //element type
//                'value' => $this->dataForm['user'], //must be without @costex.com 
                'attributes' =>[          
                       'class' =>'form-control',
                       'id'=>'created-user',                                      
                       'readonly' => true,
                ],
                'options' =>[              
                    'label' => 'CREATED BY'
                ],                     
            ]); 

            //created date (current date)
            $this->add([
                'name'=>'date',    
                'type'=>'text',                      
                   'attributes' => [
                   'class' =>'form-control',
                              'id'=>'created-user',                                      
                       'readonly' => true,
                ],
                'options' => [              
                    'label' => 'DATE'
                ],                     
            ]);             
            
             $this->add([
                'name'=>'partnumber',    
                'type'=>'text',                      
                'attributes' =>[          
                       'class' => 'form-control',
                      'readonly' => true,                     
                ],
                'options' =>[              
                    'label' => 'PART NUMBER'
                ],                     
            ]);
             //part number description
            $this->add([
                'name'=>'partnumberdesc',    
                'type'=>'text',           
                'attributes' =>[          //array of attributes
                       'class' => 'form-control', 
                      'readonly' => true,
                      
                ],
                'options' =>[              
                    'label' => 'DESCRIPTION '
                ],                     
            ]);
            
            // ------------------------------ VENDOR ------------------------
            
            $this->add([
                'name'=>'vendor',    
                'type'=>'text',                              
                'attributes' =>[         
                      'class' => 'form-control',
                      'readonly' => true,                     
                ],
                'options' =>[              
                    'label' => 'VENDOR'
                ],                     
            ]);
             //part number description
            $this->add([
                'name'=>'vendordesc',    
                'type'=>'text',                      
                'attributes' =>[          
                      'class' => 'form-control',
                      'readonly' => true,                     
                ],
                'options' =>[              
                    'label' => 'VENDOR DESCRIPTION'
                ],                     
            ]);

            // comment about the ITEM 
            $this->add([
                'name'=>'comment',    
                'type'=>'textarea',                              
                'attributes' =>[          //array of attributes
                       'class' => 'form-control',
                       'id'=>'comment',
                       'rows' => "4", 
                       'maxlenght' => 256,                                           
                       'placeholder' => 'Enter a comment',                    
                ],
                'options' =>[              
                    'label' => 'COMMENTS'
                ],                     
            ]);

            // type  
            $this->add([            
                'type'  => 'select',
                'name' => 'type',
                'options' => [
                    'label' => 'Type',
                    'value_options' => [
                        1 => 'New Part',
                        2 => 'New Vendor',                                                                                                                                    
                    ],
                ],
            ]);
       } //END: ELSE IF ($FLAG) ...
        
      /* BOTH CSRF AND SUBMIT BUTTON WILL BE ALWAYS ADDED */ 
       
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
                 'value' => $textSubmit
            ],
        ]);
        
    }//End: addElements
    
    /*
     *  This method creates input filters (used for form filtering/validation ).
     */
   private function addInputFilters() 
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
                ], 
         'validators' => [           
            [
               'name'    => 'StringLength',
               'options' => [
                   'min' => 4,
                   'max' => 19
               ],
            ], //validator: 1 
            [
               'name' => PartNumberValidator::class,
               'options' => [
                 'table' => PartNumberValidator::TABLE_WISHLIST,
                 'queryManager' => $this->queryManager, 
                 'notInTable' => true  
               ]                        
            ], //validator: 2                
         ], //END: VALIDATORS KEY                       
    ]); 
      
    $inputFilter->add([
         'name'     => 'comment',
         'required' => false,
         'filters'  => [
                    [
                      'name' => 'StringTrim',
                      'options' => [  
                          'charlist' => "&'@?*%#$",  //character to remove from comment  
                      ]    
                    ],                                                            
                    ['name' => 'StripTags'],                                                            
                ],
        'validators' => [           
            [
               'name'    => 'StringLength',
               'options' => [
                   'min' => 0,
                   'max' => 255
               ],
            ], //validator: 1                           
         ], //END: VALIDATORS KEY 
                           
    ]);   
   } //END: addFilters method()
    
    
}//END CLASS
