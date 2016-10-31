<?php
require_once(dirname(__FILE__) . '/class/category.class.php');

class CategoryPageHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
=            $result['data'] = MobileCategory::login($p['email'],$p['password']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}