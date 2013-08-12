<?php
namespace Api\PreProcessor;


use Zend\Mvc\MvcEvent;
use Api\Service\Auth;
use Api\Service\ParameterFactory;

/**
 * Class responsable for pre-processing API requisitions
 * 
 * @category Api
 * @package PreProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class PreProcessor 
{
    /**
     * Executed in pre-processing, before any action.
     * Verifies the user's resource access permission
     * 
     * @param MvcEvent $e
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    public function process(MvcEvent $e)
    {
        $this->configureEnvironment($e);
        $auth = $e->getApplication()->getServiceManager()->get('Api\Service\Auth');

        $routeMatch = $e->getRouteMatch();
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

        $cache = $e->getApplication()->getServiceManager()->get('Cache');
        $cached = $cache->getItem('api');
        
        if ($cached) {
            $token = $e->getRequest()->getHeaders('Authorization');
            if (!$token) {
                throw new \Exception("Token required");       
            }
            $this->checkAuthorization($auth, $token, $module . '.' . $request);
            return true;
        }

        $moduleConfig = null;
        switch ($routeName) {
            case 'restful':
                $moduleConfig = include $_SERVER['DOCUMENT_ROOT'] . '/../module/' . ucfirst($module) . '/config/entities.config.php';
                break;
            case 'rpc':
                $moduleConfig = include $_SERVER['DOCUMENT_ROOT'] . '/../module/' . ucfirst($module) . '/config/services.config.php';
                break;
        }

        if (! $moduleConfig) {
            throw new \Exception("Invalid path");
        }

        if (! isset($moduleConfig[$request])) {
            throw new \Exception("Not permitted");
        }
        //acesso requer um token válido e permissões de acesso
        if ($moduleConfig[$request]['authorization'] == 1) {
            $token = $e->getRequest()->getHeaders('Authorization');
            if (!$token) {
                throw new \Exception("Token required");       
            }
            
            $this->checkAuthorization($auth, $token, $module . '.' . $request);
        }
        return true;
    }

    /**
     * Performs the authorization test
     * @param  Auth $auth      Auth service
     * @param  Header $token   Requisition token
     * @param  string $request Requested service
     * @return boolean
     */
    private function checkAuthorization($auth, $token, $request)
    {
        $parameters = ParameterFactory::factory(
            array('token' => $token->value, 'resource' => $request)
        );
        switch ($auth->authorize($parameters)) {
            case Auth::INVALID:
                throw new \Exception("Invalid token");
                break;
            case Auth::EXPIRED:
                throw new \Exception("Expired token");
                break;
            case Auth::DENIED:
                throw new \Exception("Denied access");
                break;
        }
        return true;
    }

    /**
     * Verifies if the API is being accessed by a tests environment, 
     * and configures the environment accordingly
     * @param  MvcEvent $e Evento
     * @return void
     */
    private function configureEnvironment(MvcEvent $e)
    {
        if ( !method_exists($e->getRequest(), 'getHeaders')) {
            return;
        }

        $env = $e->getRequest()->getHeaders('Environment');
        if ($env) {
            switch ($env->getFieldValue()) {
                case 'testing':
                    putenv("ENV=testing");
                    break;
                case 'jenkins':
                    putenv("ENV=jenkins");
                    break;
            }
        }
        return;
    }
}