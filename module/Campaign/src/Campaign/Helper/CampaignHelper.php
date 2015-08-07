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
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
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
                'label' => 'I am at least 13 years of age'
            ),
            array(
                'selected' => '',
                'value'   => 3,
                'title'   => '18+',
                'label' => 'I am at least 18 years of age'
            ),
            array(
                'selected' => '',
                'value'   => 4,
                'title'   => '19+',
                'label' => 'I am at least 19 years of age'
            ),
            array(
                'selected' => '',
                'value'   => 5,
                'title'   => '21+',
                'label' => 'I am at least 21 years of age'
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
        return "/media/$campaignId/";
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
    
    /**
     * Return template for widget type
     * 
     * @param int $widgetType
     */
    public function getWidgetTemplate($widgetType)
    {
        $path = 'campaign/widget/';
        switch ($widgetType) {
            case 1:
                return $path . 'enter-contest';
            case 2:
                return $path . 'facebook-like';
            case 3:
                return $path . 'facebook-share';
            case 4:
                return $path . 'twitter-tweet';
            case 5:
                return $path . 'twitter-follow';
            case 6:
                return $path . 'reference';
        }
    }
    
    /**
     * Return the entrants email if the cookie is set or false otherwise
     * 
     * @return string | false
     */
    public function getEntrantEmail()
    {
        $cookie = $this->getServiceLocator()->get('request')->getHeaders()->get('Cookie');
        $entrant = false;
        
        if (array_key_exists('entrant', get_object_vars($cookie))) {
            $entrant = $cookie->entrant;
        }
        
        if ($entrant) {
            $entrantEntity = $this->getEntityManager()->find('Campaign\Entity\CampaignEntrant', $entrant);
            if ($entrantEntity) {
                $entrant = $entrantEntity->get('email');
            }
        }
        
        return $entrant;
    }
    
    /**
     * Get status to which we should toggle
     * 
     * @return int
     */
    public function getToggledStatus()
    {
        if ($this->get('status') == CampaignEntity::STATUS_ACTIVE) {
            return CampaignEntity::STATUS_PAUSED;
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_PAUSED) {
            return CampaignEntity::STATUS_ACTIVE;
        }
        
        return false;
    }
    
    /**
     *Get toggled status title
     * 
     * @return string
     */
    public function getToggledStatusTitle()
    {
        if ($this->get('status') == CampaignEntity::STATUS_ACTIVE) {
            return 'Pause';
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_PAUSED) {
            return 'Unpause';
        }
        
        return false;
    }
    
    /**
     *Get toggled status icon
     * 
     * @return string
     */
    public function getToggledStatusIcon()
    {
        if ($this->get('status') == CampaignEntity::STATUS_ACTIVE) {
            return 'fa-pause';
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_PAUSED) {
            return 'fa-play';
        }
        
        return false;
    }
    
    /**
     * Get status as string
     * 
     * @return string
     */
    public function getStatusString()
    {
        if ($this->get('status') == CampaignEntity::STATUS_ACTIVE) {
            return 'Active';
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_PAUSED) {
            return 'Paused';
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_FINISHED) {
            return 'Complete';
        }
        
        if ($this->get('status') == CampaignEntity::STATUS_CANCELED) {
            return 'Canceled';
        }
        
        return false;
    }
    
    /**
     * Get entrant id stored in the entrant cookie
     * @return int
     */
    public function getEntrantId()
    {
        $cookie = $this->getServiceLocator()->get('request')->getHeaders()->get('Cookie');
        $entrant = false;
        
        if (array_key_exists('entrant', get_object_vars($cookie))) {
            $entrant = $cookie->entrant;
        }
        
        return $entrant;
    }
    
    /**
     * Get default terms and conditions for campaign
     * 
     * @param array $campaign
     * @return string
     */
    public function getDefaultTerms($campaign)
    {
        return 'Deufalt Terms';
    }
    
    /**
     * Get domain from url
     */
    public function getDomain()
    {
        $uri = $this->getServiceLocator()->get('request')->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }
}