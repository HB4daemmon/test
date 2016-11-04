<?php
require_once(dirname(__FILE__).'/../util/global.php');
require_once(dirname(__FILE__).'/../../app/Mage.php');
Mage::app();
umask(0);
ob_start();
session_start();
Mage::getSingleton("core/session", array("name" => "frontend"));

$type = $_SERVER['REQUEST_METHOD'];
parse_str(file_get_contents('php://input'), $data);
$GLOBALS[$type] = $data;
if ($type == 'GET'){
   $GLOBALS[$type] = $_GET;
}
//print_r($GLOBALS);
///$data = array_merge($_POST, $data);


function params($params,$require_list){
//    print_r($params);
    foreach($require_list as $r){
        if(!array_key_exists($r,$params)){
            throw new Exception("Parameter [" . $r . "] is null;");
        }
    }
}