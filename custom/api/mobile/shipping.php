<?php
/**
 * Created by PhpStorm.
 * User: Daemon
 * Date: 16-11-27
 * Time: 下午5:14
 */
require_once(dirname(__FILE__) . '/class/utils.class.php');

class ShippingTimeHandler {
    function get() {
        try{
            $MobileUtils = new MobileUtils();
            $p = $GLOBALS['GET'];
            params($p,['type']);
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = $MobileUtils->getShippingtimeDate("walmart",$p['type']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}
