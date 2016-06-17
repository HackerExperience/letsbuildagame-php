<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require __DIR__ . '/../vendor/autoload.php';
use Mailgun\Mailgun;

class Email {
    
    private $key;
    private $client;
    private $mailgun;
    
    public function __construct() {
        
        $config = $this->getConfiguration();
        $this->key = $config['mailgun']['api-key'];
        $this->client = new \Http\Adapter\Guzzle6\Client();
        $this->mailgun = new Mailgun($this->key, $this->client);
        
    }
    
    public function getConfiguration() {
        $config_file = __DIR__ . '/../mailgun.local.php';
        if (!is_file($config_file)) {
            throw new Exception('Create the configuration file database. '
                    . '(mailgun.local.php)');
        }

        return include $config_file;
    }
    
    public function send($EmailTemplate) {
        

        
        try {
            $this->mailgun->sendMessage(
                $EmailTemplate->fromDomain, 
                array(
                    'from'    => $EmailTemplate->from, 
                    'to'      => $EmailTemplate->to, 
                    'subject' => $EmailTemplate->subject, 
                    'text'    => $EmailTemplate->msgText,
                    'html'    => $EmailTemplate->msgHtml
                )
            );
            return TRUE;
        } catch (Exception $e) {
            error_log($e);
            return FALSE;
        }
                
    }
    
}

class EmailTemplate {
    
    public $from;
    public $fromDomain;
    public $to;
    public $subject;
    public $msgText;
    public $msgHtml;
    
    public function __construct($from, $to, $name = '') {
        $this->from = $name . '<' . $from . '>';
        $this->to = $to;
        $this->fromDomain = substr(strrchr($from, "@"), 1);
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
