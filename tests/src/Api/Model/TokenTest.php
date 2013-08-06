<?php
namespace Api\Model;

use Core\Test\ModelTestCase;
use Api\Model\Client;
use Api\Model\Permissao;
use Api\Model\Token;
use Zend\InputFilter\InputFilterInterface;
use Core\Service\ParameterFactory;

/**
 * @group Model
 */
class TokenTest extends ModelTestCase
{
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
     * @expectedException Core\Model\EntityException
     */
    public function testTokenInputFilterInvalido()
    {
        $token = new Token();
        //token deve ter entre 1 e 255
        $token->token = '';
        $token->token = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }

    /**
     * @expectedException Core\Model\EntityException
     */
    public function testIpInputFilterInvalido()
    {
        $token = new Token();
        //ip deve ter entre 1 e 20
        $token->ip = '';
        $token->ip = 'Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos Lorem Ipsum é simplesmente uma simulação de texto da indústria tipográfica e de impressos';
    }

    /**
     * Teste de inserção de uma permissao válida
     */
    public function testInsertToken()
    {
        $client = $this->addClient();
        $access_token = $this->geraToken($client);
        $token = new Token();
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1';
        $token->status = 'VALID';

        $this->em->persist($token);
        $this->em->flush();

        $this->assertEquals(2, $token->id);//um token é gerado dentro do serviço de autenticação
        $this->assertNotNull($token->created);
    }

    /**
     * @expectedException Doctrine\DBAL\DBALException
     */
    public function testInsertInvalido()
    {
        $token = new Token();
        $token->ip = 'teste';

        $this->em->persist($token);
        $this->em->flush();
    }    

    public function testUpdate()
    {
        $client = $this->addClient();
        $access_token = $this->geraToken($client);
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

    public function testDelete()
    {
        $client = $this->addClient();
        $access_token = $this->geraToken($client);
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

    private function geraToken($client) 
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

    private function addPermission($client, $resource)
    {
        $permission = new Permission();
        $permission->client = $client;
        $permission->resource = $resource;

        return $permission;
    }

}