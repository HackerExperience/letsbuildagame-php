<?php

/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/27/16
 * Time: 5:30 PM
 */
class PDO_DB {
    public static $dbh;

    public static function factory() {

        if (!isset(self::$dbh)) {
            $connection_array = self::getConfiguration();

            if (isset($connection_array['database']) && is_array($connection_array['database'])) {
                $db_info = $connection_array['database'];

                if (!isset($db_info['db_type'])) {
                    $db_info['db_type'] = 'pgsql';
                }

                if (!isset($db_info['hostname'])) {
                    throw new Exception('Please set db hostname');
                }

                if (!isset($db_info['username'])) {
                    throw new Exception('Please set db username');
                }

                if (!isset($db_info['db_name'])) {
                    throw new Exception('Please set db name');
                }

                if (!isset($db_info['config'])) {
                    $db_info['config'] = array();
                }

                if (!isset($db_info['password'])) {
                    $db_info['password'] = '';
                }

                if (!isset($db_info['port_number'])) {
                    $db_info['port_number'] = '5432'; // Postgres default port
                }

                $dsn = "{$db_info['db_type']}:{$db_info['socket']}={$db_info['hostname']};port={$db_info['port_number']};dbname={$db_info['db_name']}";
                
                self::$dbh = new PDO($dsn, $db_info['username'], $db_info['password'], $db_info['config']);

            } else {
                throw new Exception('Tem algo errado com a sua configuração...');
            }
        }

        return self::$dbh;
    }


    public static function getConfiguration() {

        $config_file = __DIR__ . '/../database.local.php';
        if (!is_file($config_file)) {
            throw new Exception('Create the configuration file database. (database.local.php)');
        }

        return include $config_file;
    }

}