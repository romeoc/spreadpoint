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
use Zend\Http\Header\SetCookie;

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
    
    /**
     * Maximum file upload size (in megabytes)
     */
    const MAX_UPLOAD_SIZE = '10';
    
    /**
     * Allowed file upload extensions
     */
    const ALLOWED_UPLOAD_EXTENSIONS = 'jpg,jpeg,png,gif';

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
            return $this->update($data);
        } else {
            return $this->create($data);
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

        if (!$entity) {
            Session::error('You are trying to update an entry that does not exist');
            return;
        }
        
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
    
    /**
     * Return all uploaded files
     * 
     * @return array
     */
    public function getUploadedFiles()
    {
        $request = $this->getServiceLocator()->get('Request');
        return $request->getFiles()->toArray();
    }
    
    /**
     * Check if a file is valid before uploading
     * 
     * @param array $file
     */
    public function isFileValid($file)
    {
        $targetFile = $file["name"];
            
        if (!$targetFile) {
            //Session::error('You provided an empty file');
            return false;
        }

        // Validate Image Size (1048576 = 1024 * 1024)
        if ($file['size'] > self::MAX_UPLOAD_SIZE * 1048576) {
            $message =  "$targetFile is too large. Our maixmum file upload is " . self::MAX_UPLOAD_SIZE . 'MB';
            Session::error($message);
            return false;
        }

        // Validate extensions
        $allowedExtensions = explode(',', self::ALLOWED_UPLOAD_EXTENSIONS);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (!in_array($imageFileType, $allowedExtensions)) {
            Session::error("We don't allow '$imageFileType' file types yet.");
            return false;
        }
        
        return true;
    }
    
    /**
     * Upload a file
     * 
     * @param string $identifier | key in  the $_FILES array
     * @param string $location | where the file will be saved
     * @param string $rename | optional, will rename the file to this
     */
    public function uploadFile($identifier, $location, $rename = null) 
    {
        $files = $this->getUploadedFiles();
        
        if (!array_key_exists($identifier, $files)) {
            Session::error("We couldn't find any file with the '$identifier' identifier");
        }
        
        $file = $files[$identifier];
        
        // Create directory
        if (!is_dir($location)) {
            mkdir($location, 0777, true);
        }

        $targetFile = $file["name"];

        
        if ($this->isFileValid($file)) {
            // Upload File
            $newFile = ($rename) ? $location . $rename : $location . $targetFile;
                
            if (!move_uploaded_file($file['tmp_name'], $newFile)) {
                Session::error("Could not upload $targetFile");
            }
        }            
    }
    
    /**
     * Set a cookie
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setCookie($name, $value)
    {
        $expires = time() + 365 * 60 * 60 * 24;
        $cookie = new SetCookie($name, $value, $expires, '/');
        $this->getServiceLocator()->get('response')->getHeaders()->addHeader($cookie);
    }
    
    /**
     * Retrieve a cookie
     * 
     * @param string $name
     * @return mixed
     */
    public function getCookie($name)
    {
        $cookie = $this->getServiceLocator()->get('request')->getHeaders()->get('Cookie');
        $value = false;
        
        if ($cookie && array_key_exists($name, get_object_vars($cookie))) {
            $value = $cookie->$name;
        }
        
        return $value;
    }
    
    /**
     * Delete a cookie
     * 
     * @param string $name
     */
    public function clearCookie($name)
    {
        $expires = time() - 365 * 60 * 60 * 24;
        $cookie = new SetCookie($name, '', $expires, '/');
        $this->getServiceLocator()->get('response')->getHeaders()->addHeader($cookie);
    }
}
