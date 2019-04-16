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


class FormValidator extends Form 
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
        
    public function __construct($queryManager ) 
    {                 
        
        $this->queryManager = $queryManager;
       
        // Defined form name 
        parent::__construct('form-validator');
      
         // form -- method 
        $this->setAttribute('method', 'post');  
        
        $this->addElement();
        $this->addInputFilters();
              
    }//END CONSTRUCTOR
    
    
    private function addElement() 
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
            ],
            'options' =>['label' => 'PART NUMBER'],                     
        ]);
    }
  
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
             
         ], //END: VALIDATORS KEY                       
    ]); 
       
   } //END: addFilters method()
   
    
}//END CLASS
