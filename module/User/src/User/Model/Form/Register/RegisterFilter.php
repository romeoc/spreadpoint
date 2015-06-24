<?php

/**
 * Register Form Filter & Validation
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Model\Form\Register;

use Zend\InputFilter\InputFilter;

class RegisterFilter extends InputFilter
{
    public function __construct()
    {
        $this->initFilters();
    }
    
    public function initFilters()
    {
        //First Name
        $this->add(array(
            'name' => 'firstname',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 64,
                    ),
                ),
            ),
        ));
        
        //Last Name
        $this->add(array(
            'name' => 'lastname',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 64,
                    ),
                ),
            ),
        ));
        
        //Email
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'domain' => true,
                    ),
                ),
            ),
        ));
        
        //Password
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6
                    ),
                ),
            ),
        ));
    }
}
