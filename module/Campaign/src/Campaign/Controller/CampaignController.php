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
        
        // Instantiate Campaign Model
        $campaignModel = new CampaignModel();
        $campaignModel->setServiceLocator($this->_service);
        
        $data = $campaignModel->getCampaignsList();
        return new ViewModel(array('campaigns' => $data));
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
        
        $shouldSave = true;
        if ($paramId) {
            // Add ID for update
            $data['id'] = $paramId;
            // Security measure - Make sure that the campaign is assigned to the logged in user
            if (!$campaignModel->checkCampaignAuthor($paramId)) {
                Session::error('You are trying to save a campaign that is not associated to your account');
                $shouldSave = false;
            }
        }

        $params = array('action' => 'edit');
        if ($shouldSave) {
            $clonedData = clone $data;
            $campaignId = $campaignModel->process($clonedData);

            // Build redirect parameters
            if ($paramId) {
                $params['id'] = $paramId;
            } elseif ($campaignId) {
                $params['id'] = $campaignId;
            } else {
                $session = new Container('campaign');
                $session->data = $data;
            }
        }

        $this->redirect()->toRoute('campaign', $params);
    }
    
    public function viewAction()
    {
        $this->layout('layout/empty');
        $this->_service = $this->getServiceLocator();
        $id =  $this->params('id');
        
        if (!$id) {
            $this->redirect()->toRoute('home');
        }
        
        $campaignModel = new CampaignModel();
        $campaignModel->setServiceLocator($this->_service);
        $data = $campaignModel->fetchData($id);
        
        return new ViewModel($data);
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
