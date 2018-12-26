<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Zend\Db\Adapter\Adapter;
use Application\Service\MyAdapter;


/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into IndexController
 */
class MyAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

           $dbconnection = [
                        'driver'         => 'IbmDb2',
                        'database'       => 'COSTEX1',// COSTEXM15  DEVELOPMENT
                        'hostname'      =>  'COSTEXM15',
                        'username'       => 'mojeda',
                        'password'       => '1978M1ch3l',
                        'driver_options' => [
                            //'i5_commit' => DB2_I5_TXN_READ_UNCOMMITTED,
                            'autocommit' => DB2_AUTOCOMMIT_OFF,
                            'i5_lib' => "QS36F",
                        ],
                   ];

        $conAdapter= new Adapter( $dbconnection );
       
        //------- Instantiate the controller and inject dependencies
        return new MyAdapter( $conAdapter );
    }
}