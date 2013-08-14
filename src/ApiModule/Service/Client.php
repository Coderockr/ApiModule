<?php
namespace Api\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;

/**
 * Classe para acessar os serviços entre os módulos
 * @category   Core
 * @package    Service
 * @author     Elton Minetto<eminetto@coderockr.com>
 */
class Client
{
    /**
     * Token usado para acessar os serviços
     * @var string
     */
    private $apiKey;

    /**
     * Cliente http usado para conectar nos serviços
     * @var Zend\Http\Client
     */
    private $httpClient;

    /**
     * Uri da api
     * @var string
     */
    private $apiUri;

    /**
     * Uri do rpc
     * @var string
     */
    private $rpcUri;

    /**
     * Construtor da classe. Dependências injetadas automaticamente pelo ServiceManager
     * @param string $apiKey Token
     * @param string $apiUri Uri da api
     * @param string $rpcUri Uri do rpc
     */
    public function __construct($apiKey, $apiUri, $rpcUri)
    {
        $this->apiKey = $apiKey;
        $this->apiUri = $apiUri;
        $this->rpcUri = $rpcUri;
        $this->httpClient = new HttpClient();
        $headers = array(
            'Authorization' => $this->apiKey
        );
        //verifica se a api está sendo acessada de um ambiente de testes
        switch (getenv('ENV')) {
            case 'testing':
                $headers['Environment'] = 'testing';
                break;
            case 'jenkins':
                $headers['Environment'] = 'jenkins';
                break;
            
        }
        $this->httpClient->setHeaders($headers);
        $config = array(
            'timeout' => 100,
        );
        $this->httpClient->setOptions($config);
    }

    /**
     * Faz a execução do serviço
     * @param  string $uri        Uri do serviço
     * @param  array $parameters  Parâmetros a serem enviados ao serviço
     * @return arrat              Resultado da execução
     */
    public function execute($uri, $parameters = null)
    {
        //@todo filtrar os parametros para evitar problemas de segurança
        $this->httpClient->setUri($this->rpcUri . $uri);
        $this->httpClient->setMethod(Request::METHOD_POST);
        if ($parameters) {
            $this->httpClient->setParameterPost($parameters);
        }
        $response = $this->httpClient->send();
        return array(
            'status' => $response->getStatusCode(),
            'data' => $response->getBody()
        );
    }

    /**
     * Acessar a api usando o método HTTP GET
     * @param  string $uri Uri do recurso
     * @return array       Resultado da execução
     */
    public function get($uri) 
    {
        $this->httpClient->setUri($this->apiUri . $uri);
        $this->httpClient->setMethod(Request::METHOD_GET);
        $response = $this->httpClient->send();
        return array(
            'status' => $response->getStatusCode(),
            'data' => $response->getBody()
        );
    }

    /**
     * Acessar a api usando o método HTTP POST
     * @param  string $uri      Uri do recurso
     * @param  array  $fields   Parâmetros a serem enviados
     * @return array            Resultado da execução
     */
    public function post($uri, $fields)
    {
        //@todo filtrar os parametros para evitar problemas de segurança
        $this->httpClient->setUri($this->apiUri . $uri);
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setParameterPost($fields);
        $response = $this->httpClient->send();
        return array(
            'status' => $response->getStatusCode(),
            'data' => $response->getBody()
        );
    }

    /**
     * Acessar a api usando o método HTTP PUT
     * @param  string $uri      Uri do recurso
     * @param  array  $fields   Parâmetros a serem enviados
     * @return array            Resultado da execução
     */
    public function put($uri, $fields)
    {
        //@todo filtrar os parametros para evitar problemas de segurança
        $this->httpClient->setUri($this->apiUri . $uri);
        $this->httpClient->setMethod(Request::METHOD_PUT);
        $this->httpClient->setParameterPost($fields);
        $response = $this->httpClient->send();
        return array(
            'status' => $response->getStatusCode(),
            'data' => $response->getBody()
        );
    }    

    /**
     * Acessar a api usando o método HTTP DELETE
     * @param  string $uri      Uri do recurso
     * @return array            Resultado da execução
     */
    public function delete($uri)
    {
        $this->httpClient->setUri($this->apiUri . $uri);
        $this->httpClient->setMethod(Request::METHOD_DELETE);
        $response = $this->httpClient->send();
        return array(
            'status' => $response->getStatusCode(),
            'data' => $response->getBody()
        );
    }

    /**
     * Converte o objeto em Array
     * @param  stdClass|array $data Objeto a ser convertido
     * @return array      Array com os dados
     */
    public function toArray($data)
    { 
        if (is_object($data)) {
            return get_object_vars($data);
        }
        if (is_array($data)) {
            $result = array();
            foreach ($data as $d) {
                $result[] = get_object_vars($d);
            }
            return $result;
        }
    }
}