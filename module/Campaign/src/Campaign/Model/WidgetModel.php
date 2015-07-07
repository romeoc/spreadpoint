<?php

/**
 * Campaign Widget Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Doctrine\ORM\Query;
use Base\Model\AbstractModel;

class WidgetModel extends AbstractModel
{
    // Initialize Widget Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignWidget');
    }
    
    /**
     * Return all available widget types
     * 
     * @return JSON (string)
     */
    public function getWidgetTypesJson()
    {
        $widgetTypes = $this->getEntityManager()
            ->getRepository("Campaign\Entity\CampaignWidgetType")
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        
        $this->prepareWidgetTypes($widgetTypes);
        return json_encode($widgetTypes);
    }
    
    /**
     * Prepare the widget types data so they can be used by the javascript libraries
     * 
     * @param array $widgetTypes
     */
    protected function prepareWidgetTypes(&$widgetTypes)
    {
        $result = array();
        foreach ($widgetTypes as $type) {
            $result[$type['id']] = $type['name'];
        }
        
        $widgetTypes = $result;
    }
    
    /**
     * Save widgets for a campaign
     * 
     * @param int $campaignId
     * @param array $data
     */
    public function saveWidgets($campaignId, $data)
    {
        
    }
    
    /**
     * Fetch all widgets for a campaign
     * 
     * @param int $campaignId
     * @return JSON (string)
     */
    public function getAppliedWidgets($campaignId)
    {
        return '[]';
    }
}
