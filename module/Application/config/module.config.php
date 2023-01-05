<?php

declare(strict_types=1);

namespace Application;

use Application\ViewHelper\DateFormat;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    'router' => [
        'routes' => [
            'main' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/:controller[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => ReflectionBasedAbstractFactory::class,
            Controller\AuthController::class => ReflectionBasedAbstractFactory::class,
            Controller\VehicleController::class => ReflectionBasedAbstractFactory::class,
            Controller\OfficeController::class => ReflectionBasedAbstractFactory::class,
            Controller\DriverController::class => ReflectionBasedAbstractFactory::class,
            Controller\FuelController::class => ReflectionBasedAbstractFactory::class,
            Controller\MaintenanceController::class => ReflectionBasedAbstractFactory::class,
            Controller\UserController::class => ReflectionBasedAbstractFactory::class,
        ],
        'aliases' => [
            'index' => Controller\IndexController::class,
            'auth' => Controller\AuthController::class,
            'vehicle' => Controller\VehicleController::class,
            'office' => Controller\OfficeController::class,
            'driver' => Controller\DriverController::class,
            'fuel' => Controller\FuelController::class,
            'maintenance' => Controller\MaintenanceController::class,
            'user' => Controller\UserController::class,
        ]
    ],
    "service_manager"=>[
        "factories"=>[
            Model\UserTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\UserTableGateway::class);
                return new Model\UserTable($tableGateway);
            },
            Model\UserTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\User());
                return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
            },

            Model\DriverTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\DriverTableGateway::class);
                return new Model\DriverTable($tableGateway);
            },
            Model\DriverTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Driver());
                return new TableGateway('drivers', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\OfficeTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\OfficeTableGateway::class);
                return new Model\OfficeTable($tableGateway);
            },
            Model\OfficeTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Office());
                return new TableGateway('office', $dbAdapter, null, $resultSetPrototype);
            },


            Model\MaintenanceTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\MaintanenceTableGateway::class);
                return new Model\MaintenanceTable($tableGateway);
            },
            Model\MaintanenceTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Maintenance());
                return new TableGateway('maintenance', $dbAdapter, null, $resultSetPrototype);
            },
            
            
            Model\MaintenanceScheduleTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\MaintanenceShaduleTableGateway::class);
                return new Model\MaintenanceScheduleTable($tableGateway);
            },
            Model\MaintanenceShaduleTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\MaintenanceSchedule());
                return new TableGateway('maintenance_schedule', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\RapairTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\RapairTableGateway::class);
                return new Model\RapairTable($tableGateway);
            },
            Model\RapairTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Repair());
                return new TableGateway('repair', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\RefuelingTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\RefuelingTableGateway::class);
                return new Model\RefuelingTable($tableGateway);
            },
            Model\RefuelingTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Refueling());
                return new TableGateway('refueling', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\TransireTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\TransireTableGateway::class);
                return new Model\TransireTable($tableGateway);
            },
            Model\TransireTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Transire());
                return new TableGateway('transire', $dbAdapter, null, $resultSetPrototype);
            },

            Model\FuelTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\FuelTableGateway::class);
                return new Model\FuelTable($tableGateway);
            },
            Model\FuelTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Fuel());
                return new TableGateway('fuel', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\VechicleMaintanenceTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\VechicleMaintanenceTableGateway::class);
                return new Model\VechicleMaintanenceTable($tableGateway);
            },
            Model\VechicleMaintanenceTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\VechicleMaintanence());
                return new TableGateway('vechicle_maintanence', $dbAdapter, null, $resultSetPrototype);
            },
            
            Model\VehicleTable::class=>function ($container)
            {
                $tableGateway=$container->get(Model\VehicleTableTableGateway::class);
                $vehicleFuelTableGateway=$container->get(Model\VehicleFuelTableTableGateway::class);
                return new Model\VehicleTable($tableGateway, $vehicleFuelTableGateway);
            },
            Model\VehicleTableTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Vehicle());
                return new TableGateway('vehicles', $dbAdapter, null, $resultSetPrototype);
            },

            Model\VehicleFuelTableTableGateway::class=>function ($container)
            {
                $dbAdapter=$container->get(AdapterInterface::class);
                $resultSetPrototype=new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\VehicleFuel());
                return new TableGateway('vehicle_fuel', $dbAdapter, null, $resultSetPrototype);
            },
            
            
        ]
    ],
    'view_helpers' => [
        'factories' => [
            DateFormat::class => ReflectionBasedAbstractFactory::class,
        ],
        'aliases' => [
            'DateFormat' => DateFormat::class,
        ]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
