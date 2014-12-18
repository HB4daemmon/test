<?php
class Kantwait_Shippingtime_Model_ShippingtimeRange{
    public function toOptionArray()
    {
        $result=array();
        for($i=0;$i<24;$i++){
            $range = array('value'=>$i,'label'=>Mage::helper('shippingtime')->__($i.':00 - '.($i+1).':00'));
            array_push($result,$range);
        }
        return $result;
    }
}
?>