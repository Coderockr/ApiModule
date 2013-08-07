<?php
namespace Api\Model;

use Core\Test\ModelTestCase;
use Api\Model\Cliente;
use Api\Model\Permission;
use Zend\InputFilter\InputFilterInterface;

/**
 * Permission entity related tests
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Model
 */
class PermissionTest extends ModelTestCase
{
    
    /**
     * Tests the existance of filters
     */
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
    public function testInvalidInputFilter()
    {
        $permission = new Permission();
        //Resource can only have 100 characters
        $permission->resource = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }        

    /**
     * Tests a valid permission insertion
     */
    public function testInsert()
    {
        $client = $this->addClient();
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = ' * <br> ';

        $this->em->persist($permission);
        $this->em->flush();

        $this->assertEquals(1, $permission->id);
        $this->assertNotNull($permission->created);
    }

    /**
     * Tests the update of a valid permission
     */
    public function testUpdate()
    {
        $client = $this->addClient();
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

    /**
     * Tests a permission deletion
     */
    public function testDelete()
    {
        $client = $this->addClient();
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

    /**
     * Creates a new user to be used in the tests
     * @return Api\Model\User   A new user registry
     */
    private function addClient()
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