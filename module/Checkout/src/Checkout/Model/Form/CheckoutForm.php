<?php

/**
 * Checkout Form
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model\Form;

use Zend\Form\Form;

class CheckoutForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Checkout');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->initFields();
    }
    
    protected function initFields()
    {
        //Plan
        $this->add(array(
            'name' => 'plan',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Plan',
                'value_options' => array(
                    '0' => 'Puddle',
                    '1' => 'Lake',
                    '2' => 'Ocean',
                ),
            ),
        ));
        
        //Full Name on Credit Card
        $this->add(array(
            'name' => 'fullname',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'Full name on credit card'
            )
        ));
        
        //Card Number
        $this->add(array(
            'name' => 'card_number',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'Card Number',
                'maxlength' => '19',
            )
        ));
        
        //Expiry Date
        $this->add(array(
            'name' => 'expiry_date',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'MM/YY',
                'maxlength' => '5',
            )
        ));
        
        //CVC
        $this->add(array(
            'name' => 'cvc',
            'attributes' => array(
                'type' => 'text',
            ),
            'attributes' => array(
                'required' => 'required',
                'placeholder' => 'CVC'
            )
        ));
        
        //Submit Button
        $this->add(array(
            'name' => 'submit-action',
            'attributes' => array(
                'type' => 'Submit',
                'value' => 'Pay Now',
                'class' => 'checkout-submit'
            ),
        ));
    }
}
