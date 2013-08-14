<?php

namespace Api\PostProcessor;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

/**
 * Concrete class that returns xml
 * 
 * @category Api
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
       $serializer = SerializerBuilder::create()->build();
        $content = null;

        if (is_array($this->_vars) && isset($this->_vars['error-message'])) {
            if($this->_vars['error-code'] == 404) {
                $content = $this->_vars['error-message'];
                $this->_response = $this->_response->setStatusCode(404);
            } else {
                $content = $serializer->serialize($this->_vars, 'xml');
            }
        }

        if (!$content) {
            try {
                $content = array();
                if ($class) {
                    $content = $serializer->serialize($this->_vars, 'xml', SerializationContext::create()->setGroups(array($class)));
                }
                else {
                    $content = $serializer->serialize($this->_vars, 'xml');
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
                
            }
        }

        $this->_response->setContent($content);
        $headers = $this->_response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/xml');
        $this->_response->setHeaders($headers);
    }
}