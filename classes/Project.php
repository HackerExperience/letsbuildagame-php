<?php

require_once 'connection.php';

class Project {
    private $_project_id;
    private $_name;
    private $_dbo;

    public function __construct($project_id, $name) {
        $this->setProjectId($project_id);
        $this->setName($name);
        $this->_dbo = PDO_DB::factory();
    }

    public function getProjectId() {
        return $this->_project_id;
    }

    public function setProjectId($_project_id) {
        $this->_project_id = $_project_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($_name) {
        $this->_name = $_name;
    }

    public function add() {
        $sql_query = "INSERT INTO projects(name) VALUES (?) "
                . "ON CONFLICT DO NOTHING";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getName()));
    }

    public function remove() {
        $sql_query = "DELETE FROM projects WHERE projects.projects_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId()));
    }

    private function _ensureProjectId(){
        if($this->getProjectId()){
            return TRUE;
        }
        
        $query = $this->read($this->getName(), 'name');
        
        if (!$query) {
            return FALSE;
        }
        
        $this->setProjectId($query->project_id);
        
        return TRUE;
        
    }
       
    private function read($search_value, $search_method = 'id', $limit = 1) {
                
        if ($search_method == 'id') {
            $column_name = 'project_id';
        } elseif ($search_method == 'project_name' || $search_method == 'name') {
            $column_name = 'name';
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
        
        $sql_query = "SELECT * FROM projects WHERE projects.".$column_name.
                " = :value $limit";
        $stmt = $this->_dbo->prepare($sql_query);
        
        try {
            $stmt->execute(array(':value' => $search_value));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function is_subscribed($user_id){
                                
        $sql_query = "SELECT * FROM user_projects WHERE "
                . "user_projects.user_id = :user_id AND "
                . "user_projects.project_id = :project_id LIMIT 1";
        $stmt = $this->_dbo->prepare($sql_query);
        try {
            $stmt->execute(array(':user_id' => $user_id, 
                                'project_id' => $this->getProjectId()));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        return $stmt->fetch(PDO::FETCH_OBJ);

    }
    
    public function subscribe($user_id) {
        
        if (!$this->_ensureProjectId()){
            return FALSE;
        }
        
        if($this->is_subscribed($user_id)){
            return TRUE;
        }

        $sql_query = "INSERT INTO user_projects (project_id, user_id, "
                . "is_subscribed) VALUES(?, ?, TRUE)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        try {
            $sql_reg->execute(array($this->getProjectId(), $user_id));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
                
        return TRUE;
    
    }

    public function unsubscribe($user_id) {
        
        if (!$this->_ensureProjectId()){
            return FALSE;
        }
        
        $sql_query = "DELETE FROM user_projects WHERE project_id = ? AND "
                . "user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        try {
            $sql_reg->execute(array($this->getProjectId(), $user_id));
        } catch (PDOException $e) {
            error_log($e);
            return FALSE;
        }
        
        return TRUE;
    }
}


class Team {
    
    private $_team;
    
    public function __construct($name){
            
        $team_name = 'TEAM_' . strtoupper($name);
        
        $this->setTeam(new Project(NULL, $team_name));
       
    }
    
    public function getTeam(){
        return $this->_team;
    }
    
    public function setTeam($team){
        $this->_team = $team;
    }
    
    public function join($user_id) {
        return $this->getTeam()->subscribe($user_id);
    }
    
}

class Task {
    
    private $_task;
    
    public function __construct($name){
        
        $task_name = 'TASK_' .strtoupper($name);
        
        $this->setTask(new Project(NULL, $task_name));
        
    }
 
    public function getTask(){
        return $this->_task;
    }
    
    public function setTask($task){
        $this->_task = $task;
    }
    
    public function subscribe($user_id) {
        return $this->getTask()->subscribe($user_id);
    }
    
    public function unsubscribe($user_id) {
        return $this->getTask()->unsubscribe($user_id);
    }
        
}

function all_teams(){
    
    $teams = Array(
        'dev',
        'art',
        'mgt',
        'gd',
        'translation',
        'patron',
        'student',
        'gamer',
        'other'
    );
    
    return $teams;
    
}

function all_tasks(){
    
    $tasks = Array(
        
        'dev' => Array(
            'submit-patches',
            'review-code',
            'write-tests',
            'write-docs',
            
            'tag-todo',
            'tag-waitingreview',
            'tag-bug',
            'tag-discussion',
            'tag-elixir',
            'tag-python',
            'tag-elm',
            'tag-fs',
            'tag-javascript',
            'tag-php',
            'tag-frontend',
            'tag-backend',
            'tag-infrastructure',
            'tag-security',
            'tag-optimization',
            'tag-ai',
            'tag-network',
            'tag-databases',
            'tag-pm',
            'tag-linux',
            'tag-ios',
            'tag-android',
            'tag-core',
            'tag-mobile',
            'tag-web',
            'tag-terminal',
            'tag-aerospike',
            'tag-consul',
            'tag-elastic',
            'tag-docker',
            'tag-kafka',
            'tag-samza',
            'tag-nginx',
            'tag-haproxy',
            'tag-mnesia',
            'tag-phabricator',
            'tag-postgresql',
            'tag-postgis',
        ),
        
        'art' => Array(
            'discuss-ui',
            'design-ui',
            'create-assets',
            'review-ux',
            
            'tag-design',
            'tag-mobileui',
            'tag-webui',
            'tag-terminalui',
            'tag-designdiscussion',
            'tag-designsuggestion',
            'tag-designfeedback',
            'tag-ux',
        ),
        
        'mgt' => Array(
            'triage-tasks',
            'guide-users',
            'curate-content',
            'report',
            
            'tag-report',
            'tag-meta',
            'tag-community'
        ),
        
        'gd' => Array(
            'discuss-features',
            'study-game',
            'balance',
            'design-story',
            
            'tag-storyline',
            'tag-gamemechanic',
            'tag-gamedesign',
            'tag-heep-gd',
            'tag-feature',
            'tag-featurerequest',
        ),
        
        'translation' => Array(
            'translate-text',
            'improve-text',
            'fix-typos',
            
            'tag-typos',
            'tag-translationrequest',
        ),
        
        'patron' => Array(
            'spread',
            'preorder',
            'rate-review',
            'invest'
        ),
        
        'student' => Array(
            'follow-topics',
            'learn-code',
            'study-group',
            'share-knowledge',
            'adopt-student',
            'answer-guide',
            
            'tag-question',
            'tag-advice',
            'tag-tutorial',
            'tag-resource',
            'tag-studygroup',
            'tag-seekingmentor',
            'tag-seekingstudent',
        ),
        
        'gamer' => Array(
            'play',
            'become-tester',
            'submit-ideas',
            'report-bugs',
        ),
        
        'other' => Array(
            'music',
            'offer-service',
            
            'tag-marketing',
            'tag-legal',
            'tag-social-media',
            'tag-crm',
            'tag-soundtrack'
        )
        
    );
    
    return $tasks;
    
}

function register_teams($data) {
    $session = new Session();
    
    if (!$session->exists()) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }

    if (!isset($data)) {
        return Array(FALSE, 'ERR_INVALID_DATA');
    }

    $teams = all_teams();

    $team_array = $data;

    foreach ($team_array as $key => $value) {

        if ($value !== TRUE) {
            continue;
        }

        if (strpos($key, 'team-') === false) {
            continue;
        }

        $team_id = substr($key, 5);


        if (!in_array($team_id, $teams)) {
            continue;
        }

        $team_obj = new Team($team_id);
        $team_obj->join($session->getUserId());
    }
    
    return Array(TRUE, '');
    
}

function toggle_subscription($task_id, $team_id, $action = 'subscribe-task') {
    
    $session = new Session();
    if (!$session->exists()) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }

    if (!isset($task_id) || !isset($team_id)) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }
    
    if (!is_string($task_id) || !is_string($team_id)) {
        return Array(FALSE, 'ERR_INVALID_TASK');
    }

    $teams = all_teams();
    $tasks = all_tasks();

    if (!in_array($team_id, $teams)) {
        return Array(FALSE, 'ERR_INVALID_TEAM');
    }

    if (!isset($tasks[$team_id])) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }

    if (!in_array($task_id, $tasks[$team_id])) {
        return Array(FALSE, 'ERR_TASK_NOT_EXISTS');
    }

    $task = new Task($task_id);

    if ($action == 'subscribe-task') {
        $action_success = $task->subscribe($session->getUserId());
    } else {
        $action_success = $task->unsubscribe($session->getUserId());
    }

    if (!$action_success) {
        return Array(FALSE, 'SYSTEM_ERROR');
    }

    return Array(TRUE, '');
    
}
