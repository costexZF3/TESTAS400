<?php
namespace Application\Service;

use Zend\Permissions\Rbac\Rbac;
use User\Entity\User;

/**
 * This service is used for invoking user-defined RBAC dynamic assertions.
 */
class RbacAssertionManager
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    
    /**
     * Auth service.
     * @var Zend\Authentication\AuthenticationService 
     */
    private $authService;
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $authService) 
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }
    
    /**
     * This method is used for dynamic assertions. 
     * In certain situations simply checking a permission key for access may not be enough
     * For example: two users, User1 and  User2  have the article.edit permission. 
     * - What's to stop User1 from editing User2's articles?
     * The answer is dynamic assertions which allow you to specify extra runtine credentials that must
     * pass for access to be granted.
     * -This Dynamic Assertions ensure that the user loggin only can only modify her/his data and her/his 
     * -has assigned the : profile.own.view permission 
     */
    public function assert( Rbac $rbac, $permission, $params ) 
    {
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());        
                
        if ($permission=='profile.own.view' && $params['user']->getId()==$currentUser->getId())
            return true;
        
        return false;
    }
}



