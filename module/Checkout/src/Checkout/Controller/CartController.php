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

class CartController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}
