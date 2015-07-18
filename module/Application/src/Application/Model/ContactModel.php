<?php

/**
 * Contact Model
 *
 * @module     Application
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Application\Model;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Base\Model\Session;

class ContactModel 
{
    /**
     * On what email the message will be sent
     * @var string 
     */
    const SEND_TO_EMAIL = 'hello@spreadpoint.co';
    
    /**
     * The name of the receiver
     * @var string
     */
    const SEND_TO_NAME = 'SpreadPoint';
    
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
        $body = 'Sender Name: ' . $this->data['fullname'] 
                . PHP_EOL . 'Sender Email :' . $this->data['email'] 
                . PHP_EOL . PHP_EOL . 'Message: ' . $this->data['message'];
        
        $mail = new Message();
        $mail->setBody($body);
        $mail->setFrom($this->data['email'], $this->data['fullname']);
        $mail->addTo(self::SEND_TO_EMAIL, self::SEND_TO_NAME);
        $mail->setSubject('Message From Contact Form');

        try {
            $transport = new Sendmail();
            $transport->send($mail);
            Session::success('Your message was succesfully sent');
        } catch (\Exception $e) {
            Session::error('An error occured while sending your message');
        }
    }
}
