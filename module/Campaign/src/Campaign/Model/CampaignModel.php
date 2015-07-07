<?php

/**
 * Campaign Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Zend\Session\Container;

use Base\Model\AbstractModel;
use Base\Model\Session;

use Campaign\Model\PrizeModel;
use Campaign\Model\WidgetModel;
use Campaign\Entity\Campaign as CampaignEntity;
use User\Helper\UserHelper;

class CampaignModel extends AbstractModel
{
    // Initialize Campaign Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\Campaign');
    }
    
    /**
     * Process the data received from the controller and send every data to it's
     * corresponding helper
     * 
     * @param array $data
     * @return int
     */
    public function process($data)
    {
        $this->uploadFiles($data);
        
        // Widgets Data
        $widgetsData = $data['widgets-serialized'];
        unset($data['widgets-serialized']);

        // Prizes Data
        $prizeData = $data['prizes-serialized'];
        unset($data['prizes-serialized']);
        
        // Initialize Models
        $widgetModel = new WidgetModel();
        $widgetModel->setServiceLocator($this->getServiceLocator());

        $prizeModel = new PrizeModel();
        $prizeModel->setServiceLocator($this->getServiceLocator());
        
        // Save the campaign data
        $campaign = $this->saveCampaign($data);
        $campaignId = false;
        if ($campaign) {
            $campaignId = $campaign->__get('id');
            
            // If we managed to save the campaign succesfully then we save the widgets and the prizes
            $widgetsResult = $widgetModel->saveWidgets($campaignId, $widgetsData);
            $prizeResult = $prizeModel->savePrizes($campaignId, $prizeData);
            
            if (!$widgetsResult || !$prizeResult) {
                $this->updateStatus($campaignId, CampaignEntity::STATUS_PAUSED);
            } else {
                Session::success('Your campaign was succesfully saved and activated');
            }
        }
        
        return $campaignId;
    }
    
    /**
     * Save campaign
     * 
     * @param array $data
     * @return Campaign\Entity\Campaign | bool
     */
    public function saveCampaign($data)
    {
        if (!$this->validate($data)) {
            return false;
        }
        
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        
        $data['user'] = $helper->getLoggedInUser();
        if (!array_key_exists('status', $data)) {
            $data['status'] = CampaignEntity::STATUS_ACTIVE;
        }
        
        $result = $this->save($data);
        return $result;
    }
    
    /**
     * Validate Campaign Data
     * 
     * @param *array $data
     * @return boolean
     */
    protected function validate(&$data)
    {
        $errosFound = false;
        
        foreach ($data as $key => $value) {
            if (!property_exists('Campaign\Entity\Campaign', $key)) {
                unset($data[$key]);
            }
        }
        
        if (!array_key_exists('title', $data) || !$data['title']) {
            Session::error("You didn't provide a <strong>'Title'</strong> for your campign");
            $errosFound = true;
        } elseif (strlen($data['title']) > 32) {
            Session::error("The maximum <strong>'title'</strong> length is <strong>32</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('description', $data) || !$data['description']) {
            Session::error("You didn't provide a <strong>'Description'</strong> for your campign");
            $errosFound = true;
        } elseif (strlen($data['description']) > 500) {
            Session::error("The maximum <strong>'Description'</strong> length is <strong>500</strong> characters");
            $errosFound = true;
        }
        
        if (!array_key_exists('startTime', $data) || !$data['startTime']) {
            Session::error("You didn't provide a <strong>'Start Time'</strong> for your campign");
            $errosFound = true;
        }
        
        if (!array_key_exists('endTime', $data) || !$data['endTime']) {
            Session::error("You didn't provide an <strong>'End Time'</strong> for your campign");
            $errosFound = true;
        }
        
        if (!array_key_exists('banner', $data) || !$data['banner']) {
            Session::error("You didn't provide a <strong>'Banner'</strong> for your campign");
            $errosFound = true;
        }
        
        if (array_key_exists('titleCss', $data) && $data['titleCss'] && strlen($data['titleCss']) > 255) {
            Session::error("The maximum allowed<strong>'Title Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        if (array_key_exists('descriptionCss', $data) && $data['descriptionCss'] && strlen($data['descriptionCss']) > 255) {
            Session::error("The maximum allowed<strong>'Description Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        return !$errosFound;
    }
    
    /**
     * Upload all files from the session
     * 
     * @param *array $data
     */
    protected function uploadFiles(&$data)
    {
        
    }
    
    /**
     * Update the status for a campaign
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
     * Fetch the data for a campaign Id
     * if the campaign id is null check for session data
     * else return the default data
     * 
     * @param int|null $campaignId
     * @return array
     */
    public function fetchData($campaignId) 
    {
        $data = array();
        
        if ($campaignId) {
            // If a campaign Id was specified get then fetch the campaigns data
            $data = $this->load($campaignId);
        } else {
            // If some data was set on the save action
            $session = new Container('campaign');
            if ($session->data) {
                $data['data'] = $session->data;
                $session->offsetUnset('data');

                $widgetModel = new WidgetModel();
                $widgetModel->setServiceLocator($this->getServiceLocator());

                $data['entriesData'] = array(
                    'widgetTypes' => $widgetModel->getWidgetTypesJson(),
                    'appliedWidgets' => $data['data']['widgets-serialized'],
                );
                
                $data['prizesData'] = $data['data']['prizes-serialized'];
            }
        }

        // If we still have no data then return the default data
        if (!$data) {
            $data = $this->getDefaultData();
        }
        
        return $data;
    }
    
    /**
     * Fetch the default campaign form data
     * 
     * @return array
     */
    public function getDefaultData()
    {
        // Initialize Models
        $widgetModel = new WidgetModel();
        $widgetModel->setServiceLocator($this->getServiceLocator());
        
        return array(
            'messages' => \Base\Model\Session::getGlobalMessages(),
            'data' => array(),
            'entriesData' => array(
                'widgetTypes' => $widgetModel->getWidgetTypesJson(),
                'appliedWidgets' => '[]',
            ),
            'prizesData' => array(),
        );
    }
}
