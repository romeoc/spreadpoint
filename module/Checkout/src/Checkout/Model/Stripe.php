<?php

/**
 * Stripe Model
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Checkout\Helper\PlanHelper;
use Checkout\Entity\Order;

class Stripe implements ServiceLocatorAwareInterface
{   
    protected $orderModel;
    
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
    
    public function processTransaction($user, $data)
    {
        $plan = $data['plan'];
        $period = $data['period'];
        
        $billingPeriod = ($period == 0) ? 'Month' : 'Year';
        
        $planHelper = new PlanHelper();
        $planData = $planHelper->getPlan($plan);
        $planCode = $planData['name'] . '_' . strtolower($billingPeriod);
        
        $this->setSecretKey();
        
        if (!$this->planExist($planCode)) {
            $this->createPlan($planCode, $planData, strtolower($billingPeriod));
        }
        
        $customerId = $user->get('stripeCustomerId');
        if (!$customerId) {
            $token = $data['stripeToken'];
            if (!$token) {
                return false;
            }
            
            $customerId = $this->createCustomer($user, $token);
        }
        
        $startDate = $this->getStartingDate($user);
        $subscriptionId = $this->createTransaction($customerId, $planCode, $startDate, $this->getPastSubscription($user));
        
        $additionalOrderData = array(
            'amount' => $planData[strtolower($billingPeriod) . 'ly'],
            'description' => $planCode,
            'billingPeriod' => $billingPeriod,
        );
        $this->createOrder($user, $additionalOrderData, $plan, $subscriptionId);
    }
    
    protected function planExist($planCode)
    {
        $plans = \Stripe\Plan::all();
        foreach ($plans->data as $plan) {
            if ($plan->id === $planCode) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function createPlan($code, $data, $billingPeriod)
    {
        $billingPeriodField = strtolower($billingPeriod) . 'ly';
        
        \Stripe\Plan::create(array(
            'amount' => $data[$billingPeriodField] * 100,
            'interval' => $billingPeriod,
            'name' => ucfirst($data['name']) . ' Plan',
            'currency' => "usd",
            'id' => $code,
            'trial_period_days' => OrderModel::TRIAL_PERIOD
        ));
    }
    
    protected function createCustomer($user, $token)
    {
        $customer = \Stripe\Customer::create(array(
            "source" => $token,
            "description" => $user->get('firstname') . ' ' . $user->get('lastname'),
            "email" => $user->get('email')
        ));
        
        $user->set('stripeCustomerId', $customer->id);
        $this->getEntityManager()->flush();
        
        return $customer->id;
    }
    
    protected function createTransaction($customerId, $planId, $trialEnd = null, $subscriptionId = null)
    {
        $customer = \Stripe\Customer::retrieve($customerId);
        
        if ($subscriptionId) {
            // Update Subscription
            $subscription = $customer->subscriptions->retrieve($subscriptionId);
            $subscription->plan = $planId;
            
            if ($trialEnd) {
                $subscription->trial_end = strtotime($trialEnd);
            }
            
            $subscription->save();
        } else {
            // Create Subscription
            $subscriptionData = array(
                "plan" => $planId
            );
            
            if ($trialEnd) {
                $subscriptionData['trial_end'] = strtotime($trialEnd);
            }
            
            $subscription = $customer->subscriptions->create($subscriptionData);
            $subscriptionId = $subscription->id;
        }
        
        return $subscriptionId;
    }
    
    public function cancelSubscription($customerId, $subscriptionId)
    {
        $this->setSecretKey();
        
        $customer = \Stripe\Customer::retrieve($customerId);
        $subscription = $customer->subscriptions->retrieve($subscriptionId);
        $subscription->cancel();
    }
    
    protected function createOrder($user, $data, $plan, $subscriptionId)
    {
        $model = $this->getOrderModel();
        $pastOrder = $model->getActiveOrder($user);

        $additionalData = array(
            'user' => $user,
            'name' => $user->get('firstname') . ' ' . $user->get('lastname'),
            'email' => $user->get('email'),
            'plan' => $plan,
            'billingFrequency' => 1,
            'startDate' => $model->getNextBilling($user),
            'stripeSubscriptionId' => $subscriptionId,
        );
        
        
        // Entity Manager is Flushed during order save, so user data will also be persisted
        $user->set('plan', $plan);
        $data = array_merge($data, $additionalData);
        $order = $model->save($data);
        
        if ($order) {
            
            // Cancel Past Order
            $orderEntity = $this->getEntityManager()->find('Checkout\Entity\Order', $pastOrder['id']);
            $orderEntity->set('status', Order::STATUS_CANCELED);
            $this->getEntityManager()->flush();
            
            // Cancel Previous PayPal profiles (if any)
            $model->handlePaymentMethodConflicts($user, 'stripe');
            
            // Send Invoice
            $model->sendInvoice($order);
        }
        
        return !!$order;
    }
    
    public function getStartingDate($user)
    {
        $model = $this->getOrderModel();
        $startingDate = null;
        
        if ($model->hasOrder($user)) {
            $startingDate = $model->getNextBilling($user);
        }
        
        return $startingDate;
    }
    
    public function getPastSubscription($user)
    {
        $subscriptionId = null;
        $model = $this->getOrderModel();
        
        $order = $this->getOrderModel()->getActiveOrder($user) ;
        if ($order) {
            $subscriptionId = $order['stripeSubscriptionId'];
        }
        
        return $subscriptionId;
    }
    
    public function getOrderModel()
    {
        if (!$this->orderModel) {
            $order = new OrderModel();
            $order->setServiceLocator($this->getServiceLocator());
            
            $this->orderModel = $order;
        }
        
        return $this->orderModel;
    }
    
    public function setSecretKey()
    {
        $config = $this->getServiceLocator()->get('config');
        $stripe = $config['stripe'];
        
        $apiKey = ($stripe['test_mode']) ? $stripe['test_secret'] : $stripe['live_secret'];
        \Stripe\Stripe::setApiKey($apiKey);
    }
}
