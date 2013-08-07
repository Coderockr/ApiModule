<?php

namespace Api\PostProcessor;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Zend\I18n\Translator\Translator;

/**
 * Concrete class that returns JSON
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */
class Json extends AbstractPostProcessor
{   

    protected $translator;

    /**
     * Returns the content and headers in JSON format
     */
    public function process($class = null)
    {
        $serializer = SerializerBuilder::create()->build();
        $content = null;

        if (is_array($this->_vars) && isset($this->_vars['error-message'])) {
            if($this->_vars['error-code'] == 404) {
                $content = $this->_vars['error-message'];
                $this->_response = $this->_response->setStatusCode(404);
            } else {
                $content = $serializer->serialize($this->_vars, 'json');
            }
        }

        if (!$content) {
            try {
                $content = array();
                if ($class) {
                    $content = $serializer->serialize($this->_vars, 'json', SerializationContext::create()->setGroups(array($class)));
                }
                else {
                    $content = $serializer->serialize($this->_vars, 'json');
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
                
            }
        }

        $this->_response->setContent($content);
        $headers = $this->_response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/json');
        $this->_response->setHeaders($headers);
    }
}
