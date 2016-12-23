<?php
require_once(dirname(__FILE__) . '/class/order.class.php');


class OrderHandler {
    function post() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            params($p,['token']);
            $result['data'] = MobileOrder::stripe_pay($p['token'],1);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}