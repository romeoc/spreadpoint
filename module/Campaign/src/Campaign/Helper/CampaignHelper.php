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

class CampaignHelper extends AbstractHelper
{
    protected $data;
    
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
                'title'   => 'Default Layout',
                'classes' => 'first',
                'id'      => 'default-layout'
                
            ),
            array(
                'checked' => '',
                'value'   => 2,
                'title'   => 'Another Layout',
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
     * Get timezones
     */
    public function getTimezones()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }
}