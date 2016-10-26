<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/class/consts.class.php');

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
    $result = array("success"=>1,"data"=>'',"return_code"=>"");

    $consts = new MobileConsts();

    if($param['method'] == 'getTerms'){
        $result['data'] = $consts->getTerms();
    }else if($param['method'] == 'getPolicy'){
        $result['data'] =  $consts->getPolicy();
    }else if($param['method'] == 'getFreeDeliveryCount'){
        $result['data'] =  $consts->getFreeDeliveryCount();
    }else{
        throw new Exception("Invalid Method");
    }

    echo json_encode($result);
}catch(Exception $e){
    $result['return_code'] = $e->getMessage();
    $result['success'] = 0;
    echo json_encode($result);
    exit;
}