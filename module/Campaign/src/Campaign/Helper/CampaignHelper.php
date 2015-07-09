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
        return array(
            array(
                'checked' => 'checked',
                'value'   => 1,
                'title'   => 'Default Layout',
                'hint'    => 'This is a message that will tell you about this layout'
            ),
            array(
                'checked' => '',
                'value'   => 2,
                'title'   => 'Another Layout',
                'hint'    => 'This is a message that will tell you about this layout'
            ),
        );
    }
    
    /**
     * Get all age requirements
     */
    public function getAllAgeRequirements()
    {
        return array(
            array(
                'selected' => 'selected',
                'value'   => 1,
                'title'   => '13+',
            ),
            array(
                'selected' => '',
                'value'   => 2,
                'title'   => '18+ XXX',
            ),
        );
    }
    
    /**
     * Get all competition types
     */
    public function getAllCompetitionTypes()
    {
        return array(
            array(
                'checked' => 'checked',
                'value'   => 1,
                'title'   => 'One Time',
                'hint'    => 'A single competition'
            ),
            array(
                'checked' => '',
                'value'   => 2,
                'title'   => 'Repeating',
                'hint'    => 'A competition that will repeat'
            ),
        );
    }
    
    /**
     * Get timezones
     */
    public function getTimezones()
    {
        return array(
            array(
                'selected' => 'selected',
                'value'   => 1,
                'title'   => 'Europe/Berlin',
            ),
            array(
                'selected' => '',
                'value'   => 2,
                'title'   => 'Europe/Bucharest',
            ),
        );
    }
}