<?php
namespace Api\Service;

use Exception;

/**
 * Classe que fabrica um conjunto de parâmetros 
 * @category   Api
 * @package    Service
 * @author     Elton Minetto <eminetto@coderockr.com>
 */
class ParameterFactory
{

    /**
     * Parâmetros padrão
     * @var array
     */
    public static $defaultParameters = array(
        'fields' => null,
        'limit'  => null,
        'offset' => null,
        'filter' => null
    );

    /**
     * Constrói o conjunto de parâmetros
     * @param  array $cfg Configuração dos Parâmetros
     * @return ParameterSet      Conjuno de parâmetros
     */
    public static function factory($cfg)
    {
        if (!is_array($cfg)) {
            throw new Exception("Formato inválido de parâmetros");
        }
        
        $cfg = array_merge(ParameterFactory::$defaultParameters, $cfg);
        $parameterSet = new ParameterSet();
        foreach ($cfg as $name => $value) {
            $parameterSet->add(new Parameter($name, $value));
        }

        return $parameterSet;
    }
}