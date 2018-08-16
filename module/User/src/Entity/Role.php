<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class represents a role.
 * @ORM\Entity()
 * @ORM\Table(name="QS36F.ctprole")
 */
class Role
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
     * @ORM\JoinTable(name="QS36F.ctprole_hierarchy",
     *      joinColumns={@ORM\JoinColumn(name="ctp_child_role_id", referencedColumnName="ctp_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ctp_parent_role_id", referencedColumnName="ctp_id")}
     *      )
     */
    private $parentRoles;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="QS36F.ctprole_hierarchy",
     *      joinColumns={@ORM\JoinColumn(name="ctp_parent_role_id", referencedColumnName="ctp_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ctp_child_role_id", referencedColumnName="ctp_id")}
     *      )
     */
    protected $childRoles;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Permission")
     * @ORM\JoinTable(name="QS36F.ctprole_permission",
     *      joinColumns={@ORM\JoinColumn(name="ctp_role_id", referencedColumnName="ctp_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ctp_permission_id", referencedColumnName="ctp_id")}
     *      )
     */
    private $permissions;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->parentRoles = new ArrayCollection();
        $this->childRoles = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }
    
    /**
     * Returns role ID.
     * @return integer
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Sets role ID. 
     * @param int $id    
     */
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
    
    public function getParentRoles()
    {
        return $this->parentRoles;
    }
    
    public function getChildRoles()
    {
        return $this->childRoles;
    }
    
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function addParent(Role $role)
    {
        if ($this->getId() == $role->getId()) {
            return false;
        }
        if (!$this->hasParent($role)) {
            $this->parentRoles[] = $role;
            return true;
        }
        return false;
    }

    /**
     * Clear parent roles
     */
    public function clearParentRoles()
    {
        $this->parentRoles = new ArrayCollection();
    }

    /**
     * Check if parent role exists
     * @param Role $role
     * @return bool
     */
    public function hasParent(Role $role)
    {
        if ($this->getParentRoles()->contains($role)) {
            return true;
        }
        return false;
    }
}



