<?php
namespace Api\Model;

use Api\Test\ModelTestCase;
use Api\Model\Client;
use Zend\InputFilter\InputFilterInterface;

/**
 * Testes relacionados a entidade Client
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Model
 */
class ClientTest extends ModelTestCase
{
    
    /**
     * Tests the existance of filters
     */
    public function testGetInputFilter()
    {
        //testa a existência dos filtros
        $client = new Client();
        $if = $client->getInputFilter();
 
        $this->assertInstanceOf("Zend\InputFilter\InputFilter", $if);
        return $if;
    }
 
    /**
     * @depends testGetInputFilter
     * Verifies if the field's filters are created
     */
    public function testInputFilterValid($if)
    {
        $this->assertEquals(5, $if->count());
 
        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('name'));
        $this->assertTrue($if->has('login'));
        $this->assertTrue($if->has('password'));
        $this->assertTrue($if->has('status'));

    }
    
    /**
     * @expectedException Api\Model\EntityException
     * Tests if the field validation is working
     */
    public function testInvalidInputFilter()
    {
        $client = new Client();
        //Login can only have 45 characters
        $client->login = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    /**
    * Tests the insertion of a new client
    */
    public function testSaveClient()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';     

        $this->em->persist($client);
        $this->em->flush();

        $this->assertEquals(1, $client->id);

        $saved = $this->em->find('Api\Model\Client', 1);
        $this->assertEquals($client->login, $saved->login);
        $this->assertNotNull($saved->created);
    }

    /**
     * @expectedException Doctrine\DBAL\DBALException
     * Tests the insertion of a invalid client
     */
    public function testInvalidInsert()
    {
        $client = new Client();
        $client->name = 'teste';

        $this->em->persist($client);
        $this->em->flush();
    }    
    
    /**
     * Tests the update of a valid client
     */
    public function testUpdate()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';

        $this->em->persist($client);
        $this->em->flush();
        $id = $client->id;

        $this->assertEquals(1, $id);

        $saved = $this->em->find('Api\Model\Client', $id);
        $this->assertEquals('steve', $client->login);

        $client->login = 'sjobs';
        $this->em->persist($client);
        $this->em->flush();

        $saved = $this->em->find('Api\Model\Client', $id);
        $this->assertEquals('sjobs', $client->login);

    }

    /**
     * Tests a client deletion
     */
    public function testDelete()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';

        $this->em->persist($client);
        $this->em->flush();

        $saved = $this->em->find('Api\Model\Client', $client->id);
        $id = $saved->id;
        $this->em->remove($saved);
        $this->em->flush();

        $saved = $this->em->find('Api\Model\Client', $id);
        $this->assertNull($saved);

    }

}