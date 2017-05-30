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
            params($p,['user_id','token','products','address_id','delivery_date','delivery_range']);
            $result['data'] = MobileOrder::create($p['user_id'],$p['token'],$p['products'],$p['address_id'],
            $p['delivery_date'],$p['delivery_range']);
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
            $p = $GLOBALS['GET'];
            params($p,['user_id']);
            $result['data'] = MobileOrder::get_order_list($p['user_id'],$p['page'],$p['page_size']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}