<?php

namespace Api;

use Zend\Mvc\MvcEvent;
use Api\Service\Auth;
use Api\PreProcessor\PreProcessor;
use Api\PostProcessor\PostProcessor;

/**
 * Classe de configuração do módulo
 * 
 * @category Api
 */
class Module
{
    /**
     * Executada no bootstrap do módulo
     * 
     * @param MvcEvent $e
     */
    public function onBootstrap($e)
    {
        /** @var \Zend\ModuleManager\ModuleManager $moduleManager */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
        /** @var \Zend\EventManager\SharedEventManager $sharedEvents */
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        //adiciona eventos ao módulo
        //pré e pós-processadores do controller Rest
        $sharedEvents->attach('Api\Controller\RestController', MvcEvent::EVENT_DISPATCH, array(new PostProcessor, 'process'), -100);
        $sharedEvents->attach('Api\Controller\RestController', MvcEvent::EVENT_DISPATCH, array(new PreProcessor, 'process'), 100);
        
        //pré e pós-processadores do controller Rpc
        $sharedEvents->attach('Api\Controller\RpcController', MvcEvent::EVENT_DISPATCH, array(new PostProcessor, 'process'), -100);
        $sharedEvents->attach('Api\Controller\RpcController', MvcEvent::EVENT_DISPATCH, array(new PreProcessor, 'process'), 100);
    }

    /**
     * Configuração do loader
     *  
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Carrega o arquivo de configuração
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Retorna a configuração do service manager do módulo
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Api\Service\Auth' => function($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    return new Service\Auth($dbAdapter);
                },
            ),
        );
    }


    /**
     * Faz o processamento dos erros da aplicação
     * @param MvcEvent $e
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    public function errorProcess(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();

        $formatter = $routeMatch->getParam('formatter', false);
        if ($formatter == false)
            return;
        /** @var \Zend\Di\Di $di */
        $di = $e->getApplication()->getServiceManager()->get('di');

        $eventParams = $e->getParams();

        /** @var array $configuration */
        $configuration = $e->getApplication()->getConfig();

        $vars = array();
        if (isset($eventParams['exception'])) {
            /** @var \Exception $exception */
            $exception = $eventParams['exception'];

            if ($configuration['errors']['show_exceptions']['message']) {
                $vars['error-code'] = $exception->getCode();
                $vars['error-message'] = $exception->getMessage();
            }
            
            if ($configuration['errors']['show_exceptions']['trace']) {
                $vars['error-trace'] = $exception->getTrace();
            }
        }

        if (empty($vars)) {
            $vars['error'] = 'Something went wrong';
        }

        /** @var PostProcessor\AbstractPostProcessor $postProcessor */
        
        $postProcessor = $di->get(
            $configuration['errors']['post_processor'],
            array('vars' => $vars, 'response' => $e->getResponse())
        );

        $postProcessor->process();

        // if (
        //     $eventParams['error'] === \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND ||
        //     $eventParams['error'] === \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH
        // ) {
        //     $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
        // } else {
        //     $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_500);
        // }

        switch ($eventParams['error']) {
        
            case \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND:
                $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
                break;

            case \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH:
                $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
                break;

            default:
                $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_500);
                break;
        }     

        $e->stopPropagation();

        return $postProcessor->getResponse();
    }
}
