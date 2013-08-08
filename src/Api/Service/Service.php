<?php
namespace Core\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Api\Db\TableGateway;

/**
 * Classe pai dos serviços
 * @category   Api
 * @package    Service
 * @author     Elton Minetto<eminetto@coderockr.com>
 */
abstract class Service implements ServiceManagerAwareInterface
{
	/**
     * @var ServiceManager
     */
    protected $serviceManager;

     /**
    * @var Doctrine\ORM\EntityManager
    */
    protected $em;
    protected $entityName;
     
    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function getEntityManager()
    {
        if (null === $this->em) {
             $this->em = $this->getServiceManager()->get('EntityManager');
        }
        return $this->em;
    } 

    /**
     * Cache 
     * @var Cache
     */
    protected $cache;

    /**
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Retorna uma instância de serviceManager
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

 //    /**
 //     * Retorna uma instância de TableGateway
 //     * Usado para ter acesso a entidades dentro do mesmo módulo
 //     * 
 //     * @param  string $table
 //     * @return TableGateway
 //     */
	// protected function getTable($table)
 //    {
 //        $sm = $this->getServiceManager();
 //        $dbAdapter = $sm->get('DbAdapter');
 //        $tableGateway = new TableGateway($dbAdapter, $table, new $table);
 //        $tableGateway->initialize();
 //        return $tableGateway;
 //    }

    /**
     * Retorna uma instância de Service\Client
     * Usado para acessar a api/rpc e acessar outros módulos
     *
     * @return Service\Client
     */
    protected function getClient()
    {
        return $this->getServiceManager()->get('Core\Service\Client');
    }

    /**
     * Recupera a instância do cache
     * @return Zend\Cache\Storage\Adapter Cache
     */
    protected function getCache()
    {
        if (!$this->cache)
            $this->cache = $this->getServiceManager()->get('Cache');
        return $this->cache;

    }

    /**
     * Retorna uma instância de Service
     * Usado para acessar outro serviço dentro do mesmo módulo
     * 
     * @return Service
     */
    protected function getService($service)
    {
        return $this->getServiceManager()->get($service);
    }
}