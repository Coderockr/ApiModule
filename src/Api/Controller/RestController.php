<?php
/**
 * Classe responsável pelo acesso REST das entidades
 * 
 * @category Api
 * @package  Controller
 * @author   Elton Minetto <eminetto@coderockr.com>
 */

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Core\Model\EntityException;

class RestController extends AbstractRestfulController
{
    
    /**
    * @var Doctrine\ORM\EntityManager
    */
    protected $em;

    protected $entityName;
     
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getEntityManager()
    {
        if (null === $this->em) {
             $this->em = $this->getServiceLocator()->get('EntityManager');
        }
        return $this->em;
    } 

    /**
     * Retorna uma lista de entidades
     *
     * http://zf2.dev/api/v1/album.album.json
     * http://zf2.dev/api/v1/album.album.xml
     * http://zf2.dev/api/v1/album.album.json?fields=title,id
     * http://zf2.dev/api/v1/album.album.json?fields=title,id&limit=1
     * http://zf2.dev/api/v1/album.album.json?limit=10&offset=5
     * http://zf2.dev/api/v1/album.album.json?nome=Elton&idade=33&fields=nome,cidade
     * 
     * @return array
     */
    public function getList()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        
        $queryBuilder->from($this->getEntityName(), 'e');
        

        $query = $this->getRequest()->getQuery();
        if (isset($query['fields'])) {
            $fields = $pieces = explode(",", $query['fields']);
            unset($query['fields']);
        }

        if (isset($query['limit'])) {
            $limit = $query['limit'];
            $queryBuilder->setMaxResults($limit);
            unset($query['limit']);
        }

        if (isset($query['offset'])) {
            $offset = $query['offset'];
            $queryBuilder->setFirstResult($offset);
            unset($query['offset']);
        }

        if (isset($query['filter'])) {
            $filter = $query['filter'];
            foreach ($filter as $field => $condition) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('e.' . $field, '%' . $condition . '%'));
            }
            unset($query['filter']);
        }
        
        //where
        if (count($query) > 0) {
            foreach ($query as $field => $condition) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('e.' . $field, "'" . $condition . "'"));
            }
        }

        $queryBuilder->select('e');

        // $queryBuilder->select('e')
        //              ->where('u.id = ?1')
        //              ->orderBy('u.name', 'ASC');
        
        // $fields, $where, $limit, $offset
           
        $result = $queryBuilder->getQuery()->getResult();

        if (!$result) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            return $response;
        }

        $lang = $this->getRequest()->getHeaders('lang');
       
        if ($lang) {
            if (!isset($_SESSION)) {
                session_start();
            }
            $lang = $lang->getFieldValue();

            switch ($lang) {
                case 'pt-BR':
                case 'pt-PT':
                case 'pt':
                    $lang = 'pt_BR';  
                    break;
                case 'en-US':
                case 'en':
                case 'en-GB':
                    $lang = 'en_US';  
                    break;
                case 'es-ES':
                case 'es':
                    $lang = 'es_ES';  
                    break;
                default:
                    $lang = 'en_US';  
                    break;
            }

            $_SESSION['lang'] = $lang;
        
        }

        return $result;
    }


    /**
     * Retorna uma única entidade
     *  
     * http://zf2.dev:8080/api/v1/album.album.json/1
     * 
     * @param int $id  Id da entidade
     * 
     * @return array
     */
    public function get($id)
    {
        $entityName = $this->getEntityName();
        $entity = $this->getEntityManager()->getRepository($entityName)->find($id);
        if (!$entity) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            return $response;
        }
        return $entity;
    }

    /**
     * Cria uma nova entidade
     *
     * @param array $data  Dados da entidade sendo salva
     * 
     * @return array
     */
    public function create($data)
    {
        $entity = $this->getTableObject();
        $entity->setData($data);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        
        return $entity;
    }

    /**
     * Atualiza uma entidade
     * @param  int $id O código da entidade a ser atualizada
     * @param  array $data Os dados sendo alterados
     * 
     * @return array       Retorna a entidade atualizada
     */
    public function update($id, $data)
    {
        $entity = $this->get($id);
        $data = array_merge($entity->getData(), $data);
        $entity->setData($data);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        
        return $entity;
    }

    /**
     * Exclui uma entidade
     *
     * @param  int $id Id da entidade sendo excluída
     * 
     * @return int 
     */
    public function delete($id)
    {
        $entity = $this->get($id);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return 1;
    }

    /**
     * Retorna uma instância da entidade
     *
     * @return Core\Model\Entity
     */
    protected function getEntityName()
    {
        if (! isset($this->entityName)) {
            $module = $this->getEvent()->getRouteMatch()->getParam('module');
            $entity = $this->getEvent()->getRouteMatch()->getParam('entity');
            $this->entityName = $this->getCallParameters($module, $entity);
        }
        
        return $this->entityName;
    }

    /**
     * Retorna uma instância da entidade
     *
     * @return Core\Mode\Entity
     */
    protected function getTableObject()
    {
        if (! isset($this->tableObject)) {
            $module = $this->getEvent()->getRouteMatch()->getParam('module');
            $entity = $this->getEvent()->getRouteMatch()->getParam('entity');
            $class = $this->getCallParameters($module, $entity);
            $this->tableObject = new $class;
        }
        
        return $this->tableObject;
    }

    /**
     * Retorna os parâmetros da entidade a ser executada
     * @param string $module  Módulo da entidade
     * @param string $entity  Nome da entidade
     * 
     * @return string          Classe da entidade
     */
    private function getCallParameters($module, $entity)
    {
        $sm = $this->getServiceLocator();
        $cache = $sm->get('Cache');
        $cached = $cache->getItem('api');
        if ($cached) {
            foreach ($cached['resources'] as $r) {
                if ($r['action'] == $entity) {
                    return $r['uri'];
                }
            }
        }
        $moduleConfig = include __DIR__ .
                        '/../../../../' . 
                        ucfirst($module). 
                        '/config/entities.config.php';
        
        return $moduleConfig[$entity]['class'];
    }
}