<?php

namespace ApiModule\PostProcessor;

/**
 * Concrete class that returns an image
 * 
 * @category ApiModule
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 */
class Image extends AbstractPostProcessor
{
    /**
     * Returns an image headers
     */
    public function process()
    {
        $result = $this->_vars['image'];

        $this->_response->setContent($result);

        $headers = $this->_response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'image/' . $this->_vars['type']);
        $headers->addHeaderLine('Cache-Control', 'max-age=86400');
        $this->_response->setHeaders($headers);
    }
}
