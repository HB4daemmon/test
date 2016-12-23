<?php
require_once(dirname(__FILE__) . '/../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/class/consts.class.php');

class ConstsTermsHandler {
    //
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileConsts::getTerms();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class ConstsPolicyHandler {
    //
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileConsts::getPolicy();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class ConstsDeliveryCountHandler {
    //
    function get() {
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileConsts::getFreeDeliveryCount();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}

class ConstsHelpfulQuestionsHandler{
    function get(){
        try{
            $result = array("success"=>1,"data"=>'',"return_code"=>"");
            $result['data'] = MobileConsts::getHelpfulQuestions();
        }catch(Exception $e){
            $result['return_code'] = $e->getMessage();
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
}