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

        // if(isset($_SESSION['lang'])) {
        //     $lang = $_SESSION['lang'];
        //     $this->translator = new Translator; 
        //     $this->translator->setLocale($lang);
        //     $this->translator->addTranslationFile('phparray','/tmp/'.$lang.'.php','default',$lang);
        //     $arrayContents = json_decode($content, true);
        //     $content = json_encode($this->translate($arrayContents));
        // }
       
        $this->_response->setContent($content);
        $headers = $this->_response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/json');
        $this->_response->setHeaders($headers);
    }

    // /**
    //  * Faz  tradução do conteúdo do array
    //  * @param  array $content 
    //  * @return array Conteúdo traduzido
    //  */
    // private function translate($content)
    // {
    //     $result = array();
    //     foreach ($content as $key => $value) {
    //         if (is_array($value)) {
    //             $result[$key] = $this->translate($value);
    //             continue;
    //         }
    //         $result[$key] = $this->translator->translate($value);
    //     }
    //     return $result;
    // }
}
