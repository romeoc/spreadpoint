<?php

/**
 * Mail Model
 * 
 * Module: Base
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 * 
 */

namespace Base\Model;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\View\Model\ViewModel;

class Mail
{
    /**
     * The email template to be used
     */
    const TEMPLATE = 'email/templates/default';
    
    /**
     * On what email the message will be sent
     * @var string 
     */
    const EMAIL = 'hello@spreadpoint.co';
    
    /**
     * The name of the receiver
     * @var string
     */
    const NAME = 'SpreadPoint';
    
    public static function send(
            $body, 
            $subject, 
            $fromEmail = self::EMAIL, 
            $fromName = self::NAME, 
            $toEmail = self::EMAIL, 
            $toName = self::NAME,
            $service = null
    ) {
        
        if ($service) {
            $body = self::getContent($service, $body, self::TEMPLATE);
        }
        
        var_dump($body); die;
        
        $html = new MimePart($body);
        $html->type = "text/html";
        
        $body = new MimeMessage();
        $body->setParts(array($html));
        
        $mail = new Message();
        $mail->setBody($body);
        $mail->setFrom($fromEmail, $fromName);
        $mail->addTo($toEmail, $toName);
        $mail->setSubject($subject);

        try {
            $transport = new Sendmail();
            $transport->send($mail);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public static function getContent($service, $data, $template)
    {
        if (!is_array($data)) {
            $data = array('body' => $data);
        }
        
        $view = new ViewModel($data);
        $view->setTemplate($template);
        $view->setTerminal(true);
        
        $viewRender = $service->get('ViewRenderer');
        return $viewRender->render($view);
    }
}
