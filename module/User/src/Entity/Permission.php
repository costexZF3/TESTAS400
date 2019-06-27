<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class represents a permission.
 * @ORM\Entity()
 * @ORM\Table(name="QS36F.ctppe00001") //ctppermission
 */
class Permission
{
    /**
     * @ORM\Id
     * @ORM\Column(name="ctp_id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="ctp_name")  
     */
    protected $name;
    
    /** 
     * @ORM\Column(name="ctp_description")  
     */
    protected $description;

    /** 
     * @ORM\Column(name="ctp_date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="QS36F.ctprole_permission",
     *      joinColumns={@ORM\JoinColumn(name="ctp_permission_id", referencedColumnName="ctp_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ctp_role_id", referencedColumnName="ctp_id")}
     *      )
     */
    private $roles;

    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->roles = new ArrayCollection();
    }
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }
    
    public function getRoles()
    {
        return $this->roles;
    }
}



