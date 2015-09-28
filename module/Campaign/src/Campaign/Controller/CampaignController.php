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
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Base\Model\Session;
use User\Helper\UserHelper;
use Campaign\Model\CampaignModel;
use Campaign\Model\EntrantModel;
use Campaign\Model\WinnerModel;

class CampaignController extends AbstractActionController
{
    protected $_service;
    
    public function listAction()
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Dashboard - Campaigns');
        $this->layout('layout/dashboard');
        if ($this->checkAuthentication()) {
            if ($this->getUserPlan() == -1) {
                $this->redirect()->toRoute('checkout');
            }

            // Instantiate Campaign Model
            $campaignModel = new CampaignModel();
            $campaignModel->setServiceLocator($this->_service);

            $data = $campaignModel->getCampaignsList();
            return new ViewModel(array('campaigns' => $data));
        }
    }
    
    public function editAction()
    {
        $this->layout('layout/dashboard');
        if ($this->checkAuthentication()) {
            if ($this->getUserPlan() == -1) {
                $this->redirect()->toRoute('checkout');
            }

            // Instantiate Campaign Model
            $campaignModel = new CampaignModel();
            $campaignModel->setServiceLocator($this->_service);

            $campaignId =  $this->params('id');
            $data = $campaignModel->fetchData($campaignId);

            $title = array_key_exists('title', $data['data']) ? $data['data']['title'] : 'New Campaign';
            
            $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Dashboard - ' . $title);
            return new ViewModel($data);
        }
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
            return $this->redirect()->toRoute('home');
        }
        
        $campaignModel = new CampaignModel();
        $campaignModel->setServiceLocator($this->_service);
        
        if (!$campaignModel->validateEntrantCookie($id)) {
            // Refresh page so cookies are updated
            return $this->redirect()->refresh();
        }
        
        $data = $campaignModel->fetchView($id);
        $campaignModel->extractAndAddFonts($data['data']['titleCss'] . $data['data']['descriptionCss']);
        
        $campaignHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('campaignHelper');
        $bannerUrl = $campaignHelper->getDomain() . $campaignHelper->getBaseImagePath($data['data']['id']) . $data['data']['banner'];
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set($data['data']['title']);
        $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer')->headMeta()->appendName('og:image', $bannerUrl);
        
        return new ViewModel($data);
    }
    
    public function referenceAction()
    {
        $this->_service = $this->getServiceLocator();
        $referenceId =  $this->params('id');
        
        if (!$referenceId) {
            $this->redirect()->toRoute('home');
        }
        
        $model = new EntrantModel();
        $model->setServiceLocator($this->_service);
        
        $campaignId = $model->getCampaignIdForEntrant($referenceId);
        if ($campaignId) {
            $model->setCookie('reference', $referenceId);
            $this->redirect()->toRoute('campaign', array('action' => 'view', 'id' => $campaignId));
        } else {
            $this->redirect()->toRoute('home');
        }
    }
    
    public function updateStatusAction()
    {
        if ($this->checkAuthentication()) {
            $id =  $this->params('id');
            $status = $this->params('status');

            if (!$id) {
                $this->redirect()->toRoute('home');
            }

            if (!$status) {
                $this->redirect()->toRoute('campaign', array('action' => 'edit', 'id' => $id));
            }

            $campaignModel = new CampaignModel();
            $campaignModel->setServiceLocator($this->_service);
            $campaignModel->updateStatus($id, $status);

            $this->redirect()->toRoute('campaign', array('action' => 'edit', 'id' => $id));
        }
    }
    
    public function enterAction()
    {
        $this->_service = $this->getServiceLocator();
        
        $id =  $this->params('id');
        $data = $this->request->getPost();

        if (!$id) {
            $this->redirect()->toRoute('home');
        }
        
        $model = new EntrantModel();
        $model->setServiceLocator($this->_service);
        
        $model->add($id, $data);
        $this->redirect()->toRoute('campaign', array('action' => 'view', 'id' => $id));
    }
    
    public function completeAction()
    {
        $this->_service = $this->getServiceLocator();
        $widgetId =  $this->params('id');
        
        if (!$widgetId) {
            return new JsonModel(array(
                'status' => false,
                'message' => 'No widget Specified!'
            ));
        }
        
        $model = new EntrantModel();
        $model->setServiceLocator($this->_service);
        
        $entrant = $model->getLoadedEntrant();
        
        if (!$entrant) {
            return new JsonModel(array(
                'status' => false,
                'message' => 'No entrant found!'
            ));
        }
        
        $result = $model->addChance($entrant, $widgetId);
        
        return new JsonModel(array(
            'status' => !!$result,
            'message' => 'Save completed'
        ));
    }
    
    public function clearSessionAction()
    {
        $this->_service = $this->getServiceLocator();
        $id =  $this->params('id');
        
        $model = new EntrantModel();
        $model->setServiceLocator($this->_service);
        $model->clearCookie('entrant');
        
        if (!$id) {
            $this->redirect()->toRoute('home');
        }
        
        $this->redirect()->toRoute('campaign', array('action' => 'view', 'id' => $id));
    }
    
    public function entrantsAction()
    {
        $this->layout('layout/dashboard');
        if ($this->checkAuthentication()) {
            if ($this->getUserPlan() == -1) {
                $this->redirect()->toRoute('checkout');
            }

            $model = new EntrantModel();
            $model->setServiceLocator($this->_service);

            $entrants = $model->entrantsList();
            
            $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Dashboard - Entrants');
            return new ViewModel(array('entrants' => $entrants));
        }
    }
    
    public function saveWinnersAction()
    {
        $data = $this->request->getPost();
        $winners = $data['winners-serialized'];
        $cycle = $data['cycle'];
        
        if (empty($winners) || empty($cycle) || !$this->checkAuthentication()) {
            $this->redirect()->toRoute('campaign');
        } else {
            $model = new WinnerModel();
            $model->setServiceLocator($this->getServiceLocator());
            
            $result = $model->saveWinners($winners, $cycle);
            
            if ($result) {
                $this->redirect()->toRoute('campaign', array('action' => 'edit', 'id' => $result));
            } else {
                $this->redirect()->toRoute('campaign');
            }
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
    
    public function exportCsvAction()
    {
        $id =  $this->params('id');
        
        $model = new EntrantModel();
        $model->setServiceLocator($this->getServiceLocator());
        
        $entrants = false;
        if ($id) {
            $entrants = $model->getEntrantsForCampaign($id);
        } else {
            $entrants = $model->entrantsList();
        }
        
        $formatedData = $model->getCsvData($entrants);
        $header = array('Entrant Id','Email','Name','Registration Date','Campaign Id','Reference Id','Chances Earned','Widgets Completed','References Brought');
        
        $response = $this->csvExport('entrants.csv', $header, $formatedData);
        return $response;
    }
    
    public function exportWinnersAction()
    {
        $id =  $this->params('id');
        
        if (!$id) {
            Session::error('No was campaign specified');
            return $this->redirect()->toRoute('campaign');
        }
        
        $model = new WinnerModel();
        $model->setServiceLocator($this->getServiceLocator());
        
        $winners = $model->getCsvData($id);
        $header = array('Id', 'Email', 'Name', 'Prize');
        
        $response = $this->csvExport('winners.csv', $header, $winners);
        return $response;
    }
    
    protected function getUserPlan()
    {
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        return $helper->getLoggedInUser()->get('plan');
    }
}
