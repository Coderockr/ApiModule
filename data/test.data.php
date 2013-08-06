<?php

return array(
    'api_cliente' => array(
        'COLUMNS' => array(
            'id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'nome' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 100,
                'is_nullable' => false,
            ),
            'login' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 45,
                'is_nullable' => false,
            ),
            'senha' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 255,
                'is_nullable' => false,
            ),
            'status' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 10,
                'is_nullable' => true,
            ),
            'created' => array(
                'data_type' => 'DATE',
                'is_nullable' => false,
            ),
        ),
        'PRIMARY_KEY' => array('COLUMNS' => 'id', 'AUTO_INCREMENT' => true),
    ),
    'api_permissao' => array(
        'COLUMNS' => array(
            'id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'cliente_id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'recurso' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 100,
                'is_nullable' => false,
            ),
            'created' => array(
                'data_type' => 'DATE',
                'is_nullable' => false,
            ),    
        ),
        'PRIMARY_KEY' => array('COLUMNS' => 'id', 'AUTO_INCREMENT' => true),
//        'FOREING_KEY' => array(
//            array('COLUMNS' => 'cliente_id', 'CONSTRAINT_REFERENCES' => array('TABLE' => 'cliente', 'COLUMNS' => 'id')),
//        ),
    ),
    'api_token' => array(
        'COLUMNS' => array(
            'id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'cliente_id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'token' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 255,
                'is_nullable' => false,
            ),
            'ip' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 20,
                'is_nullable' => false,
            ),
            'status' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 10,
                'is_nullable' => true,
            ),
            'created' => array(
                'data_type' => 'DATE',
                'is_nullable' => false,
            ),
        ),
        'PRIMARY_KEY' => array('COLUMNS' => 'id', 'AUTO_INCREMENT' => true),
//        'FOREING_KEY' => array(
//            array('COLUMNS' => 'cliente_id', 'CONSTRAINT_REFERENCES' => array('TABLE' => 'cliente', 'COLUMNS' => 'id')),
//        ),
    ),
    'api_log' => array(
        'COLUMNS' => array(
            'id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'token_id' => array(
                'data_type' => 'NUMBER',
                'numeric_precision' => 10,
                'numeric_scale' => 0,
                'is_nullable' => false,
            ),
            'recurso' => array(
                'data_type' => 'VARCHAR',
                'character_maximum_length' => 100,
                'is_nullable' => false,
            ),
            'created' => array(
                'data_type' => 'DATE',
                'is_nullable' => false,
            ),
        ),
        'PRIMARY_KEY' => array('COLUMNS' => 'id', 'AUTO_INCREMENT' => true),
    ),
);