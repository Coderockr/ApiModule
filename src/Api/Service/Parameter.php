<?php
namespace Core\Service;

/**
 * Classe que representa um parâmetro de um service
 * @category   Core
 * @package    Service
 * @author     Elton Minetto <eminetto@coderockr.com>
 */
class Parameter 
{

    /**
     * Nome do parâmetro
     * @var string
     */
    private $name;


    /**
     * Valor do parâmetro
     * @var mixed
     */
    private $value;

    /**
     * Construtor da classe
     * @param string $name  Nome do parâmetro
     * @param mixed $value Valor do parâmetro
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }


    /**
     * Retorna o nome do parâmetro
     * @return string Nome
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retorna o valor do parâmetro
     * @return mixed Valor
     */
    public function getValue()
    {
        return $this->value;
    }
}