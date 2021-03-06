<?php

/**
 * Cart Controller
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Checkout\Model\PayPal as PayPalModel;
use Checkout\Model\Stripe as StripeModel;
use User\Helper\UserHelper;
use Base\Model\Session;

class CartController extends AbstractActionController
{
    public function indexAction()
    {
        $user = $this->getUser();
        if ($user && $user->get('plan') != -1) {
            return $this->redirect()->toRoute('checkout', array('controller' => 'cart', 'action' => 'upgrade'));
        }
        
        $plan = $this->params()->fromQuery('package');
        $plan = ($plan == 0 || $plan == 1) ? $plan : null;
        
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Checkout - SpreadPoint');
        $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer')->headMeta()->appendName('robots', 'noindex, nofollow');
        
        return new ViewModel(array('plan' => $plan));
    }
    
    public function upgradeAction()
    {
        $this->layout('layout/dashboard');
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Dashboard - Upgrade');
        
        $this->getUserHelper()->userHasAccess();
        return new ViewModel();
    }
    
    public function submitAction()
    {
        $data = $this->request->getPost();      
        $user = $this->getUser();
        
        if ($data && $user) {
            $model = new StripeModel();
            $model->setServiceLocator($this->getServiceLocator());
            
            $model->processTransaction($user, $data);
            return $this->redirect()->toRoute('checkout', array('action' => 'success'));
        }
        
        return $this->redirect()->toRoute('checkout');
    }
    
    public function paypalStartAction()
    {
        $data = $this->request->getPost();
        
        if (!$data) {
            $this->redirect()->toRoute('checkout');
        }
        
        $session = new Container('checkout');
        $session->plan = $data['plan'];
        $session->period = $data['period'];
        
        $result = $this->getPayPalModel()->startExpressCheckout($data);
        return $this->redirect()->toUrl($result);
    }
    
    public function placeOrderAction()
    {
        $token = $this->params()->fromQuery('token');
        $payerId = $this->params()->fromQuery('PayerID');
        
        if (!$payerId) {
            $this->redirect()->toRoute('checkout');
        }
        
        $session = new Container('checkout');
        $plan = $session->plan;
        $period = $session->period;
        $session->offsetUnset('plan');
        $session->offsetUnset('period');
        
        if ($this->getPayPalModel()->doExpressCheckout($token, $payerId, $this->getUser(), $plan, $period)) {
            $this->redirect()->toRoute('checkout', array('action' => 'success'));
        } else {
            $this->redirect()->toRoute('checkout');
        }
    }
    
    public function cancelAction()
    {
        Session::error('Payment canceled');
        $this->redirect()->toRoute('checkout');
    }
    
    public function successAction()
    {
        Session::success('The transaction was succesful. You can now start creating awesome campaigns.');
        $this->redirect()->toRoute('campaign');
    }
    
    
    protected function getUserHelper()
    {
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        
        return $helper;
    }
    
    protected function getUser()
    {
        return $this->getUserHelper()->getLoggedInUser();
    }
    
    protected function getPayPalModel()
    {
        $model = new PayPalModel();
        $model->setServiceLocator($this->getServiceLocator());
        return $model;
    }
}
