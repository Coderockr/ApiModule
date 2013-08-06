<?php
namespace Api\PreProcessor;


use Zend\Mvc\MvcEvent;
use Api\Service\Auth;
use Core\Service\ParameterFactory;

/**
 * Responsável por fazer o pré-processamento das requisições da APi
 * 
 * @category Api
 * @package PreProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class PreProcessor 
{
    /**
     * Executado no pré-processamento, antes de qualquer action
     * Verifica se o usuário tem permissão de acessar o recurso
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

        //verifica se a entidade ou o service sendo invocados estão disponíveis
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
                throw new \Exception("Token requirido");       
            }
            $this->checkAuthorization($auth, $token, $module . '.' . $request);
            return true;
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

        if (! $moduleConfig) {
            throw new \Exception("Caminho inválido");
        }

        if (! isset($moduleConfig[$request])) {
            throw new \Exception("Não permitido");
        }
        //acesso requer um token válido e permissões de acesso
        if ($moduleConfig[$request]['authorization'] == 1) {
            $token = $e->getRequest()->getHeaders('Authorization');
            if (!$token) {
                throw new \Exception("Token requirido");       
            }
            
            $this->checkAuthorization($auth, $token, $module . '.' . $request);
        }
        return true;
    }

    /**
     * Executa o teste da autorização
     * @param  Auth $auth      Serviço de auth
     * @param  Header $token   Token enviado na requisição
     * @param  string $request Serviço sendo requisitado
     * @return boolean
     */
    private function checkAuthorization($auth, $token, $request)
    {
        $parameters = ParameterFactory::factory(
            array('token' => $token->value, 'resource' => $request)
        );
        switch ($auth->authorize($parameters)) {
            case Auth::INVALID:
                throw new \Exception("Token inválido");
                break;
            case Auth::EXPIRED:
                throw new \Exception("Token expirado");
                break;
            case Auth::DENIED:
                throw new \Exception("Acesso negado");
                break;
        }
        return true;
    }

    /**
     * Verifica se a api está sendo acessada de um ambiente de testes 
     * e configura o ambiente
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