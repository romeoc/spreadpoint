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
use Base\Model\Mail;

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
            return $this->authenticate($data['email'], $data['password']);
        }
    }
    
    public function authenticate($username, $password)
    {
        $password = $username . ':' . $password . ':' . UserEntity::SALT;
        $password = hash('sha256', $password);
        
        $auth = $this->service->get('doctrine.authenticationservice.orm_default');
        $auth->getAdapter()->setIdentityValue($username);
        $auth->getAdapter()->setCredentialValue($password);

        if ($auth->authenticate()->isValid()) {
            $entity = $auth->getIdentity();
            return $entity->get('firstname');
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
    
    public function prepare(&$data)
    {
        $data['notifications'] = array_key_exists('notifications', $data);
    }
    
    public function initiatePasswordReset($email)
    {
        $user = $this->loadByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $code = md5($email . bin2hex(openssl_random_pseudo_bytes(4)));
        $user->set('recoveryCode', $code);
        $this->getEntityManager()->flush();
        
        $this->sendPasswordResetEmail($email, $user->get('name'), $code);
        
        return true;
    }
    
    public function sendPasswordResetEmail($email, $name, $code)
    {
        $uri = $this->getServiceLocator()->get('request')->getUri();
        $domain = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        $resetUrl = $domain . "account/reset/key/{$code}";
        
        $subject = 'SpreadPoint - Reset Your Password';
        $body = 'To reste your password, please visit the following link: '
                . PHP_EOL . $resetUrl;
                
        Mail::send($body, $subject, Mail::EMAIL, Mail::NAME, $email, $name);
    }
    
    public function loadByEmail($email)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from($this->entity, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function loadByResetCode($code)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from($this->entity, 'u')
            ->where('u.recoveryCode = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function changePassword($email, $password) 
    {
        $user = $this->loadByEmail($email);
        
        if ($user) {
            $password = $email . ':' . $password . ':' . UserEntity::SALT;
            $password = hash('sha256', $password);
            
            $user->set('password', $password);
            $user->set('recoveryCode', null);
            
            $this->getEntityManager()->flush();
            
            return true;
        }
        
        return false;
    }
}
