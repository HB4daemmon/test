<?php
require_once(dirname(__FILE__).'/global.php');

function db_connect($dbname='') {
    $xml_array = simplexml_load_file(dirname(__FILE__).'/../../app/etc/local.xml');
    $host = $xml_array->global->resources->default_setup->connection->host;
    $username = $xml_array->global->resources->default_setup->connection->username;
    $password = $xml_array->global->resources->default_setup->connection->password;
    if($dbname != ''){
        $db_name = $dbname;
    }else{
        $db_name = $xml_array->global->resources->default_setup->connection->dbname;
    }

    $res = new mysqli($host, $username, $password, $db_name);
    if ($res->connect_errno) {
        throw new Exception("Failed to connect database");
    }
    $res->query("SET NAMES utf8");
    return $res;
}

function getImageBase(){
    return 'http://www.cartgogogo.com/media/catalog/product';
}

?>