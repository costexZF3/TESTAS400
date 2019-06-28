<?php
namespace User\Validator;

use Zend\Validator\AbstractValidator;
use User\Entity\UserAS400;
/**
 * This validator class is designed for checking if there is an existing user 
 * with such an email.
 */
class UserExistsValidatorAS400 extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'user' => null
    );
    
    // Validation failure message IDs.
    const NOT_SCALAR  = 'notScalar';
    const USER_NO_EXISTS = 'userNoExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SCALAR  => "The email must be a scalar value",
        self::USER_NO_EXISTS  => "The user MUST be created in the System"        
    );
    
    /**
     * Constructor.     
     */
    public function __construct($options = null) 
    {
        // Set filter options (if provided).
        if(is_array($options)) {            
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
            if(isset($options['user']))
                $this->options['user'] = $options['user'];
        }
        
        // Call the parent class constructor
        parent::__construct($options);
    }
        
    /**
     * Check if user exists.
     */
    public function isValid($value) 
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return $false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        // Taking off the mail address from the 
        $value = strtoupper(str_replace('@costex.com', '', $value));
        
        $user = $entityManager->getRepository(UserAS400::class)
                ->findOneByUser($value);

        if($this->options['user']==null) {
            $isValid = ($user!=null);
        } else {
            if($this->options['user']->getUser()!=$value && $user!=null) {
                $isValid = false;
            }
            else { 
                $isValid = true;
            }
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::USER_NO_EXISTS );            
        }
        
        // Return validation result.
        return $isValid;
    }
}

