<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/* services to retrieve from the Service Manager */
use Purchasing\Service\ProductDevManager;
use Purchasing\Service\WishListManager;

use Application\Service\QueryManager;


use Purchasing\Controller\ProductdevController;

/**
 * This is the factory for ProductDevController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class ProductdevControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    {       
       /* retrieving SERVICES */
        $entityManager = $container->get('doctrine.entitymanager.orm_default'); 
        $productDevManager = $container->get( ProductDevManager::class );       
        $wishListManager = $container->get( WishListManager::class );       
        $queryManager = $container->get( QueryManager::class ); //      
        $sessionManager = $container->get('WishListSession');
        
        // Instantiating and injecting dependencies  to the CONTROLLER (services) 
        return new ProductdevController( $entityManager, $wishListManager, $productDevManager, $queryManager, $sessionManager );
    }
}