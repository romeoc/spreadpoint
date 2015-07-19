<?php

/* 
 *  Campaign Helper
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */
namespace Campaign\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use User\Helper\UserHelper;
use Campaign\Entity\Campaign as CampaignEntity;

class CampaignHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $data;

    protected $service;
    
    public function getServiceLocator() 
    {
        return $this->service;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) 
    {
        $this->service = $serviceLocator->getServiceLocator();
    }
    
    public function updateServiceLocator($service)
    {
        $this->service = $service;
    }

    // Get data by key
    public function get($key)
    {
        $result = '';
        if (array_key_exists($key, $this->data)) {
            $result = $this->data[$key];
        }
        
        return $result;
    }
    
    // Return checked if bool element is set to 1
    public function getChecked($key)
    {
        $result = '';
        if (array_key_exists($key, $this->data) && $this->data[$key] == 1) {
            $result = 'checked';
        }
        
        return $result;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    /** 
     * Get all available campaign layouts
     */
    public function getAvailableLayouts()
    {
        $layouts = array(
            array(
                'checked' => '',
                'value'   => 1,
                'title'   => 'Default',
                'classes' => 'first',
                'id'      => 'default-layout'
                
            ),
            array(
                'checked' => '',
                'value'   => 2,
                'title'   => 'Another',
                'classes' => 'first',
                'id'      => 'another-layout'
            ),
        );
        
        $currentLayout = $this->get('layout');
        if ($currentLayout) {
            $layouts[$currentLayout - 1]['checked'] = 'checked';
        } else {
            $layouts[0]['checked'] = 'checked';
        }
        
        return $layouts;
    }
    
    /**
     * Get all age requirements
     */
    public function getAllAgeRequirements()
    {
        $ageRequirements = array(
            array(
                'selected' => '',
                'value'   => 1,
                'title'   => 'None',
            ),
            array(
                'selected' => '',
                'value'   => 2,
                'title'   => '13+',
            ),
            array(
                'selected' => '',
                'value'   => 3,
                'title'   => '18+',
            ),
            array(
                'selected' => '',
                'value'   => 4,
                'title'   => '19+',
            ),
            array(
                'selected' => '',
                'value'   => 5,
                'title'   => '21+',
            ),
        );
        
        $currentAgeRequirement = $this->get('ageRequirement');
        if ($currentAgeRequirement) {
            $ageRequirements[$currentAgeRequirement - 1]['selected'] = 'selected';
        } else {
            $ageRequirements[0]['selected'] = 'selected';
        }
        
        return $ageRequirements;
    }
    
    /**
     * Get all competition types
     */
    public function getAllCompetitionTypes()
    {
        $types = array(
            array(
                'checked' => '',
                'value'   => 1,
                'title'   => 'One Time',
                'classes' => 'first',
                'id'      => 'one-time'
            ),
            array(
                'checked' => '',
                'value'   => 2,
                'title'   => 'Repeating',
                'classes' => 'last',
                'id'      => 'repeating'
            ),
        );
        
        $currentType = $this->get('type');
        if ($currentType) {
            $types[$currentType - 1]['checked'] = 'checked';
        } else {
            $types[0]['checked'] = 'checked';
        }
        
        return $types;
    }
    
    /**
     * Return a UserHelper instance
     * 
     * @return \User\Helper\UserHelper
     */
    public function getUserHelper()
    {
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        
        return $helper;
    }
    
    /**
     * Get timezones
     */
    public function getTimezones()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }
    
    /**
     * Returns the url to the campaigns banner
     * 
     * @param int $campaignId
     * @return string (url)
     */
    public function getBannerUrl($campaignId = null, $filename = null)
    {
        if (!$campaignId) {
            $campaignId = $this->get('id');
        }

        if (!$filename) {
            $filename = $this->get('banner');
        }
        
        return $this->getBaseImagePath($campaignId) . $filename;
    }
    
    /**
     * Get base image path for a campaign
     * 
     * @param int $campaignId
     * @return string
     */
    public function getBaseImagePath($campaignId)
    {
        $userId = $this->getUserHelper()->getLoggedInUserId();
        return "/media/$userId/$campaignId/";
    }
    
    /**
     * Returns the icon class for the status
     * 
     * @param int $status
     */
    public function getIconForCampaignStatus($status)
    {
        switch ($status) {
            case CampaignEntity::STATUS_PAUSED:
                return 'fa-pause';
            case CampaignEntity::STATUS_FINISHED:
                return 'fa-check';
            case CampaignEntity::STATUS_CANCELED:
                return 'fa-times';
        }
    }
}