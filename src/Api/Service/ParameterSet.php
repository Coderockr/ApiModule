<?php
namespace Api\Service;

use Exception;
use Countable;

/**
 * Classe que representa um conjunto de parâmetros
 * @category   Api
 * @package    Service
 * @author     Elton Minetto <eminetto@coderockr.com>
 */
class ParameterSet implements Countable
{

    /**
     * Conjunto de parâmetros
     * @var array
     */
    private $parameters;


    /**
     * Retorna um parâmetro
     * @param  string $name Nome do parâmetro
     * @return Parameter    Parâmetro
     */
    public function get($name)
    {
        foreach ($this->parameters as $p) {
            if ($p->getName() == $name) {
                return $p;
            }
        }
        
        throw new Exception("Parâmetro não existe");
    }


    /**
     * Adiciona um parâmetro ao conjunto 
     * @param Parameter $parameter Parâmetro sendo adicionado
     */ 
    public function add(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * Verifica se existe o parâmetro
     * @param  string  $name Nome do parâmetro
     * @return boolean 
     */
    public function has($name)
    {
        foreach ($this->parameters as $p) {
            if ($p->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conta o número de parâmetros armazenados
     * @return integer
     */
    public function count()
    {
        return count($this->parameters);
    }
}