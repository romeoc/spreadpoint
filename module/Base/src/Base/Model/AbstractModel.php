<?php

/**
 * Abstract Model with Service Locator
 * 
 * Module: Base
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 * 
 */


namespace Base\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Base\Model\Session;

class AbstractModel implements ServiceLocatorAwareInterface
{
    /**
     * The doctrine entity we are dealing with
     *
     * @var string 
     */
    protected $entity;
    
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface 
     */
    protected $service;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->service;
    }
    
    /**
     * Get doctrine entity manager
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
    
    public function init($entity)
    {
        $this->entity = $entity;
    }
    
    /**
     * Create or Update the entity
     * 
     * @param array $data
     */
    public function save($data)
    {
        if (!empty($data['id'])) {
            $this->update($data);
        } else {
            $this->create($data);
        }
    }
    
    /**
     * Create a new entity with the provided data
     * - will return the newly created entity or false if an error occurs
     * 
     * @param array $data
     * @return boolean | doctrineEntity (Base\Entity\AbstractEntity)
     */
    public function create($data)
    {
        $entityManager = $this->getEntityManager();

        // Creaty the new entity and set it's data
        $entity = new $this->entity();
        $entity->setData($data);
        
        // Trigger before save events of the entity
        $entity->beforeSave();
        $entity->beforeCreate();

        try {
            // Save entity
            $entityManager->persist($entity);
            $entityManager->flush();
            
            // Trigger after save events of the entity    
            $entity->afterSave();
            $entity->afterCreate();
        
            return $entity;
        } catch (\Exception $ex) {
            Session::error($ex->getMessage());
            return false;
        }
    }

    /**
     * Update the data of an existing entity
     * 
     * @param array $data
     * @return doctrineEntity (Base\Entity\AbstractEntity)
     */
    public function update($data)
    {
        // Fetch entity
        $entityManager = $this->getEntityManager();
        $entity = $entityManager->find($this->entity, $data['id']);

        // Set new data
        unset($data['id']);
        $entity->setData($data);
        
        // Trigger before save events
        $entity->beforeSave();
        $entity->beforeUpdate();

        // Save entity
        $entityManager->flush();
        
        // Trigger after save events
        $entity->afterSave();
        $entity->afterUpdate();
        
        return $entity;
    }

    /**
     * Delete a doctrine entity
     * 
     * @param int $id
     */
    public function delete($id)
    {
        $entityManager = $this->getEntityManager();

        // Find entry and remove it
        $entity = $entityManager->find($this->entity, $id);
        $entityManager->remove($entity);

        // Save changes and trigger before & after delete events
        $entity->beforeDelete();
        $entityManager->flush();
        $entity->afterDelete();
    }
}
