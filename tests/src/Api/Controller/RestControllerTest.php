<?php
namespace Api\Controller;

use Api\Test\ControllerTestCase;
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
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Controller
 */
class RestControllerTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Api\Controller\RestController';
    protected $controllerRoute = 'restful';

    /**
    * Does the tests setup
    */  
    public function setup()
    {
        parent::setup();
        // rote and options configuration
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setParam('formatter', 'json');
    }    

    /**
    * Tests the action that returns the table registries
    */  
    public function testGetList()
    {
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logA->resource, $result[0]->resource);
        $this->assertEquals(2, count($result));
    }

    /**
    * Tests the action that returns the table registries
    */  
    public function testGetListExtraParametersFields()
    {
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

    /**
    * Tests the action that returns the table registries
    */  
    public function testGetListExtraParametersLimit()
    {
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        //limit
        $this->request->getQuery()->set('limit', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));
    }

    /**
    * Tests the action that returns the table registries
    */  
    public function testGetListExtraParametersOffset()
    {
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

    /**
    * Tests the action that returns the table registries
    */   
    public function testGetListExtraParametersWhere()
    {
        $logA = $this->buildLog();
        $logB = $this->buildLog('widget');

        $this->request->getQuery()->set('resource', 'widget');
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));
        $this->assertEquals($logB->resource, $result[0]->resource);
    }
 
    /**
    * Tests the action that returns the table registries
    */   
    public function testGetListExtraParametersMultiple()
    {
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

    /**
    * Tests the action that returns the table registries
    */   
    public function testGetListNotFound()
    {
        $logA = $this->buildLog();

        $this->request->getQuery()->set('resource', 'widget');
        
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
    * Tests the action that returns the table registries
    */   
    public function testGet()
    {
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logA->resource, $result->resource);
        $this->assertEquals(1, $result->id);
    }
    
    /**
    * Tests when action doesn't found what was being requested
    */  
    public function testGetNotFound()
    {

        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
    * Tests the action that creates a registry
    */   
    public function testCreate()
    {
        $this->request->setMethod('post');
        $this->request->getPost()->set('token_id', 1);
        $this->request->getPost()->set('resource', '*');

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        
        $this->assertEquals('*', $result->resource);
        $this->assertEquals(1, $result->id);
    }  

    /**
     * Tests the update action
     */
    public function testUpdateRest()
    {
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

    /**
     * Testes the delete action
     */ 
    public function testDelete()
    {
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
     * Creates a new registry to be used in the tests
     * @return Api\Model\Log    A new log registry
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
     * Creates a new user to be used in the tests
     * @return Api\Model\Client    A new client
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
}