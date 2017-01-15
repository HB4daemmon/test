<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');


class MobileUtils{
    public function validateOrderCount($date,$time,$type){
        try{
            $count = count($date);
            $conn = db_connect();
            $new_date = array();
            $test = array();

            for($i = 0; $i<$count;$i++){
                $d = $date[$i];
                $ts = $time[$i];
                $new_time[$i] = array();
                //$format_date = date('m-d-Y',strtotime($d));
                foreach($ts as $t){
                    $sql = "select count(1) as order_num from sales_flat_order_storegroup s, sales_flat_order o
                    where s.order_id = o.entity_id
                    and o.status = 'processing'
                    and s.date = '$d'
                    and s.time_range = '$t';";
                    $sqlres = $conn->query($sql);
                    if(!$sqlres){
                        throw new Exception("select order num error");
                    }
                    $row = $sqlres->fetch_assoc();
                    if($row['order_num'] <= 3){
                        array_push($new_time[$i],$t);
                    }
                    array_push($test,$sql);
                }
                if(count($new_time)>0){
                    array_push($new_date,$d);
                }

            }


            if($type == 'date'){
                return $new_date;
            }else if($type == 'range'){
                return $new_time;
            }
            $conn->close();
        }catch (Exception $e){
            $conn->close();
            return false;
        }
    }

    public function getShippingtimeConfig($store,$method){
        $configstr = Mage::getStoreConfig("shippingtime_options/shippingtime_".$store."_label");
        $config_workday_str = $configstr["shippingtime_".$store."_".$method."_options"];
        $config = explode(',',trim($config_workday_str));
        return $config;
    }

    public function getShippingtimeDate($store,$type){
        $local_date = strtotime(Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        $current_date = strtotime("+2 hours",$local_date);
        $numOfWeek = idate("w",$current_date);
        $hour = idate("H",$current_date);

        $config[1]= $this->getShippingtimeConfig($store,'monday');
        $config[2]= $this->getShippingtimeConfig($store,'tuesday');
        $config[3]= $this->getShippingtimeConfig($store,'wednesday');
        $config[4]= $this->getShippingtimeConfig($store,'thursday');
        $config[5]= $this->getShippingtimeConfig($store,'friday');
        $config[6]= $this->getShippingtimeConfig($store,'saturday');
        $config[0]= $this->getShippingtimeConfig($store,'sunday');

        $result = array();
        $rangeResult =  array();
        $dateResult = array();

        $config_day = $config[$numOfWeek];

        $this_day_option = [];
        foreach($config_day as $c){
            if ($c >= $hour){
                array_push($this_day_option,$c);
            }
        }

        if(count($this_day_option) > 0){
            //This day
            for($i=0;$i<7;$i++){
                if($i == 0){
                    $_date = strtotime("+3 hours",$local_date);
                }elseif($i==1){
                    $_date = strtotime("+3 hours +1 day",$local_date);
                }else{
                    $_date =  strtotime("+3 hours +".$i." days",$local_date);
                }
                $_numOfWeek = idate("w",$_date);
                $_dateTemp = date('m-d-Y',$_date);
                $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                array_push($result,$option);
                array_push($dateResult,$_dateTemp);

                if($i==0){
                    $_range = $this_day_option;
                }else{
                    $_range = $config[$_numOfWeek+1];
                }
                array_push($rangeResult,$_range);

            }

        }else{
            //The next day
            for($i=1;$i<8;$i++){
                if($i==1){
                    $_date = strtotime("+3 hours +1 day",$local_date);
                }else{
                    $_date =  strtotime("+3 hours +".$i." days",$local_date);
                }
                $_numOfWeek = idate("w",$_date);
                $_dateTemp = date('m-d-Y',$_date);
                $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                array_push($result,$option);
                array_push($dateResult,$_dateTemp);

                $_range = $config[$_numOfWeek+1];
                array_push($rangeResult,$_range);
            }
        }

        if($type == 'date'){
            return $this->validateOrderCount($dateResult,$rangeResult,'date');
        }elseif($type == 'range'){
            return $this->validateOrderCount($dateResult,$rangeResult,'range');
        }else{
            return $result;
        }

    }

    public static function getCityList(){
        $city_list = ["Champaign","Urbana"];
        return $city_list;
    }

    public static function getZipCode(){
        $zip_code = [61820,61801,61802];
        return $zip_code;
    }

    public static function getState(){
        $state = ["Illionis"];
        return $state;
    }

}

//$mc = new MobileUtils();
//print_r($mc->getShippingtimeDate('walmart',"date"));
//print_r($mc->getShippingtimeDate('walmart',"range"));