<?php

require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.php');
require_once(dirname(__FILE__) . '/category.php');
require_once(dirname(__FILE__) . '/address.php');
require_once(dirname(__FILE__) . '/consts.php');
require_once(dirname(__FILE__) . '/order.php');
require_once(dirname(__FILE__) . '/shipping.php');

class MainHandler {
    #get current user
    function get() {
        echo "GET";
        print_r($_POST);
    }

    function post(){
        echo "POST";
        print_r($_POST);
    }

    function delete(){
        echo "DELETE";
        print_r($_POST);
    }

    function put(){
        echo "PUT";
        print_r($_POST);
    }
}

Toro::serve(array(
    //user
    "/user" => "UserHandler",
    "/user/login" => "UserLoginHandler",
    "/user/logout" => "UserLogoutHandler",
    "/user/reset" => "UserResetHandler",
    //category
    "/category/mainpage" => "CategoryMainPageHandler",
    "/category/product/list" => "CategoryProductListHandler",
    "/category/product/query" => "CategoryProductSearchHandler",
    "/category/product" => "CategoryProductHandler",
    //address
    "/address" => "AddressHandler",
    "/address/config" => "AddressConfigHandler",
    //consts
    "/consts/terms" => "ConstsTermsHandler",
    "/consts/policy" => "ConstsPolicyHandler",
    "/consts/deliverycount" => "ConstsDeliveryCountHandler",
    "/consts/helpfulquestions" => "ConstsHelpfulQuestionsHandler",
    //order
    "/order" => "OrderHandler",
    //shipping
    "/shipping/time" => "ShippingTimeHandler",
    "/main" => "MainHandler",
));