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
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignWidgetType")
     * @ORM\JoinColumn(name="widget_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $widgetType;
    
    /**
     * @ORM\Column(type="integer", name="widget_id", nullable=true)
     */
    protected $widgetId;
    
    /**
     * @ORM\Column(type="integer", name="earning_value")
     */
    protected $earningValue;
}
