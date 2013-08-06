<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Core\Service\ParameterFactory;

/**
 * Classe responsável pelo acesso RPC dos serviços
 * 
 * @category Api
 * @package Controller
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class RpcController extends AbstractActionController
{

    /**
     * Performs the service
     * 
     * http://zf2.dev:8080/rpc/v1/album.getAllAlbums.json
     * http://zf2.dev:8080/rpc/v1/album.getAllAlbums.xml
     * 
     * @return array
     */
    public function indexAction()
    {
        $sm = $this->getServiceLocator();

        $module = $this->getEvent()->getRouteMatch()->getParam('module');
        $service = $this->getEvent()->getRouteMatch()->getParam('service');
                        
        $params = $this->getRequest()->getPost()->toArray();
        $parameterSet = ParameterFactory::factory($params);
        $callParameters = $this->getCallParameters($module, $service);
        $class = $callParameters['class'];

        $method = $callParameters['method'];

        $serviceObject = $sm->get($class);
        $result = $serviceObject->$method($parameterSet);

        return $result;
    }

    /**
     * Returns the parameters of the Service to be executed
     * @param  string $module  Service module
     * @param  string $service Service name
     * @return array           Parameters array
     */
    private function getCallParameters($module, $service)
    {
        $sm = $this->getServiceLocator();
        $cache = $sm->get('Cache');
        $cached = $cache->getItem('api');
        if ($cached) {
            foreach ($cached['resources'] as $r) {
                if ($r['action'] == $service) {
                    return array(
                        'class'  => $r['uri'],
                        'method' => $r['action']
                    );
                }
            }
        }
        $moduleConfig = include __DIR__ . 
                        '/../../../../' . 
                        ucfirst($module). 
                        '/config/services.config.php';
                        
        return array(
            'class'  => $moduleConfig[$service]['class'],
            'method' => $moduleConfig[$service]['method']
        );
    }
}
