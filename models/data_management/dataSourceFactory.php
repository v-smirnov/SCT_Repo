<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 11.03.14
 * Time: 23:39
 */
require_once("mySQLDataSource.php");

class DataSourceFactory {
    private $data_source;

    function __construct()
    {
        $this->data_source = new MySQLDataSource();
    }

    function getDataSource()
    {
        return $this->data_source;
    }
} 