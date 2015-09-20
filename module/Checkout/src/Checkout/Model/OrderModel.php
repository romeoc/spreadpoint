<?php

/**
 * Order Model
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model;

use Base\Model\AbstractModel;
use Base\Model\Mail;

use Checkout\Helper\PlanHelper;
use Checkout\Entity\Order;
use Checkout\Model\PayPal as PayPalModel;
use Checkout\Model\Stripe as StripeModel;

class OrderModel extends AbstractModel
{
    const INCREMENT_VARIANCE = 17530000;
    const TRIAL_PERIOD = 30;
    
    protected $activeOrder;
    
    // Initialize Order Model
    public function __construct() 
    {
        $this->init('Checkout\Entity\Order');
    }
    
    /**
     * 
     * @param User\Entity\User $user
     * @return Checkout\Entity\Order
     */
    public function getActiveOrder($user)
    {
        if (!$this->activeOrder) {
            $queryBuilder = $this->getEntityManager()->createQueryBuilder();
            $order = $queryBuilder->select('e.profileId, e.id, e.startDate, e.billingPeriod, e.stripeSubscriptionId')
                ->from($this->entity, 'e')
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
            
            $this->activeOrder = $order;
        }
        
        return $this->activeOrder;
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
    
    public function getNextBilling($user)
    {
        $order = $this->getActiveOrder($user);

        $trialPeriod = self::TRIAL_PERIOD;
        $date = strtotime("+{$trialPeriod} day");
        
        if ($order) {
            $billingPeriod = $order['billingPeriod'];
            $date = strtotime($order['startDate']);
            
            while ($date <= time()) {
                $date = strtotime("+1 {$billingPeriod}", $date);
            }
        }
        
        return date('Y-m-d\TH:i:s\Z', $date);
    }
    
    public function hasOrder($user)
    {
        return !!$this->getActiveOrder($user);
    }
    
    public function handlePaymentMethodConflicts($user, $method)
    {
        $order = $this->getActiveOrder($user);
        if (!$order) {
            return true;
        }
        
        $pastMethod = ($order['stripeSubscriptionId']) ? 'stripe' : 'paypal';
        
        if ($method !== $pastMethod) {
            if ($pastMethod === 'stripe') {
                // Cancel Stripe Subscription
                $subscriptionId = $order['stripeSubscriptionId'];
                
                $model = new StripeModel();
                $model->setServiceLocator($this->getServiceLocator());
                
                $model->cancelSubscription($user->get('stripeCustomerId'), $subscriptionId);
            } else {
                $profileId = $order['profileId'];

                $model = new PayPalModel();
                $model->setServiceLocator($this->getServiceLocator());
                
                $model->cancelProfile($profileId, PayPal::ACTION_CANCEL, 'Switching Payment Methods');
            }
            
            $orderEntity = $this->getEntityManager()->find($this->entity, $order['id']);
            $orderEntity->set('status', Order::STATUS_CANCELED);
            $this->getEntityManager()->flush();
        }
    }
}
