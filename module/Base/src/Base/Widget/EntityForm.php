<?php

namespace Base\Widget;

/**
 * Create a form for an entity
 * 
 * Module: Application
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 * 
 */

use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class EntityForm implements ServiceLocatorAwareInterface
{
    protected $service;
    
    protected $entity;
    
    protected $id;
    
    protected $form;
    
    protected $parentId;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->service;
    }
    
    public function __construct($service, $entity, $id = null, $parentId = null)
    {
        $this->entity = $entity;
        $this->setServiceLocator($service);
        $this->id = $id;
        $this->parentId = $parentId;
    }
    
    public function getForm()
    {
        $this->prepareForm();
        return $this->form;
    }
    
    public function toHtml()
    { 
        $this->prepareForm();
        
        $controller = explode('\\', $this->entity);
        $controller = strtolower(end($controller));
        
        $view =  new ViewModel(array(
            'form'          => $this->form,
            'controller'    => $controller,
            'entity_id'     => $this->id,
            'parentEntity'  => $this->getParentEntity($controller),
            'parentId'      => $this->parentId
        ));
        
        $view->setTemplate('widget/form');

        return $view;
    }
    
    protected function getParentEntity($entity)
    {
        switch ($entity) {
            case "language": 
                return null; 
                break;
            case "level": 
                return "language";
                break;
            case "lesson":
                return "level";
                break;
            case "module":
                return "lesson";
                break;
            case "exercise":
                return "module";
                break;
        }
    }
    
    protected function prepareForm()
    {
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        
        if (!empty($this->id)) {
            $repository = $entityManager->getRepository($this->entity);
            $entity = $repository->find($this->id);
        } else {
            $entity = new $this->entity();
        }
      
        $builder = new AnnotationBuilder($entityManager);
        $form = $builder->createForm($entity);
        $form->setHydrator(new DoctrineHydrator($entityManager,$this->entity));
        $form->bind($entity);

        $this->setFormValues($form);
        
        if (!empty($this->id)) {
            $id = new \Zend\Form\Element\Hidden('id');
            $id->setValue($this->id);
            $form->add($id);
        }
        
        $this->form = $form;
    }
    
    protected function setFormValues($form)
    {
        $object = $form->getObject(); 
        foreach ($form as $element) {
            $data = $object->__get($element->getName());
            if ($element->hasAttribute('convertion_type')) {
                $convertionClass = $element->getAttribute('convertion_class');
                switch ($element->getAttribute('convertion_type')){
                    case ('dateTime'): 
                        if (!empty($data)) {
                            $data = $data->format('Y-m-d H:i:s');
                            $element->setValue($data);
                        }
                        break;
                    case ('manyToOne'):
                        $elementId = (!empty($data)) ? $data->__get('id') : null;
                        $options = $this->getOneToManyOptions($convertionClass,$elementId);
                        $element->setValueOptions($options);
                        break;
                    case ('manyToMany'):
                        $options = $this->getManyToManyOptions($convertionClass, $data);
                        $element->setValueOptions($options); 
                        break;
                    case ('oneToMany'):
                        break;
                }
            } else {
                if(!is_object($data)){
                    $element->setValue($data);
                }

            }
            
            if ($element->getAttribute('type') == 'select' && $element->getAttribute('multiple') != 'multiple') {
                $element->setEmptyOption('-- Plese select an option --');
            }
        }
    }
    
    protected function getManyToManyOptions($entity, $data)
    {
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $entries = $entityManager->createQuery("SELECT u FROM $entity u")->getResult();
        
        $selectedIds = array();
        $options = array();
        
        foreach ($data as $entry) {
            $selectedIds[] = $entry->__get('id');
        }
        
        foreach ($entries as $entry) {
            $id = $entry->__get('id');
            $options[$id] = array(
                'value' => $id,
                'label' => $entry->__get('name'),
                'selected' => (in_array($id, $selectedIds)),
            );
        }
        
        return $options;
    }
    
    protected function getOneToManyOptions($entity, $selectedId = null)
    {
        $parts = explode('\\',$entity);
        $length = count($parts);
        $entity = $parts[$length-3].'\\'.$parts[$length-2].'\\'.$parts[$length-1];
        
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $entries = $entityManager->createQuery("SELECT u FROM $entity u")->getResult();
        
        $options = array();
        
        foreach ($entries as $entry) {
            $id = $entry->__get('id');
            $options[$id] = array(
                'value' => $id,
                'label' => $entry->__get('name'),
                'selected' => ($id == $selectedId),
            );
            if (!$entry->__isset('name')){
                $options[$id]['label'] = $entry->__get('email');
            }
        }
        
        return $options;
    }

}