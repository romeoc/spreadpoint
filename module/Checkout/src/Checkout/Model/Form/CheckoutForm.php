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
use Checkout\Helper\PlanHelper;

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
        $helper = new PlanHelper();
        
        //Plan
        $this->add(array(
            'name' => 'plan',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Plan',
                'value_options' => $helper->getPlanOptions()
            ),
        ));
        
        //Period
        $this->add(array(
            'name' => 'period',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'required'  => 'required',
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'label' => 'Monthly',
                        'attributes' => array(
                            'class' => 'kalypsify',
                        )
                    ),
                    array(
                        'value' => '1',
                        'label' => 'Yearly',
                        'attributes' => array(
                            'class' => 'kalypsify',
                        )
                    )
                ),
            ),
        ));
    }
}
