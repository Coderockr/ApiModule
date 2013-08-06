<?php
namespace Api\Controller;

use Core\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Log;
use Api\Model\Token;
use Api\Model\Client;

/**
 * RestController functional tests
 * 
 * @category Api
 * @package Controller
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Controller
 */
class RestControllerTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Api\Controller\RestController';
    protected $controllerRoute = 'restful';

    //faz o setup dos testes
    public function setup()
    {
        parent::setup();
        //configura a rota e as opções
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setParam('formatter', 'json');
    }    

    public function testGetList()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logA->resource, $result[0]->resource);
        $this->assertEquals(2, count($result));
    }

    public function testGetListExtraParametersFields()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        //fields
        $this->request->getQuery()->set('fields', 'resource');
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logA->resource, $result[0]->resource);
        $this->assertEquals(2, count($result));
        $this->assertFalse(isset($result->token));

    }

    public function testGetListExtraParametersLimit()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        //limit
        $this->request->getQuery()->set('limit', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));
    }

    public function testGetListExtraParametersOffset()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $this->request->getQuery()->set('limit', 1);
        $this->request->getQuery()->set('offset', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));
        $this->assertEquals($logB->resource, $result[0]->resource);
    }

    public function testGetListExtraParametersWhere()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $logB = $this->buildLog('widget');

        $this->request->getQuery()->set('resource', 'widget');
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));
        $this->assertEquals($logB->resource, $result[0]->resource);
    }

    public function testGetListExtraParametersMultiple()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();
        $widgets = array();
        for($i=0; $i<10; $i++) {
            $widgets[$i] = $this->buildLog('widget');
        }

        $this->request->getQuery()->set('resource', 'widget');
        $this->request->getQuery()->set('limit', 5);
        $this->request->getQuery()->set('limit', 5);
        $this->request->getQuery()->set('fields', 'resource');

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(5, count($result));
        $this->assertEquals($widgets[5]->resource, $result[0]->resource);
        $this->assertFalse(isset($result->token));
    }

    public function testGetListNotFound()
    {
        //testa a ação que retorna todos os registro da tabela
        $logA = $this->buildLog();

        $this->request->getQuery()->set('resource', 'widget');
        
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }


    public function testGet()
    {
        //testa a ação que retorna um registro específico
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logA->resource, $result->resource);
        $this->assertEquals(1, $result->id);
    }

    public function testGetNotFound()
    {

        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }


    public function testCreate()
    {
        //testa a ação que faz a criação de um registro
        $this->request->setMethod('post');
        $this->request->getPost()->set('token_id', 1);
        $this->request->getPost()->set('resource', '*');

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        
        $this->assertEquals('*', $result->resource);
        $this->assertEquals(1, $result->id);
    }    

    public function testUpdateRest()
    {
        //testa a ação de atualização
        $log = $this->buildLog();
        $this->request->setMethod('put');
        $this->request->setContent('resource=api.log');

        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('api.log', $result->resource);
        $this->assertEquals(1, $result->id);

    }    

    public function testDelete()
    {
        //testa a ação de exclusão
        $log = $this->buildLog();
        $this->request->setMethod('delete');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(1, $result);
        $logs = $this->em->getRepository('Api\Model\Log')->findAll();
        $this->assertEquals(0, count($logs));
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
}