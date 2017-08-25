<?php
require_once(dirname(__FILE__) . '/class/address.class.php');
require_once(dirname(__FILE__) . '/class/oauth.class.php');

class AddressHandler {
    // get products
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            $user_id = MobileOauth::oauth_validate("order");
            $result['data'] = MobileAddress::get($user_id);
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }

    function post(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['POST'];
            $user_id = MobileOauth::oauth_validate("order");
            params($p,['first_name', 'last_name', 'street', 'postcode', 'city', 'telephone']);
            $result['data'] = MobileAddress::create($user_id,$p['first_name'],$p['last_name'],$p['street'],$p['postcode'],$p['city'],$p['telephone']);
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
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
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
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
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class AddressConfigHandler{
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            params($p,['method']);
            $result['data'] = MobileAddress::getAddressConfig($p['method']);
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class AddressDefaultHandler{
    function get(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            $user_id = MobileOauth::oauth_validate("order");
            $result['data'] = MobileAddress::getDefaultAddress($user_id);
        }catch(OauthException $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = -1;
        }
        catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}