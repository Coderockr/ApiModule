<?php
namespace Api\Service;

use Core\Test\ServiceTestCase;
use Api\Model\Client;
use Api\Model\Permission;
use Api\Service\Auth;
use Core\Service\ParameterFactory;

/**
 * Auth related tests
 * 
 * @category Api
 * @package Service
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */

/**
 * @group Service
 */
class AuthTest extends ServiceTestCase
{
    /**
     * A valid password to be used in the tests
     * @var string
     */
    protected $validPassword  = '0febfd3464e60216072a60ea095b2ceb6c0d3e87';
    
    /**
     * Cache used to store the token
     * @var Zend\Cache\Storage\Adapter
     */
    protected $cache;

    /**
     * Auth service instance
     * @var Api\Service\Auth
     */
    protected $authService;

    /**
     * Tests setup
     * @return void
     */
    public function setup()
    {
        parent::setup();
        $this->cache = $this->getService('Cache');
        $this->cache->flush();
        $this->authService = $this->getService('Api\Service\Auth');
        //Must use the tests entityManager
        $this->authService->setEntityManager($this->em);
    }
    
    /**
     * Tests the auth with valid credential
     * @return void
     */
    public function testValidAuthentication() 
    {
        
        $permission = $this->addPermission('*');
        $this->em->persist($permission);

        $client = $this->addClient();
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

        //Verifies if a token registry was created
        $token = $this->em
                      ->getRepository('Api\Model\Token')
                      ->findAll();
        $this->assertEquals(count($token), 1);
        $this->assertEquals($token[0]->token, $result['access_token']);

        //Verifies if it's in cache
        $cached = $this->cache->getItem($result['access_token']);
        $this->assertEquals($cached['status'], Auth::VALID);
        $permission = $this->em
                           ->getRepository('Api\Model\Permission')
                           ->findAll();
        $this->assertEquals($cached['resources'][0]->resource, $permission[0]->resource);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid login or password
     * Tests the authentication with an invalid password
     */
    public function testInvalidAuthentication() 
    {
        $client = $this->addClient();
        $client->senha = $this->validPassword;
        $this->em->persist($client);
        $this->em->flush();
        
        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'invalid password'
            )
        );

        $result = $this->authService->authenticate($parameters);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid parameters
     */
    public function testAuthenticationWithoutParameters() 
    {
        $client = $this->addClient();
        $client->senha = $this->validPassword;
        $this->em->persist($client);
        $this->em->flush();
        
        $parameters = ParameterFactory::factory(
            array('login' => $client->login)
        );
        $result = $this->authService->authenticate($parameters);
    }

    /**
     * Tests the permitted access
     * @return void
     */
    public function testValidAuthorization() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addClient();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'test'
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
     * Tests a resource access with a cached expired token
     * @return void
     */
    public function testExpiredCachedValidAuthorization() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addClient();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'test'
            )
        );
        //Garanties that the adapter user is for tests
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
     * Tests the acces with a invalid tokenTesta o acesso com um token inválido
     * @return void
     */
    public function testInvalidAuthorization() 
    {
        $parameters = ParameterFactory::factory(
            array(
                'token' => 'invalid token',
                'resource' => 'album.album'
            )
        );

        $result = $this->authService->authorize($parameters);
        $this->assertEquals($result, Auth::INVALID);
    }

    /**
     * Tests an access to a resource that the user doesn't have permission
     * @return void
     */
    public function testDeniedAuthorization() 
    {
        $permission = $this->addPermission('album.album');
        $this->em->persist($permission);

        $client = $this->addClient();
        $client->addPermission($permission);
        $client->password = $this->validPassword;
        $this->em->persist($client);
        
        $this->em->flush();

        $parameters = ParameterFactory::factory(
            array(
                'login' => $client->login,
                'password' => 'test'
            )
        );
        
        //Garanties that the adapter user is for tests
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
        

        return $client;
    }

    /**
     * Creates a new permission registry for tests
     * @param string $resource The resource being accessed
     * @return Api\Model\Permission  A new permission registry
     */
    private function addPermission($resource)
    {
        $permission = new Permission();
        $permission->resource = $resource;
        return $permission;
    }

}