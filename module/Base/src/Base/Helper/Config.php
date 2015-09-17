<?php

namespace Base\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Config extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $config = array();

    public function getServiceLocator() 
    {
        return $this->service;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) 
    {
        $this->service = $serviceLocator->getServiceLocator();
    }
    
    public function get($config)
    {
        if (!$this->config) {
            $this->config = $this->getServiceLocator()->get('config');
        }
        
        $value = false;
        
        if (array_key_exists($config, $this->config)) {
            $value = $this->config[$config];
        }
        
        return $value;
    }
}
