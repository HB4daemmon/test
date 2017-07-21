<?php
require_once(dirname(__FILE__) . '/class/cart.class.php');
require_once(dirname(__FILE__) . '/class/oauth.class.php');

class CartHandler {
    function get(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            $user_id = MobileOauth::oauth_validate("order");
            $result['data'] = MobileCart::get_cart($user_id);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function delete() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['DELETE'];
            $user_id = MobileOauth::oauth_validate("order");
            $result['data'] = MobileCart::clear_cart($user_id);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function put(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['PUT'];
            params($p,['products']);
            $user_id = MobileOauth::oauth_validate("order");
            $result['data'] = MobileCart::update_cart($user_id,$p['products']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}