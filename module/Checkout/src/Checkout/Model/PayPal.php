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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Base\Model\Session;
use Base\Model\Mail;
use Checkout\Model\OrderModel;
use Checkout\Helper\PlanHelper;
use Checkout\Entity\Order;

use Checkout\Model\PayPal\CreateRecurringPaymentsProfile;
use Checkout\Model\PayPal\SetExpressCheckout;
use SpeckPaypal\Request\ManageRecurringPaymentsProfileStatus;
use SpeckPaypal\Service\Request as PayPalRequest;

use SpeckPaypal\Element\Config as PayPalConfig;
use SpeckPaypal\Element\PaymentDetails;
use SpeckPaypal\Element\PaymentItem;

class PayPal implements ServiceLocatorAwareInterface
{
    protected $service;
    
    protected $sandBoxMode = true;
    
    // Creditentials
    const API_ASSOCIATED_EMAIL = 'unknown@qa.com';
    const API_USERNAME  = 'unknown_api1.qa.com';
    const API_PASSWORD  = 'HSW4MPQB2UCZSN4J';
    const API_SIGNATURE = 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AOLbJdEoUspXAg5lqFqu9fQBbSMv';
    
    const ACTION_SUSPEND = 'Suspend';
    const ACTION_CANCEL = 'Cancel';
    
    const TRIAL_PERIOD = 30;
    const INCREMENT_VARIANCE = 17530000;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->service;
    }
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
    
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
        $billingPeriod = ($data['period'] == 0) ? 'Month' : 'Year';
        $priceKey = ($data['period'] == 0) ? 'monthly' : 'yearly';
        $plans = $this->getPlans();
        
        $payment = new CreateRecurringPaymentsProfile();
        $payment->setDesc($plans[$data['plan']]['name']);
        $payment->setSubscriberName($data['fullname']);
        $payment->setProfileStartDate($this->getStartDate($user, $billingPeriod));
        $payment->setBillingPeriod($billingPeriod);
        $payment->setBillingFrequency(1);
        $payment->setAmt($plans[$data['plan']][$priceKey]);
        $payment->setCurrencyCode('USD');
        $payment->setCardNumber(str_replace(' ', '', $data['card_number']));
        $payment->setExpirationDate(str_replace('/','',$data['expiry_date']));
        $payment->setCvv2($data['cvc']);
        $payment->setEmail($user->get('email'));
        
        if ($payment->isValid()) {
            $response = $this->getRequest()->send($payment);
            if ($response->isSuccess()) {
                return $this->createOrder($payment, $response, $user, $data['plan']);
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
    
    public function startExpressCheckout($data)
    {
        $uri = $this->getServiceLocator()->get('request')->getUri();
        $domain = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        $plans = $this->getPlans();
        $priceKey = ($data['period'] == 0) ? 'monthly' : 'yearly';
        
        $paymentItem = new PaymentItem();
        $paymentItem->setItemCategory(PaymentItem::CATEGORY_DIGITAL);
        $paymentItem->setAmt($plans[$data['plan']][$priceKey]);
        $paymentItem->setName($plans[$data['plan']]['name']);
        
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setAmt($plans[$data['plan']][$priceKey]);
        $paymentDetails->setRecurring('Y');
        $paymentDetails->setItems(array($paymentItem));
        
        $express = new SetExpressCheckout();
        $express->setPaymentDetails($paymentDetails);
        $express->setReturnUrl("{$domain}/checkout/cart/placeOrder");
        $express->setCancelUrl("{$domain}/checkout/cart/cancel");
        
        $billingAgreement = array(
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => $plans[$data['plan']]['name']
        );

        $express->setBillingAgreements($billingAgreement);

        $response = $this->getRequest()->send($express);
        
        return 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->getToken();
    }
    
    public function doExpressCheckout($token, $payerId, $user, $plan, $period)
    {
        $billingPeriod = ($period == 0) ? 'Month' : 'Year';
        $priceKey = ($period == 0) ? 'monthly' : 'yearly';
        $plans = $this->getPlans();
        
        $payment = new CreateRecurringPaymentsProfile();
        $payment->setToken($token);
        $payment->setPayerId($payerId);
        $payment->setDesc($plans[$plan]['name']);
        $payment->setProfileStartDate($this->getStartDate($user, $billingPeriod));
        $payment->setBillingPeriod($billingPeriod);
        $payment->setBillingFrequency(1);
        $payment->setAmt($plans[$plan][$priceKey]);
        $payment->setCurrencyCode('USD');
        
        $response = $this->getRequest()->send($payment);
        if ($response->isSuccess()) {
            return $this->createOrder($payment, $response, $user, $plan);
        } else {
            foreach ($response->getErrorMessages() as $error) {
                Session::error($error);
            }
            
            return false;
        }
    }
    
    protected function createOrder($payment, $response, $user, $plan)
    {
        $pastOrder = $this->getPastOrder($user);

        $data = array(
            'user' => $user,
            'name' => $user->get('firstname') . ' ' . $user->get('lastname'),
            'email' => $user->get('email'),
            'plan' => $plan,
            'profileId' => $response->getProfileId(),
            'correlationId' => $response->getCorrelationId(),
            'payerId' => $payment->getPayerId(),
            'description' => $payment->getDesc(),
            'amount' => $payment->getAmt(),
            'startDate' => $payment->getProfileStartDate(),
            'billingPeriod' => $payment->getBillingPeriod(),
            'billingFrequency' => $payment->getBillingFrequency(),
        );
        
        $order = new OrderModel();
        $order->setServiceLocator($this->getServiceLocator());
        
        // Entity Manager is Flushed furing order save, so user data will also be persisted
        $user->set('plan', $plan);
        $order = $order->save($data);
        
        if ($order) {
            $this->handlePastProfiles($pastOrder);
            $this->sendInvoice($order);
        }
        
        return !!$order;
    }
    
    public function cancelProfile($profileId, $action = self::ACTION_CANCEL, $note = 'Upgrading Plan')
    {
        $transaction = new ManageRecurringPaymentsProfileStatus();
        $transaction->setProfileId($profileId);
        $transaction->setAction($action);
        
        if ($note) {
            $transaction->setNote($note);
        }
        
        $response = $this->getRequest()->send($transaction);
        
        if (!$response->isSuccess()) {
            return $response->getErrorMessages();
        }
        
        return true;
    }
    
    public function getPastOrder($user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $order = $queryBuilder->select('e.profileId, e.id, e.startDate')
            ->from('Checkout\Entity\Order', 'e')
            ->where('e.user= :user')
            ->andWhere('e.status= :status')
            ->setParameter('user', $user)
            ->setParameter('status', Order::STATUS_ACTIVE)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
        
        if ($order) {
            $order = $order[0];
        }
        
        return $order;
    }
    
    public function handlePastProfiles($order)
    {
        if ($order) {
            $profileId = $order['profileId'];
            $cancelProfileResult = $this->cancelProfile($profileId);
            
            if ($cancelProfileResult !== true) {
                $message = "Could not cancel profile from order {$order['id']} while upgrading plan!" . PHP_EOL;
                
                foreach ($cancelProfileResult as $errorMessage) {
                    $message .= PHP_EOL . $errorMessage;
                }
                
                $this->sendEmailNotification($message);
            } else {
                $orderEntity = $this->getEntityManager()->find('Checkout\Entity\Order', $order['id']);
                $orderEntity->set('status', Order::STATUS_CANCELED);
                $this->getEntityManager()->flush();
            }
        }
    }
    
    public function sendEmailNotification($message)
    {
        $now = new \DateTime();

        $subject = 'SpreadPoint - Error Notification';
        $body = '<p><strong>Time</strong>: ' . $now->format('g:ia \o\n l jS F Y') . '</p>'
                . '<p><strong style="color: red;">Error Notification:</strong> ' . $message . '</p>';
        
        $emailData = array(
            'body' => $body,
            'subject' => $subject,
        );
        
        Mail::send($emailData);
    }
    
    public function getStartDate($user, $billingPeriod)
    {
        $order = $this->getPastOrder($user);

        $trialPeriod = self::TRIAL_PERIOD;
        $date = strtotime("+{$trialPeriod} day");
        
        if ($order) {
            $date = strtotime($order['startDate']);
            while ($date <= time()) {
                $date = strtotime("+1 {$billingPeriod}", $date);
            }
        }
        
        return date('Y-m-d\TH:i:s\Z', $date);
    }
    
    public function getPlans()
    {
        $helper = new PlanHelper();
        return $helper->getAllPlans();
    }
    
    public function sendInvoice($order)
    {
        $planHelper = new PlanHelper();
        $plan = $planHelper->getPlan($order->get('plan'));
        
        $createdAt = $order->get('createdAt');
        $createdAt->setTimezone(new \DateTimeZone('America/New_York'));

        $templateData = array(
            'name' => $order->get('name'),
            'incrementId' => self::INCREMENT_VARIANCE + $order->get('id'),
            'createdAt' => $createdAt->format('l jS F Y, h:i:s A T'),
            'plan' => ucfirst($plan['name']) . ' Plan',
            'billingPeriod' => $order->get('billingPeriod') . 'ly',
            'price' => '$' . $order->get('amount')
        );
        
        $emailData = array(
            'body' => '',
            'subject' => 'SpreadPoint Subscription',
            'toEmail' => $order->get('email'),
            'toName' => $order->get('name'),
            'service' => $this->getServiceLocator(),
            'template' => 'email/templates/invoice',
            'templateData' => $templateData,
        );

        Mail::send($emailData);
    }
}
