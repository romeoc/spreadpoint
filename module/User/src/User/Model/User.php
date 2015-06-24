<?php

/**
 * User Model
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Model;

use Base\Model\AbstractModel;
use User\Entity\User as UserEntity;
use Zend\Form\Element;

class User extends AbstractModel
{    
    public function __construct()
    {
        $this->init('User\Entity\User');
    }
    
    public function create($data)
    {
        if (parent::create($data)) {
            return $this->authenticate($data['email'], hash('sha512',$data['password']));
        }
    }
    
    public function authenticate($username, $password)
    {
        $password = $username. ':' . $password . ':' . UserEntity::SALT;
        $auth = $this->service->get('doctrine.authenticationservice.orm_default');
        $auth->getAdapter()->setIdentityValue($username);
        $auth->getAdapter()->setCredentialValue($password);

        if ($auth->authenticate()->isValid()) {
            $entity = $auth->getIdentity();
            return $entity->__get('firstname');
        } 
        
        return false;
    }
    
    protected function getEntityObject()
    {
        $form = parent::getEntityObject();
        $password = new Element\Password('password');
        $password->setLabel('Password')
                 ->setAttributes(array(
                    'required'  => 'required',
            )
        );
        $form->add($password);
        return $form;
    }
}
