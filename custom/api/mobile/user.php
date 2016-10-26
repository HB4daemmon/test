<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/class/user.class.php');

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

    $user = new MobileUser();

    if($param['method'] == 'register'){
        $result['data'] = $user->register($param['firstname'],$param['lastname'],$param['email'],$param['password'],$param['phonenumber']);
    }else if($param['method'] == 'login'){
        $result['data'] =  $user->login($param['email'],$param['password']);
    }else if($param['method'] == 'isLogin'){
        $result['data'] =  $user->isLogin($param['sessionId']);
    }else if($param['method'] == 'getCustomerId'){
        $result['data'] =  $user->getCustomerId();
    }else if($param['method'] == 'resetPassword'){
        $result['data'] =  $user->resetPassword($param['email']);
    }else if($param['method'] == 'logout'){
        $result['data'] =  $user->logout();
    }else if($param['method'] == 'edit'){
        $result['data'] =  $user->edit($param['id'],$param['firstname'],$param['lastname'],$param['email'],$param['phone_number']);
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