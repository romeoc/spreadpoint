<?php

/**
 * Campaign Widget Entity
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/** 
 * @ORM\Entity 
 * @ORM\Table(name="campaign_widget")
 */
class CampaignWidget extends AbstractEntity
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_REMOVED = 3;
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $campaign;
    
    /**
     * @ORM\Column(type="integer", name="widget_type")
     */
    protected $widgetType;
    
    /** 
     * @ORM\Column(type="text", name="options_serialized")
     */
    protected $optionsSerialized;
    
    /**
     * @ORM\Column(type="integer", name="earning_value")
     */
    protected $earningValue;
    
    /**
     * @ORM\Column(type="smallint", length=1)
     */
    protected $status = self::STATUS_ACTIVE;
}
