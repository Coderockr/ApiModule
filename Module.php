<?php

namespace Api;

use Zend\Mvc\MvcEvent;
use Api\Service\Auth;
use Api\PreProcessor\PreProcessor;
use Api\PostProcessor\PostProcessor;

/**
 * Module configuration class
 * 
 * @category Api
 */
class Module
{
    /**
     * Executed on module bootsrap
     * 
     * @param MvcEvent $e
     */
    public function onBootstrap($e)
    {
        /** @var \Zend\ModuleManager\ModuleManager $moduleManager */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
        /** @var \Zend\EventManager\SharedEventManager $sharedEvents */
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        //Adds module events
        //Controller REST pre and post-processors 
        $sharedEvents->attach('Api\Controller\RestController', MvcEvent::EVENT_DISPATCH, array(new PostProcessor, 'process'), -100);
        $sharedEvents->attach('Api\Controller\RestController', MvcEvent::EVENT_DISPATCH, array(new PreProcessor, 'process'), 100);
        
        //Controller RPC pre and post-processors 
        $sharedEvents->attach('Api\Controller\RpcController', MvcEvent::EVENT_DISPATCH, array(new PostProcessor, 'process'), -100);
        $sharedEvents->attach('Api\Controller\RpcController', MvcEvent::EVENT_DISPATCH, array(new PreProcessor, 'process'), 100);

        //evento de erros da aplicação
        $sharedEvents->attach(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'errorProcess'),
            999
        );
    }

    /**
     * Loader configuration
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
     * Loads the configuration file
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Returns the module service manager configuration
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
     * Process the application errors
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

        //api error
        if (isset($vars['error-code']) && $vars['error-code'] == 404) {
            $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_404);
            $e->stopPropagation();
            return $postProcessor->getResponse();
        }
        //framework error
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
