<?php

/**
 * Global Messages Session Model
 * 
 * Module: Base
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 * 
 */

namespace Base\Model;

use Zend\Session\Container;

class Session
{
    protected static $_session;
    
    // Get global messages session
    public static function getSession() 
    {
        if (!self::$_session) {
            self::$_session = new Container('global_messages');
        }
        
        return self::$_session;
    }
    
    public static function getGlobalMessages()
    {
        $session = self::getSession();
        return $session->messages;
    }
    
    protected static function addMessage($message, $type)
    {
        $session = self::getSession();
        $messages = $session->messages;
        
        $messages[] = array('type' => $type, 'message' => $message);
        $session->messages = $messages;
    }
    
    public static function success($message)
    {
        self::addMessage($message, 'success');
    }
    
    public static function notice($message)
    {
        self::addMessage($message, 'notice');
    }
    
    public static function error($message)
    {
        self::addMessage($message, 'error');
    }
    
    public static function clear()
    {
        $session = self::getSession();
        $session->offsetUnset('messages');
    }
}
