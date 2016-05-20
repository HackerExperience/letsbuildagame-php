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

$username = $_POST['username'];
$password = $_POST['password'];

$user_login = new User($username, '', $password);
$login = $user_login->login();

var_dump($login);

var_dump($_SESSION);