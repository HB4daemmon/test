<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/class/order.class.php');

try{
    $param = array();
    foreach($_REQUEST as $k=>$v){
        $param[addslashes($k)] = addslashes($v);
    }
    if (!isset($param['method'])){
        throw new Exception("Method is null");
    }

    if(!$param['method']){
        throw new Exception("Method is null");
    }

    $order = new MobileOrder();

    if($param['method'] == 'create'){
        print_r($order->create());
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}