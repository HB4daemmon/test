<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/oauth.class.php');
require_once 'Cryozonic/Stripe/init.php';

class MobileUser{
    public static function login( $email, $password ){
        $websiteId = 1;
        $store = Mage::app()->getStore();
        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);
        $customer->loadByEmail($email);
        $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
        $session->login($email, $password);

        $user = [];
        $user['id'] = $customer->getId();
        $user['name'] = $customer->getName(); // Full Name
        $user['firstname'] = $customer->getFirstname(); // First Name
        $user['middlename'] = $customer->getMiddlename(); // Middle Name
        $user['lastname'] = $customer->getLastname(); // Last Name
        $user['email'] = $customer->getEmail();
        $user['phone_number'] = $customer->getPhoneNumber();
        $user['token'] = MobileOauth::generate($email,$password,"app");
//        print_r($customer->getResourceCollection());
        return $user;
    }

    public static function edit($id,$firstname,$lastname,$email,$phone_number){
        try {
            $customer = Mage::getModel("customer/customer")->load($id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

            $customer->setFirstName($firstname);
            $customer->setLastName($lastname);
            $customer->setEmail($email);
            $customer->setPhoneNumber($phone_number);
            $customer->save();
            return 'success';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    public static function register($firstname,$lastname,$email,$password,$phonenumber){
        $customer = Mage::getModel("customer/customer");
        $customer   ->setWebsiteId(1)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setPassword($password)
            ->setPhoneNumber($phonenumber);

        return $customer->save();
    }

    public static function isLogin(){
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            return 0;
        }else{
            return 1;
        }
    }

    public static function getCustomerId(){
        return Mage::getSingleton('customer/session')->getCustomerId();
    }

    public static function logout(){
        return  Mage::getSingleton('customer/session')->logout();
    }

    public static function getCurrentCustomer($json=true){
        $websiteId = 1;
        $store = Mage::app()->getStore();
        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);
        $customer->load(MobileUser::getCustomerId());
        $data = $customer->getData();
        if (!array_key_exists('entity_id',$data)){
            throw new Exception("No user has login");
        }
        unset($data['password_hash']);
        unset($data['rp_token']);

        if($json){
            return $data;
        }else{
            return $customer;
        }

    }

    public static function resetPassword($email){
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
                throw new Exception($e->getMessage());
            }
        }
    }

    public static function getStripeCustomerId($customerId){
        $customer = Mage::getModel("customer/customer")->load($customerId);
        if ($customer->getId() == null){
            throw new Exception("User is not existed");
        }
        Mage::getSingleton('customer/session')->loginById($customer->getId());
        $stripe = Mage::getModel('cryozonic_stripe/standard');
        $customer = $stripe->getStripeCustomer()->id;
        Mage::getSingleton('customer/session')->logout();
        return $customer;
    }

    public static function getStripeCustomerCard($customerId){
        $customer = Mage::getModel("customer/customer")->load($customerId);
        if ($customer->getId() == null){
            throw new Exception("User is not existed");
        }
        Mage::getSingleton('customer/session')->loginById($customer->getId());
        $stripe = Mage::getModel('cryozonic_stripe/standard');
        $cards = $stripe->getCustomerCards(true,$customerId);
        Mage::getSingleton('customer/session')->logout();
        return $cards;
    }

    public static function fbLogin($code,$redirect_uri){
        $model = Mage::getSingleton("pslogin/Facebook");
        $model->setRedirectUri($redirect_uri);
        $model->loadUserData($code);

//        $model->setUserData("user_id",$fb_userid);
//        $model->setUserData("email",$fb_email);
//        $model->setUserData("first_name",$fb_firstname);
//        $model->setUserData("last_name",$fb_lastname);

        if ($customerId = $model->getCustomerIdByUserId())
        {
            //Check and replace fakeEmail with normal email
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId()) {
                $user = [];
                $user['id'] = $customer->getId();
                $user['name'] = $customer->getName(); // Full Name
                $user['firstname'] = $customer->getFirstname(); // First Name
                $user['middlename'] = $customer->getMiddlename(); // Middle Name
                $user['lastname'] = $customer->getLastname(); // Last Name
                $user['email'] = $customer->getEmail();
                $user['phone_number'] = $customer->getPhoneNumber();
                $user['token'] = MobileOauth::generate($user['email'],'fb_login',"app");
            }
            # Do auth.
        }
        elseif ($customerId = $model->getCustomerIdByEmail())
        {
            # Customer with received email was placed in db.
            // Remember customer.
            $model->setCustomerIdByUserId($customerId);
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $responseEmail = $model->getUserData('email');
            if ($customer->getId()) {
                $user = [];
                $user['id'] = $customer->getId();
                $user['name'] = $customer->getName(); // Full Name
                $user['firstname'] = $customer->getFirstname(); // First Name
                $user['middlename'] = $customer->getMiddlename(); // Middle Name
                $user['lastname'] = $customer->getLastname(); // Last Name
                $user['email'] = $customer->getEmail();
                $user['phone_number'] = $customer->getPhoneNumber();
                $user['token'] = MobileOauth::generate($responseEmail,'fb_login',"app");
            }
        }
        else
        {
            # Registration customer.
            if ($customerId = $model->registrationCustomer()) {
                // Remember customer.
                $model->setCustomerIdByUserId($customerId);
                // Post mail.
                $model->postToMail();
                $model->setCustomerIdByUserId($customerId);
                $customer = Mage::getModel('customer/customer')->load($customerId);
                $responseEmail = $model->getUserData('email');
                if ($customer->getId()) {
                    $user = [];
                    $user['id'] = $customer->getId();
                    $user['name'] = $customer->getName(); // Full Name
                    $user['firstname'] = $customer->getFirstname(); // First Name
                    $user['middlename'] = $customer->getMiddlename(); // Middle Name
                    $user['lastname'] = $customer->getLastname(); // Last Name
                    $user['email'] = $customer->getEmail();
                    $user['phone_number'] = $customer->getPhoneNumber();
                    $user['token'] = MobileOauth::generate($user['email'],'fb_login',"app");
                }
            } else {
                # Error.
                if ($errors = $model->getErrors()) {
                    foreach ($errors as $error) {
                        throw new Exception($error);
                    }
                }
            }
        }
        return $user;
    }
}
