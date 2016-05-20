<?php

require_once 'connection.php';

class Newsletter {
    private $_user_id;
    private $_frequency;
    private $_dbo;

    public function __construct($user_id, $frequency) {
        $this->setUserId($user_id);
        $this->setFrequency($frequency);
        $this->_dbo = PDO_DB::factory();
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->_user_id;
    }

    /**
     * @param mixed $_user_id
     */
    public function setUserId($_user_id) {
        $this->_user_id = $_user_id;
    }

    /**
     * @return mixed
     */
    public function getFrequency() {
        return $this->_frequency;
    }

    /**
     * @param mixed $_frequency
     */
    public function setFrequency($_frequency) {
        $this->_frequency = $_frequency;
    }

    public function subscribe() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "INSERT INTO newsletter (frequency, user_id) VALUES(?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getFrequency(), $this->getUserId()));
    }

    public function unsubscribe() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "DELETE FROM newsletter WHERE user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }

    public function update_frequency($daily = false, $weekly = false, $monthly = true) {
        $this->_dbo = PDO_DB::factory();

        if ($monthly) {
            $frequency = 30;
        } elseif ($weekly) {
            $frequency = 14;
        } elseif ($daily) {
            $frequency = 7;
        } else {
            $frequency = 30;
        }

        $sql_query = "UPDATE newsletter SET frequency = ? WHERE user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($frequency, $this->getUserId()));
    }
}
