<?php
/**
 * Entrant Chance Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Base\Model\AbstractModel;

class ChanceModel extends AbstractModel
{
    // Initialize Entrant Chance Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignEntrantChance');
    }
}