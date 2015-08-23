<?php

/**
 * Campaign Entrant Entity
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
 * @ORM\Table(name="campaign_entrant")
 */
class CampaignEntrant extends AbstractEntity
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $campaign;
    
    /** 
     * @ORM\Column(length=256) 
     */
    protected $email;
    
    /** 
     * @ORM\Column(length=256) 
     */
    protected $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignEntrant")
     * @ORM\JoinColumn(name="reference", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reference;
    
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
