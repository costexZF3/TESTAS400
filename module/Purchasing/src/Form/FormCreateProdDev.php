<?php

namespace Purchasing\Form;


/**
 * FormCreateProdDev (FORM)
 * - This form contains all elements that will be shown (or entered by keyboard) 
 *   for a new development project
 *  
 * @author mojeda
 * - For convention(FormNameOfTheForm) all in CamelCase:
 * - FormCreateProdDev
 */

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

use Application\Validator\VendorExistValidator;
use Application\Service\QueryManager as queryManager;

class FormCreateProdDev extends Form 
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
      parent::__construct('form-createprodev');

       // form -- method 
      $this->setAttribute('method', 'post');  

      /* method for add items to the form */
      $this->selectElements();
              
   }//END CONSTRUCTOR
    
   private function  selectElements() 
   {
      switch ( $this->scenario ) {
         case 'new' :                  
            $this->addElementScenarioNew();
            $this->addElementsProductDevelopment();            
            $this->addInputFiltersSC1();

             //adding CSRF and a Submit Button
             $this->addCommonElements('Create Project'); 
         break;
         case 'add' : //there are data                   
             $this->addElementSC2();                 
             $this->addInputFiltersSC2(); 
         break;            

      }//END: SWITCH
   }//END: selectElements

   /**
    * This method add the commons elements to each scenario 
    * 
    * @param string $submitValue | value
    */
   private function addCommonElements( $submitValue = "SUBMIT" ) 
   {
      /* Add the CSRF field (CROSS SITE REQUEST FORGUERY ATTACK ) */
      $this->add([
          'type' => 'csrf',
          'name' => 'csrf',
          'options' => [
              'csrf_options' => 
                  ['timeout' => 60000]
          ],
      ]);

      // Add the Submit button
      $this->add([
          'type'  => 'submit',
          'name' => 'submit',
          'attributes' => ['value' => $submitValue],
      ]);
      
   }//END addCommonElements()
   
   /**
    * This method adds the new ones form elements 
    * for retrieving the needed data
    * 
    */
   private function addElementScenarioNew() 
   {            
      //adding info from WL 
      
      /* wish list code */       
      $this->add([
          'name'=>'wlcode',    
          'type'=>'text',                              
          'attributes' => [  
              'class' => 'form-control',
              'id'=>'wlcode',    
              'readonly'=> true
          ],
          'options' =>['label' => 'WISH LIST#'],                     
      ]);
     
      /* creation date */       
      $this->add([
          'name'=>'creationdate',    
          'type'=>'text',                              
          'attributes' => [ 
              'class' => 'form-control',
              'id'=>'creationdate', 
              'readonly' => true
          ],
          'options' =>['label' => 'CREATION DATE'],                     
      ]);
      
      /* BY USER -- user who create it */       
      $this->add([
          'name'=>'usercreated',    
          'type'=>'text',                              
          'attributes' => [             
              'class' => 'form-control',
              'id'=>'usercreated',            
              'readonly' => true
          ],
          'options' =>['label' => 'USER CREATED'],                     
      ]);

      // -------------------- ROW 2 ------------------------

      /* current vendor */
      $this->add([
          'name'=>'currentvendor',    
          'type'=>'text',                              
          'attributes' =>[         
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'CURRENT VENDOR'],                     
      ]);

      
      //current vendor description
      $this->add([
          'name'=>'vendordesc',    
          'type'=>'text',                      
          'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'DESCRIPTION'],                     
      ]);
      
      // -------------------- ROW 3 assigned to, reason type  ------------------------
      
      /* ASSIGNED TO */
      $this->add([
          'name'=>'assignedto',    
          'type'=>'text',                      
          'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'ASSIGNED TO'],                     
      ]);
      
      /* REASON TYPE*/
      $this->add([
          'name'=>'reasontype',    
          'type'=>'text',                      
          'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'REASON TYPE'],                     
      ]);
      
       // -------------------- ROW 4 quantity sold, times quote last year  ------------------------
      
      /* QUANTITY SOLD */
      $this->add([
          'name'=>'qtysold',    
          'type'=>'text',                      
          'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'QTY SOLD'],                     
      ]);
      
      /* TIMES QUOTE LAST YEAR */
      $this->add([
          'name'=>'timesqtely',    
          'type'=>'text',                      
          'attributes' =>[          
                'class' => 'form-control',
                'readonly' => true,                     
          ],
          'options' =>['label' => 'TIMES QUOTE LY'],                     
      ]);
      
      
      /* --------------------- COMMENTS ---------------------------------*/
      
      // comment about the ITEM 
      $this->add([
          'name'=>'comments',    
          'type'=>'textarea',                              
          'attributes' =>[   
              'class' => 'form-control',
              'id'=>'comments',
              'rows' => "9",                                                          
              'readonly' => true,                    
          ],
          'options' =>['label' => 'COMMENTS'],                     
      ]);
   }//END: addElementScenarioNew()  
   
     
   private function addElementsProductDevelopment() 
   {   /* NEW ELEMENTS WILL BE ENTERED BY THE PROJECT */ 
       
      //MAYBE NOT...IMAGE OF THE PART
      $this->add([
          'name'=>'image',    
          'type'=>'image',    
          'attributes' =>[          
              'class' =>'form-control',
              'id'=>'image',                                      
              'readonly' => true,
          ],
          'options' =>[              
              'label' => ''
          ],                     
      ]);
      // PART NUMBER
      $this->add([
           'name'=>'partnumber',    
           'type'=>'text',                    
           'attributes' =>[      
               'class' =>'form-control',
               'id'       => 'code',                                          
               'readonly' => true,
           ],
           'options' =>['label' => 'PART NUMBER'],                     
      ]);
      // CTP PART NUMBER
      $this->add([
           'name'=>'ctppartnumber',    
           'type'=>'text',                    
           'attributes' =>[      
               'class' =>'form-control',
               'id'       => 'ctpnumber',                                          
               'readonly' => true,
           ],
           'options' =>['label' => 'CTP #'],                     
      ]);
      
      // part description
      $this->add([
           'name'=>'ctppartnumber',    
           'type'=>'text',                    
           'attributes' =>[      
               'class' =>'form-control',
               'id'       => 'ctppartnumber',                                          
               'readonly' => true,
           ],
           'options' =>['label' => 'CTP #'],                     
      ]);
      // PART NUMBER DESCRIPTION
      $this->add([
           'name'=>'partnumberdesc',    
           'type'=>'text',                    
           'attributes' =>[      
               'class' =>'form-control',
               'id'       => 'partnumberdesc',                                          
               'readonly' => true,
           ],
           'options' =>['label' => 'DESCRIPTION'],                     
      ]);      
      
      /************************ ELEMENTS ENTERED BY KEYBOARD ****************************/
      
      // PROJECT CODE
      $this->add([
           'name'=>'projectcode',    
           'type'=>'text',                    
           'attributes' =>[      
               'class' =>'form-control',
               'id'       => 'code',                                                                    
               'readonly' => true
            ],
            
           'options' =>['label' => 'PROJECT CODE'],                     
      ]);

      //PROJECT NAME
      $this->add([
          'name'=>'projectname',    
          'type'=>'text',    
          'attributes' =>[          
              'class' =>'form-control',
              'id'=>'project-name',  
              'placeholder' => 'Enter a new Project Name'
          ],
          'options' =>[              
              'label' => 'PROJECT NAME'
          ],                     
      ]); 

      //NEW VENDOR CODE
      $this->add([
          'name'=>'newvendorname',    
          'type'=>'text',                      
          'attributes' => [
              'class' =>'form-control',
              'id'=>'newvendor', 
              'placeholder' => 'Enter a vendor number'
          ],
          'options' => ['label' => 'NEW VENDOR'],                     
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

      // OEM PRICE
      $this->add([
          'name'=>'oemprice',    
          'type'=>'text',               
          'attributes' =>[       
              'class' => 'form-control',
              'id'=>'price',   
              'readonly'=> true,
          ],
          'options' =>['label' => 'OEM PRICE'],                     
      ]);
      
      // MINOR CODE
      $this->add([
            'name'=>'minorcode',    
            'type'=>'text',                    
            'attributes' =>[      
                'class' =>'form-control',
                'id'       => 'minorcode',                                          
                'readonly' => true,
            ],
            'options' =>['label' => 'MINOR CODE'],                     
        ]);
        
   }//End: SCENARIO 1
  
   /*
     *  This method creates input filters (used for form filtering/validation ).
   */
   private function addInputFiltersSC1() 
   { 
      // Create main input filter
       $inputFilter = new InputFilter();        
       $this->setInputFilter( $inputFilter );

       $inputFilter->add([
       'name'     => 'projectname',
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
               'options' => ['min' => 8,'max' => 255],
           ],  
        ], //END: VALIDATORS KEY                       
      ]); 
       
      $inputFilter->add([
       'name'     => 'newvendorname',
       'required' => true,
       
         'filters'  => [
           ['name' => 'StringTrim'],                                                            
           ['name' => 'StripTags'],                                                            
           ['name' => 'StripNewlines'],             
         ], 
          
         'validators' => [ 
            //validator: 1           
            ['name'    => 'StringLength',
                'options' => ['min' => 2,'max' => 6],
            ],              
            
            //validator: 2   if you not define 'table' index then it takes VNMAS BY DEFAULT         
            ['name' => VendorExistValidator::class,
             'options' => [
//                'table' => VendorExistValidator::TABLE_DVINVA,
                'queryManager' => $this->queryManager, 
                'notintable' => false  
              ]                        
            ],                    
             
         ], //END: VALIDATORS KEY  
      ]);
       
   } //END: addFilters method()
        
}//END CLASS
