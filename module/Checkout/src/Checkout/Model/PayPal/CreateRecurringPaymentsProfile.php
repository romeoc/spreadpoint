<?php

/**
 * PayPal Create Recurring Payments Profile
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model\PayPal;

use SpeckPaypal\Request\CreateRecurringPaymentsProfile as SpeckPaypalCreateRecurringPaymentsProfile;

class CreateRecurringPaymentsProfile extends SpeckPaypalCreateRecurringPaymentsProfile
{
    /**
     * Validate the minimum set of required values
     * 
     * @return boolean
     * @see SpeckPaypal\Request\CreateRecurringPaymentsProfile::isValid()
     */
    public function isValid()
    {
        //validate recurringPayment values
        $checkEmpty = array(
            $this->profileStartDate,
            $this->desc,
            $this->billingPeriod,
            $this->billingFrequency,
            $this->amt,
            $this->currencyCode
        );
        
        if (!$this->payerId) {
            $checkEmpty[] = $this->acct;
            $checkEmpty[] = $this->email;
        }

        if(!$this->checkEmpty($checkEmpty)) {
            return false;
        }

        return true;
    }
}
