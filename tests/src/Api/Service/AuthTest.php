<?php
namespace Api\Service;

use Core\Test\ServiceTestCase;
use Api\Model\Client;
use Api\Model\Permission;
use Api\Service\Auth;
use Core\Service\ParameterFactory;

/**
 * Testes relacionados ao serviço Auth
 * 
 * @category Api
 * @package Service
 * @author  Elton Minetto<eminetto@coderockr.com>
 */

/**
 * @group Service
 */
class AuthTest extends ServiceTestCase
{
    /**
     * Uma senha válida para ser usada nos testes
     * @var string
     */
    protected $validPassword  = '0febfd3464e60216072a60ea095b2ceb6c0d3e87';
    
    /**
     * Cache usado para armazenar o token
     * @var Zend\Cache\Storage\Adapter
     */
    protected $cache;

    /**
     * Uma instância do serviço Auth
     * @var Api\Service\Auth
     */
    protected $authService;
    

    /**
     * Faz o setup dos testes
     * @return void
     */
    public function setup()
    {
        parent::setup();
        $this->cache = $this->getService('Cache');
        $this->cache->flush();
        $this->authService = $this->getService('Api\Service\Auth');
        //deve usar o entityManager dos testes
        $this->authService->setEntityManager($this->em);
    }
    
    /**
     * Faz o teste com credenciais válidas
     * @return void
     */
    public function testAutenticacaoValida() 
    {
        
        $permission = $this->addPermission('*');
        $this->em->persist($permission);

        $client = $this->addCliente();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();
        
        $this->assertEquals('Api\Service\Auth', get_class($this->authService));
        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'teste'
            )
        );
        $result = $this->authService->authenticate($parameters);
        $this->assertEquals(count($result['access_token']), 1);

        //verifica se criou registro na tabela Token
        $token = $this->em
                      ->getRepository('Api\Model\Token')
                      ->findAll();
        $this->assertEquals(count($token), 1);
        $this->assertEquals($token[0]->token, $result['access_token']);

        //verifica se está no cache
        $cached = $this->cache->getItem($result['access_token']);
        $this->assertEquals($cached['status'], Auth::VALID);
        $permission = $this->em
                           ->getRepository('Api\Model\Permission')
                           ->findAll();
        $this->assertEquals($cached['resources'][0]->resource, $permission[0]->resource);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Login ou senha inválidos
     */
    public function testAutenticacaoInvalida() 
    {
        //faz o teste com uma senha inválida
        $client = $this->addCliente();
        $client->senha = $this->validPassword;
        $this->em->persist($client);
        $this->em->flush();
        
        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'senha invalida'
            )
        );

        $result = $this->authService->authenticate($parameters);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Parâmetros inválidos
     */
    public function testAutenticacaoSemParametros() 
    {
        $client = $this->addCliente();
        $client->senha = $this->validPassword;
        $this->em->persist($client);
        $this->em->flush();
        
        $parameters = ParameterFactory::factory(
            array('login' => $client->login)
        );
        $result = $this->authService->authenticate($parameters);
    }

    /**
     * Faz o teste com um acesso permitido
     * @return void
     */
    public function testAutorizacaoValida() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addCliente();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'teste'
            )
        );
        $result = $this->authService->authenticate($parameters);

        $parameters = ParameterFactory::factory(
            array(
                'token' => $result['access_token'],
                'resource' => 'album.album'
            )
        );
       
        $result = $this->authService->authorize($parameters);
        $this->assertEquals($result, Auth::VALID);
    }

    /**
     * Testa o acesso a um resource com o token expirado no cache
     * @return void
     */
    public function testAutorizacaoValidaCacheExpirado() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addCliente();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'teste'
            )
        );
        //garante que o adapter usado é o dos testes
        $result = $this->authService
                       ->authenticate($parameters);

        $this->cache->removeItem($result['access_token']);

        $parameters = ParameterFactory::factory(
            array(
                'token' => $result['access_token'],
                'resource' => 'album.album'
            )
        );
        $result = $this->authService->authorize($parameters);
        $this->assertEquals($result, Auth::VALID);
    }    

    /**
     * Testa o acesso com um token inválido
     * @return void
     */
    public function testAutorizacaoInvalida() 
    {
        $parameters = ParameterFactory::factory(
            array(
                'token' => 'token invalido',
                'resource' => 'album.album'
            )
        );

        $result = $this->authService->authorize($parameters);
        $this->assertEquals($result, Auth::INVALID);
    }

    /**
     * Faz o teste acessando um resource que o usuário não tem permissão
     * @return void
     */
    public function testAutorizacaoNegada() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addCliente();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'teste'
            )
        );
        
        //garante que o adapter usado é o dos testes
        $dbAdapter = $this->serviceManager->get('dbAdapter');
        $result = $this->authService
                       ->authenticate($parameters);
        
        $parameters = ParameterFactory::factory(
            array(
                'token' => $result['access_token'],
                'resource' => 'album.getAllAlbums'
            )
        );
        $result = $this->authService->authorize($parameters);
        $this->assertEquals($result, Auth::DENIED);
    }

    /**
     * Cria um novo registro para os testes
     * @return Api\Model\Client   Um novo Client
     */
    private function addCliente()
    {
        $client = new Client();
        $client->name = 'Steve Jobs';
        $client->login = 'steve';
        $client->password = 'teste';
        $client->status = 'ACTIVE';
        
        return $client;
    }

    /**
     * Cria um novo registro para os testes
     * @param string $resource O resource sendo acessado
     * @return Api\Model\Permission  Um novo registro de Permission
     */
    private function addPermission($resource)
    {
        $permission = new Permission();
        $permission->resource = $resource;
        return $permission;
    }

}

