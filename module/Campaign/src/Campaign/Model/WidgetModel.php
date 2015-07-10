<?php

/**
 * Campaign Widget Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Campaign\Entity\CampaignWidget;
use Base\Model\AbstractModel;
use Base\Model\Session;

class WidgetModel extends AbstractModel
{
    const DEAFULT_WIDGET_ID = 1;
    
    protected $widgetsMap = array(
        1 => 'Enter Contest',
        2 => 'Facebook Page Like',
    );
    
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
        return json_encode($this->widgetsMap);
    }
    
    /**
     * Save widgets for a campaign
     * 
     * @param Campaign\Entity\Campaign $campaign
     * @param string(JSON) $data
     * @return bool | success or not
     */
    public function saveWidgets($campaign, $data)
    {
        $widgets = json_decode($data, true);
        $this->removeEliminatedWidgets($campaign->__get('id'), $widgets);
        
        $allValid = true;
        $hasDefaultWidget = false;
        foreach ($widgets as $widget) {
            // Skip empty values
            if (!$widget) {
                continue;
            }
            
            // We don't need the referenceId, this is only used by the javascript controller
            unset($widget['referenceId']);

            // Prepare options data
            $options = $widget;
            unset($options['widgetType']);
            unset($options['earningValue']);

            // Set additional data
            $widget['optionsSerialized'] = serialize($options);
            $widget['campaign'] = $campaign;
            
            // We need at least one default widget for the campaign to run
            if ($widget['widgetType'] == self::DEAFULT_WIDGET_ID) {
                $hasDefaultWidget = true;
            }
            
            // Save the widget if all data are valid
            if ($this->validate($widget)) {
                $saveResult = $this->save($widget);
                if (!$saveResult) {
                    Session::error("One of your widgets was not saved properly!");
                    $allValid = false;
                } 
            } else {
                $allValid = false;
            }
        }
        
        // We check if at least one default widget was found
        if (!$hasDefaultWidget) {
            $defaultWidgetName = $this->widgetsMap[self::DEAFULT_WIDGET_ID];
            Session::error("Your campaign must have an <strong>$defaultWidgetName</strong> widget!");
            return false;
        }
        
        // If all widgets were succesfully saved
        if ($allValid) {
            Session::success("All your widgets were succesfully saved");
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove the widgets that were eliminated from the database
     *
     * @param int $campaignId 
     * @param array $data
     */
    protected function removeEliminatedWidgets($campaignId, $data)
    {
        // We fetch all widget ids for our campaign
        $allCampaignWidgets = $this->getEntityManager()
            ->createQuery("SELECT e.id FROM $this->entity e WHERE e.campaign = $campaignId")
            ->getScalarResult();
        
        $widgetIds = array_map('current', $allCampaignWidgets);
        
        // Then we remove from this list all widget ids that we received from the request
        foreach ($data as $widget) {
            if ($widget && array_key_exists('id', $widget)) {
                array_diff($widgetIds, $widget['id']);
            }
        }
        
        // Everything that is left was removed by the user so we set it's status to "removed"
        // We don't delete the widget because we still need the data for analysis
        foreach ($widgetIds as $widgetId) {
            $this->updateStatus($widgetId, CampaignWidget::STATUS_REMOVED);
        }
    }
    
    /**
     * Update the status for a widget
     * 
     * @param int $entityId
     * @param int $status
     */
    public function updateStatus($entityId, $status) 
    {
        $data = array (
            'id' => $entityId,
            'status' => $status
        );
        
        $this->update($data);
    }
    
    /**
     * Validate widget data before saving
     * 
     * @param array $data
     * @return bool
     */
    protected function validate($data) 
    {
        $errorsFound = false;
        
        foreach ($data as $key => $value) {
            if (!property_exists($this->entity, $key)) {
                unset($data[$key]);
            }
        }
        
        if (!array_key_exists('campaign', $data) || get_class($data['campaign']) != 'Campaign\Entity\Campaign') {
            Session::error("An invalid campaign was specified to one of the widgets!");
            $errorsFound = true;
        }
        
        if (!array_key_exists('widgetType', $data) || !array_key_exists($data['widgetType'], $this->widgetsMap)) {
            Session::error("You are trying to add an invalid widget type!");
            $errorsFound = true;
        }
        
        if (!array_key_exists('earningValue', $data) || empty($data['earningValue'])) {
            Session::error("Every widget must have an earning value!");
            $errorsFound = true;
        } elseif (!is_numeric($data['earningValue'])) {
            Session::error("Widget earning values must be numeric!");
            $errorsFound = true;
        } elseif ($data['earningValue'] <= 0) {
            Session::error("Widgets Earning Values must be greater than 0!");
            $errorsFound = true;
        } else if ($data['earningValue'] > 10000) {
            Session::error("Widgets Earning Values cannot be greater than 10000!");
            $errorsFound = true;
        }
        
        if (array_key_exists('optionsSerialized', $data) && strlen($data['optionsSerialized']) > 10000) {
            Session::error("You have to many options on your widget!");
            $errorsFound = true;
        }
        
        return !$errorsFound;
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
