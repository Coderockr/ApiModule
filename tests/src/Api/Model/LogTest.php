<?php
namespace Api\Model;

use Core\Test\ModelTestCase;
use Api\Model\Log;
use Zend\InputFilter\InputFilterInterface;

/**
 * Testes relacionados a entidade Log
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Model
 */
class LogTest extends ModelTestCase
{
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
     * @expectedException Core\Model\EntityException
     */
    public function testInputFilterInvalido()
    {
        $log = new Log();
        //recurso só pode ter 100 caracteres
        $log->resource = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    /**
     * Teste de inserção de um log válido
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
    public function testInsertInvalido()
    {
        $log = new Log();
        $log->recurso = 'teste';

        $this->em->persist($log);
        $this->em->flush();
    }    

    private function addToken() 
    {
        $client = $this->getClient();
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

    private function getClient()
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