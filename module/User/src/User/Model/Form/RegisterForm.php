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
            ),
            'attributes' => array(
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
                            \Zend\Validator\
                            EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                        )
                    )
                )
            )
        ));
        
        //Password
        $password = new Element\Password('password');
        $password->setAttributes(array(
            'required'  => 'required',
            'placeholder' => 'Password'
        ));
        
        $this->add($password);

        
        //Terms & Conditions
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'terms-and-conditions',
            'options' => array(
                'label' => 'I agree to the Terms and Conditions',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ),
            'attributes' => array(
                'required' => 'required'
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
