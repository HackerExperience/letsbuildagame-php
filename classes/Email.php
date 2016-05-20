<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'sendgrid/sendgrid-php.php';

class Email {
    
    private $key;
    private $sendgrid;
    
    public function __construct() {
        
        $config = $this->getConfiguration();
        $this->key = $config['sendgrid']['api-key'];
        $this->sendgrid = new SendGrid($this->key);
        
    }
    
    public function getConfiguration() {
        $config_file = __DIR__ . '/../sendgrid.local.php';
        if (!is_file($config_file)) {
            throw new Exception('Create the configuration file database. (sendgrid.local.php)');
        }

        return include $config_file;
    }
    
    public function send($EmailTemplate) {
        
        $email = new SendGrid\Email();
        $email
                ->addTo($EmailTemplate->to)
                ->setFrom($EmailTemplate->from)
                ->setSubject($EmailTemplate->subject)
                ->setText($EmailTemplate->msgText)
                ->setHtml($EmailTemplate->msgHtml)
        ;
        
        try {
            $this->sendgrid->send($email);
            return TRUE;
        } catch (SendGrid\Exception $e) {
            error_log($e);
            return FALSE;
        }
                
    }
    
}

class EmailTemplate {
    
    public $from;
    public $to;
    public $subject;
    public $msgText;
    public $msgHtml;
    
    public function __construct($from, $to) {
        $this->from = $from;
        $this->to = $to;
    }
    
    public function confirm_email($code) {
        
	$subject = "Confirm your account on Let's Build a Game";
        
        //$link = 'https://subscribe.hackerexperience.com/?code='.$code;
        
        $msgHtml = 'Thank you for creating an account on Let\'s Build a Game.<br/><br/>'
                . 'Your verification code is <strong>'.$code.'</strong>.<br/><br/>'
                . 'Please copy it and paste on the registration form.<br/><br/>'
                . 'Sincerely,<br/>the Let\'s Build a Game team.';
        $msgText = strip_tags($msgHtml);
        
        $this->subject = $subject;
        $this->msgHtml = $msgHtml;
        $this->msgText = $msgText;
        
    }

    
}
