<?php

/**
 * Application Controller
 *
 * @module     Application
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Model\ContactModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function pricingAction()
    {
        return new ViewModel();
    }
    
    public function contactAction()
    {
        return new ViewModel();
    }
    
    public function termsOfServiceAction()
    {
        return new ViewModel();
    }
    
    public function privacyPolicyAction()
    {
        return new ViewModel(); 
    }
    
    public function sendEmailAction()
    {
        $data = $this->request->getPost();

        if (!empty($data)) {
            $contactModel = new ContactModel();
            $contactModel->sendEmail($data);
        }
        
        $this->redirect()->toRoute('contact');
    }
}
