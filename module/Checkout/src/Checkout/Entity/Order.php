<?php

/**
 * Order Entity
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/** 
 * @ORM\Entity 
 * @ORM\Table(name="sales_order")
 */
class Order extends AbstractEntity
{
    const PLAN_PUDDLE = 0;
    const PLAN_LAKE = 1;
    const PLAN_OCEAN = 2;
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /** 
     * @ORM\Column(length=128, unique=true) 
     */
    protected $email;
    
    /**
     * @ORM\Column(length=64) 
     */
    protected $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;
    
    /**
     * @ORM\Column(type="smallint", length=1)
     */
    protected $plan;
    
    /** 
     * @ORM\Column(length=16, name="payer_id", nullable=true) 
     */
    protected $payerId;
    
    /** 
     * @ORM\Column(length=16, name="profile_id") 
     */
    protected $profileId;
    
    /** 
     * @ORM\Column(length=32) 
     */
    protected $description;
    
    /** 
     * @ORM\Column(length=8) 
     */
    protected $amount;
    
    /**
     * @ORM\Column(length=32, name="start_date") 
     */
    protected $startDate;

    /**
     * @ORM\Column(length=16, name="billing_period")
     */
    protected $billingPeriod;
    
    /**
     * @ORM\Column(type="integer", length=4, name="billing_frequency")
     */
    protected $billingFrequency;
    
    /** 
     * @ORM\Column(length=16, name="correlation_id") 
     */
    protected $correlationId;
    
    /** 
     * @ORM\Column(type="datetime", name="created_at", nullable=true) 
     */
    protected $createdAt;
    
    public function beforeCreate()
    {
        if (is_null($this->get('createdAt'))) {
            $this->set('createdAt', new \DateTime());
        }
        
        return $this;
    }
}
