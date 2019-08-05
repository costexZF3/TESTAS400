<?php
   namespace Purchasing\Service\Factory;

   use Interop\Container\ContainerInterface;
   use Zend\ServiceManager\Factory\FactoryInterface;

   /* services to retrieve from the Service Manager */
   /* these are the services create by Kristov for being */
   use Application\Service\QueryManager;
   use Application\Service\PartNumberManager;
   use Application\Service\VendorManager;

   /*service wich will be created instance with this FACTORY */
   use Purchasing\Service\ProductDevManager;

   /**
    * This is the factory for IndexController. Its purpose is to instantiate the
    * controller and inject dependencies into it.
    */
   class ProductDevManagerFactory implements FactoryInterface
   {
       public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
       {
           $entityManager = $container->get('doctrine.entitymanager.orm_default'); 
           $queryManager = $container->get( QueryManager::class );
           $partNumberManager = $container->get( PartNumberManager::class );    
           $vendorManager = $container->get( VendorManager::class );

           // Injecting dependencies into the Service ProductDevManager 
           return new ProductDevManager( $entityManager, $queryManager, $partNumberManager, $vendorManager );
       }
   }