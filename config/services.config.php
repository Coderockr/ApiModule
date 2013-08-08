<?php
//RPC available services
return array(
    'authenticate' => array(
        'class' => 'Api\Service\Auth',
        'method' => 'authenticate',
        'authorization' => 0
    ),
);