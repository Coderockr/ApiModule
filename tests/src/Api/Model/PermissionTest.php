<?php
namespace Api\Model;

use Core\Test\ModelTestCase;
use Api\Model\Cliente;
use Api\Model\Permission;
use Zend\InputFilter\InputFilterInterface;

/**
 * @group Model
 */
class PermissionTest extends ModelTestCase
{
    public function testGetInputFilter()
    {
        $permission = new Permission();
        $if = $permission->getInputFilter();
 
        $this->assertInstanceOf("Zend\InputFilter\InputFilter", $if);
        return $if;
    }
 
    /**
     * @depends testGetInputFilter
     */
    public function testInputFilterValid($if)
    {
        $this->assertEquals(2, $if->count());
 
        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('resource'));
    }
    

    /**
     * @expectedException Core\Model\EntityException
     */
    public function testInputFilterInvalido()
    {
        $permission = new Permission();
        //recurso só pode ter 100 caracteres
        $permission->resource = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    /**
     * Teste de inserção de uma permission válida
     */
    public function testInsert()
    {
        $client = $this->addCliente();
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = ' * <br> ';

        $this->em->persist($permission);
        $this->em->flush();

        $this->assertEquals(1, $permission->id);
        $this->assertNotNull($permission->created);
    }


    public function testUpdate()
    {
        $client = $this->addCliente();
        $permission = new Permission();
        $permission->cliente = $client;
        $permission->resource = ' * <br> ';

        $this->em->persist($permission);
        $this->em->flush();
        $id = $permission->id;

        $this->assertEquals(1, $id);

        $permission = $this->em->find('Api\Model\Permission', $id);
        $this->assertEquals('*', $permission->resource);

        $permission->resource = 'album.album';
        $this->em->persist($permission);
        $this->em->flush();

        $permission = $this->em->find('Api\Model\Permission', $id);
        $this->assertEquals('album.album', $permission->resource);

    }

    public function testDelete()
    {
        $client = $this->addCliente();
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = ' * <br> ';

        $this->em->persist($permission);
        $this->em->flush();
        $id = $permission->id;

        $permission = $this->em->find('Api\Model\Permission', $id);
        $this->em->remove($permission);
        $this->em->flush();

        $permission = $this->em->find('Api\Model\Permission', $id);
        $this->assertNull($permission);
    }

    private function addCliente()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve<br> ';
        $client->password = 'teste';
        $client->status = 'ATIVO';

        $this->em->persist($client);
        $this->em->flush();
        return $client;
    }

}