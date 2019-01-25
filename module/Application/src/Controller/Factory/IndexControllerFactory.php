<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\IndexController;
use Application\Service\MyAdapter;


/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
              
        // retrieving an instance of MyAdapter Service from the Service Manager
        $dbconnection = $container->get( MyAdapter::class );
        $connAdapter = $dbconnection->getAdapter();
        
        // Instantiate the controller and inject dependencies
        return new IndexController( $connAdapter);
    }
}