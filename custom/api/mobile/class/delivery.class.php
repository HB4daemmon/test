<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.class.php');
//require_once(dirname(__FILE__) . '/../../../vendor/stripe-php/init.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);

class MobileDelivery{
    public static function setDeliveryNumber($configs)
    {
        try {
            $conn = db_connect();

            $clear = "TRUNCATE TABLE `custom_delivery_number`";
            $conn->query($clear);
            $values = "";
            foreach($configs as $k=>$v){
                foreach($v as $c){
                    if ($c['value'] != '' and $c['value'] != 0 and $c['value'] != 'undefined'){
                        $values .= "(";
                        $values .= $k.",'".$c['time']."','".$c['value'];
                        $values .= "'),";
                    }
                }

            }
            $values = rtrim($values,',');

            $sql = "INSERT INTO `custom_delivery_number` (day_of_week,time_range,delivery_number) values ".$values;
            $sqlres = $conn->query($sql);
            if (!$sqlres) {
                throw new Exception("select order num error");
            }
            $conn->close();
            return "success";
        } catch (Exception $e) {
            $conn->close();
            return false;
        }
    }

    public static function getDeliveryNumber(){
        try {
            $conn = db_connect();

            $sql = "select * from `custom_delivery_number` ";
            $sqlres = $conn->query($sql);
            if (!$sqlres) {
                throw new Exception("select order num error");
            }
            $configs = Array();
            while($row = $sqlres->fetch_assoc()){
                $configs[''.$row['day_of_week']][$row['time_range']] = $row['delivery_number'];
            }
            $conn->close();
            return $configs;
        } catch (Exception $e) {
            $conn->close();
            return false;
        }
    }

}
