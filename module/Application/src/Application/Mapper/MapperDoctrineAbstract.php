<?php

namespace Application\Mapper;

use Application\Entity\EntityAbstract;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * MapperDoctrineAbstract
 *
 * @package     Application
 * @subpackage  Mapper
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) ContinuousPHP - All rights reserved
 * @license     Unauthorized copying of this source code, via any medium is strictly
 *              prohibited, proprietary and confidential.
 */
class MapperDoctrineAbstract implements ServiceLocatorAwareInterface, MapperDoctrineInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var string
     */
    private $entityClassName;

    /**
     * Get entity manager
     *
     * @return EntityManager
     * @throws Exception
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            if ($this->getServiceLocator()->has('entity_manager')) {
                $this->setEntityManager($this->getServiceLocator()->get('entity_manager'));
            } else {
                throw new Exception('no service entity manager set');
            }
        }

        return $this->entityManager;
    }

    /**
     * Set entity Manager
     *
     * @param EntityManager $entityManager
     *
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Set entityRepository
     *
     * @param EntityRepository $entityRepository
     */
    public function setEntityRepository(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * Get entityRepository
     *
     * @return EntityRepository
     * @throws Exception
     */
    public function getEntityRepository()
    {
        if (!$this->entityRepository) {
            if ($this->getEntityClassName()) {
                $this->entityRepository = $this->getEntityManager()->getRepository($this->getEntityClassName());
            } else {
                try {
                    throw new \Exception('No Entity Class defined');
                }catch(\Exception $e) {
                    var_dump($e->getTraceAsString());
                    throw $e;
                }
            }
        }

        return $this->entityRepository;
    }

    /**
     * set entity class name
     *
     * @param string $entityClassName
     *
     * @return $this
     */
    public function setEntityClassName($entityClassName)
    {
        $this->entityClassName = (string) $entityClassName;

        return $this;
    }

    /**
     * get entity class Name
     *
     * @return string
     */
    public function getEntityClassName()
    {
        return $this->entityClassName;
    }

    /**
     * Persists the passed entity
     *
     * @param EntityAbstract $entity
     *
     * @return $this
     */
    public function store(EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function remove(EntityAbstract $entity)
    {
        $this->getEntityManager()->remove($entity);

        return $this;
    }

    /**
     * Flush queries
     *
     * @return $this
     */
    public function flush(EntityAbstract $entity = null)
    {
        $this->getEntityManager()->flush($entity);

        return $this;
    }

    /**
     * Begin a transaction
     *
     * @return $this
     */
    public function transactionBegin()
    {
        $this->getEntityManager()->beginTransaction();

        return $this;
    }

    /**
     * Commit a transaction
     *
     * @return $this
     */
    public function transactionCommit()
    {
        $this->getEntityManager()->commit();

        return $this;
    }

    /**
     * Rollback change
     *
     * @return $this
     */
    public function transactionRollback()
    {
        $this->getEntityManager()->rollback();

        return $this;
    }

    public function find($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getEntityRepository()->findOneBy($criteria, $orderBy);
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }
}