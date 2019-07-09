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
use Purchasing\Service\WishListManager as WLM;

class FormUpdateMultiple extends Form 
{     
    private $wlManager;
    
    public function __construct( $wlManager ) 
    {    
        $this->wlManager = $wlManager;
       
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
            'N/A' =>'N/A',
            WLM::STATUS_OPEN => $this->wlManager->getStatus(WLM::STATUS_OPEN ),
            WLM::STATUS_DOCUMENTATION => $this->wlManager->getStatus( WLM::STATUS_DOCUMENTATION ),
            WLM::STATUS_TO_DEVELOP => $this->wlManager->getStatus( WLM::STATUS_TO_DEVELOP ),                                                                                           
            //WLM::STATUS_CLOSE_BY_DEV => $this->wlManager->getStatus( WLM::STATUS_CLOSE_BY_DEV ),                                                                                           
            WLM::STATUS_REOPEN => $this->wlManager->getStatus( WLM::STATUS_REOPEN ),                                                                                           
            WLM::STATUS_REJECTED => $this->wlManager->getStatus( WLM::STATUS_REJECTED ),                                                                                           
        ];
        
        //recovering the Pa from the AS400 using the wlManager service
        $userList = ['N/A'=>'N/A'];
        $usersAS400 = $this->wlManager->usersPAAS400();
        foreach ($usersAS400 as $user) {
            $userList[$user['USER']] = $user['USER'];
        }
        
        $this->addElementSC1( $status, $userList ); 
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
    
    private function addElementSC1( $status, $users ) 
    {  
        // User in charge
        $this->add([            
            'type'  => 'select',
            'name' => 'name',
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
    
  
    
}//END CLASS
