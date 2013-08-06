<?php

namespace Api\PostProcessor;

/**
 * Classe abstrata usada pelos pós-processadores
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
abstract class AbstractPostProcessor
{
    /**
     * @var array|null
     */
    protected $_vars = null;

    /**
     * @var null|\Zend\Http\Response
     */
    protected $_response = null;

    /**
     * @param $vars
     * @param \Zend\Http\Response $response
     */
    public function __construct(\Zend\Http\Response $response, $vars = null)
    {
        $this->_vars = $vars;
        $this->_response = $response;
    }

    /**
     * @return null|\Zend\Http\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @abstract
     */
    abstract public function process();
}
