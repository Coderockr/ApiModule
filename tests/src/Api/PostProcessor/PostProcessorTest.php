<?php
namespace Api\PostProcessor;

use Core\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Log;
use Api\Model\Token;
use Api\Model\Client;
use Api\PostProcessor\PostProcessor;

/**
 * Testes relacionados ao pós-processaor
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Controller
 */
class PostProcessorTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Application\Controller\IndexController';
    protected $controllerRoute = 'rpc';


    /**
     * Setup dos testes
     * @return void
     */
    public function setup()
    {   
        parent::setup();
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setMatchedRouteName('restful');
    }    
    
    /**
     * Testa caso o usuário não envie o campo formatter
     * @return void
     */
    public function testProcessSemFormatter()
    {
        $this->routeMatch->setParam('entity', 'log');
        
        $logA = $this->buildLog();
        $logB = $this->buildLog();
        $result = $this->controller->dispatch($this->request) ;
        $response = $this->controller->getResponse();
        $pp = new PostProcessor;
        $result = $pp->process($this->event);
        $this->assertNull($result);

    }

    /**
     * Testa caso o usuário envie o campo formatter=json
     * @return void
     */
    public function testProcessFormatterJson()
    {
        $pp = new PostProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setParam('formatter', 'json');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
        $contentType = $result->getHeaders()->get('Content-Type')->value;
        $content = json_decode($result->getContent());
        $this->assertEquals(1, count($content));
        $this->assertEquals('application/json', $contentType);
    }

    /**
     * Testa caso o usuário envie o campo formatter=xml
     * @return void
     */
    public function testProcessFormatterXml()
    {
        $this->markTestSkipped(
          'To be fineshed'
        );
        
        $pp = new PostProcessor;
        $logA = $this->buildLog();
        $logB = $this->buildLog();

        $this->routeMatch->setParam('entity', 'log');
        $this->routeMatch->setParam('formatter', 'xml');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $result = $pp->process($this->event);
        $contentType = $result->getHeaders()->get('Content-Type')->value;
        $this->assertEquals('application/xml', $contentType);
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