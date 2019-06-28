<?php
    namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a registered user in the AS400.
 * @ORM\Entity()
 * @ORM\Table(name="QS36F.CSUSER")
 */
class UserAS400 
{
    // User status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
    /**
     * @ORM\Id
     * @ORM\Column(name="USUSER")
     * 
     */
    protected $user;

    /** 
     * @ORM\Column(name="USEMAI")  
     */
    protected $email;
    
    /** 
     * @ORM\Column(name="USNAME")  
     */
    protected $fullName;

     /** 
     * @ORM\Column(name="USPTY9")  //I DON'T KNOW IF IT'S TRUE  
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="USPURC")  //I DON'T KNOW IF IT'S TRUE  
     */
    protected $paCode;  
     
    /**
     * Constructor.
     */
    public function __construct() 
    {
        
    }
    
    /**
     * Returns user ID.
     * @return string
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Sets user ID. 
     * @param string $id    
     */
    public function setUser($id) 
    {
        $this->name = $id;
    }
    
    /**
     * Returns email.     
     * @return string
     */
    public function getEmail() 
    {
        return strtolower( $this->email );
    }

    /**
     * Sets email.     
     * @param string $email
     */
    public function setEmail($email) 
    {
        $this->email = strtolower( $email );
    }
    
    /**
     * Returns full name.
     * @return string     
     */
    public function getFullName() 
    {
        return $this->fullName;
    }       

    /**
     * Sets full name.
     * @param string $fullName
     */
    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }
    
    /**
     * Purchasing Agent Code
     * 
     * @param int $pacode
     */
    public function setPaCode( $pacode )
    {
        $this->paCode = $pacode;
    }
    
    /**
     * Return the Purchasing Agent code
     * 
     * @return int
     */
    public function getPaCode()
    {
        return $this->paCode;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        $index = $this->status =='R' ? 2 : 1; 
        return $list[$index] ?? 'Unknown';      
    }    
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns password.
     * @return string
     */
    public function getPassword() 
    {
       return $this->password; 
    }
    
    /**
     * Sets password.     
     * @param string $password
     */
    public function setPassword($password) 
    {
        $this->password = $password;
    }
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
}


