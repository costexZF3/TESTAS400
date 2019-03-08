<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Application\Service\QueryRecover;
use Application\Service\PartNumberManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class PartNumberManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
         // retrieving an instance of the service QueryRecover 
        $queryManager = $container->get( QueryRecover::class );
        
        /* Injecting  and inject dependencies */ 
        return new PartNumberManager( $queryManager );
    }
}