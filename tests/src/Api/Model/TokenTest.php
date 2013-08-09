<?php
namespace Api\Model;

use Api\Test\ModelTestCase;
use Api\Model\Client;
use Api\Model\Permissao;
use Api\Model\Token;
use Zend\InputFilter\InputFilterInterface;
use Api\Service\ParameterFactory;

/**
 * Token entity related tests
 * 
 * @category Api
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Model
 */
class TokenTest extends ModelTestCase
{
    /**
     * Tests the existance of filters
     */
    public function testGetInputFilter()
    {
        $token = new Token();
        $if = $token->getInputFilter();
 
        $this->assertInstanceOf("Zend\InputFilter\InputFilter", $if);
        return $if;
    }
 
    /**
     * @depends testGetInputFilter
     */
    public function testInputFilterValid($if)
    {
        $this->assertEquals(4, $if->count());
 
        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('token'));
        $this->assertTrue($if->has('ip'));
        $this->assertTrue($if->has('status'));
    }
    
    /**
     * @expectedException Api\Model\EntityException
     */
    public function testTokenInvalidInputFilter()
    {
        $token = new Token();
        //Token must have between 1 and 255 characters
        $token->token = '';
        $token->token = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }

    /**
     * @expectedException Api\Model\EntityException
     */
    public function testIpInvalidInputFilter()
    {
        $token = new Token();
        //ip must have between 1 and 20 characters
        $token->ip = '';
        $token->ip = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }

    /**
     * Tests a valid token insertion
     */
    public function testInsertToken()
    {
        $client = $this->addClient();
        $access_token = $this->generatesToken($client);
        $token = new Token();
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1';
        $token->status = 'VALID';

        $this->em->persist($token);
        $this->em->flush();
        
        $this->assertEquals(2, $token->id);

        //A token is generated inside the authentication service
        $this->assertNotNull($token->created);
    }

    /**
     * @expectedException Doctrine\DBAL\DBALException
     * Tests an invalid toke insertion
     */
    public function testInvalidInsert()
    {
        $token = new Token();
        $token->ip = 'teste';

        $this->em->persist($token);
        $this->em->flush();
    }    

    /**
     * Tests the update of a valid token
     */
    public function testUpdate()
    {
        $client = $this->addClient();
        $access_token = $this->generatesToken($client);
        $token = new Token();
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1';
        $token->status = 'VALID';

        $this->em->persist($token);
        $this->em->flush();
        $id = $token->id;

        $this->assertEquals(2, $id);

        $token = $this->em->find('Api\Model\Token', $id);
        $this->assertEquals($access_token, $token->token);
        $this->assertEquals('VALID', $token->status);

        $token->status = 'INVALID';
        $this->em->persist($token);
        $this->em->flush();

        $token = $this->em->find('Api\Model\Token', $id);
        $this->assertEquals('INVALID', $token->status);

    }

    /**
     * Tests a token deletion
     */
    public function testDelete()
    {
        $client = $this->addClient();
        $access_token = $this->generatesToken($client);
        $token = new Token();
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1';
        $token->status = 'VALID';

        $this->em->persist($token);
        $this->em->flush();
        $id = $token->id;

        $token = $this->em->find('Api\Model\Token', $id);
        $this->em->remove($token);
        $this->em->flush();

        $token = $this->em->find('Api\Model\Token', $id);
        $this->assertNull($token); 
    }

    /**
     * Generates a new access token
     */
    private function generatesToken($client) 
    {
        $permission = $this->addPermission($client, '*');
        $this->em->persist($permission);
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'teste'
            )
        );
        
        $authService = $this->getService('Api\Service\Auth');
        $authService->setEntityManager($this->em);
        $result = $authService->authenticate($parameters);
        return $result['access_token'];
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

    /**
     * Creates a new permission to be used in the tests
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