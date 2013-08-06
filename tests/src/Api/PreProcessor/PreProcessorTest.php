<?php

namespace Api\PreProcessor;

use Core\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Log;
use Api\Model\Client;
use Api\Model\Permission;
use Api\Model\Token;
use Api\PreProcessor\PreProcessor;
use DateTime;
use Core\Service\ParameterFactory;

/**
 * Testes relacionados ao pré-processaor
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Controller
 */
class PreProcessorTest extends ControllerTestCase
{

    protected $controllerFQDN = 'Api\Controller\RestController';
    protected $controllerRoute = 'restful';

    /**
     * Setup dos testes
     * @return void
     */
    public function setup()
    {
        parent::setup();
        //configuração da rota
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setParam('formatter', 'json');
        $cache = $this->getService('Cache');
        $cache->removeItem('api');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Token requirido
     */
    public function testProcessSemToken()
    {
        //testa caso o usuário não envie um token
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $pp = new PreProcessor;
        $result = $pp->process($this->event);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Token inválido
     */
    public function testProcessTokenInvalido()
    {
        //testa caso o usuário envie um token inválido
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $this->request->getHeaders()->addHeaders(array('Authorization' => 'invalido'));
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $pp = new PreProcessor;
        $result = $pp->process($this->event);
    }

    /**
     * Testa com um token válido
     * @return void
     */
    public function testProcessTokenValido()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $client = $this->addClient();
        $permission = $this->addPermission($client, '*');

        $token = $this->geraToken($client);
        $this->request->getHeaders()->addHeaders(array('Authorization' => $token));

        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
        $this->assertTrue($result);

        $this->routeMatch->setParam('service', 'authenticate');
        $this->routeMatch->setMatchedRouteName('rpc');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Acesso negado
     */
    public function testProcessAcessoNaoPermitido()
    {
        //testa caso o usuário tente acessar um serviço/entidade que não tem permissão
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.authenticate');
        $token = $this->geraToken($client);
        $this->request->getHeaders()->addHeaders(array('Authorization' =>$token));
        $cache = $this->getService('Cache');
        $cache->removeItem('api');
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
    }

    public function testProcessAcessoPermitido()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.log');
        $token = $this->geraToken($client);
        $this->request->getHeaders()->addHeaders(array('Authorization' =>$token));
        $cache = $this->getService('Cache');
        $cache->removeItem('api');
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
        $this->assertTrue($result);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Não permitido
     */
    public function testProcessRecursoNaoExistente()
    {
        //testa caso o usuário tente acessar um recurso que não existe
        //ou não está configurado para acesso remoto
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.log');

        $token = $this->geraToken($client);
        $this->request->getHeaders()->addHeaders(array('Authorization' => $token));

        $this->routeMatch->setParam('entity', 'nao_existe');
        $this->routeMatch->setMatchedRouteName('restful');
        $result = $pp->process($this->event);
        $this->assertNull($result);
    }

    /**
     * Cria um novo registro para ser usado nos testes
     * @return Api\Model\Log    Um novo registro de Log
     */
    private function buildLog($resource = '*')
    {
        $client = $this->addClient();
        $token = new Token();
        $token->client = $client;
        $token->token = 'access_token';
        $token->ip = '127.0.0.1';
        $token->status = 'VALID';
    
        $log = new Log();
        $log->token = $token;
        $log->resource = $resource;
        $this->em->persist($log);
        $this->em->flush();
        
        return $log;
    }

    /**
     * Gera um novo token para ser usado nos testes
     * @param  Api\Model\Client $client O client que está acessando
     * @return string    Um novo token
     */
    private function geraToken($client)
    {
        $parameters = ParameterFactory::factory(
                        array(
                            'login' => $client->login,
                            'password' => 'teste'
                        )
        );

        //garante que o adapter usado é o dos testes
        $dbAdapter = $this->serviceManager->get('dbAdapter');
        $authService = $this->getService('Api\Service\Auth');
        $result = $authService->authenticate($parameters);
        return $result['access_token'];
    }

    /**
     * Gera um novo registro para os testes
     * @return Api\Model\Client Um novo client
     */
    private function addClient()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve';
        $client->password = '0febfd3464e60216072a60ea095b2ceb6c0d3e87';
        $client->status = 'ACTIVE';
        
        $this->em->persist($client);
        $this->em->flush();

        return $client;
    } 

    /**
     * Adiciona um novo registro para os testes
     * @param Api\Model\Client $client O client acessando o recurso
     * @param string $recurso O recurso sendo acessado
     * @return Api\Model\Permission   Um novo registro de Permission
     */
    private function addPermission($client, $resource)
    {
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = $resource;

        return $permission;
    }    
}