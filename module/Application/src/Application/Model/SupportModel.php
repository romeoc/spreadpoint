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

class SupportModel 
{
    /**
     * Current Data
     * @var array
     */
    protected $data;
    
    /**
     * Maximum file upload size (in megabytes)
     */
    const MAX_UPLOAD_SIZE = '10';
    
    /**
     * Send an email if the data is valid
     * @param array $data
     */
    public function sendEmail($data, $files)
    {
        $this->data = $data;
        $this->files = $files;
        
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
        
        if (!array_key_exists('subject', $this->data) || !$this->data['subject']) {
            Session::error('Please enter a subject');
            $allValid = false;
        } elseif (strlen($this->data['subject']) > 500) {
            Session::error('Your subject should not be longer than 500 characters');
            $allValid = false;
        }
        
        if (!array_key_exists('message', $this->data) || !$this->data['message']) {
            Session::error('Please enter a message');
            $allValid = false;
        } elseif (strlen($this->data['message']) > 20000) {
            Session::error('Your message should not be longer than 20000 characters');
            $allValid = false;
        }
        
        if (array_key_exists('file-upload', $this->files)) {
            $allValid = $this->isFileValid($this->files['file-upload']);
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
        
        $data = array(
            'body' => $body,
            'subject' => $this->data['subject'],
            'fromEmail' => $this->data['email'],
            'fromName' => $this->data['fullname']
        );
        
        if (array_key_exists('file-upload', $this->files) && !empty($this->files['file-upload']['name'])) {
            $data['attachments'] = array($this->files['file-upload']);
        }
        
        if (Mail::send($data)) {
            Session::success('Your message was succesfully sent');
        } else {
            Session::error('An error occured while sending your message');
        }
    }
    
    public function isFileValid($file)
    {
        $targetFile = $file["name"];

        // Validate Image Size (1048576 = 1024 * 1024)
        if ($file['size'] > self::MAX_UPLOAD_SIZE * 1048576) {
            $message =  "$targetFile is too large. Our maixmum file upload is " . self::MAX_UPLOAD_SIZE . 'MB';
            Session::error($message);
            return false;
        }
        
        return true;
    }
}
