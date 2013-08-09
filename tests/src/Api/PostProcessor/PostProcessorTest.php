<?php
namespace Api\PostProcessor;

use Api\Test\ControllerTestCase;
use Api\Controller\RestController;
use Api\Model\Log;
use Api\Model\Token;
use Api\Model\Client;
use Api\PostProcessor\PostProcessor;

/**
 * Post-processor related tests
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Controller
 */
class PostProcessorTest extends ControllerTestCase
{
    protected $controllerFQDN = 'Application\Controller\IndexController';
    protected $controllerRoute = 'rpc';

    /**
     * Tests setup
     * @return void
     */
    public function setup()
    {   
        parent::setup();
        $this->routeMatch->setParam('module', 'api');
        $this->routeMatch->setMatchedRouteName('restful');
    }    
    
    /**
     * Tests if the user doesn't send the formatter field
     * @return void
     */
    public function testProcessWithoutFormatter()
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
     * Tests if the user send the formatter field = JSON
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
     * Tests if the user send the formatter field = XML
     * @return void
     */
    public function testProcessFormatterXml()
    {
        $this->markTestSkipped(
          'To be finished'
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
     * Creates a new log registry to be used in the tests
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
     * @return Api\Model\User   A new user registry
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