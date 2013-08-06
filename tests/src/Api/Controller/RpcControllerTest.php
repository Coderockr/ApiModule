<?php
namespace Api\Controller;

use Core\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Client;
use Api\Model\Permission;

/**
 * Testes relacionados ao RpcController
 * 
 * @category Api
 * @package Controller
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Controller
 */
class RpcControllerTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Api\Controller\RpcController';
    protected $controllerRoute = 'rpc';

    //faz o setup dos testes
    public function setup()
    {
        parent::setup();
        //configura a rota e as opções
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setParam('service', 'authenticate');
        $this->routeMatch->setParam('formatter', 'json');
        $this->routeMatch->setParam('action', 'index');
    }    


    public function testIndexRpc()
    {
        //testa a execução de um serviço
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
     * Cria um novo registro para ser usado nos testes
     * @return Api\Model\Client    Um novo registro de Client
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
     * Cria um novo registro para ser usado nos testes
     * @return Api\Model\Permissao    Um novo registro de Permissao
     */
    private function addPermission($client, $resource)
    {
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = $resource;
        return $permission;
    }    

}