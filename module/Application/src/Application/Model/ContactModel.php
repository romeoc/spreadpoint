<?php

/**
 * Contact Model
 *
 * @module     Application
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Application\Model;

use Base\Model\Mail;
use Base\Model\Session;

class ContactModel 
{
    /**
     * Current Data
     * @var array
     */
    protected $data;
    
    /**
     * Send an email if the data is valid
     * @param array $data
     */
    public function sendEmail($data)
    {
        $this->data = $data;
        
        if ($this->validate()) {
            $this->send();
        }
    }
    
    /**
     * Validate contact form data
     */
    protected function validate()
    {
        $allValid = true;
        
        if (!array_key_exists('fullname', $this->data) || !$this->data['fullname']) {
            Session::error('Please enter your full nume');
            $allValid = false;
        } elseif (strlen($this->data['fullname']) > 500) {
            Session::error('Your full name should not be longer than 500 characters');
            $allValid = false;
        }
        
        $emailValidator = new \Zend\Validator\EmailAddress();
        if (!array_key_exists('email', $this->data) || !$this->data['email']) {
            Session::error('Please enter your email');
            $allValid = false;
        } elseif (strlen($this->data['email']) > 500) {
            Session::error('Your email should not be longer than 500 characters');
            $allValid = false;
        } elseif (!$emailValidator->isValid($this->data['email'])) {
            Session::error('Please enter a valid email address');
            $allValid = false;
        }
        
        if (!array_key_exists('message', $this->data) || !$this->data['message']) {
            Session::error('Please enter a message');
            $allValid = false;
        } elseif (strlen($this->data['message']) > 20000) {
            Session::error('Your email should not be longer than 20000 characters');
            $allValid = false;
        }
        
        return $allValid;
    }
    
    /**
     * Sending the actual email
     */
    protected function send()
    {
        $body = "<p><strong>Sender Name:</strong> {$this->data['fullname']}</p>" 
                . "<p><strong>Sender Email:</strong> {$this->data['email']}</p><br />"
                . "<p>{$this->data['message']}</p>";
        
        $data = array(
            'body' => $body,
            'subject' => 'Message From Contact Form',
            'fromEmail' => $this->data['email'],
            'fromName' => $this->data['fullname']
        );
        
        if (Mail::send($data)) {
            Session::success('Your message was succesfully sent');
        } else {
            Session::error('An error occured while sending your message');
        }
    }
}
