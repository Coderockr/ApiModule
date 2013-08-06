<?php
namespace Api\Service;

use Core\Service\Service;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Api\Model\Token;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Core\Service\ParameterSet;
use Core\Service\ParameterFactory;

/**
 * Serviço responsável pela autenticação, 
 * autorização e log de uso da api 
 * (Authentication, Authorization and Accounting)
 * 
 * @category Api
 * @package Service
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class Auth extends Service
{

    /** 
     * Token inválido 
     * @var int
     */
    const INVALID = 0;

    /** 
     * Token expirado
     * @var int
     */
    const EXPIRED = 1;

    /** 
     * Acesso ao recurso negado
     * @var int
     */
    const DENIED = 2;

    /** 
     * Token válido e acesso permitido
     * @var int
     */
    const VALID = 3;

    /** 
     * Construtor da classe
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Faz a autenticação dos usuários e salva o token na memória
     * 
     * @param ParameterSet $params
     * @return array
     */
    public function authenticate(ParameterSet $params)
    {
        $this->cache = $this->getCache();
        if (! $params->has('login') || ! $params->has('password')) {
            throw new \Exception("Parâmetros inválidos");
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
            throw new \Exception("Login ou senha inválidos");
        }
        $parameters = ParameterFactory::factory(
            array(
                'login' => $login, 
                'password' => $password
            )
        );
        $access_token = $this->generateToken($parameters);
        
        //salva o token na base de dados
        $token = new Token;
        $token->client = $client;
        $token->token = $access_token;
        $token->ip = '127.0.0.1'; //@todo pegar o ip da requisição?
        $token->status = 'VALID';
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        
        $permissions = array();
        if ($client->permissionCollection) {
            $permissions = $client->permissionCollection->toArray();
        }
        //salva o token no cache
        $this->cache->addItem($access_token, array('status' => $this::VALID, 'resources' => $permissions));

        return array ('access_token' => $access_token);
    }

    /**
     * Verifica se o token está valido e se o client tem acesso ao recurso requisitado
     * 
     * @todo colocar log em gearman para salvar os registros de acesso
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

        //verifica se existe um token valido no banco de dados
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
            //salva no cache
            $this->cache->addItem($access_token, array('status' => $this::VALID, 'resources' => $permissions));
            return $this::VALID;
        }
        return $this::INVALID;
    }


    /**
     * Gera a senha do client
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
     * Gera o token do client
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