<?php
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 11:52 PM
 */

require_once 'classes/User.php';

//if (isset($_POST['idtoken'])) {
//    $gsession = new GoogleSession($_POST['idtoken']);
//    $gsession->login();
//    echo $_SESSION['user_id'];
//}

$session = Session::getInstance();
$session->create('2cat0n2rq6ingf87dfm5l1jcg5');

//list($login, $msg) = assert_login();
//
//var_dump($login);
//
//if (!$login) {
//    $user = new User('renato', '', 'renato');
//    var_dump($user->login());
//    var_dump($_SESSION);
//    
//    
//    var_dump($session->exists());
//    
//    exit('oi');
//} else {
//    $session = new Session();
//    $session->exists();
//    var_dump($_SESSION);
//    $user = $session->getUser();
//}
//$up = new UserProject($user);
//
//var_dump(assert_login());
//var_dump($up);

fetch_user_data();