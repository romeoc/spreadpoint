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
            'options' => array(
                'label' => 'Name',
            ),
            'attributes' => array(
                'required' => 'required'
            )
        ));
        
        //Last Name
        $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
            'attributes' => array(
                'required' => 'required'
            )
        ));
        
        //E-mail
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
            ),
            'options' => array(
                'label' => 'Email',
            ),
            'attributes' => array(
                'required' => 'required'
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
        $password->setLabel('Password')
                 ->setAttributes(array(
                    'required'  => 'required',
            )
        );
        
        $this->add($password);
        
        //Submit Button
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'Submit',
                'value' => 'Submit',
                'class' => 'account-submit'
            ),
        ));
    }
}
