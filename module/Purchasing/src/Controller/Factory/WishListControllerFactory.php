<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Purchasing\Controller\WishListController;

//use Application\Service\MyAdapter;
use Application\Service\QueryRecover;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class WishListControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {       
        /* recovering SERVICES QueryRecover */
        $queryRecover = $container->get( QueryRecover::class );       
        
        // Instantiate the controller and inject dependencies
        return new WishListController( $queryRecover );
    }
}