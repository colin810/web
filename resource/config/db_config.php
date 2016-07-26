<?php

//数据库配置信息
return array(
    // required
    'database_type' => 'mysql',
    'database_name' => 'app_dev',
    'server'        => '127.0.0.1',
    'username'      => 'root',
    'password'      => '123456',
    'charset'       => 'utf8',
    // [optional]
    'port'          => 3306,
    // [optional] Table prefix
    'prefix'        => '',
    // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option'        => array(
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
    ),
);
