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
use Application\Model\SupportModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $description = 'With SpreadPoint you run customizable giveaways, secured raffles and challenging contests. Start your free trial and engage your customers like never before.';
        $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer')->headMeta()->appendName('description', $description);
        return new ViewModel();
    }
    
    public function pricingAction()
    {
        $description = 'Run customizable giveaways with the best suited pricing plan and make your followers love you. Start your free trial today!';
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Pricing - SpreadPoint');
        $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer')->headMeta()->appendName('description', $description);
        return new ViewModel();
    }
    
    public function contactAction()
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Contact - SpreadPoint');
        return new ViewModel();
    }
    
    public function termsOfServiceAction()
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Terms of Service - SpreadPoint');
        return new ViewModel();
    }
    
    public function privacyPolicyAction()
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Privacy Policy - SpreadPoint');
        return new ViewModel(); 
    }
    
    public function supportAction()
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Support - SpreadPoint');
        return new ViewModel(); 
    }
    
    public function sendContactEmailAction()
    {
        $data = $this->request->getPost();

        if (!empty($data)) {
            $contactModel = new ContactModel();
            $contactModel->sendEmail($data);
        }
        
        $this->redirect()->toRoute('contact');
    }
    
    public function sendSupportEmailAction()
    {
        $data = $this->request->getPost();
        
        if (!empty($data)) {
            $request = $this->getServiceLocator()->get('Request');
            $files = $request->getFiles()->toArray();
            
            $supportModel = new SupportModel();
            $supportModel->sendEmail($data, $files);
        }
        
        $this->redirect()->toRoute('support');
    }
}
