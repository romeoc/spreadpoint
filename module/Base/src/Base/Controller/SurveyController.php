<?php

/**
 * Survey Controller
 *
 * @module     Survey
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Base\Model\Mail;

class SurveyController extends AbstractActionController
{
    public function sendAction()
    {
        $question = $this->request->getPost('question');
        $answer = $this->request->getPost('answer');
        
        if ($question && $answer) {
            $body = "<h2><strong>Question:</strong> {$question}</h2><br />"
                . "<p><strong>Answer:</strong> {$answer}</p>";
                
            $data = array(
                'body' => $body,
                'subject' => 'A Survey was Submited'
            );
            
            return Mail::send($data);
        }
        
        return false;
    }
}
