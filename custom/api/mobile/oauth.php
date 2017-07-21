<?php
require_once(dirname(__FILE__) . '/class/oauth.class.php');


class OauthHandler {
    function get(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $p = $GLOBALS['GET'];
            params($p,['token','scope']);
            $result['data'] = MobileOauth::validate($p['token'],$p['scope']);
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
            params($p,['email','password','scopes']);
            $result['data'] = MobileOauth::generate($p['email'],$p['password'],$p['scopes']);
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}
