<?php

/* 
 *  User Helper
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Checkout\Entity\Order;

class UserHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $service;
    
    public function getServiceLocator() 
    {
        return $this->service;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) 
    {
        $this->service = $serviceLocator->getServiceLocator();
    }
    
    public function updateServiceLocator($service)
    {
        $this->service = $service;
    }
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
    
    public function isLoggedIn()
    {
        $auth = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        return $auth->hasIdentity();
    }
    
    public function getUsername()
    {
        $service = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $username = false;
        
        if ($service->hasIdentity()) {
            $entity = $service->getIdentity();
            $username = $entity->get('firstname');
        }
        
        return $username;
    }
    
    public function getLoggedInUserId()
    {
        $service = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $id = false;
        
        if ($service->hasIdentity()) {
            $entity = $service->getIdentity();
            $id = $entity->get('id');
        }
        
        return $id;
    }
    
    public function getLoggedInUser()
    {
        $service = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $entity = false;
        
        if ($service->hasIdentity()) {
            $entity = $service->getIdentity();
        }
        
        return $entity;
    }
    
    public function getActiveOrder($user = null)
    {
        $user = ($user) ? $user : $this->getLoggedInUser();
        $order = null;
        
        if ($user) {
            $order = $this->getEntityManager()->createQueryBuilder()
                ->select('o')
                ->from('Checkout\Entity\Order', 'o')
                ->where('o.user= :user')
                ->andWhere('o.status= :status')
                ->setParameter('user', $user)
                ->setParameter('status', Order::STATUS_ACTIVE)
                ->getQuery()
                ->setMaxResults(1)
                ->getResult();
        }
        
        if ($order) {
            $order = $order[0];
        }
        
        return $order;
    }
}
