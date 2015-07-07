<?php

/**
 * Campaign Prize Entity
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
 * @ORM\Table(name="campaign_prize")
 */
class CampaignPrize extends AbstractEntity
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\Column(length=32)
     */
    protected $name;
    
    /**
     * @ORM\Column(length=16)
     */
    protected $image;
    
    /** 
     * @ORM\Column(type="text") 
     */
    protected $description;
    
    /**
     * @ORM\Column(type="integer", name="widget_id", nullable=true)
     */
    protected $count;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $campaign;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignEntrant")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $winner;
}