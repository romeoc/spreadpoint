<?php


/**
 * User Register Form
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Model\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class RegisterForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Register');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->initFields();
    }
    
    protected function initFields()
    {
        //First Name
        $this->add(array(
            'name' => 'firstname',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'First Name'
            )
        ));
        
        //Last Name
        $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'Last Name'
            )
        ));
        
        //E-mail
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'required' => 'required',
                'placeholder' => 'Email'
            ),
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                        )
                    )
                )
            )
        ));
        
        //Password
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'placeholder' => 'Password'
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6,
                        'max' => 32
                    ),
                    'messages' => array(
                        \Zend\Validator\StringLength::TOO_SHORT => 'Your password should have at least 6 characters',
                        \Zend\Validator\StringLength::TOO_LONG => 'Maximum password length is 32 characters',
                    )
                ),
            ),
        ));
        
        //Terms & Conditions
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'terms-and-conditions',
            'options' => array(
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ),
            'attributes' => array(
                'required' => 'required',
                'class' => 'terms-checkbox'
            )
        ));
        
        //Submit Button
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'Submit',
                'value' => 'Join the Party',
                'class' => 'account-submit'
            ),
        ));
    }
}
