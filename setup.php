<?php

//if(php_sapi_name() != 'cli') exit();

require_once 'classes/Project.php';
require_once 'classes/Notification.php';

// -----------------------------------------------------------------------------
// Add all teams to the database
// -----------------------------------------------------------------------------

$all_teams = all_teams();

foreach ($all_teams as $name => $team) {
    $teamobj = new Team($team);
    $teamobj->getTeam()->add();
}

// -----------------------------------------------------------------------------
// Add all tasks to the database
// -----------------------------------------------------------------------------

$all_tasks = all_tasks();

foreach ($all_tasks as $name => $task_group) {
    foreach ($task_group as $task) {
        $taskobj = new Task($task);
        $taskobj->getTask()->add();
    }
}

// -----------------------------------------------------------------------------
// Add all notifications to the database
// -----------------------------------------------------------------------------

$all_notifications = all_notifications();

foreach ($all_notifications as $notification_id => $notification_name) {
    $notificationobj = new Notification($notification_id, $notification_name);
    $notificationobj->add();
}