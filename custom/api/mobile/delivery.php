<?php
require_once(dirname(__FILE__) . '/class/delivery.class.php');

class DeliveryTimeHandler {
    function put() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['PUT'];
            params($p,['configs']);
            $result['data'] = MobileDelivery::setDeliveryNumber($p['configs']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileDelivery::getDeliveryNumber();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

}
