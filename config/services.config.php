<?php
//RPC available services
return array(
    'authenticate' => array(
        'class' => 'ApiModule\Service\Auth',
        'method' => 'authenticate',
        'authorization' => 0
    ),
);