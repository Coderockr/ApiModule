<?php

namespace Api\Db;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Api\Model\EntityException;
use Zend\Db\TableGateway\Feature\SequenceFeature;
use Zend\Db\TableGateway\Feature\FeatureSet;
use Zend\Db\Sql\Expression;

/**
 * TableGateway é responsável pelas manipulações das entidades
 * 
 * @category Api
 * @package Controller
 * @author  Elton Minetto<eminetto@coderockr.com>
 */
class TableGateway extends AbstractTableGateway
{

    /**
     * Nome do campo da chave primária
     *
     * @var string
     */
    protected $primaryKeyField;

    /**
     * ObjectPrototype
     * @var stdClass
     */
    protected $objectPrototype;

    /**
     * Construtor. A dependência é injetada automaticamente
     * @param Adapter $adapter Conexão com o banco de dados
     * @param  string $tableName Nome da entidade/tabela
     * @param  Api\Entity $object    Objeto a ser manipulado
     */
    public function __construct(Adapter $adapter, $table, $objectPrototype)
    {
        $this->adapter = $adapter;
        $this->table = $objectPrototype->getTableName();
        $this->objectPrototype = $objectPrototype;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype($objectPrototype);
    }

    /**
     * Faz a inicialização do TableGateway
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        //se não foi configurada uma chave primária assume o campo ID
        $this->primaryKeyField = $this->objectPrototype->primaryKeyField;
        if (!is_string($this->primaryKeyField) && !is_array($this->primaryKeyField)) {
            $this->primaryKeyField = 'id';
        }

        if ($this->objectPrototype->sequenceName && $this->needSequence()) {
            $feature = new SequenceFeature(
                            $this->primaryKeyField,
                            $this->objectPrototype->sequenceName
            );
            $this->featureSet = new FeatureSet(array($feature));
            $feature->setTableGateway($this);
        }
    }

    /**
     * Verifica se o adapter atual precisa de sequence para gerar o ID
     * @return boolean 
     */
    private function needSequence()
    {
        $platformName = $this->getAdapter()->getPlatform()->getName();
        if ($platformName == 'Oracle' || $platformName == 'PostgreSQL') {
            return true;
        }
        return false;
    }

    /**
     * Configura um adaptador para o TableGateway diferente do injetado no construtor
     * @param Adapter $adapter
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($this->adapter, $this->table);
        return $this;
    }

    /**
     * Faz o select dos dados da entidade
     * @param  string $columns Nome das colunas a serem retornadas pela consulta
     * @param  string $where   Cláusula where a ser usada
     * @param  int $limit      Limite dos resultados
     * @param  int $offset     Offset a ser usado pelo limit
     * @param  string $order   Critério de ordenação
     * @return null|ResultSetInterface   Resultado da consulta
     */
    public function fetchAll($columns = null, $where = null, $limit = null, $offset = null, $order = null)
    {
        $select = new Select();
        $select->from($this->getTable());

        if ($columns) {
            $select->columns($columns);
        }

        if ($where) {
            $select->where($where);
        }

        if ($limit) {
            $select->limit((int) $limit);
        }

        if ($offset) {
            $select->offset((int) $offset);
        }

        if ($order) {
            $select->order($order);
        }

        $this->featureSet->apply('preSelect', $select);
        return $this->selectWith($select);
    }

    /**
     * Recupera uma entidade pela sua chave primária
     * @param  int $id Código da entidade
     * @return Api\Entity    Uma entidade
     */
    public function get($id)
    {
        if (is_array($id)) {
            return $this->getComplexKey($id);
        }

        return $this->getSimpleKey($id);
    }

    private function getSimpleKey($id)
    {
        $id = (int) $id;
        $rowset = $this->select(array($this->primaryKeyField => $id));

        $row = $rowset->current();   
        if (!$row) {
            throw new EntityException("Could not find row $id");
        }
        return $row;
    }

    private function getComplexKey($id)
    {
        $rowset = $this->fetchAll(null, $id);
        $row = $rowset->current();
        if (!$row) {
            throw new EntityException("Could not find row " . var_export($id, true));
        }
        return $row;
    }

    /**
     * Faz a persistência da entidade na tabela
     * @param  Api\Entity $object A entidade a ser salva
     * @return Api\Entity         A entidade salva
     */
    public function save($object)
    {
        if (is_string($this->primaryKeyField)) {
            return $this->saveSimpleKey($object);
        }
        if (is_array($this->primaryKeyField)) {
            return $this->saveComplexKey($object);
        }

        return false;
    }

    /**
     * Faz a persistência da entidade na tabela, para entidades com chave primária simples
     * @param  Api\Entity $object A entidade a ser salva
     * @return Api\Entity         A entidade salva
     */
    private function saveSimpleKey($object)
    {
        $data = $object->getData();
        $id = (int) isset($data[$this->primaryKeyField]) ? $data[$this->primaryKeyField] : 0;
        if ($id == 0) {
            if ($this->insert($object) < 1) {
                throw new EntityException("Erro ao inserir", 1);
            }

            $primaryKeyField = $this->primaryKeyField;
            $object->$primaryKeyField = $this->lastInsertValue;
        } else {
            if (!$this->get($id)) {
                throw new EntityException('Id does not exist');
            }
            if ($this->update($object, array($this->primaryKeyField => $id)) < 1) {
                throw new EntityException("Erro ao atualizar", 1);
            }
        }
        return $object;
    }

    /**
     * Faz a persistência da entidade na tabela, para entidades com chave primária composta
     * @param  Api\Entity $object A entidade a ser salva
     * @return Api\Entity         A entidade salva
     */
    private function saveComplexKey($object)
    {
        $data = $object->getData();
        $where = array();
        foreach ($this->primaryKeyField as $key => $value) {
            $where[$value] = $data[$value];
        }

        //tenta atualizar. se não existir inclui
        if ($this->update($object, $where) < 1) {
            if ($this->insert($object) < 1) {
                throw new \Exception("Erro salvando entidade " . get_class($object));
            }
        }
        return $object;
    }

    /**
     * Exclui uma entidade
     * @param  int|string|array $param O id da entidade ou uma clausula where
     * @return int     Número de registros excluídos
     */
    public function delete($param)
    {
        if (is_array($param)) {
            return parent::delete($param);
        }

        if (is_string($param) && (int) $param == 0) {
            return parent::delete($param);
        }

        return parent::delete(array($this->primaryKeyField => (int) $param));
    }

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set)
    {
        if (is_array($set)) {
            //Executa as validações
            $this->objectPrototype->setData($set);
            $set = $this->objectPrototype->getData();
        }else
        if (is_object($set)) {
            $set = $set->getData();
        }
        $set = $this->formatDateFromDatabase($set);
        return parent::insert($set);
    }

    /**
     * Update
     *
     * @param  array $set
     * @param  string|array|closure $where
     * @return int
     */
    public function update($set, $where = null)
    {
        if (is_array($set)) {
            //Executa as validações
            $this->objectPrototype->setData($set);
            $set = $this->objectPrototype->getData();
        }else
        if (is_object($set)) {
            $set = $set->getData();
        }
        $set = $this->formatDateFromDatabase($set);
        
        return parent::update($set, $where);
    }

    protected function formatDateFromDatabase($data)
    {
        $platformName = $this->getAdapter()->getPlatform()->getName();        
        $filterDate = new \Api\Filter\Date();
        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $filterDate->formatDateFromDatabase($platformName, $value);     
            }
        }

        return $data;
    }

    
}