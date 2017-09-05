<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');


class MobileUtils
{
    public function validateOrderCount($date, $time, $type)
    {
        try {
            $count = count($date);
            $conn = db_connect();
            $new_date = array();
            $test = array();

            for ($i = 0; $i < $count; $i++) {
                $d = $date[$i];
                $ts = $time[$i];
                $new_time[$i] = array();
                $d_array = explode('-',$d);
                $day_of_week = date('w',mktime(0,0,0,$d_array[0],$d_array[1],$d_array[2]));
                foreach ($ts as $t) {
                    $sql = "select count(1) as order_num from sales_flat_order_storegroup s, sales_flat_order o
                    where s.order_id = o.entity_id
                    and o.state = 'processing'
                    and s.date = '$d'
                    and s.time_range = '$t'
                    and o.parent_order_id is not null;";
                    $sqlres = $conn->query($sql);
                    if (!$sqlres) {
                        throw new Exception("select order num error");
                    }
                    $row = $sqlres->fetch_assoc();
                    $delivery_number_sql = "select delivery_number from custom_delivery_number where day_of_week = $day_of_week and time_range = $t";
                    $num_res = $conn->query($delivery_number_sql);
                    $num = $num_res->fetch_assoc();
                    if($num){
                        $delivery_number = $num['delivery_number'];
                    }else{
                        $delivery_number = 3;
                    }
//                    print_r("Day:".$d."  Time:".$t."  order_num：".$row['order_num'].'  delivery_number'.$delivery_number."\n");
                    if ($row['order_num'] <= $delivery_number) {
                        array_push($new_time[$i], $t);
                    }
                    array_push($test, $sql);
                }
                if (count($new_time) > 0) {
                    array_push($new_date, $d);
                }

            }


            if ($type == 'date') {
                return $new_date;
            } else if ($type == 'range') {
                return $new_time;
            }
            $conn->close();
        } catch (Exception $e) {
            $conn->close();
            return false;
        }
    }

    public function getShippingtimeConfig($store, $method)
    {
        $configstr = Mage::getStoreConfig("shippingtime_options/shippingtime_" . $store . "_label");
        $config_workday_str = $configstr["shippingtime_" . $store . "_" . $method . "_options"];
        $config = explode(',', trim($config_workday_str));
        return $config;
    }

    public function getShippingtimeDate($store, $type)
    {
        $local_date = strtotime(Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        $during_time = Mage::getStoreConfig("shippingtime_options/shippingtime_during_label")["shippingtime_during"] + 1;
        $current_date = strtotime("+".$during_time." hours", $local_date);
        $numOfWeek = idate("w", $current_date);
        $hour = idate("H", $current_date);



        $config[1] = $this->getShippingtimeConfig($store, 'monday');
        $config[2] = $this->getShippingtimeConfig($store, 'tuesday');
        $config[3] = $this->getShippingtimeConfig($store, 'wednesday');
        $config[4] = $this->getShippingtimeConfig($store, 'thursday');
        $config[5] = $this->getShippingtimeConfig($store, 'friday');
        $config[6] = $this->getShippingtimeConfig($store, 'saturday');
        $config[0] = $this->getShippingtimeConfig($store, 'sunday');

        $test['config'] = $config;
        $test['current_date'] = $current_date;
        $test['numOfWeek'] = $numOfWeek;
        $test['during_time'] = $during_time;

        $result = array();
        $rangeResult = array();
        $dateResult = array();

        $config_day = $config[$numOfWeek];

        $this_day_option = [];
        foreach ($config_day as $c) {
            if ($c >= $hour) {
                array_push($this_day_option, $c);
            }
        }

        $test['this_day_option'] = $this_day_option;
        $test['hour'] = $hour;

        if (count($this_day_option) > 0) {
            //This day
            for ($i = 0; $i < 7; $i++) {
                if ($i == 0) {
                    $_date = strtotime("+".$during_time." hours", $local_date);
                } elseif ($i == 1) {
                    $_date = strtotime("+".$during_time." hours +1 day", $local_date);
                } else {
                    $_date = strtotime("+".$during_time." hours +" . $i . " days", $local_date);
                }
                $_numOfWeek = idate("w", $_date);
                $_dateTemp = date('m-d-Y', $_date);
                $option = array('value' => $_dateTemp, 'label' => Mage::helper('shippingtime')->__($_dateTemp));
                array_push($result, $option);
                array_push($dateResult, $_dateTemp);

                if ($i == 0) {
                    $_range = $this_day_option;
                } else {
                    $_range = $config[$_numOfWeek];
                }
                array_push($rangeResult, $_range);

            }

        } else {
            //The next day
            for ($i = 1; $i < 8; $i++) {
                if ($i == 1) {
                    $_date = strtotime("+".$during_time." hours +1 day", $local_date);
                } else {
                    $_date = strtotime("+".$during_time." hours +" . $i . " days", $local_date);
                }
                $_numOfWeek = idate("w", $_date);
                $_dateTemp = date('m-d-Y', $_date);
                $option = array('value' => $_dateTemp, 'label' => Mage::helper('shippingtime')->__($_dateTemp));
                array_push($result, $option);
                array_push($dateResult, $_dateTemp);

                $_range = $config[$_numOfWeek];
                array_push($rangeResult, $_range);
            }
        }

        $test['dateResult'] = $dateResult;
        $test['rangeResult'] = $rangeResult;

        if ($type == 'date') {
            return $this->validateOrderCount($dateResult, $rangeResult, 'date');
        } elseif ($type == 'range') {
            return $this->validateOrderCount($dateResult, $rangeResult, 'range');
        }elseif($type == 'test'){
            return $test;
        }elseif($type == 'config'){
            return $config;
        }
        else {
            return $result;
        }

    }

    public static function getCityList()
    {
        $city_list = ["Champaign", "Urbana"];
        return $city_list;
    }

    public static function getZipCode()
    {
        $zip_code = [61820, 61801, 61802];
        return $zip_code;
    }

    public static function getState()
    {
        $state = ["Illionis"];
        return $state;
    }

    public static function count_page($length, $page, $page_size)
    {
        $length = intval($length);
        $page = intval($page);
        $page_size = intval($page_size);
        $page_show = 10;
        $res = array();

        if ($length == 0) {
            $res = array("enable" => False,
                "page_size" => $page_size,
                "page" => $page,
                "skip" => 0);
            return $res;
        }

        $max_page = intval(ceil(floatval($length) / $page_size));
        $page_num = intval(ceil(floatval($page) / $page_show));
        $skip = ($page - 1) * $page_size;
        if ($page >= $max_page) {
            $has_more = False;
        } else {
            $has_more = True;
        }
        $res['page_size'] = $page_size;
        $res['max_page'] = $max_page;
        $res['page_num'] = $page_num;
        $res['skip'] = $skip;
        $res['page'] = $page;
        $res['enable'] = True;
        $res['has_more'] = $has_more;
        $res['length'] = $length;
        return $res;
   }

}

//$mc = new MobileUtils();
//print_r($mc->getShippingtimeDate('walmart',"date"));
//print_r($mc->getShippingtimeDate('walmart',"range"));