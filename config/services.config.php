<?php
//serviços que estão disponíveis via RPC
return array(
    'authenticate' => array(
        'class' => 'Api\Service\Auth',
        'method' => 'authenticate',
        'authorization' => 0
    ),
);