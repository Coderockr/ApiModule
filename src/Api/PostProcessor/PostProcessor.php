<?php
namespace Api\PostProcessor;

use Zend\Mvc\MvcEvent;
use Api\Service\Auth;

/**
 * Responsável por fazer o pós-processamento das requisições da APi
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class PostProcessor
{
    /**
     * Executado no pós-processamento, após qualquer action
     * Verifica o formato requisitado (json ou xml) e gera a saída correspondente
     * 
     * @param MvcEvent $e
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    public function process(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $formatter = $routeMatch->getParam('formatter', false);
        $routeName = $routeMatch->getMatchedRouteName();
        $module = $routeMatch->getParam('module', false);

        //verifica se a entidade ou o service sendo invocados estão disponíveis
        switch ($routeName) {
            case 'restful':
                $request = $routeMatch->getParam('entity', false);
                break;
            case 'rpc':
                $request = $routeMatch->getParam('service', false);
                break;
        }

        $moduleConfig = null;
        switch ($routeName) {
            case 'restful':
                $moduleConfig = include __DIR__ . '/../../../../' . ucfirst($module) . '/config/entities.config.php';
                break;
            case 'rpc':
                $moduleConfig = include __DIR__ . '/../../../../' . ucfirst($module) . '/config/services.config.php';
                break;
        }

        /** @var \Zend\Di\Di $di */
        $di = $e->getTarget()->getServiceLocator()->get('di');

        if ($formatter !== false) {
            if ($e->getResult() instanceof \Zend\View\Model\ViewModel) {
                $vars = null;
                if (is_array($e->getResult()->getVariables())) {
                    $vars = $e->getResult()->getVariables();
                } 
            } else {
                $vars = $e->getResult();
            }

            /** @var PostProcessor\AbstractPostProcessor $postProcessor */
            $postProcessor = $di->get($formatter . '-pp', array(
                'response' => $e->getResponse(),
                'vars' => $vars,
            ));
            $postProcessor->process($moduleConfig[$request]['class']);
            return $postProcessor->getResponse();
        }

        return null;
    }
}