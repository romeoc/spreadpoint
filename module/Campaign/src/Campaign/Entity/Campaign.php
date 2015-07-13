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
    
    const CAMPAIGN_TYPE_SINGLE = 1;
    const CAMPAIGN_TYPE_CYCLE = 2;
    
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
     */
    protected $startTime;
    
    /**
     * @ORM\Column(type="datetime", name="end_time", nullable=true)
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
     * @ORM\Column(type="smallint", length=1)
     */
    protected $type = self::CAMPAIGN_TYPE_SINGLE;
    
    /**
     * @ORM\Column(length=32)
     */
    protected $timezone;
    
    /**
     * @ORM\Column(type="integer", name="cycle_duration", nullable=true)
     */
    protected $cycleDuration;
    
    /**
     * @ORM\Column(type="integer", name="cycles_count", nullable=true)
     */
    protected $cyclesCount;
    
    /** 
     * @ORM\Column(type="text") 
     */
    protected $layout;
    
    /** 
     * @ORM\Column(type="text", nullable=true) 
     */
    protected $restrictions;
    
    /** 
     * @ORM\Column(type="text", name="terms_and_conditions", nullable=true) 
     */
    protected $termsAndConditions;
    
    /**
     * @ORM\Column(type="smallint", name="show_entrants", length=1)
     */
    protected $showEntrants = 0;
    
    /**
     * @ORM\Column(type="integer", name="age_requirement", nullable=true)
     */
    protected $ageRequirement;
    
    /** 
     * @ORM\Column(type="text", name="welcome_email", nullable=true) 
     */
    protected $welcomeEmail;
    
    /**
     * @ORM\Column(type="smallint", name="send_welcome_email", length=1)
     */
    protected $sendWelcomeEmail = 0;
    
    /**
     * @ORM\Column(type="smallint", name="retain_previous_entrants", length=1)
     */
    protected $retainPreviousEntrants = 0;
    
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
