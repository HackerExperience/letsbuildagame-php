<?php

require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'classes/EmailVerification.php';
require_once 'classes/Project.php';
require_once 'classes/Notification.php';
require_once 'classes/Settings.php';
require_once 'classes/Email.php';

$result = Array(
    'status' => FALSE,
    'msg' => ''
);

function update_result($status_array) {
    list($result['status'], $result['msg']) = $status_array;
    return $result;
}

function function_dispatcher($function) {
    
    if (isset($_POST['sess'])) {
        $session = new Session();
        $session->create($_POST['sess']);
    }
    
    switch ($function) {
        
        case 'validate-user':
            return update_result(validate_user($_POST['data']));
            
        case 'validate-email':
            return update_result(validate_email($_POST['data']));
        
        case 'register-user':
            return 
                update_result(
                    register_user(
                        $_POST['username'], 
                        $_POST['password'], 
                        $_POST['email']
                    )
                );
            
        case 'verify-email':
            return update_result(verify_email($_POST['code']));
            
        case 'register-teams':
            return update_result(register_teams($_POST['data']));
            
        case 'subscribe-task':
        case 'unsubscribe-task':
            return 
                update_result(
                    toggle_subscription(
                        $_POST['task_id'], 
                        $_POST['team_id'], 
                        $function
                    )
                );

        case 'subscribe-notification':
        case 'unsubscribe-notification':
            return 
                update_result(
                toggle_notification(
                        $_POST['notification_desc'], 
                        $function
                    )
                );
            
        case 'update-settings':
            return 
                update_result(
                    upsert_setting(
                        $_POST['setting_name'], 
                        $_POST['setting_value']
                    )
                );
            
            
        default:
            return update_result(Array(FALSE, 'INVALID_FUNCTION'));
        
    }
    
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['func'])) {
    $result = function_dispatcher($_POST['func']);
}

# Return the content in JSON format
header('Content-type: application/json');
die(json_encode($result));