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

class Mail
{
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
            $toName = self::NAME
    ) {
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
}
