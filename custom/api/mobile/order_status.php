<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');

function changeOrderStatus($orderid,$order_status){
    $_order = Mage::getModel('sales/order')->load($orderid);
    $state = $_order['state'];
    if($state != 'processing'){
        throw new Exception("Order status is not valid");
    }
    $_order->setStatus($order_status);
    $history = $_order->addStatusHistoryComment('Manually set order to '. $order_status .'.', false);
    $history->setIsCustomerNotified(false);
    $_order->save();
    return json_encode(array("success"=>1,"data"=>'success',"error_msg"=>''));
}


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

    if($param['method'] == 'changeOrderStatus'){
        print (changeOrderStatus($param['orderid'],$param['order_status'])) ;
//        echo "success";
    }else if($param['method'] == 'getOrderDetail'){
        echo json_encode(getOrderDetail($param['increment_id']));
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}