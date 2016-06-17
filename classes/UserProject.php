<?php

require_once 'connection.php';
require_once 'classes/User.php';
require_once 'classes/Project.php';

class UserProject {
    
    private $_user;
    private $_dbo;
    
    public function __construct($user) {
        $this->setUser($user);
        $this->_dbo = PDO_DB::factory();
    }
    
    public function getUser() {
        return $this->_user;
    }

    public function setUser($_user) {
        $this->_user = $_user;
    }
    
    public function get_all_teams() {
        
        $sql = '
            SELECT projects.name
            FROM user_projects
            LEFT JOIN projects ON user_projects.project_id = projects.project_id
            WHERE user_projects.user_id = :user_id AND projects.name LIKE \'%TEAM_%\'';
        $stmt = $this->_dbo->prepare($sql);

        try {
            $stmt->execute(array(':user_id' => $this->getUser()->getUserId()));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        $query = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $all_teams = Array();
        
        for ($i = 0; $i < sizeof($query); $i++) {
            $all_teams[] = Team::nice_name($query[$i]->name);
        }
                
        return $all_teams;
                
    }
    
    public function get_all_tasks() {
        
        $sql = '
            SELECT projects.name
            FROM user_projects
            LEFT JOIN projects ON user_projects.project_id = projects.project_id
            WHERE user_projects.user_id = :user_id AND projects.name LIKE \'%TASK_%\'';
        $stmt = $this->_dbo->prepare($sql);

        try {
            $stmt->execute(array(':user_id' => $this->getUser()->getUserId()));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        $query = $stmt->fetchAll(PDO::FETCH_OBJ);        
        
        $all_tasks = Array();
        
        for ($i = 0; $i < sizeof($query); $i++) {
            $all_tasks[] = Task::nice_name($query[$i]->name);
        }
                        
        return $all_tasks;
        
    }

}

function fetch_all_teams() {
    
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array(FALSE, $login_msg);
    }
    
    $session = Session::getInstance();
    
    $user_project = new UserProject($session->getUser());
    
    return $user_project->get_all_teams();
    
}

function fetch_all_tasks() {
    
    list($login_status, $login_msg) = assert_login();
    if (!$login_status) {
        return Array(FALSE, $login_msg);
    }
    
    $session = Session::getInstance();
    
    $user_project = new UserProject($session->getUser());
    
    return $user_project->get_all_tasks();
    
}