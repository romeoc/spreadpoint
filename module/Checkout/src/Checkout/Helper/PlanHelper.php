<?php

/**
 * Plan Helper
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Helper;

use Zend\View\Helper\AbstractHelper;

class PlanHelper extends AbstractHelper
{
    const PLAN_TYPE_FIXED = 'fixed';
    const PLAN_TYPE_CUSTOM = 'custom';
    
    const PUDDLE_PLAN = 0;
    const LAKE_PLAN = 1;
    const OCEAN_PLAN = 2;
    
    protected $plans = array(
        0 => array(
            'name' => 'puddle',
            'type' => self::PLAN_TYPE_FIXED,
            'monthly' => 49,
            'yearly' => 499,
            'image' => 'img/puddle.png'
        ),
        1 => array(
            'name' => 'lake',
            'type' => self::PLAN_TYPE_FIXED,
            'monthly' => 299,
            'yearly' => 2999,
            'image' => 'img/lake.png'
        ),
        2 => array(
            'name' => 'ocean',
            'type' => self::PLAN_TYPE_CUSTOM,
            'image' => 'img/ocean.png'
        )
    );
    
    public function getAllPlans()
    {
        return $this->plans;
    }
    
    public function getPlan($plan)
    {
        return $this->plans[$plan];
    }
    
    public function getPlanOptions()
    {
        $plans = array();
        
        foreach ($this->plans as $key => $plan) {
            $plans[$key] = ucfirst($plan['name']);
        }
        
        return $plans;
    }
}
