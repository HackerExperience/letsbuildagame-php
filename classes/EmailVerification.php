<?php

require_once 'connection.php';

class EmailVerification {
    
    private $_user;
    private $_code;
    private $_dbo;
    
    public function __construct($code = '', $user = ''){
        $this->setCode($code);
        $this->setUser($user);        
        $this->_dbo = PDO_DB::factory();
    }
 
    public function getUser(){
        return $this->_user;
    }
    
    public function setUser($user){
        $this->_user = $user;
    }
    
    public function getCode(){
        return $this->_code;
    }
    
    public function setCode($code){
        $this->_code = $code;
    }
    
    public function validate(){
        
        $user = $this->getUser();
        $code = $this->getCode();
        
        // Application has defined an user object and no code. It wants to know
        // if that user already had his email validated.
        if(!empty($user) && empty($code)){
            
            $database_code = $this->_read($user->getUserId(), 'user');
            
            if (!$database_code) {
                return TRUE;
            }
            
            return FALSE;
            
        // Application has provided a code, but no user object. It wants to know
        // if that code is valid.
        } elseif(empty($user) && !empty($code)){
            
            $database_code = $this->_read($code, 'code');
                        
            if (!$database_code) {
                return FALSE;
            }

            $this->_removeCode();
            
            return TRUE;
            
        // User object and code provided.
        } else {
            
        }
        
    }
    
    private function _read($search_value, $search_method, $limit = 1){
        
        if ($search_method == 'user') {
            $column_name = 'user_id';
        } elseif($search_method == 'code') {
            $column_name = 'code';
        } else {
            throw new Exception('No valid arguments for read.');
        }
        
        if ($limit === FALSE) {
            $limit = '';
        } elseif (is_int($limit)) {
           $limit = 'LIMIT ' . (int)$limit; 
        } else {
            throw new Exception('Invalid limit parameter');
        }
        
        $dbo = PDO_DB::factory();
        $sql_query = "SELECT * FROM email_verification WHERE email_verification.".$column_name." = :value $limit";
        $stmt = $dbo->prepare($sql_query);
        $stmt->execute(array(':value' => $search_value));
        
        return $stmt->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function generateCode(){
        $code = strtoupper(uniqid('LBAG'));
        
        $this->setCode($code);
        
        $this->_saveCode();
        
        return $this->getCode();
    }
    
    private function _saveCode(){
        $sql_query = "INSERT INTO email_verification (user_id, email, code) VALUES (?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUser()->getUserId(), $this->getUser()->getEmail(), $this->getCode()));
    }
    
    private function _removeCode(){
        $sql_query = "DELETE FROM email_verification WHERE email_verification.code = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getCode()));
    }
    
}
