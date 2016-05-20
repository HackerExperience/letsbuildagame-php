<?php

require_once 'connection.php';

class Notification {
    
    private $_notification_id;
    private $_notification_desc;
    private $_user_id;
    private $_dbo;

    public function __construct($notification_id = FALSE, 
                                $notification_desc = FALSE) {
        $this->setNotificationId($notification_id);
        $this->setNotificationDesc($notification_desc);
        $this->_dbo = PDO_DB::factory();
    }
    
    public function getNotificationId() {
        return $this->_notification_id;
    }
    
    public function setNotificationId($notification_id) {
        $this->_notification_id = $notification_id;
    }
    
    public function setNotificationDesc($notification_desc) {
        $this->_notification_desc = $notification_desc;
    }
    
    public function getNotificationDesc() {
        return $this->_notification_desc;
    }
    
    public function getUserId() {
        return $this->_user_id;
    }
    
    public function setUserId($_user_id) {
        $this->_user_id = (int)$_user_id;
    }
    
    public function isSubscribed($user_id = FALSE) {

        if (!$this->_assert_notification_id()) {
            return Array(FALSE, 'ERR_NOTIFICATION_NOT_EXISTS');
        }
                
        if ($user_id) {
            $this->setUserId($user_id);
        } elseif (!$this->_assert_user_id()) {
            return Array(FALSE, 'MISSING_USER_ID');
        }
                
        return $this->_read($this->getUserId(), 'user_id');
        
    }
    
    public function subscribe($user_id = FALSE) {               
        
        if (!$this->_assert_notification_id()) {
            return Array(FALSE, 'ERR_NOTIFICATION_NOT_EXISTS');
        }
        
        if (!$user_id && !$this->_assert_user_id()) {
            return Array(FALSE, 'SYSTEM_ERROR');
        } else {
            $this->setUserId($user_id);
        }
        
        if ($this->isSubscribed()) {
            return Array(TRUE, '');
        }
                
        $insert = $this->_insert_subscriber();
        return Array($insert, '');
        
    }
    
    public function unsubscribe($user_id = FALSE) {
        
        if (!$this->_assert_notification_id()) {
            return Array(FALSE, 'ERR_NOTIFICATION_NOT_EXISTS');
        }
        
        if (!$user_id && !$this->_assert_user_id()) {
            return Array(FALSE, 'SYSTEM_ERROR');
        } else {
            $this->setUserId($user_id);
        }
        
        $remove = $this->_delete_subscriber();
        return Array($remove, '');
        
    }
    
    public function create() {
        
        
        
    }
    
    private function _read($search_value, $search_method='id', $limit = 1) {
                
        if ($search_method == 'id' || $search_method == 'notification_id') {
            $table_name = 'notifications';
            $column_name = 'notification_id';
        } elseif ($search_method == 'name' || $search_method == 'desc') {
            $table_name = 'notifications';
            $column_name = 'notification_desc';
        } elseif ($search_method == 'user_id' || $search_method == 'user') {
            $table_name = 'user_notifications';
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
        $sql_query = 'SELECT * FROM '.$table_name.' WHERE '.$table_name.
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
    
    private function _assert_notification_id() {
                
        if ($this->getNotificationId()) {
            return TRUE;
        }
        
        if (!$this->getNotificationDesc()) {
            return FALSE;
        }

        $query = $this->_read($this->_notification_desc, 'desc');
        
        if (!$query) {
            return FALSE;
        }
        
        $this->setNotificationId($query->notification_id);
        
        return TRUE;
        
    }
    
    private function _assert_user_id() {
        
        if ($this->_user_id) {
            return TRUE;
        }
        
        return FALSE;
        
    }
    
    private function _insert_subscriber() {
        
        if (!$this->_assert_notification_id()) {
            return FALSE;
        }
        
        if (!$this->_assert_user_id()) {
            return FALSE;
        }
        
        $sql_query = "INSERT INTO user_notifications (notification_id, user_id)"
            . " VALUES (?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        
        try {
            $sql_reg->execute(array($this->getNotificationId(), 
                                    $this->getUserId()));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }

        return TRUE;
    }
    
    private function _delete_subscriber() {
        
        if (!$this->_assert_notification_id()) {
            return FALSE;
        }
        
        if (!$this->_assert_user_id()) {
            return FALSE;
        }
        
        $sql_query = "DELETE FROM user_notifications WHERE "
                . "user_notifications.notification_id = ? AND "
                . "user_notifications.user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        
        try {
            $sql_reg->execute(array($this->getNotificationId(), 
                                    $this->getUserId()));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }

        return TRUE;
        
    }
    
}


function all_notifications() {
    
    return Array(
        'important_updates',
        'step4_starts',
        'game_released'
    );
    
}


function toggle_notification($notification_desc, $action) {
    
    $session = new Session();
    if (!$session->exists()) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }
    
    if (!isset($notification_desc)) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }    

    if (!is_string($notification_desc)) {
        return Array(FALSE, 'ERR_INVALID_NOTIFICATION');
    }
    
    $notifications = all_notifications();
    
    if (!in_array($notification_desc, $notifications)) {
        return Array(FALSE, 'ERR_NOTIFICATION_NOT_EXISTS');
    }

    $notification = new Notification();
    $notification->setNotificationDesc($notification_desc);

    if ($action == 'subscribe-notification') {
        return $notification->subscribe($session->getUserId());
    } else {
        return $notification->unsubscribe($session->getUserId());
    }
    
}
