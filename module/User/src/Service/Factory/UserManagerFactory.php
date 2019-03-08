<?php
namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use User\Service\UserManager;
use User\Service\RoleManager;
use User\Service\PermissionManager;
use Zend\Config\Config as ConfigObject;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class UserManagerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $roleManager = $container->get(RoleManager::class);
        $permissionManager = $container->get(PermissionManager::class);
        
        $viewRenderer = $container->get('ViewRenderer');
        
        /* initial configuracion for sent an email...
         *  KRISTOV....YOU MUST CREATE A NEW SERVICE FOR SHARING THE sendEmail Logic 
         *  with all Modules and Injected from anywhere
         */

        $config = ['smtp' =>
                   [
                        'name' => 'mail.costex.com',
                        'host' => '172.0.0.12',                   
                        'connection_config' => [
                                 'username' => 'misonline@costex.com', 
                                 'password' => 'sys61001', 
                                     'port' => 25],
                   ],
                  ];
                    
        
        $smtpMail = $config['smtp'];
        
        return new UserManager($entityManager, $roleManager, $permissionManager, $viewRenderer, $smtpMail);    
    }
}
