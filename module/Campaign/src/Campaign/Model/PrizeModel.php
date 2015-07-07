<?php

/**
 * Campaign Prize Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Base\Model\AbstractModel;

class PrizeModel extends AbstractModel
{
    // Initialize Campaign Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignPrize');
    }
    
    /**
     * Save prizes for a campaign
     * 
     * @param int $campaignId
     * @param array $data
     */
    public function savePrizes($campaignId, $data)
    {
        
    }
}
