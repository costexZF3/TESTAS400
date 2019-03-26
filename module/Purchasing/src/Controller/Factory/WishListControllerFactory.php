<?php
namespace Purchasing\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Purchasing\Controller\WishListController;

use Application\Service\QueryRecover;
use Application\Service\PartNumberManager;


/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class WishListControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    {       
       /* retrieving SERVICES queryRecover */
        $queryManager = $container->get( QueryRecover::class );       
        $partNumberManager = $container->get( PartNumberManager::class );       

        // Instantiate the controller and inject dependencies
        return new WishListController( $queryManager, $partNumberManager );
    }
}