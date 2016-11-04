<?php
require_once(dirname(__FILE__) . '/class/address.class.php');

class AddressHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileAddress::get();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function post(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            params($p,['first_name', 'last_name', 'street', 'postcode', 'city', 'telephone']);
            $result['data'] = MobileAddress::create($p['first_name'],$p['last_name'],$p['street'],$p['postcode'],$p['city'],$p['telephone']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function delete(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['DELETE'];
            params($p,['id']);
            $result['data'] = MobileAddress::delete($p['id']);
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
            params($p,['id', 'first_name', 'last_name', 'street', 'postcode', 'city', 'telephone']);
            $result['data'] = MobileAddress::edit($p['id'],$p['first_name'],$p['last_name'],$p['street'],$p['postcode'],$p['city'],$p['telephone']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}
