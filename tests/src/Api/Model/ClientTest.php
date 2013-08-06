<?php
namespace Api\Model;

use Core\Test\ModelTestCase;
use Api\Model\Client;
use Zend\InputFilter\InputFilterInterface;

/**
 * Testes relacionados a entidade Client
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Model
 */
class ClientTest extends ModelTestCase
{
    
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
     */
    public function testInputFilterValid($if)
    {
        //verifica se os filtros dos campos estão criados
        $this->assertEquals(5, $if->count());
 
        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('name'));
        $this->assertTrue($if->has('login'));
        $this->assertTrue($if->has('password'));
        $this->assertTrue($if->has('status'));

    }
    
    /**
     * @expectedException Core\Model\EntityException
     */
    public function testInputFilterInvalido()
    {
        //testa se a validação do campo está funcionando
        $client = new Client();
        //login só pode ter 45 caracteres
        $client->login = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    public function testSaveClient()
    {
        //Teste de inserção de um client válido
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';     

        $this->em->persist($client);
        $this->em->flush();

        $this->assertEquals(1, $client->id);
    }

    public function testClientInsert()
    {
        //Teste de inserção de um client válido
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';

        $this->em->persist($client);
        $this->em->flush();

        $saved = $this->em->find('Api\Model\Client', 1);
        $this->assertEquals('steve', $saved->login);
        $this->assertNotNull($saved->created);
    }

    /**
     * @expectedException Doctrine\DBAL\DBALException
     */
    public function testInsertInvalido()
    {
        //Teste de inserção de um client inválido
        $client = new Client();
        $client->name = 'teste';

        $this->em->persist($client);
        $this->em->flush();
    }    

    public function testUpdate()
    {
        //Teste de atualização de um client válido
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

    public function testDelete()
    {
        //Teste de exclusão de um client
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