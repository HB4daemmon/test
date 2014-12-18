<?php
class Kantwait_Shippingtime_Model_Sales_Order extends Mage_Sales_Model_Order{
	public function hasCustomFields(){
		$var = $this->getSelect();
		if($var && !empty($var)){
			return true;
		}else{
			return false;
		}
	}
	public function getFieldHtml(){
		$var = $this->getSelect();
		$html = '<b>Select:</b>'.$var.'<br/>';
		return $html;
	}
}