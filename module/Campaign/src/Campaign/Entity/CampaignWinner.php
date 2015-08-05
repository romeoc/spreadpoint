<?php

/**
 * Campaign Winner Entity
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
 * @ORM\Table(name="campaign_winner")
 */
class CampaignWinner extends AbstractEntity
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignPrize")
     * @ORM\JoinColumn(name="prize_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $prize;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign\Entity\CampaignEntrant")
     * @ORM\JoinColumn(name="entrant_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entrant;
}
