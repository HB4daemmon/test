<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');
require_once(dirname(__FILE__) . '/exception.class.php');
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);

class MobileOauth{
    public static function get_scopes($role){
        $scope_roles = Array("app"=> "user,cart,address,order");
        return $scope_roles[$role];
    }

    public static function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = md5(uniqid(rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }

    public static function generate($email,$password,$role,$expired_time=24){
        try {
            $now = time();
            $scopes = MobileOauth::get_scopes($role);
            $local_date = Mage::getSingleton('core/date')->date('Y-m-d H:i:s',$now);
            $expired_date = Mage::getSingleton('core/date')->date('Y-m-d H:i:s',strtotime("+ ".$expired_time." hours",$now));
            $token = MobileOauth::guid();
            if ($role == 'app'){
                $token = "app_".$token;
                $websiteId = 1;
                $store = Mage::app()->getStore();
                $customer = Mage::getModel("customer/customer");
                $customer->website_id = $websiteId;
                $customer->setStore($store);
                $customer->loadByEmail($email);
                $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                if($password != 'fb_login'){
                    $session->login($email, $password);
                }
                $user_id = $customer->getId();

                $conn = db_connect();
                $sql = sprintf("insert into oauth_access_token(client_id,grant_type,user_id,token,create_time,expires_at,scopes) values ('%s','%s',%s,'%s','%s','%s','%s')",
                    $user_id,'client_credentials',$user_id,$token,$local_date,$expired_date,$scopes);
                $res = $conn->query($sql);
                if ($res != true){
                    throw new Exception( $conn->error);
                }
            }else{ }

            return $token;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function validate($token,$scope){
        try {
            $token_obj = Array();
            $now = time();
            $local_date = Mage::getSingleton('core/date')->date('Y-m-d H:i:s',$now);
            if (str_starts_with($token,"app_")){
                $conn = db_connect();
                $sql = sprintf("select * from oauth_access_token where token = '%s'",
                    $token);
                $res = $conn->query($sql);
                while($row = $res->fetch_assoc()){
                    $token_obj = $row;
                    break;
                }
            }else{ }

            if (count($token_obj) == 0){
                throw new OAuthException("This token is not valid!");
            }

            if ($local_date > $token_obj['expires_at']){
                throw new OAuthException("This token is expired!");
            }

            $scopes_arr = explode(',',$token_obj['scopes']);
            if (!in_array($scope,$scopes_arr)){
                throw new OAuthException("Scope is not accepted!");
            }

            return $token_obj['user_id'];
        }catch(OAuthException $e){
            throw new OAuthException($e->getMessage());
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function oauth_validate($scope){
        // password and email header
        $headers = apache_request_headers();
        if (array_key_exists("Authorization",$headers)){
            $token = $headers['Authorization'];
        }else{
            throw new OAuthException("This api need Authorization token!");
        }
        return MobileOauth::validate($token,$scope);
    }

}
