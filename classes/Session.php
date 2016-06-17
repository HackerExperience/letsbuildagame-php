<?php

require_once 'classes/connection.php';
//require_once '../vendor/autoload.php';

class Session {
    private static $_instance;
    private $_user;
    private $_dbo;

    protected function __construct() {
        $this->start();
        $this->_dbo = PDO_DB::factory();
    }

    public function getUserId() {
        return $this->_user->getUserId();
    }
    
    public function getUser() {
        return $this->_user;
    }
    
    public function setUser($user) {
        $this->_user = $user;
    }
    
    public static function getInstance() {
        
        if (static::$_instance === NULL) {
            static::$_instance = new static();
        }
        
        return static::$_instance;
    }
    
    public function exists(){
        if(isset($_SESSION['user_id']) && isset($_SESSION['ua'])){
            if ($this->validate()) {

                $user = new User('', '', '');
                $user->setUserId($_SESSION['user_id']);
                $user->fetch_data_from_id();
                
                $this->setUser($user);
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function start(){
        if (session_status() == PHP_SESSION_NONE) {
            //session_set_cookie_params(0, '/', '.letsbuildagame.org');
            session_start();
        }
    }
    
    public function create($sess_id = FALSE){
        
        if ($sess_id) {
            
            # The session we are asking to create is the current one 
            if (session_id() == $sess_id) {
                return;
            }
            
            # Destroy current session and recreate the requested one
            $this->destroy();
            session_id($sess_id);
            $this->start();
            return;
        }
        
        session_regenerate_id(TRUE);
        $_SESSION['user_id'] = $this->getUserId();
        
        if(isset($_SERVER['HTTP_USER_AGENT'])){
            $ua = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $ua = '';
        }
        
        $_SESSION['ua'] = $ua;
    }
    
    public function destroy(){
        $_SESSION = NULL;
        session_destroy();
    }
    
    public function validate(){
        if(!is_int($_SESSION['user_id'])){
            $this->destroy();
            return FALSE;
        }
	if( $_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT']){
            $this->destroy();
            return FALSE;
        }
        return TRUE;
    }

    // public static function login_google_auth($token) {
    //     global $google_client_id;
    //     global $google_client_secret;
    //
    //     $client = new Google_Client();
    //     $client->setClientId($google_client_id);
    //     $client->setClientSecret($google_client_secret);
    //     $client->setRedirectUri("http://localhost:8080/index.php");
    //
    //     session_start();
    //     session_regenerate_id();
    //
    //     $ticket = $client->verifyIdToken($token);
    //     if ($ticket) {
    //         $data = $ticket->getAttributes();
    //         $_SESSION['user_id'] = $data['payload']['sub'];
    //         $_SESSION['user_name'] = $data['payload']['name'];
    //         return $data['payload']['sub'];
    //     }
    //     return false;
    // }
}