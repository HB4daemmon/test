<?php
require_once(dirname(__FILE__) . '/class/order.class.php');
require_once(dirname(__FILE__) . '/class/oauth.class.php');


class OrderHandler {
    function get(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            params($p,['order_id']);
            $result['data'] = MobileOrder::getOrderDetail($p['order_id']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function post() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            $user_id = MobileOauth::oauth_validate("order");
            params($p,['token','products','address_id','delivery_date','delivery_range','tips']);
            $result['data'] = MobileOrder::place2($user_id,$p['token'],$p['products'],$p['address_id'],
            $p['delivery_date'],$p['delivery_range'],$p['tips']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class OrderListHandler{
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $user_id = MobileOauth::oauth_validate("order");
            $p = $GLOBALS['GET'];
            $result['data'] = MobileOrder::get_order_list($user_id,$p['page'],$p['page_size']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class OrderReviewHandler{
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            $user_id = MobileOauth::oauth_validate("order");
            params($p,["address_id","tips"]);
            $result['data'] = MobileOrder::review($user_id,$p['address_id'],$p['tips']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}