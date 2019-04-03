<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/* services to retrieve from the Service Manager */
use Purchasing\Service\WishListManager;
use Application\Service\QueryManager;
use Zend\Session\SessionManager;

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
        $wishListManager = $container->get( WishListManager::class );       
        $queryManager = $container->get( QueryManager::class );       
        $sessionManager = $container->get('WishListSeccion');
                       

        // Instantiating the controller and injecting dependencies (services) 
        return new WishlistController( $wishListManager, $queryManager, $sessionManager );
    }
}