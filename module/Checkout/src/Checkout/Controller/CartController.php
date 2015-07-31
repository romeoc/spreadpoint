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
use User\Helper\UserHelper;
use Base\Model\Session;

class CartController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function submitAction()
    {
        $data = $this->request->getPost();      
        $user = $this->getUser();
        
        if ($this->getPayPalModel()->createRecurringPayment($data, $user)) {
            $this->redirect()->toRoute('checkout', array('action' => 'success'));
        } else {
            $this->redirect()->toRoute('checkout');
        }
    }
    
    public function paypalStartAction()
    {
        $data = $this->request->getPost();
        
        $session = new Container('checkout');
        $session->plan = $data['plan'];
        
        $result = $this->getPayPalModel()->startExpressCheckout($data);
        return $this->redirect()->toUrl($result);
    }
    
    public function placeOrderAction()
    {
        $token = $this->params()->fromQuery('token');
        $payerId = $this->params()->fromQuery('PayerID');
        
        $session = new Container('checkout');
        $plan = $session->plan;
        $session->offsetUnset('plan');
        
        if ($this->getPayPalModel()->doExpressCheckout($token, $payerId, $this->getUser(), $plan)) {
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
        var_dump('Oh Yeah! You did it'); die;
    }
    
    protected function getUser()
    {
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        return $helper->getLoggedInUser();
    }
    
    protected function getPayPalModel()
    {
        $model = new PayPalModel();
        $model->setServiceLocator($this->getServiceLocator());
        return $model;
    }
}
