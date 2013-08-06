<?php
return array(
    'di' => array( //cria alias para os pós-processadores de json, xml e image
        'instance' => array(
            'alias' => array(
                'json-pp'  => 'Api\PostProcessor\Json',
                'xml-pp'  => 'Api\PostProcessor\Xml',
                'image-pp' => 'Api\PostProcessor\Image',
            )
        )
    ),
    'controllers' => array( //lista os dois controllers do modulo
        'invokables' => array(
            'rest' => 'Api\Controller\RestController',
            'rpc' => 'Api\Controller\RpcController',
        )
    ),
    'router' => array( //rotas dos controllers
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
    // 'db' => array( //database espefício do modulo
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
//    'db' => array(
//        'driver' => 'Pdo',
//        'dsn' => 'oci:dbname=desenvunoline.unochapeco.edu.br/desenv.unochapeco.edu.br;charset=WE8ISO8859P1',
//        'username' => 'acad',
//        'password' => 'cataratas',
//        'driver_options' => array(
//            PDO::ATTR_CASE => PDO::CASE_LOWER,
//            PDO::ATTR_PERSISTENT => true,
//            PDO::ATTR_AUTOCOMMIT => 1,
//            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//        ),
//        'platform_options' => array('quote_identifiers' => false)
//    ),
    
);

