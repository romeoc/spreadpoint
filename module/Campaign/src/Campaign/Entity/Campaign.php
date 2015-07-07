<?php

/**
 * Campaign Entity
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/** @ORM\Entity */
class Campaign extends AbstractEntity
{
    const STATUS_ACTIVE = 1;
    const STATUS_PAUSED = 2;
    const STATUS_FINISHED = 3;
    const STATUS_CANCELED = 4;
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;
    
    /**
     * @ORM\Column(length=32)
     */
    protected $title;
    
    /** 
     * @ORM\Column(type="text") 
     */
    protected $description;
    
    /**
     * @ORM\Column(type="datetime", name="start_time")
     * @ORM\Version
     */
    protected $startTime;
    
    /**
     * @ORM\Column(type="datetime", name="end_time")
     * @ORM\Version
     */
    protected $endTime;
    
    /**
     * @ORM\Column(length=16)
     */
    protected $banner;
    
    /** 
     * @ORM\Column(type="text", nullable=true) 
     */
    protected $titleCss;
    
    /** 
     * @ORM\Column(type="text", nullable=true) 
     */
    protected $descriptionCss;
    
    /**
     * @ORM\Column(type="smallint", length=1)
     */
    protected $status = self::STATUS_ACTIVE;
    
    /** 
     * @ORM\Column(type="datetime", name="created_at", nullable=true) 
     */
    protected $createdAt;
    
    public function beforeCreate()
    {
        if (is_null($this->__get('createdAt'))) {
            $this->__set('createdAt', new \DateTime());
        }
        
        return $this;
    }
}
