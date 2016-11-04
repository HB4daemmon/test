<?php
require_once(dirname(__FILE__) . '/class/category.class.php');

class CategoryMainPageHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileCategory::getPage();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class CategoryProductListHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
//            print_r($p);
            params($p,['category_id']);
            $result['data'] = MobileCategory::getProducts($p['category_id'],$p['page'],$p['page_size']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class CategoryProductHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            params($p,['product_id']);
            $result['data'] = MobileCategory::getProduct($p['product_id']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}