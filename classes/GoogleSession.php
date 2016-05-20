<?php

require_once 'vendor/autoload.php';
require_once 'connection.php';
require_once 'secrets.local.php';

class GoogleSession {
    private $_client;
    private $_token;
    private $_dbo;
    private $_data;

    public function __construct($token) {
        global $google_client_id;
        global $google_client_secret;

        $this->_client = new Google_Client();
        $this->_client->setClientId($google_client_id);
        $this->_client->setClientSecret($google_client_secret);
        $this->_client->setRedirectUri("http://localhost:8080/index.php");

        $this->_token = $token;

        $this->_dbo = PDO_DB::factory();
    }

    private function checkEmail() {
        $sql_query = "SELEFT * FROM users WHERE email=? AND provider=google";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->_data['payload']['email']));

        if ($sql_reg->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function insertUser() {
        $sql_query = "INSERT INTO users(name, email, provider) VALUES (?, ?, 'google')";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array(
            $this->_data['payload']['name'],
            $this->_data['payload']['email']
        ));
    }

    private function insertUserLDAP() {
        $ldap = new LDAP();

        //$ldap->createUser($this->_data['email'], $this->_data['email'], ????);
    }

    public function login() {
        session_start();
        session_regenerate_id();

        $ticket = $this->_client->verifyIdToken($this->_token);
        if ($ticket) {
            $this->_data = $ticket->getAttributes();

            $_SESSION['user_id'] = $this->_data['payload']['sub'];
            $_SESSION['user_name'] = $this->_data['payload']['name'];

            if (! $this->checkEmail()) {
                $this->insertUser();
                $this->insertUserLDAP();
            }
            return $this->_data['payload']['sub'];
        }
        return false;
    }
}
