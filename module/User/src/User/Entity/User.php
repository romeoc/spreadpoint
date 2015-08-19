<?php

/**
 * User Entity
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

use Base\Entity\AbstractEntity;

/** 
 * @ORM\Entity 
 * @ORM\Table(name="user")
 */
class User extends AbstractEntity
{
    const SALT = '3$fa^hW*a';
    const DEFAULT_PASSWORD = 'welovespreadpoint2015';
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /** 
     * @ORM\Column(length=128, unique=true) 
     * @Annotation\Type("Zend\Form\Element\Email")
     * @Annotation\Options({"label":"Email"})
     */
    protected $email;
    
    /** 
     * @ORM\Column(length=64) 
     * @Annotation\Exclude()
     */
    protected $password;
    
    /** 
     * @ORM\Column(length=64) 
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Firstname"})
     */
    protected $firstname;
    
    /** 
     * @ORM\Column(length=64) 
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Lastname"})
     */
    protected $lastname;
    
    /**
     * @ORM\Column(type="smallint", length=1)
     */
    protected $plan = -1;
    
    /**
     * @ORM\Column(type="smallint", length=1)
     */
    protected $notifications = 1;
    
    /**
     * @ORM\Column(length=32, nullable=true)
     */
    protected $recoveryCode;
    
    /** 
     * @ORM\Column(type="datetime", name="created_at", nullable=true) 
     * @Annotation\Attributes({"type":"text", "readonly":"true", "convertion_type":"dateTime"})
     * @Annotation\Options({"label":"Create At"})
     */
    protected $createdAt;
    
    /**
     * @ORM\Column(type="datetime", name="last_log_in", nullable=true) 
     * @Annotation\Attributes({"type":"text", "readonly":"true", "convertion_type":"dateTime"})
     * @Annotation\Options({"label":"Last Log In"})
     */
    protected $lastLogIn;
    
    public function beforeCreate()
    {
        if (empty($this->password)) {
            $this->password = self::DEFAULT_PASSWORD;
        }
        
        $password = $this->email. ':' . $this->password . ':' . self::SALT;
        $this->password = hash('sha256', $password);
        
        if (is_null($this->get('createdAt'))) {
            $this->set('createdAt', new \DateTime());
        }
        
        return $this;
    }
    
    public function beforeSave()
    {
        $this->set('lastLogIn', new \DateTime());
        return $this;
    }
}
