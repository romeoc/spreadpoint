<?php

/**
 * Order Model
 *
 * @module     Checkout
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Checkout\Model;

use Base\Model\AbstractModel;

class OrderModel extends AbstractModel
{
    // Initialize Order Model
    public function __construct() 
    {
        $this->init('Checkout\Entity\Order');
    }
}
