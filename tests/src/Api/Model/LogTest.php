<?php
namespace Api\Model;

use Api\Test\ModelTestCase;
use Api\Model\Log;
use Zend\InputFilter\InputFilterInterface;

/**
 * Log entity related tests
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Model
 */
class LogTest extends ModelTestCase
{
    
    /**
     * Tests the existance of filters
     */
    public function testGetInputFilter()
    {
        $log = new Log();
        $if = $log->getInputFilter();
 
        $this->assertInstanceOf("Zend\InputFilter\InputFilter", $if);
        return $if;
    }
 
    /**
     * @depends testGetInputFilter
     */
    public function testInputFilterValid($if)
    {
        $this->assertEquals(1, $if->count());
 
        $this->assertTrue($if->has('resource'));
    }
    
    /**
     * @expectedException Api\Model\EntityException
     */
    public function testInvalidInputFilter()
    {
        $log = new Log();
        //Resource can only have 100 characters
        $log->resource = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    /**
     * Tests a valid log insertion
     */
    public function testInsert()
    {
        $log = new Log();
        $log->token = $this->addToken();
        $log->resource = ' * <br> ';

        $this->em->persist($log);
        $this->em->flush();

        $this->assertEquals(1, $log->id);
    }

    /**
     * @expectedException Doctrine\DBAL\DBALException
     */
    public function testInvalidInsert()
    {
        $log = new Log();
        $log->recurso = 'teste';

        $this->em->persist($log);
        $this->em->flush();
    }    

    /**
     * Creates a new token to be used in the tests
     * @return Api\Model\Token   A new token registry
     */
    private function addToken() 
    {
        $client = $this->addClient();
        $access_token = '1111';
        $token = new Token();
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1';
        $token->status = 1;

        $this->em->persist($token);
        $this->em->flush();

        return $token;
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
        $client->password = 'teste';
        $client->status = 'ATIVO';
        
        $this->em->persist($client);
        $this->em->flush();
        
        return $client;
    }

}