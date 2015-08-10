<?php

/**
 * Account Controller
 *
 * @module     User
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use User\Model\User;
use User\Helper\UserHelper;
use User\Model\Form\LoginForm;
use User\Model\Form\RegisterForm;
use User\Model\Form\Register\RegisterFilter;
use Base\Model\Session;

class AccountController extends AbstractActionController
{
    public function indexAction()
    {
        if (!$this->getHelper()->isLoggedIn()) {
            return $this->redirect()->toRoute('home');
        }
        
        return new ViewModel();
    }
    
    public function loginAction()
    {
        return new ViewModel(array('form' => new LoginForm()));
    }
    
    /**
     * create a new user
     */
    public function createAction() 
    {
        $post = $this->request->getPost();

        $form = new RegisterForm();
        $filter = new RegisterFilter();
        
        $form->setInputFilter($filter);
        $form->setData($post);
        
        if (!$form->isValid()) {
            return new JsonModel(
                array(
                    'status' => false,
                    'message' => 'We encountered a problem while validating your data.'
                )
            );
        }
        
        unset($post['terms-and-conditions']);
        
        $userModel = new User();
        $userModel->setServiceLocator($this->getServiceLocator());
        $registrationResult = $userModel->create($post);
        
        if (!$registrationResult) {
            return new JsonModel(
                array(
                    'status' => false,
                    'message' => 'There is already an account registered to '.$post['email']
                )
            );
        }
        
        return new JsonModel(
            array(
                'status' => true,
                'message' => 'Registration was succesful'
            )
        );
    }
    
    public function authAction()
    {
        $data = $this->request->getPost();
        
        $userModel = new User();
        $userModel->setServiceLocator($this->getServiceLocator());
        $authenticationResult = $userModel->authenticate($data['email'], $data['password']);
        
        if ($authenticationResult) {
            if ($data['as-json']) {
                return new JsonModel(array(
                    'status'    => true, 
                    'message'   => 'Login Succesful'
                ));
            } else {
                return $this->redirect()->toRoute('campaign');
            }
        }

        if ($data['as-json']) {
            return new JsonModel(array(
                'status' => false,
                'message' => 'Your authentication credentials are not valid'
            ));
        } else {
            Session::error('Your authentication credentials are not valid');
            $this->redirect()->toRoute('account', array('action' => 'login'));
        }
    }
    
    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $auth->clearIdentity();
        
        return $this->redirect()->toRoute('home');
    }
    
    public function settingsAction()
    {
        $helper = $this->getHelper();
        
        if (!$helper->isLoggedIn()) {
            $this->redirect()->toRoute('account', array('action' => 'login'));
        }
        
        return new ViewModel(array('user' => $helper->getLoggedInUser()));
    }
    
    public function saveAction()
    {
        $userId = $this->getHelper()->getLoggedInUserId();
        $data = $this->request->getPost();
        
        if ($data && $userId) {
            $user = new User();
            $user->setServiceLocator($this->getServiceLocator());
            $data['id'] = $userId;

            $user->prepare($data);
            $user->save($data);
        }
        
        $this->redirect()->toRoute('account', array('action' => 'settings'));
    }
    
    public function getHelper()
    {
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->getServiceLocator());
        
        return $userHelper;
    }
}
