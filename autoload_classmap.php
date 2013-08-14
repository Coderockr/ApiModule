<?php
/**
 * Returns the paths that are going to be loaded
 * 
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

return array(
	'Api\Module'                                  => __DIR__ . '/Module.php',
	'Api\PostProcessor\AbstractPostProcessor'     => __DIR__ . '/src/Api/PostProcessor/AbstractPostProcessor.php',
	'Api\PostProcessor\Json'                      => __DIR__ . '/src/Api/PostProcessor/Json.php',
	'Api\PostProcessor\Image'                     => __DIR__ . '/src/Api/PostProcessor/Image.php',

	'Api\Controller\RestController' => __DIR__ . '/src/Api/Controller/RestController.php',
	'Api\Controller\RpcController' => __DIR__ . '/src/Api/Controller/RpcController.php',
);
