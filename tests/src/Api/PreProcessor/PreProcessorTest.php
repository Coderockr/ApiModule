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
 * Pre-processor related tests
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Controller
 */
class PreProcessorTest extends ControllerTestCase
{

    protected $controllerFQDN = 'Api\Controller\RestController';
    protected $controllerRoute = 'restful';

    /**
     * Tests setup
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
     * Tests for when the user doesn't send token
     */
    public function testProcessWithoutToken()
    {
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
     * Tests for when the user sends an invalid token
     */
    public function testProcessInvalidToken()
    {
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
     * Tests for when the user sends an valid token
     * @return void
     */
    public function testProcessValidToken()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $client = $this->addClient();
        $permission = $this->addPermission($client, '*');

        $token = $this->addToken($client);
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
     * Tests for when the user tries to access an unauthorized service/entity
     */
    public function testProcessNonPermittedAcess()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.authenticate');
        $token = $this->addToken($client);
        $this->request->getHeaders()->addHeaders(array('Authorization' =>$token));
        $cache = $this->getService('Cache');
        $cache->removeItem('api');
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setMatchedRouteName('restful');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
    }
    /**
     * Tests for when the user tries to access an authorized service/entity
     */
    public function testProcessPermittedAcess()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.log');
        $token = $this->addToken($client);
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
     * @expectedExceptionMessage Not permitted
     * Tests if the user tries to access a non-existant resource
     * or not configured for remote access
     */
    public function testProcessNonExistantResource()
    {
        $pp = new PreProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $client = $this->addClient();
        $permission = $this->addPermission($client, 'api.log');

        $token = $this->addToken($client);
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
     * Creates a new token to be used in the tests
     * Gera um novo token para ser usado nos testes
     * @param  Api\Model\Client $client The accessing client
     * @return string    A new access token
     */
    private function addToken($client)
    {
        $parameters = ParameterFactory::factory(
                        array(
                            'login' => $client->login,
                            'password' => 'teste'
                        )
        );

        //Garanties that the used adapter is for tests
        $dbAdapter = $this->serviceManager->get('dbAdapter');
        $authService = $this->getService('Api\Service\Auth');
        $result = $authService->authenticate($parameters);
        return $result['access_token'];
    }

    /**
     * Generates a new client for tests
     * @return Api\Model\Client a new client
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
     * Creates a new permission registry for tests
     * @param Api\Model\Client $client The client accessing the resource
     * @param string $recurso The resource being accessed
     * @return Api\Model\Permission   A new permission entity for tests
     */
    private function addPermission($client, $resource)
    {
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = $resource;

        return $permission;
    }    
}