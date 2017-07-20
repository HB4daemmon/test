<?php
require_once(dirname(__FILE__) . '/class/order.class.php');


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
            params($p,['user_id','token','products','address_id','delivery_date','delivery_range','tips']);
            $result['data'] = MobileOrder::place2($p['user_id'],$p['token'],$p['products'],$p['address_id'],
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
            if (!isset($GLOBALS['_SERVER']['HTTP_EMAIL']) or !isset($GLOBALS['_SERVER']['HTTP_PASSWORD'])){
                throw new Exception("This api need email or password header");
            }
            $email = $GLOBALS['_SERVER']['HTTP_EMAIL'];
            $password = $GLOBALS['_SERVER']['HTTP_PASSWORD'];
            $p = $GLOBALS['GET'];
            params($p,['user_id']);
            $result['data'] = MobileOrder::get_order_list($p['user_id'],$email,$password,$p['page'],$p['page_size']);
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
            params($p,['user_id',"address_id","tips"]);
            $result['data'] = MobileOrder::review($p['user_id'],$p['address_id'],$p['tips']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}