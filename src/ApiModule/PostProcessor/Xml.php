<?php

namespace ApiModule\PostProcessor;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Concrete class that returns JSON
 * 
 * @category ApiModule
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class Xml extends AbstractPostProcessor
{
    /**
     * Returns the content and headers in XML format
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