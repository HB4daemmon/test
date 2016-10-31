<?php

require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.php');

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
    "/user" => "UserHandler",
    "/user/login" => "UserLoginHandler",
    "/user/logout" => "UserLogoutHandler",
    "/user/reset" => "UserResetHandler",
    "/main" => "MainHandler",
));