<?php
namespace Api\Service;

use Api\Service\Service;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Api\Model\Token;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Api\Service\ParameterSet;
use Api\Service\ParameterFactory;

/**
 * Service responsable for authentication, 
 * authorization and api log use. 
 * (Authentication, Authorization and Accounting)
 * 
 * @category Api
 * @package Service
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class Auth extends Service
{

    /** 
     * Invalid token
     * @var int
     */
    const INVALID = 0;

    /** 
     * Expired token
     * @var int
     */
    const EXPIRED = 1;

    /** 
     * Resourse access denied
     * @var int
     */
    const DENIED = 2;

    /** 
     * Valid token and valid access
     * @var int
     */
    const VALID = 3;

    /** 
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Does user authentication and saves the token in memory
     * 
     * @param ParameterSet $params
     * @return array
     */
    public function authenticate(ParameterSet $params)
    {
        $this->cache = $this->getCache();
        if (! $params->has('login') || ! $params->has('password')) {
            throw new \Exception("Invalid parameters");
        }
        $parameters = ParameterFactory::factory(
            array(
                'login' => $params->get('login')->getValue(), 
                'password' => $params->get('password')->getValue()
            )
        );
        $login = $params->get('login')->getValue();
        $password = $this->generatePassword($parameters);

        $sm = $this->getServiceManager();
        $client = $this->getEntityManager()
                       ->getRepository('Api\Model\Client')
                       ->findOneBy(array('login' => $login));

        if (! $client || $client->password != $password || $client->status != 'ACTIVE' ) {
            throw new \Exception("Invalid login or password");
        }
        $parameters = ParameterFactory::factory(
            array(
                'login' => $login, 
                'password' => $password
            )
        );
        $access_token = $this->generateToken($parameters);
        
        //Saves the token in the database
        $token = new Token;
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1'; //@todo get the requisition IP?
        $token->status = 'VALID';
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        
        $permissions = array();
        if ($client->permissionCollection) {
            $permissions = $client->permissionCollection->toArray();
        }
        //Save the token in the cache
        $this->cache->addItem($access_token, array('status' => $this::VALID, 'resources' => $permissions));

        return array ('access_token' => $access_token);
    }

    /**
     * Verifies if the toke is valid and if the client has the requested access
     * 
     * @todo put the log in gearman to save access registry
     * @param ParameterSet $params
     * @return int
     */
    public function authorize(ParameterSet $params)
    {
        $this->cache = $this->getCache();
        $resource = $params->get('resource')->getValue();
        $access_token = $params->get('token')->getValue();

        //testar o cache
        $cached = $this->cache->getItem($access_token);
        if ( isset($cached) && isset($cached['resources'])) {
            foreach ($cached['resources'] as $r) {
                if ($r->resource == $resource || $r->resource == '*') {
                    return $this::VALID;
                }
            }
            return $this::DENIED;
        }  

        //Verifies if an valid token exists in the database
        $token = $this->getEntityManager()
                      ->getRepository('Api\Model\Token')
                      ->findOneBy(array('token' => $access_token));
        
        $permissions = array();
        if ($token) {
            $permissions = $token->client->permissionCollection;
        }
        
        $valid = false;
        if (count($permissions) > 0) {
            foreach ($permissions as $r) {
                if ($r->resource == $resource || $r->resource == '*') {
                    $valid = true;
                }
            }
        }
        if ($valid) {
            //Saves in cache
            $this->cache->addItem($access_token, array('status' => $this::VALID, 'resources' => $permissions));
            return $this::VALID;
        }
        return $this::INVALID;
    }


    /**
     * Generates the client's password
     * 
     * @param ParameterSet $params
     * @return string
     */
    public function generatePassword(ParameterSet $params)
    {
        $login = $params->get('login')->getValue();
        $password = $params->get('password')->getValue();

        $salt = sha1($login) . sha1($password);
        $password = sha1($salt . $login . $password);
        return $password;
    }

    /**
     * Generates the client's token
     * 
     * @param ParameterSet $params
     * @return string
     */
    public function generateToken(ParameterSet $params)
    {
        $login = $params->get('login')->getValue();
        $password = $params->get('password')->getValue();   

        $parameters = ParameterFactory::factory(
            array('login' => $login, 'password' => $password)
        );
        $salt = $this->generatePassword($parameters);
        return sha1(date('h:m:s') .$salt . $login . $password);
    }
}