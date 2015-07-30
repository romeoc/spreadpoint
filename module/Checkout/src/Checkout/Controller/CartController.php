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
        $model = new PayPalModel();
        
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        $user = $helper->getLoggedInUser();
        
        if ($model->createRecurringPayment($data, $user)) {
            $this->redirect()->toRoute('checkout', array('action' => 'success'));
        } else {
            $this->redirect()->toRoute('checkout');
        }
    }
    
    public function paypalStartAction()
    {
        $data = $this->request->getPost();
        $model = new PayPalModel();
        
        $session = new Container('checkout');
        $session->plan = $data['plan'];
        
        $uri = $this->getServiceLocator()->get('request')->getUri();
        $domain = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        
        $result = $model->startExpressCheckout($data, $domain);
        return $this->redirect()->toUrl($result);
    }
    
    public function placeOrderAction()
    {
        $token = $this->params()->fromQuery('token');
        $payerId = $this->params()->fromQuery('PayerID');
        
        $session = new Container('checkout');
        $plan = $session->plan;
        $session->offsetUnset('plan');
        
        $model = new PayPalModel();
        if ($model->doExpressCheckout($token, $payerId, $plan)) {
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
}
