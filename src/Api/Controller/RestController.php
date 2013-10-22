<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Api\Model\EntityException;

/**
 * Class responsable for Entities REST access
 * 
 * @category Api
 * @package  Controller
 * @author   Elton Minetto <eminetto@coderockr.com>
 * @author   Mateus Guerra <mateus@coderockr.com>
 */
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
     * Returns the entities list
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
            $filter = json_decode($query['filter']);
            foreach ($filter as $field => $condition) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('e.' . $field, "'%" . $condition . "%'"));
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

        $result = $queryBuilder->getQuery()->getResult();

        if (!$result) {
            throw new \Exception('Entity not found', 404);
        }

        return $result;
    }


    /**
     * Retuns only one entity
     *  
     * @param int $id  Entity Id
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
     * Creates a new Entity
     *
     * @param array $data  Entity data
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
     * Entity update
     * @param int $id  Entity Id
     * @param array $data  Entity updated data
     * 
     * @return array       Returns the updated Entity
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
     * Deletes an Entity
     *
     * @param  int $id Entity id for deletion
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
     * Returns an Entity instance
     *
     * @return Api\Model\Entity
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
     * Returns an Entity table
     *
     * @return Api\Model\Entity
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
     * Returns the parameters of the Entity to be executed
     * @param string $module  Entity module
     * @param string $entity  Entity name
     * 
     * @return string         Entity class
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
        $moduleConfig = include $_SERVER['DOCUMENT_ROOT'] . '/../module/' . ucfirst($module) . '/config/entities.config.php';
        
        return $moduleConfig[$entity]['class'];
    }
}