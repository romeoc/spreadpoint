<?php

/**
 * Campaign Entrant Chance Entity
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
 * @ORM\Table(name="campaign_entrant_chance")
 */
class CampaignEntrantChance extends AbstractEntity
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignEntrant")
     * @ORM\JoinColumn(name="entrant_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entrant;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignWidget")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $widget;
    
    /** 
     * @ORM\Column(type="datetime", name="created_at", nullable=true) 
     */
    protected $earningDate;
    
    public function beforeCreate()
    {
        if (is_null($this->__get('earningDate'))) {
            $this->__set('earningDate', new \DateTime());
        }
        
        return $this;
    }
}
