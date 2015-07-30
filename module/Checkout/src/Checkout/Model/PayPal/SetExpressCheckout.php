<?php

/**
 * PayPal Set Express Checkout
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model\PayPal;

use SpeckPaypal\Request\SetExpressCheckout as SpeckPaypalSetExpressCheckout;

class SetExpressCheckout extends SpeckPaypalSetExpressCheckout
{
    /**
     * @see SpeckPaypal\Request\SetExpressCheckout::toArray();
     */
    public function toArray()
    {
        $data = parent::toArray();
        
        if ($this->_billingAgreement) {
            foreach($this->_billingAgreement as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
        
    }
}
