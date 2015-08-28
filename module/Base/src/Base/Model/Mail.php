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
use Zend\Mime\Mime;
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
    
    public static function send($data) 
    {
        $body = $data['body'];
        $subject = $data['subject'];
        $fromEmail = (array_key_exists('fromEmail', $data)) ? $data['fromEmail'] : self::EMAIL;
        $fromName = (array_key_exists('fromName', $data)) ? $data['fromName'] : self::NAME;
        $toEmail = (array_key_exists('toEmail', $data)) ? $data['toEmail'] : self::EMAIL;
        $toName = (array_key_exists('toName', $data)) ? $data['toName'] : self::NAME;
        
        $body = self::repalceVariables($body);
        
        $allowedTags = '<address><blockquote><h1><h2><h3><h4><h5><h6><hr><caption>'
                . '<p><pre><ol><ul><li><br><dl><lh><dt><dd><table><th><tr><td>'
                . '<a><cite><code><em><i><strong><b><big><small><sub><sup>';
        $body = strip_tags($body, $allowedTags);
        
        if (array_key_exists('service', $data)) {
            $template = array_key_exists('template', $data) ? $data['template'] : self::TEMPLATE;
            $templateData = array_key_exists('templateData', $data) ? $data['templateData'] : $body;

            $body = self::getContent($data['service'], $templateData, $template);
        }
        
        $parts = array();
        
        $html = new MimePart($body);
        $html->type = "text/html";
        $parts[] = $html;
        
        if (array_key_exists('attachments', $data)) {
            foreach ($data['attachments'] as $file) {
                $attachment = new MimePart(fopen($file['tmp_name'], 'r'));
                $attachment->type = $file['type'];
                $attachment->encoding    = Mime::ENCODING_BASE64;
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                
                $parts[] = $attachment;
            }
        }
        
        $body = new MimeMessage();
        $body->setParts($parts);
        
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
    
    public static function repalceVariables($content)
    {
        // bold text
        $content = preg_replace('#\*(.*?)\*#', '<strong>$1</strong>', $content);
        
        // italic text
        $content = preg_replace('#\_(.*?)\_#', '<em>$1</em>', $content);
        
        // New Lines
        $content = str_replace("\n", '<br />', $content);
        
        // URLs
        $urls = array();
        preg_match_all('/\<(.*?)\>/s', $content, $urls);
        foreach ($urls as $url) {
            $stripedUrl = substr($url[0], 1, -1);
            if (strpos($stripedUrl,'|') !== false) {
                list($title, $link) = explode('|', $stripedUrl);
                $tag = "<a href='{$link}' title='{$title}'>{$title}</a>";
                $content = str_replace($url[0], $tag, $content);
            }
        }
        
        return $content;
    }
    
    public static function replaceCustomVariables($content, $data)
    {
        // Custom variables
        foreach ($data as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
     
        return $content;
    }
}
