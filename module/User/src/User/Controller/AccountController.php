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
use User\Model\Form\Register\RegisterFilter;

class AccountController extends AbstractActionController
{
    public function indexAction()
    {
        $service = $this->getServiceLocator();
        $helper = new UserHelper();
        $helper->updateServiceLocator($service);
        
        if (!$helper->isLoggedIn()) {
            return $this->redirect()->toRoute('home');
        }
        
        return new ViewModel();
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
    
    public function loginAction()
    {
        $data = $this->request->getPost();
        
        $userModel = new User();
        $userModel->setServiceLocator($this->getServiceLocator());
        $authenticationResult = $userModel->authenticate($data['email'], $data['password']);
        
        if ($authenticationResult) {
            return new JsonModel(array(
                'status'    => true, 
                'message'   => 'Login Succesful'
            ));
        }

        return new JsonModel(array(
            'status' => false,
            'message' => 'Your authentication credentials are not valid'
        ));
    }
    
    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
        $auth->clearIdentity();
        
        return $this->redirect()->toRoute('home');
    }
}
