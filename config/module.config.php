<?php
return array(
    'di' => array( //creates an alias for json, xml and image post-processors
        'instance' => array(
            'alias' => array(
                'json-pp'  => 'Api\PostProcessor\Json',
                'xml-pp'  => 'Api\PostProcessor\Xml',
                'image-pp' => 'Api\PostProcessor\Image',
            )
        )
    ),
    'controllers' => array( //lists both controllers of the module
        'invokables' => array(
            'rest' => 'Api\Controller\RestController',
            'rpc' => 'Api\Controller\RpcController',
        )
    ),
    'cache' => array(
        'adapter' => 'memory'
    ),
    'router' => array( //controllers routes
        'routes' => array(
            'restful' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/api/v1/:module[.:entity][.:formatter][/:id]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'entity' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'formatter'  => '[a-zA-Z]+',
                        'id'         => '[a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'rest',
                    ),
                ),
            ),
            'rpc' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/rpc/v1/:module[.:service][.:formatter]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'service' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'formatter'  => '[a-zA-Z]+',
                    ),
                    'defaults' => array(
                        'controller' => 'rpc',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    // 'db' => array( //Module database
    //     'driver' => 'Pdo',
    //     'dsn'    => 'pgsql:host=localhost;port=5432;dbname=api;user=postgres;password=coderockr',
    // ),
    'db' => array(
        'driver' => 'PDO_SQLite',
        'dsn' => 'sqlite:' . __DIR__ .'/../data/api.db',
        'driver_options' => array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        )
    ),
     'service_manager' => array(
        'factories' => array(
            'Cache' => function($sm) {
                $config = $sm->get('Configuration');
                $cache = \Zend\Cache\StorageFactory::factory(
                    array(
                        'adapter' => $config['cache']['adapter'],
                        'plugins' => array(
                            'exception_handler' => array('throw_exceptions' => false),
                            'Serializer'
                        ),
                    )
                );

                return $cache;
            },
            'DbAdapter' => 'Api\Db\AdapterServiceFactory', 
            'Api\Service\Client' => function($sm) { 
                $config = $sm->get('Configuration');
                $apiConfig = $config['api'];
                return new Service\Client($apiConfig['apiKey'], $apiConfig['apiUri'], $apiConfig['rpcUri']);
            },
        ),
    )
//    'db' => array(
//        'driver' => 'Pdo',
//        'dsn' => 'oci:dbname=apimobile;charset=WE8ISO8859P1',
//        'username' => '',
//        'password' => '',
//        'driver_options' => array(
//            PDO::ATTR_CASE => PDO::CASE_LOWER,
//            PDO::ATTR_PERSISTENT => true,
//            PDO::ATTR_AUTOCOMMIT => 1,
//            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//        ),
//        'platform_options' => array('quote_identifiers' => false)
//    ),
    
);

