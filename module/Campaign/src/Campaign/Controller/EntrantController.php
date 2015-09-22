<?php

/**
 * Entrant Controller
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Base\Model\Session;
use User\Helper\UserHelper;
use Campaign\Model\EntrantModel;

class EntrantController extends AbstractActionController
{
    protected $_service;
    
    public function detailsAction()
    {
        $this->layout('layout/dashboard');
        
        $entrantId = $this->params('id');
        $entrant = false;
        
        if ($entrantId) {
            $model = new EntrantModel();
            $model->setServiceLocator($this->getServiceLocator());
            
            $entrant = $model->load($entrantId);
        }
        
        if ($entrant && $this->validateAccess($entrant['entrant']->get('campaign')->get('user')->get('id'))) {
            $entrantName = $entrant['entrant']->get('name');
            $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Entrant - ' . $entrantName);
            
            return new ViewModel(array('data' => $entrant));
        } else {
            Session::error('You are trying to access data from an entrant that does not belong to your campaigns');
            $this->redirect()->toRoute('campaign');
        }
    }
    
    public function checkAuthentication()
    {
        $this->_service = $this->getServiceLocator();
        
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->_service);
        
        if (!$userHelper->isLoggedIn()) {
            $this->redirect()->toRoute('account', array('action' => 'login'));
            return false;
        }
        
        return true;
    }
    
    public function validateAccess($userId)
    {
        if ($this->checkAuthentication()) {
            $userHelper = new UserHelper();
            $userHelper->updateServiceLocator($this->_service);
            
            return $userHelper->getLoggedInUserId() == $userId;
        }
        
        return false;
    }
}
