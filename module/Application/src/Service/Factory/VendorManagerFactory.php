<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Application\Service\QueryManager;
use Application\Service\VendorManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class VendorManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
         // retrieving an instance of the service QueryRecover 
        $queryManager = $container->get( QueryManager::class );
        
        /* Injecting  and inject dependencies */ 
        return new VendorManager( $queryManager );
    }
}