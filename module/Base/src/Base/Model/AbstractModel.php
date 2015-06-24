<?php

namespace Base\Model;

/**
 * Abstract Model
 *
 * Module: Application
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 *
 */

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class AbstractModel implements ServiceLocatorAwareInterface
{
    protected $service;

    protected $entity;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->service;
    }

    public function init($entity)
    {
        $this->entity = $entity;
    }

    public function save($data)
    {
        $this->saveFiles();
        if (!empty($data['id'])) {
            $this->update($data);
        } else {
            $this->create($data);
        }
    }
    
    public function saveFiles()
    {
        $path = 'public/media/uploads/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        foreach ($_FILES as $file) {
            $httpadapter = new \Zend\File\Transfer\Adapter\Http(); 
            $filesize  = new \Zend\Validator\File\Size(array('max' => '10MB' ));
            $extension = new \Zend\Validator\File\Extension(array('extension' => array('jpg','png','mp3','aac')));
            $httpadapter->setValidators(array($filesize, $extension), $file['name']);
            if ($httpadapter->isValid()) {
                $httpadapter->setDestination($path);
                $httpadapter->receive($file['name']);
            }
        }
    }

    public function create($data)
    {
        $entity = new $this->entity();
        $service = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $this->setData($entity, $data);
        $entity->beforeSave();
        $entity->beforeCreate();

        try {
            $service->persist($entity);
            $service->flush();
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function update($data)
    {
        $service = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $entity = $service->find($this->entity, $data['id']);

        unset($data['id']);
        $this->setData($entity, $data);
        $entity->beforeSave();
        $entity->beforeUpdate();

        $service->flush();
    }

    public function delete($id)
    {
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $entity = $entityManager->find($this->entity, $id);
        $entityManager->remove($entity);

        $entity->beforeDelete();
        $entityManager->flush();
    }

    public function setData($entity, $data)
    {
        $this->clearRelations($entity);
        $object = $this->getEntityObject();
        foreach ($data as $key => $value) {
            if (empty($value) || (is_array($value) && empty($value[0]))) {
                $value = null;
            }
            $annotation = $object->get($key);
            if (!empty($annotation) && $annotation->hasAttribute('convertion_type')) {
                $convertionClass = $annotation->getAttribute('convertion_class');
                switch ($annotation->getAttribute('convertion_type')){
                    case ('dateTime'):
                        if (!empty($value)) {
                            $date = new \DateTime($value);
                            $date->format('Y-m-d H:i:s');
                            $entity->__set($key, $date);
                        }
                        break;
                    case ('manyToOne'):
                        if (!empty($value)) {
                            $entity->__set($key, $this->getOneToManyValue($convertionClass,$value));
                        } else {
                            $entity->__set($key, null);
                        }
                        break;
                    case ('manyToMany'):
                        if (!empty($value)) {
                            $this->setManyToManyValue($convertionClass, $value, $entity->__get($key));
                        } else {
                            $entity->__set($key, null);
                        }
                        break;
                    case ('oneToMany'):
                        $entity->__set($key, null);
                        break;
                }
            } else {
                $entity->__set($key, $value);
            }
        }
    }

    protected function clearRelations($entity)
    {
        $object = $this->getEntityObject();
        foreach ($object as $element) {
            if ($element->hasAttribute('convertion_type') && $element->getAttribute('convertion_type') == 'manyToMany') {
                $entity->__get($element->getName())->clear();

            }
        }
    }

    protected function getEntityObject()
    {
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $entity = new $this->entity();
        $builder = new AnnotationBuilder($entityManager);
        $form = $builder->createForm($entity);
        $form->setHydrator(new DoctrineHydrator($entityManager,$this->entity));
        $form->bind($entity);

        return $form;
    }

    public function setManyToManyValue($entity, $data, $element)
    {
        if (!empty($data)) {
            $service = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            foreach ($data as $value) {
                $entry = $service->find($entity, $value);
                $element->add($entry);
            }
        }
    }

    public function getOneToManyValue($entity, $id)
    {
        $parts = explode('\\',$entity);
        $length = count($parts);
        $entity = $parts[$length-3].'\\'.$parts[$length-2].'\\'.$parts[$length-1];

        $service = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return $service->find($entity, $id);

    }
}
