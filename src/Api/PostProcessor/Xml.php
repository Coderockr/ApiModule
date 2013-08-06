<?php

namespace Api\PostProcessor;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Classe concreta que retorna XML
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class Xml extends AbstractPostProcessor
{
    /**
     * Retorna os cabeçalhos e conteúdo no formato XML
     */
    public function process()
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('xml' => new XmlEncoder()));
        $content = null;
        
        if (isset($this->_vars['error-message'])) {
            $content = $serializer->serialize($this->_vars['error-message'], 'xml');
        }

        if (!$content) {
            $content = $serializer->serialize($this->_vars, 'xml');         
        }
        
        $this->_response->setContent($content);

        $headers = $this->_response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/xml');
        $this->_response->setHeaders($headers);
    }
}