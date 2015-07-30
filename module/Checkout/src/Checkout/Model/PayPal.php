<?php

/**
 * PayPal Model
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model;

use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl as CurlAdapter;
use Base\Model\Session;

use SpeckPaypal\Request\CreateRecurringPaymentsProfile;
use SpeckPaypal\Request\DoExpressCheckoutPayment;
use SpeckPaypal\Request\SetExpressCheckout;
use SpeckPaypal\Service\Request as PayPalRequest;

use SpeckPaypal\Element\Config as PayPalConfig;
use SpeckPaypal\Element\Address;
use SpeckPaypal\Element\PaymentDetails;
use SpeckPaypal\Element\PaymentItem;

class PayPal 
{
    protected $sandBoxMode = true;
    
    // Creditentials
    const API_ASSOCIATED_EMAIL = 'unknown@qa.com';
    const API_USERNAME  = 'unknown_api1.qa.com';
    const API_PASSWORD  = 'HSW4MPQB2UCZSN4J';
    const API_SIGNATURE = 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AOLbJdEoUspXAg5lqFqu9fQBbSMv';
    
    protected $packageMap = array(
        0 => array(
            'name'  => 'Puddle Package',
            'price' => 29.00
        ),
        1 => array(
            'name' => 'Lake Package',
            'price' => 199.00
        ),
        2 => array(
            'name' => 'Ocean Package',
            'price' => 599.00,
        )
    );
    
    protected function getConfig()
    {
        $endpoint = ($this->sandBoxMode) 
            ? 'https://api-3t.sandbox.paypal.com/nvp'
            : 'https://api-3t.paypal.com/nvp';
        
        $config = array(
            'username'      => self::API_USERNAME,
            'password'      => self::API_PASSWORD,
            'signature'     => self::API_SIGNATURE,
            'endpoint'      => $endpoint
        );
        
        return new PayPalConfig($config);
    }
    
    protected function getRequest()
    {
        $config = $this->getConfig();
            
        $client = new Client();
        $client->setMethod('POST');
        $client->setAdapter(new CurlAdapter());

        $paypalRequest = new PayPalRequest();
        $paypalRequest->setClient($client);
        $paypalRequest->setConfig($config);
        
        return $paypalRequest;
    }
    
    public function createRecurringPayment($data, $user)
    {
        $payment = new CreateRecurringPaymentsProfile();
        $payment->setDesc($this->packageMap[$data['plan']]['name']);
        $payment->setSubscriberName($data['fullname']);
        $payment->setProfileStartDate(date("Y-m-d\TH:i:s\Z"));
        $payment->setBillingPeriod('Month');
        $payment->setBillingFrequency(1);
        $payment->setAmt($this->packageMap[$data['plan']]['price']);
        $payment->setCurrencyCode('USD');
        $payment->setCardNumber(str_replace(' ', '', $data['card_number']));
        $payment->setExpirationDate(str_replace('/','20',$data['expiry_date']));
        $payment->setCvv2($data['cvc']);
        $payment->setEmail($user->get('email'));
        
        if ($payment->isValid()) {
            $response = $this->getRequest()->send($payment);
            if ($response->isSuccess()) {
                // Handle Billing
//                return array(
//                    'payment'  => $payment,
//                    'response' => $response,
//                    'user'     => $user
//                );
                return true;
            } else {
                foreach ($response->getErrorMessages() as $error) {
                    Session::error($error);
                }
                return false;
            }
        } else {
            Session::error('Some of the data provided is not valid');
            return false;
        }
    }
    
    public function startExpressCheckout($data, $domain)
    {
        $paymentItem = new PaymentItem();
        $paymentItem->setItemCategory(PaymentItem::CATEGORY_DIGITAL);
        $paymentItem->setAmt($this->packageMap[$data['plan']]['price']);
        $paymentItem->setName($this->packageMap[$data['plan']]['name']);
        
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setAmt($this->packageMap[$data['plan']]['price']);
        $paymentDetails->setRecurring('Y');
        $paymentDetails->setItems(array($paymentItem));
        
        $express = new SetExpressCheckout();
        $express->setPaymentDetails($paymentDetails);
        $express->setReturnUrl("{$domain}/checkout/cart/placeOrder");
        $express->setCancelUrl("{$domain}/checkout/cart/cancel");
        
        $billingAgreement = array(
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => $this->packageMap[$data['plan']]['name']
        );

        $express->setBillingAgreements($billingAgreement);

        $response = $this->getRequest()->send($express);
        
        return 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->getToken();
    }
    
    public function doExpressCheckout($token, $payerId, $plan)
    {
        set_time_limit(0);
        $payment = new CreateRecurringPaymentsProfile();
        $payment->setToken($token);
        $payment->setPayerId($payerId);
        $payment->setDesc($this->packageMap[$plan]['name']);
        $payment->setProfileStartDate(date("Y-m-d\TH:i:s\Z"));
        $payment->setBillingPeriod('Month');
        $payment->setBillingFrequency(1);
        $payment->setAmt($this->packageMap[$plan]['price']);
        $payment->setCurrencyCode('USD');
        
        $response = $this->getRequest()->send($payment);
        if ($response->isSuccess()) {
            // Handle Billing section
            return true;
        } else {
            foreach ($response->getErrorMessages() as $error) {
                Session::error($error);
            }
            
            return false;
        }
    }
}