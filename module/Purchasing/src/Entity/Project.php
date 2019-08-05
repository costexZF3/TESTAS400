<?php

namespace Purchasing\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Purchasing\Entity\Detail;

/**
 * THIS IS AN ENTITY CLASS WHICH MAPS THE THE FILE: PRDVLH
 * IN THE AS400.
 * - you can retrieve all information about the vendor
 * 
 * @ORM\Entity()
 * @ORM\Table(name="QS36F.PRDVLH")
 */
class Project {
   // PROJECT STATUS CONSTANTS
   const STATUS_FINALIZED    = 'F'; // Finalized.
   const STATUS_INITIALIZED  = 'I'; //  Initialized.
        
  /**
   * @ORM\Id
   * @ORM\Column(name="PRHCOD")
   */      
   private $code;  //PRHCOD
   
   /** 
   * @ORM\Column(name="PRDATE")  
   */
   private $date; // PRDATE
   
   /** 
   * @ORM\Column(name="PRINFO")  
   */
   private $description;  //PRINFO
   
   /** 
   * @ORM\Column(name="PRNAME")  
   */
   private $name; // PRNAME
   
   /** 
   * @ORM\Column(name="PRSTAT")  
   */
   private $status; // PRSTAT
   
   /** 
   * @ORM\Column(name="CRDATE")  
   */
   private $creationDate  = ''; // CRDATE
   
   /** 
   * @ORM\Column(name="CRUSER")  
   */
   private $creationUser; // CRUSER
   
   /** 
   * @ORM\Column(name="MODATE")  
   */
   private $modifyDate; // MODATE
   
   /** 
   * @ORM\Column(name="MOUSER")  
   */
   private $modifyUser; // MOUSER
   
   /** 
    * @ORM\Column(name="PRPECH")  
    */
   private $userInCharge;  //PRPECH
  
   
 /* constructor
 *  - this receives from the SERVICE PartNumberManager 
  *   a dataSET for recovering data from AS400
 *    @var dataSet array
 */
    
   public function __construct() {       
//      $this->listparts = new ArrayCollection();
   }      
   
   
   
   public function getDetails(){
//      return $this->listparts;
   }
   
   public function setDetails( $detail ) 
   {
//      $this->listparts[] = $detail;              
   }
   
   /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_FINALIZED => 'Finalized',
            self::STATUS_INITIALIZED => 'Initialized'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        $index = ($this->status =='F') ? 'F' : 'I'; 
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
     * 
     * @param integer $code
     */
    public function setCode($code) 
    {
      $this->code = $code;
    }
    
    /**
     * 
     * @param string $name
     */
    public function setName( $name ) 
    {
      $this->name = $name;
    }
    
    /**
     * 
     * @param string $description
     */
    public function setDescription($description) 
    {
      $this->description = $description;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setCreationUser($user) 
    {
      $this->creationUser = $user;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setModifyUser($user) 
    {
      $this->modifyUser = $user;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setUserInCharge($user) 
    {
      $this->userInCharge = $user;
    }
    
    
   /* getters */  

   public function getCode() {
      return $this->code;
   }
   public function getDate() {
      return $this->date;
   }
   public function getDescription() {
      return $this->description;
   }
   public function getName() {
      return $this->name;
   }
   public function getStatus() {
      return $this->status;
   }
   public function getCreationUser() {
      return $this->creationUser;
   }
   public function getCreationDate() {
      return $this->creationDate;
   }
   
   public function getModifyDate() {
      return $this->modifyDate;
   }

   public function getModifyUser() {
      return $this->modifyUser;
   }

   public function getUserInCharge() {
      return $this->userInCharge;
   }

}
