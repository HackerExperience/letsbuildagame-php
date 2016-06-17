<?php

require_once 'connection.php';

class Settings {
    
    private $_user_id;
    private $_settings;
    private $_dbo;

    public function __construct($user_id = FALSE) {
        $this->setUserId($user_id);
        $this->_dbo = PDO_DB::factory();
    }
    
    public function getSettings() {
        return $this->_settings;
    }
    
    public function setSettings($setting_array) {
        $this->_settings = $setting_array;
    }
    
    public function getUserId() {
        return $this->_user_id;
    }
    
    public function setUserId($_user_id) {
        $this->_user_id = (int)$_user_id;
    }
    
    public function exists($setting_name) {
        return in_array($setting_name, self::all_settings());
    }
    
    public function issetEntry($setting_name) {
        
        if (!$this->exists($setting_name)) {
            return FALSE;
        }
        
        if (!$this->_assert_user_id()) {
            return FALSE;
        }
        
//        $query = $this->_read($this->getUserId());       
//        
//        if (!$query) {
//            return FALSE;
//        }
//
//        if (!property_exists($query, $setting_name)) {
//            return FALSE;
//        }
//        
//        $this->setSettings((array)$query);
        
        return TRUE;
        
    }
    
    public function addEntry($setting_name, $setting_value) {
        return $this->updateEntry($setting_name, $setting_value);
    }
    
    public function updateEntry($setting_name, $setting_value) {
        
        if (!$this->_assert_user_id()) {
            return Array(FALSE, 'SYSTEM_ERROR');
        }
        
        if (!$this->issetEntry($setting_name)) {
            return Array(FALSE, 'SETTING_NOT_EXISTS');
        }
        
        if (!$this->_assert_response($setting_name, $setting_value)) {
            return Array(FALSE, 'INVALID_RESPONSE');
        }
        
        $update_status = $this->_update($setting_name, $setting_value);
        
        return Array($update_status, '');
        
        
    }

    private function _assert_user_id() {
        
        if ($this->getUserId()) {
           return TRUE; 
        }
        
        return FALSE;
        
    }
    
    private function _assert_response($setting_name, $setting_value) {
        
        $all_responses = self::all_responses();
        
        // If the setting is not listed on `all_responses`, then any input is OK
        if (!isset($all_responses[$setting_name])) {
            return TRUE;
        }
        
        return in_array($setting_value, $all_responses[$setting_name]);
        
    }
    
    private function _read($search_value, $search_method = 'user', $limit = 1) {
                
        if ($search_method == 'user') {
            $column_name = 'user_id';
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
        $sql_query = 'SELECT * FROM settings WHERE settings'.
            '.'.$column_name.' = :value '.$limit;
        $stmt = $dbo->prepare($sql_query);

        try {
            $stmt->execute(array(':value' => $search_value));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    private function _update($setting_name, $setting_value) {
        
        if (!$this->exists($setting_name)) {
            return FALSE;
        }
        
        $sql_query = "INSERT INTO settings (user_id, ".$setting_name.")
        VALUES (:user_id, :setting_value)
        ON CONFLICT (user_id)
        DO UPDATE SET ".$setting_name." = :setting_value";
        
        $sql_reg = $this->_dbo->prepare($sql_query);
        
        try {
            $sql_reg->execute(array(':user_id' => $this->getUserId(), 
                                    ':setting_value' => $setting_value));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }

        return TRUE;
        
    }
    
    static public function all_settings() {
        return Array(
            'notifications_frequency'
        );
    }
    
    static public function all_responses() {
        
        return Array(
            
            'notifications_frequency' => Array(
                '0', '1x1', '2x7', '1x7', '2x30', '1x30'
            ),
            
        );
        
    }
    
    public function get_user_settings() {
        
        if (!$this->_assert_user_id()) {
            return FALSE;
        }
        
        $query = $this->_read($this->getUserId());
        
        if (!$query) {
            return FALSE;
        }
        
        $settings = (array)$query;
        unset($settings['user_id']);
     
        return $settings;
    }
    
}

function upsert_setting($setting_name, $setting_value) {    
    
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array($login_status, $login_msg);
    }
    
    $session = Session::getInstance();
    
    $settings = new Settings($session->getUserId());
    return $settings->updateEntry($setting_name, $setting_value);
    
}


function fetch_all_settings() {
    
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array(FALSE, $login_msg);
    }
    
    $session = Session::getInstance();
    
    $settings = new Settings($session->getUserId());
    return $settings->get_user_settings();
    
}