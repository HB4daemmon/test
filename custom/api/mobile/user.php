<?php
//require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/class/user.class.php');

class UserHandler {
    // get current user
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileUser::getCurrentCustomer();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    // Register
    function post() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            params($p,['firstname','lastname','email','password','phone_number']);
            $result['data'] = MobileUser::register($p['firstname'],$p['lastname'],$p['email'],$p['password'],$p['phonenumber']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    // edit the user
//    function put(){
//        try{
//            $result = array("success"=>1,"data"=>'',"return_code"=>"");
//            $p = $GLOBALS['PUT'];
//            params($p,['id','firstname','lastname','email','phone_number']);
//            $result['data'] = MobileUser::edit($p['id'],$p['firstname'],$p['lastname'],$p['email'],$p['phone_number']);
//        }catch(Exception $e){
//            $result['return_code'] = $e->getMessage();
//            $result['success'] = 0;
//        }
//        echo json_encode($result);
//    }
}

class UserLoginHandler {
    // Login
    function post() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            params($p,['email','password']);
            $result['data'] = MobileUser::login($p['email'],$p['password']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class UserLogoutHandler {
    // Logout
    function post() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileUser::logout();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class UserResetHandler {
    // Logout
    function put() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['PUT'];
            params($p,['email']);
            $result['data'] = MobileUser::resetPassword($p['email']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}