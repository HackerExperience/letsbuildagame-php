<?php

require_once 'classes/connection.php';
require_once 'classes/EmailVerification.php';
require_once 'classes/Session.php';
require_once 'classes/UserProject.php';


class User {
    
    private $_user_id;
    private $_username;
    private $_email;
    private $_password;
    private $_date_registered;
    private $_dbo;

    public function __construct($username, $email, $password) {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->_dbo = PDO_DB::factory();
    }

    public function getUserId() {
        return $this->_user_id;
    }

    public function setUserId($_user_id) {
        $this->_user_id = (int)$_user_id;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setUsername($_name) {
        $this->_username = (string)$_name;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function setEmail($_email) {
        $this->_email = (string)$_email;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setPassword($password) {
        $this->_password = (string)$password;
    }
    
    public function validateUsername() {
        
        $username = $this->getUsername();
        
        // Do not accept usernames made only of numbers
        if (ctype_digit($username)) {
            return Array(FALSE, 'ERR_INVALID_USERNAME');
        }
        
        // Do not accept too big or too small usernames
        if (strlen($username) >= 15) {
            return Array(FALSE, 'ERR_USERNAME_TOO_BIG');
        } elseif (strlen($username) < 2) {
            return Array(FALSE, 'ERR_USERNAME_TOO_SMALL');
        }
        
        // Assert username starts with a character or any of: _.-
        if (!preg_match('/^[a-zA-Z0-9_.-]{2,15}$/', $username)) {
            return Array(FALSE, 'ERR_INVALID_USERNAME');
        }
        
        //Check if user already exists
        if ($this->read($username, 'username')) { 
           return Array(FALSE, 'ERR_USERNAME_EXISTS');
        }
        
        // Username is valid.
        return Array(TRUE, '');
    }
    
    public function validateEmail() {
        
        $email = $this->getEmail();
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Array(FALSE, 'ERR_INVALID_EMAIL');
        }
        
        // Check if email already exists
        if ($this->read($email, 'email')) { 
           return Array(FALSE, 'ERR_EMAIL_EXISTS');
        }
        
        // Email is valid.
        return Array(TRUE, '');
        
    }
    
    public function validatePassword(){
        
        $password = $this->getPassword();
        
        if(strlen($password) < 6){
            return Array(FALSE, 'ERR_PASSWORD_SMALL');
        }
        
        if($password == $this->getUsername()){
            return Array(FALSE, 'ERR_PASSWORD_WEAK');
        }
        
        return Array(TRUE, '');
        
    }
    
    public function validateAll(){
        list($vu, $vu_msg) = $this->validateUsername();
        if (!$vu){
            return Array(FALSE, $vu_msg);
        }
        
        list($ve, $ve_msg) = $this->validateEmail();
        if(!$ve){
            return Array(FALSE, $ve_msg);
        }
        
        list($vp, $vp_msg) = $this->validatePassword();
        if(!$vp){
            return Array(FALSE, $vp_msg);
        }
        
        return Array(TRUE, '');
    }
    
    public function login(){
        
        $session = Session::getInstance();
        
        if($session->exists()){
            $session->destroy();
            $session->start();
        }
        
        list($auth_success, $auth_msg) = $this->_authenticate();
        
        if(!$auth_success){
            return Array(FALSE, $auth_msg);
        }
        
        $session->setUser($this);
        $session->create();
        
        return Array(TRUE, '');
        
    }
    
    public function logout(){
        
        $session = Session::getInstance();
        
        if(!$session->exists()){
            return Array(FALSE, 'NOT_LOGGED_IN');
        }
        
        $session->destroy();
        
        return Array(TRUE, '');
        
    }
    
    private function _authenticate(){

        $username = $this->getUsername();
        $password = $this->hashPassword($this->getPassword());
        
        $search_value = $username;
        $search_method = 'username';
        
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->setEmail($username);
            $this->setUsername(NULL);
            $search_method = 'email';
        }
        
        $query = $this->read($search_value, $search_method);
                
        if(!$query){
            return Array(FALSE, 'USERNAME_DOESNT_EXISTS');
        }
        
        if (!password_verify($this->getPassword(), $password)) {
            return Array(FALSE, 'PASSWORD_MISMATCH');
        }
        
        $this->setUserId($query->user_id);
        $this->setUsername($query->username);
        $this->setEmail($query->email);
        
        return Array(TRUE, '');
        
    }
    
    public function fetch_data_from_id() {
        $query = $this->read($this->getUserId(), 'id');
        
        $this->setUsername($query->username);
        $this->setEmail($query->email);
        
        return $this;
    }
    
    public function create() {
        
        list($validate_success, $validate_msg) = $this->validateAll();
        
        if(!$validate_success){
            return Array(FALSE, $validate_msg);
        }        
        
        $sql_query = "INSERT INTO users(username, email, password, origin) VALUES (?, ?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        
        try {
            $sql_reg->execute(array($this->getUsername(), $this->getEmail(), 
                                    User::hashPassword($this->getPassword()),
                                    Session::getOrigin()));
            $userID = $this->_dbo->lastInsertId('users_user_id_seq');
        } catch (PDOException $e) {
            error_log($e);
            return Array(FALSE, 'SYSTEM_ERROR');
        }
        
        $this->setUserId($userID);
        
        $email_verification = new EmailVerification('', $this);
        $verification_code = $email_verification->generateCode();
        
        $this->send_code($this->getEmail(), $verification_code);
        
        return Array(TRUE, '');
    }

    private function read($search_value, $search_method='id', $limit = 1) {
                
        if ($search_method == 'id') {
            $column_name = 'user_id';
        } elseif($search_method == 'username' || $search_method == 'name' ) {
            $column_name = 'username';
        } elseif($search_method == 'email') {
            $column_name = 'email';
        } else {
            throw new Exception('No valid arguments for user read.');
        }
        
        if ($limit === FALSE) {
            $limit = '';
        } elseif (is_int($limit)) {
           $limit = 'LIMIT ' . (int)$limit; 
        } else {
            throw new Exception('Invalid limit parameter');
        }
        
        $dbo = PDO_DB::factory();
        $sql_query = "SELECT * FROM users WHERE users.".$column_name." = :value $limit";
        $stmt = $dbo->prepare($sql_query);
        
        try {
            $stmt->execute(array(':value' => $search_value));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    private static function hashPassword($password) {
        $options = [
            'cost' => 14,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function send_code($to, $code) {
        
        $origin = Session::getOrigin();
        
        if ($origin == 'lbag') {
            $name = 'Let\'s Build a Game';
        } else {
            $name = 'Vamos Fazer um Jogo';
        }
        
        $tpl = new EmailTemplate('contact@letsbuildagame.org', $to, $name);
        $tpl->confirm_email($code);

        $email = new Email();
        return $email->send($tpl);
    }
    
    public function fetch_user_metadata() {
        
        return Array(
            'username' => $this->getUsername(),
        );
        
    }
}

function validate_user($username) {
    
    if (!isset($username)) {
        return Array(FALSE, 'ERR_INVALID_DATA');
    }

    $user = new User($username, '', '');

    return $user->validateUsername();
    
}


function validate_email($email) {
    
    if (!isset($email)) {
        return Array(FALSE, 'ERR_INVALID_DATA');
    }

    $user = new User('', $email, '');

    return $user->validateEmail();
    
}

function register_user($username, $password, $email) {
    
    if (!isset($username) || !isset($email) || !isset($password)) {
        return Array(FALSE, 'ERR_INVALID_DATA');
    }

    $user = new User($username, $email, $password);
    list($registration_success, $registration_msg) = $user->create();

    if ($registration_success) {
        $user->login();
        $registration_msg = session_id();
    }
    
    return Array($registration_success, $registration_msg);
    
}

function verify_email($code) {
    
    if (!isset($code)) {
        return Array(FALSE, 'ERR_INVALID_DATA');
    }

    $verification = new EmailVerification($code, '');

    return Array($verification->validate(), '');
    
}

function login_user($username, $password) {
    
    $user = new User($username, '', $password);
    
    list($login_success, $login_msg) = $user->login();
    
    if ($login_success) {
        $login_msg = session_id();
    }
    
    return Array($login_success, $login_msg);
    
}

function assert_login() {
    
    $error_msg = 'NOT_LOGGED_IN';
    
    $session = Session::getInstance();
    if (!$session->exists()) {
        return Array(FALSE, $error_msg);
    }
    
    $is_valid = $session->validate();
    
    if ($is_valid) {
        $error_msg = '';
    }
        
    return Array($is_valid, $error_msg);
}


function fetch_user_data() {
        
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array(FALSE, $login_msg);
    }
    
    $data = Array(
        'meta' => fetch_metadata(),
        'teams' => fetch_all_teams(),
        'tasks' => fetch_all_tasks(),
        'settings' => fetch_all_settings(),
        'notifications' => fetch_all_notifications()
    );
    
    return Array(TRUE, $data);
    
}

function fetch_metadata() {
    
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array(FALSE, $login_msg);
    }
    
    $session = Session::getInstance();
    return $session->getUser()->fetch_user_metadata();
    
}