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

use Campaign\Entity\CampaignWidget;
use Base\Model\AbstractModel;
use Base\Model\Session;

class WidgetModel extends AbstractModel
{
    /**
     * The dault widget ID. This widget is required for any campaign.
     */
    const DEAFULT_WIDGET_ID = 1;
 
    /**
     * All available widgets
     *
     * @var array
     */
    protected $widgetsMap = array(
        1 => 'Enter Contest',
        2 => 'Facebook Like',
        3 => 'Facebook Share',
        4 => 'Twitter Tweet',
        5 => 'Twitter Follow',
        6 => 'Reference'
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
        $this->removeEliminatedWidgets($campaign->get('id'), $widgets);
        
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
            unset($options['title']);

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
        
        return $allValid;
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
                $widgetIds = array_diff($widgetIds, [$widget['id']]);
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
        } elseif ($data['earningValue'] > 10000) {
            Session::error("Widgets Earning Values cannot be greater than 10000!");
            $errorsFound = true;
        }
        
        if (!array_key_exists('title', $data) || empty($data['title'])) {
            Session::error("Every widget must have a title!");
            $errorsFound = true;
        } elseif ($data['title'] > 32) {
            Session::error("The widget Title cannot be longer than 32 characters!");
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
     * @return array
     */
    public function getAppliedWidgets($campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $widgets = $queryBuilder->select('e')
            ->from($this->entity, 'e')
            ->where('e.campaign= :campaign')
            ->andWhere('e.status= :status')
            ->setParameter('campaign', $campaignId)
            ->setParameter('status', CampaignWidget::STATUS_ACTIVE)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        
        return $widgets;
    }
    
    /**
     * Fetch all widgets for a campaign in JSON format
     * 
     * @param int $campaignId
     * @return JSON (string)
     */
    public function getAppliedWidgetsForJavaScript($campaignId)
    {
        $widgets = $this->getAppliedWidgets($campaignId);
        
        if ($widgets) {
            $count = 1;
            foreach ($widgets as &$widget) {
                $optionsSerialized = $widget['optionsSerialized'];
                $optionsSerialized = unserialize($optionsSerialized);
                unset($widget['optionsSerialized']);

                $widget = array_merge($widget, $optionsSerialized);
                $widget['referenceId'] = $count++;
            }
        }
        
        return json_encode($widgets);
    }
    
    /**
     * Get all widgets for the "entrant" cookie or return de "default" widget otherwise
     * @param int $campaignId
     * @return JSON (string)
     */
    public function getAppliedWidgetsForEntrant($campaignId)
    {
        $widgets = $this->getAppliedWidgets($campaignId);
        $entrant = $this->getCookie('entrant');
        $completedWidgets = array();
        
        if ($entrant) {
            $changeModel = new ChanceModel();
            $changeModel->setServiceLocator($this->getServiceLocator());
            $completedWidgets = $changeModel->getCompletedWidgetIds($entrant);
        }
        
        foreach ($widgets as $key => &$widget) {
            // We will show only the default widget if no entrant is set 
            // and remove the default widget if the entrant is set
            if (($entrant && $widget['widgetType'] == self::DEAFULT_WIDGET_ID) 
                    || (!$entrant && $widget['widgetType'] != self::DEAFULT_WIDGET_ID)) {
                unset($widgets[$key]);
                continue;
            }
            
            $optionsSerialized = $widget['optionsSerialized'];
            $optionsSerialized = unserialize($optionsSerialized);
            unset($widget['optionsSerialized']);
            
            $widget['completed'] = in_array($widget['id'], $completedWidgets);
            $widget = array_merge($widget, $optionsSerialized);
        }
        
        return json_encode($widgets);
    }
}
