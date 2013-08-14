<?php
namespace ApiModule\Db;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

/**
 * Classe responsável por criar uma nova instância do Zend\Db\Adapter\Adapter
 * de acordo com a configuração da aplicação ou do módulo
 * 
 * @category ApiModule
 * @package Db
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class AdapterServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $env = getenv('ENV');
        if ( $env == 'testing' || $env == 'jenkins') {
            $config = include getenv('PROJECT_ROOT') . '/config/test.config.php';
            return new Adapter($config[$env]['db']);
        }

        $config = $serviceLocator->get('Configuration');
        $mvcEvent = $serviceLocator->get('Application')->getMvcEvent();
        if ($mvcEvent) {
            $routeMatch = $mvcEvent->getRouteMatch();
            $moduleName = $routeMatch->getParam('module');
            $moduleConfig = include getenv('PROJECT_ROOT') . '/module/' . ucfirst($moduleName) . '/config/module.config.php';
            //se o módulo possui configuração específica de banco de dados
            //esa configuração é usada
            if (isset($moduleConfig['db'])) 
                $config['db'] = $moduleConfig['db'];
        }
        return new Adapter($config['db']);
    }
}
