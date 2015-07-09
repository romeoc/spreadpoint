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
        $widgetsData = '';
        if (array_key_exists('widgets-serialized', $data) && $data['widgets-serialized']) {
            $widgetsData = $data['widgets-serialized'];
        } else {
            Session::error("Your users have no ways to participate, please add some widgets");
        }
        unset($data['widgets-serialized']);

        // Prizes Data
        $prizeData = '';
        if (array_key_exists('prizes-serialized', $data) && $data['prizes-serialized']) {
            $prizeData = $data['prizes-serialized'];
        } else {
            Session::error("Your users have nothing to win, please add some prizes");
        }
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
            Session::success("Your campaign was successfully saved");
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
        
        $data['startTime'] = new \DateTime($data['startTime']);
        $data['endTime'] = new \DateTime($data['endTime']);
        
        $data['showEntrants'] = (array_key_exists('showEntrants', $data)) ? 1 : 0;
        $data['sendWelcomeEmail'] = (array_key_exists('sendWelcomeEmail', $data)) ? 1 : 0;
        
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
        
        if (!array_key_exists('banner', $data) || !$data['banner']) {
            Session::error("You didn't provide a <strong>'Banner'</strong> for your campign");
            $errosFound = true;
        }
        
        if (array_key_exists('titleCss', $data) && $data['titleCss'] && strlen($data['titleCss']) > 255) {
            Session::error("The maximum allowed <strong>'Title Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        if (array_key_exists('descriptionCss', $data) && $data['descriptionCss'] && strlen($data['descriptionCss']) > 255) {
            Session::error("The maximum allowed <strong>'Description Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        if (!array_key_exists('layout', $data) || !$data['layout']) {
            Session::error("Please choose a layout for your campaign");
            $errosFound = true;
        } elseif ($data['layout'] != 1 && $data['layout'] != 2) {
            Session::error("You specified an invalid layout");
            $errosFound = true;
        }
        
        if (array_key_exists('termsAndConditions', $data) && $data['termsAndConditions'] && strlen($data['termsAndConditions']) > 50000) {
            Session::error("There is a limit of <strong>50000</strong> characters to the <strong>'Terms & Conditions'</strong> field");
            $errosFound = true;
        }
        
        if (array_key_exists('sendWelcomeEmail', $data) && !(array_key_exists('welcomeEmail', $data) && $data['welcomeEmail'])) {
            Session::error("Please provide a <strong>Welcome Email</strong> or uncheck <strong>'Send Welcome Email'</strong>");
            $errosFound = true;
        }
        
        if (array_key_exists('welcomeEmail', $data) && $data['welcomeEmail'] && strlen($data['welcomeEmail']) > 20000) {
            Session::error("There is a limit of <strong>20000</strong> characters to the <strong>'Welcome Email'</strong> field");
            $errosFound = true;
        }
        
        if (array_key_exists('ageRequirement', $data) && $data['ageRequirement'] && strlen($data['ageRequirement']) > 2) {
            Session::error("Invalid <strong>Age Requirement</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('type', $data) || !$data['type'] 
                || ($data['type'] != CampaignEntity::CAMPAIGN_TYPE_SINGLE 
                && $data['type'] != CampaignEntity::CAMPAIGN_TYPE_CYCLE)) {
            Session::error("Invalid <strong>Competition Type</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('startTime', $data) || !$data['startTime']) {
            Session::error("You didn't provide a <strong>'Start Time'</strong> for your campign");
            $errosFound = true;
        } elseif (!$this->isValidDateTime($data['startTime'])) {
            Session::error("Invalid <strong>'Start Time'</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('timezone', $data) || !$data['timezone']) {
            Session::error("You didn't provide a <strong>'Time Zone'</strong> for your campign");
            $errosFound = true;
        }
        
        if (array_key_exists('type', $data) && $data['type'] == CampaignEntity::CAMPAIGN_TYPE_SINGLE) {
            if (!array_key_exists('endTime', $data) || !$data['endTime']) {
                Session::error("You didn't provide an <strong>'End Time'</strong> for your campign");
                $errosFound = true;
            } elseif (!$this->isValidDateTime($data['endTime'])) {
                Session::error("Invalid <strong>'End Time'</strong>");
                $errosFound = true;
            }
        }
        
        if (array_key_exists('type', $data) && $data['type'] == CampaignEntity::CAMPAIGN_TYPE_CYCLE 
                && (!array_key_exists('cycleDuration', $data) || !$data['cycleDuration'])) {
            Session::error("You didn't provide a <strong>'Campaign Cycle Duration'</strong> for your campign");
            $errosFound = true;
        }
        
        if (array_key_exists('type', $data) && $data['type'] == CampaignEntity::CAMPAIGN_TYPE_CYCLE 
                && (!array_key_exists('cyclesCount', $data) || !$data['cyclesCount'])) {
            Session::error("You didn't provide a <strong>'Campaign Cycle Count'</strong> for your campign");
            $errosFound = true;
        }
        
        return !$errosFound;
    }
    
    public function isValidDateTime($datetime, $format = 'Y-m-d\TH:i') 
    {
        $temp = \DateTime::createFromFormat($format, $datetime);
        return $temp && $temp->format($format) == $datetime;
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
            /** 
             * If some data was set on the save action 
             * Saving the campaign must fail for the session to have data
             * but the widgets and prizes don't have to succed
             */
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
            'prizesData' => '[]',
        );
    }
}
