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


class FormAddItemWL extends Form 
{
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
        
    public function __construct( $scenario, $queryManager ) 
    {                 
        $this->scenario = $scenario;
        $this->queryManager = $queryManager;
       
        // Defined form name 
        parent::__construct('form-additem-wl');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
        
        /* changing the default route when the user click in submit */
//        if ($scenario == 'create') {
//            // ( Optionally ) set action for this form
//            
//            $this->setAttribute('action', 'wishlist/insert');
//        }      

         /* method for add items to the form */
        $this->selectElements();
              
    }//END CONSTRUCTOR
    
    /**
     * 
     * @param string $scenario
     */
    private function  selectElements() 
    {
        switch ( $this->scenario ) {
            case 'initial' :  
                
                $this->addElementSC1(); 
                $this->addCommonElements();
                $this->addInputFiltersSC1(); 
                 
            break;
            case 'insert' : //there are data                   
                $this->addElementSC2(); 
                $this->addSubElementsVendors();
                $this->addCommonElements('ADD');
                $this->addInputFiltersSC2(); 
            break;            
            case 'create' : //there are data    
                $this->addElementSC2();
                $this->addElementSC3(); 
                $this->addCommonElements('CREATE');
                $this->addInputFiltersSC3(); 
            break;            
        }//END: SWITCH
    }//END: selectElements

    /**
     * this method add the commons elements to each scenario 
     * @param string $submitValue | value
     */
    private function addCommonElements ( $submitValue = "SUBMIT" ) 
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
    
    private function addElementSC1() 
    {            
        /* part number */       
        $this->add([
            'name'=>'partnumber',    
            'type'=>'text',                              
            'attributes' => [          //array of attributes                       
                'id'=>'partnumber',
                'minlength' => "5",
                'maxlength' => "19",                   
                'required' => true, 
                'placeholder' => 'must be created',
                'text' => 'PartNumber MUST BE exist'
            ],
            'options' =>['label' => 'PART NUMBER'],                     
        ]);
    }
    
    private function addSubElementsVendors()
    {
        // ------------------------------ VENDOR ------------------------

        $this->add([
            'name'=>'vendor',    
            'type'=>'text',                              
            'attributes' =>[         
                  'class' => 'form-control',
                  'readonly' => true,                     
            ],
            'options' =>['label' => 'VENDOR'],                     
        ]);
        
        //part number description
        $this->add([
            'name'=>'vendordesc',    
            'type'=>'text',                      
            'attributes' =>[          
                  'class' => 'form-control',
                  'readonly' => true,                     
            ],
            'options' =>['label' => 'VENDOR DESCRIPTION'],                     
        ]);
    }
    
    private function addElementSC2() 
    {   //CODE OF THE ITEM INSIDE THE WISHLIST    
        $this->add([
            'name'=>'code',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'code',                                          
                'readonly' => true,
            ],
            'options' =>['label' => 'CODE'],                     
        ]);

        //CREATED BY USER XYZ 
        $this->add([
            'name'=>'user',    
            'type'=>'text',    
            'attributes' =>[          
                'class' =>'form-control',
                'id'=>'created-user',                                      
                'readonly' => true,
            ],
            'options' =>[              
                'label' => 'USER'
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
            'options' => ['label' => 'DATE'],                     
        ]);             
        
        // PART NUMBER BUT ONLY INFORMATION IT WILL BE READ ONLY AS WELL.
        $this->add([
            'name'=>'partnumber',    
            'type'=>'text',                      
            'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
            ],
            'options' =>['label' => 'PART NUMBER'],                     
        ]);
        
        // PART NUMBER DESCRIPTION 
        $this->add([
            'name'=>'partnumberdesc',    
            'type'=>'text',           
            'attributes' =>[          //array of attributes
                    'class' => 'form-control', 
                    'readonly' => true,
            ],
            'options' =>['label' => 'DESCRIPTION '],                     
        ]);
        
        

        
        // comment about the ITEM 
        $this->add([
            'name'=>'comment',    
            'type'=>'textarea',                              
            'attributes' =>[          //array of attributes
                'class' => 'form-control',
                'id'=>'comment',
                'rows' => "3", 
                'maxlenght' => 256,                                           
                'placeholder' => 'Enter a comment',                    
            ],
            'options' =>['label' => 'COMMENTS'],                     
        ]);
        
        // price
        $this->add([
            'name'=>'price',    
            'type'=>'text',               
            'attributes' =>[          //array of attributes
                'class' => 'form-control',
                'id'=>'price',   
                'readonly'=> true,
            ],
            'options' =>['label' => 'PRICE'],                     
        ]);

        // type  
        $this->add([            
            'type'  => 'select',
            'name' => 'type',
            'options' => [
                'label' => 'TYPE',
                'value_options' => [
                    1 => 'New Part',
                    2 => 'New Vendor',                                                                                                                                    
                ],
            ],
        ]);
        
        
    }//End: SCENARIO 1
    
     private function addElementSC3() 
    {
        /* data that will be inserted into PRDWLADD (FILE) and this will be inserted into the
         * INMSTA file when the part will be picked up for development
         */
        $this->add([
            'name'=>'model',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'code',                                          
//                'readonly' => true,
            ],
            'options' =>['label' => 'MODEL'],                     
        ]);
        
        /* MAJOR CODE */
        $this->add([
            'name'=>'major',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'major',                                          
                'minlength' => "2",
                'maxlength' => "2",                   
                'required' => true, 
                'placeholder' => 'eg. 99',
            ],
            'options' =>['label' => 'MAJOR'],                     
        ]);
        
         /* MINOR CODE */
        $this->add([
            'type'  => 'select',
            'name' => 'minor',
            'options' => [
                'label' => 'MINOR',
                'disable_inarray_validator' => true, //IMPORTANTTTTT
            ],                   
        ]);
        
        /* CATEGORY */
        $this->add([
            'name'=>'category',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'category',                                          
                'minlength' => "3",
                'maxlength' => "3",                   
                'required' => true, 
                'placeholder' => 'eg. GEN',
            ],
            'options' =>['label' => 'CATEGORY'],                     
        ]);
        
        /* SUB-CATEGORY*/
        $this->add([
            'name'=>'subcategory',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'subcategory',                                          
                'minlength' => "3",
                'maxlength' => "3",                   
                'required' => true, 
                'placeholder' => 'eg. N01',
            ],
            'options' =>['label' => 'SUB-CATEGORY'],                     
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
            
            //validator: 2
            //validate that the part NOT EXIST in PRDWL (WISHLIST)
            ['name' => PartNumberValidator::class,
             'options' => [
                'table' => PartNumberValidator::TABLE_WISHLIST,
                'queryManager' => $this->queryManager, 
                'notintable' => true  
              ]                        
            ], 
             //validator: 2 
//            [ //VALIDATE THAT EXIST IN INMSTA : BY DEFAULT
//               'name' => PartNumberValidator::class,
//               'options' => [                
//                 'queryManager' => $this->queryManager,                    
//               ]                        
//            ],            
             
         ], //END: VALIDATORS KEY                       
    ]); 
       
   } //END: addFilters method()
    
   
    /*
     *  This method creates input filters (used for form filtering/validation ).
     */
   private function addInputFiltersSC2() 
   { 
       // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter( $inputFilter );
        
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
   private function addInputFiltersSC3() 
   { 
       // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter( $inputFilter );
      
        $inputFilter->add([
        'name'     => 'major',
        'required' => true,
        'filters'  => [
            ['name' => 'StringTrim'],                                                            
            ['name' => 'StripTags'],                                                            
            ['name' => 'StripNewlines'],                                                            
        ], 
        'validators' => [ 
            //validator: 1           
            ['name'    => 'StringLength',
                'options' => ['min' =>1 ,'max' => 3],
            ],  
            
            //validator: 2
            //validate that the code EXIST in THE MAJOR TABLE 
            ['name' => PartNumberValidator::class,
             'options' => [
                'table' => PartNumberValidator::TABLE_MAJOR,
                'queryManager' => $this->queryManager, 
//                'notintable' => true  
              ]                        
            ],         
         ], //END: VALIDATORS KEY                       
   ]); }
    
}//END CLASS
