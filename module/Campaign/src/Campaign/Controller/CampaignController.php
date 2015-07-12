<?php

/**
 * Campaign Controller
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use User\Helper\UserHelper;
use Campaign\Model\CampaignModel;

class CampaignController extends AbstractActionController
{
    protected $_service;
    
    public function listAction()
    {
        $this->checkAuthentication();
        return new ViewModel();
    }
    
    public function editAction()
    {
        // TODO: Check if user is the one that is logged in!!!!!
        $this->checkAuthentication();

        // Instantiate Campaign Model
        $campaignModel = new CampaignModel();
        $campaignModel->setServiceLocator($this->_service);
        
        $campaignId =  $this->params('id');
        $data = $campaignModel->fetchData($campaignId);
        
        return new ViewModel($data);
    }
    
    public function saveAction()
    {
        $this->_service = $this->getServiceLocator();
        $data = $this->request->getPost();
        $paramId =  $this->params('id');
        
        $campaignModel = new CampaignModel();
        $campaignModel->setServiceLocator($this->_service);
        
        $clonedData = clone $data;
        $campaignId = $campaignModel->process($clonedData);
        
        // Build redirect parameters
        $params = array('action' => 'edit');
        if ($paramId) {
            $params['id'] = $paramId;
        } elseif ($campaignId) {
            $params['id'] = $campaignId;
        } else {
            $session = new Container('campaign');
            $session->data = $data;
        }

        $this->redirect()->toRoute('campaign', $params);
    }
    
    public function checkAuthentication()
    {
        $this->_service = $this->getServiceLocator();
        
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->_service);
        
        if (!$userHelper->isLoggedIn()) {
            $this->redirect()->toRoute('account', array('action' => 'login'));
        }
        
        return $this;
    }
}
