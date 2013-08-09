<?php
namespace Api\Controller;

use Api\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Client;
use Api\Model\Permission;

/**
 * RpctController related tests
 * 
 * @category Api
 * @package Controller
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Controller
 */
class RpcControllerTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Api\Controller\RpcController';
    protected $controllerRoute = 'rpc';

    /**
    * Does the tests setup
    */  
    public function setup()
    {
        parent::setup();
        // rote and options configuration
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setParam('service', 'authenticate');
        $this->routeMatch->setParam('formatter', 'json');
        $this->routeMatch->setParam('action', 'index');
    }    

    /**
     * Tests the execution of a service
     */
    public function testIndexRpc()
    {
        $client = $this->addClient();
        $client->password = '0febfd3464e60216072a60ea095b2ceb6c0d3e87';
        $this->em->persist($client);
        $this->em->flush();        
        
        $permission = $this->addPermission($client, '*');
        $this->em->persist($permission);
        $this->em->flush();    

        $this->request->getPost()->set('login', $client->login);
        $this->request->getPost()->set('password', 'teste');

        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, count($result));


    }
    
    /**
     * Creates a new client to be used in the tests
     * @return Api\Model\Client    A new client
     */
    private function addClient()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve';
        $client->password = 'teste';
        $client->status = 'ACTIVE';
        
        return $client;
    }

    /**
     * Creates a new registry to be used in the tests
     * @return Api\Model\Permission   A new permission registry
     */
    private function addPermission($client, $resource)
    {
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = $resource;
        return $permission;
    }    

}