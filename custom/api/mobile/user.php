<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');

class MobileUser{
    function login( $email, $password ){
        Mage::getSingleton("core/session", array("name" => "frontend"));

        $websiteId = 1;
        $store = Mage::app()->getStore();
        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);
        try {
            $customer->loadByEmail($email);
            $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $session->login($email, $password);
            return $session->getSessionId();
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function register($firstname,$lastname,$email,$password,$phonenumber){
        $customer = Mage::getModel("customer/customer");
        $customer   ->setWebsiteId(1)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setPassword($password)
            ->setPhoneNumber($phonenumber);

        try{
            $customer->save();
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function isLogin($sessionId){
        try {
            $session = Mage::getSingleton('core/session', array('value'=>$sessionId));
//            $session = Mage::getSingleton('customer/session')->setSessionId($sessionId);
            return $session->getCustomerId().'---'.$session->isLoggedIn().'---'.$session->getSessionId();
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function getCustomerId(){
        return Mage::getSingleton('customer/session')->getCustomerId();
    }



    public function resetPassword($email){
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1)
            ->loadByEmail($email);
        if ($customer->getId()) {
            try {
                $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
                return $newResetPasswordLinkToken;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }
}



try{
    $param = array();
    foreach($_REQUEST as $k=>$v){
        $param[addslashes($k)] = addslashes($v);
    }
    if (!isset($param['method'])){
        throw new Exception("Method is null");
    }

    if(!$param['method']){
        throw new Exception("Method is null");
    }

    $user = new MobileUser();

    if($param['method'] == 'register'){
        $user->register($param['firstname'],$param['lastname'],$param['email'],$param['password'],$param['phonenumber']);
        echo "success";
    }else if($param['method'] == 'login'){
        echo $user->login($param['email'],$param['password']);
    }else if($param['method'] == 'isLogin'){
        echo $user->isLogin($param['sessionId']);
    }else if($param['method'] == 'getCustomerId'){
        echo $user->getCustomerId();
    }else if($param['method'] == 'resetPassword'){
        echo $user->resetPassword($param['email']);
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}