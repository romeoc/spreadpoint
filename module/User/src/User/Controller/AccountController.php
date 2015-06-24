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
use User\Model\Form\RegisterForm;
use User\Model\Form\LoginForm;
use User\Model\Form\Register\RegisterFilter;

class AccountController extends AbstractActionController
{
    public function indexAction()
    {
        $service = $this->getServiceLocator();
        $helper = new UserHelper();
        $helper->updateServiceLocator($service);
        
        if (!$helper->isLoggedIn()) {
            return $this->redirect()->toRoute('account', array('controller' => 'account', 'action' => 'login'));
        }
        
        var_dump('Welcome lad!'); die;
    }
    
    public function registerAction()
    {
        $form = new RegisterForm();
        $view = new ViewModel(array(
            'form' => $form
        ));
        
        $view->setTerminal(true);
        return $view;
    }
    
    public function loginAction()
    {
        $form = new LoginForm();
        $view = new ViewModel(array(
            'form' => $form
        ));
        
        $view->setTerminal(true);
        return $view;
    }
    
    /**
     * create a new user
     */
    public function processRegisterAction() 
    {
        $post = $this->request->getPost();

        $form = new RegisterForm();
        $filter = new RegisterFilter();
        
        $form->setInputFilter($filter);
        $form->setData($post);
        
        if (!$form->isValid()) {
            $view = new ViewModel(array(
                'error' => true,
                'form' => $form,
            ));
            $view->setTemplate('user/account/register');
            $view->setTerminal(true);
            return $view;
        }
        
        unset($post['submit']);
        $userModel = new User();
        $userModel->setServiceLocator($this->getServiceLocator());
        $registrationResult = $userModel->create($post);
        
        if (!$registrationResult) {
            $view = new ViewModel(array(
                'message' => $post['email'].' belongs to an existing account',
                'form' => $form,
            ));
            $view->setTemplate('user/account/register');
            $view->setTerminal(true);
            return $view;
        }
        
        return new JsonModel(array(
            'success'  => true, 
            'redirect' => true,
            'user'     => $registrationResult,
        ));
    }
    
    public function authenticateAction()
    {
        $data = $this->getRequest()->getPost();
        
        $userModel = new User();
        $userModel->setServiceLocator($this->getServiceLocator());
        $authenticationResult = $userModel->authenticate($data['email'], hash('sha512',$data['password']));
        
        if ($authenticationResult) {
            return new JsonModel(array(
                'success' => true, 
                'user'    => $authenticationResult
            ));
        }

        $view = new ViewModel(array(
            'error' => 'Your authentication credentials are not valid',
            'form' => new LoginForm(),
        ));

        $view->setTemplate('user/account/login');
        $view->setTerminal(true);
        return $view;
    }
    
    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $auth->clearIdentity();
        
        return $this->redirect()->toRoute('home');
    }
}
