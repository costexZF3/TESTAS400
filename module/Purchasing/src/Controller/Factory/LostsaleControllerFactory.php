<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Purchasing\Controller\LostsaleController;


use Application\Service\MyAdapter;
use Purchasing\Service\WishListManager;


/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class LostsaleControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        
         // retrieving an instance of MyAdapter Service from the Service Manager
        $dbconnection = $container->get( MyAdapter::class );
        $connAdapter = $dbconnection->getAdapter();
        $wishListManager = $container->get( WishListManager::class ); 
        
        // Instantiate the controller and inject dependencies
        return new LostsaleController( $entityManager, $connAdapter, $wishListManager );
    }
}