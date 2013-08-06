<?php

namespace Api\PostProcessor;

/**
 * Classe concreta que retorna uma imagem
 * 
 * @category Api
 * @package PostProcessor
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class Image extends AbstractPostProcessor
{
    /**
     * Retorna os cabeçalhos de uma imagem
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
