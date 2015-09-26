<?php

namespace Base\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Base extends AbstractHelper implements ServiceLocatorAwareInterface
{
    public function getServiceLocator() 
    {
        return $this->service;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) 
    {
        $this->service = $serviceLocator->getServiceLocator();
    }
    
    public function getDomain()
    {
        $uri = $this->getServiceLocator()->get('request')->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }
    
    public function getBaseUrl($path)
    {
        return $this->getDomain() . '/' . $path;
    }
}
