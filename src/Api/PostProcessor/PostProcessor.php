<?php
namespace Api\PostProcessor;

use Zend\Mvc\MvcEvent;
use Api\Service\Auth;

/**
 * Class responsable for post-processing Api requisitions
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class PostProcessor
{
    /**
     * Executed in post-processing, after any action.
     * Verifies the requested format and generates the correspondent response (json ou xml)
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

        // Verifies the availability of the chosen entity/service
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

        /**
        * @var \Zend\Di\Di $di 
        */
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

            /** 
            * @var PostProcessor\AbstractPostProcessor $postProcessor 
            */
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