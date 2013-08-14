<?php
/**
 * Returns the paths that are going to be loaded
 * 
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

return array(
	'ApiModule\Module'                                  => __DIR__ . '/Module.php',
	'ApiModule\PostProcessor\AbstractPostProcessor'     => __DIR__ . '/src/ApiModule/PostProcessor/AbstractPostProcessor.php',
	'ApiModule\PostProcessor\Json'                      => __DIR__ . '/src/ApiModule/PostProcessor/Json.php',
	'ApiModule\PostProcessor\Image'                     => __DIR__ . '/src/ApiModule/PostProcessor/Image.php',

	'ApiModule\Controller\RestController' => __DIR__ . '/src/ApiModule/Controller/RestController.php',
	'ApiModule\Controller\RpcController' => __DIR__ . '/src/ApiModule/Controller/RpcController.php',
);
