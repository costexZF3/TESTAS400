<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/* services to retrieve from the Service Manager */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;
use Zend\Session\SessionManager as SM;
use Purchasing\Service\ProductDevManager;

/* service that will be injected into the Controller: WishListController */
use Purchasing\Controller\WishlistController;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class WishlistControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    {       
       /* retrieving SERVICES queryRecover */
        $productDevManager = $container->get(ProductDevManager::class); 
        $wishListManager = $container->get( WishListManager::class );       
        $queryManager = $container->get( QueryManager::class ); //      
        $sessionManager = $container->get('WishListSession');
          
//        $sessionManagerMain =$container->get('');

        // Instantiating the controller and injecting dependencies (services) 
        return new WishlistController( $productDevManager, $wishListManager, $queryManager, $sessionManager );
    }
}