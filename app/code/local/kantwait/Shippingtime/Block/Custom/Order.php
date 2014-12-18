<?php
class Kantwait_Shippingtime_Block_Custom_Order extends Mage_Core_Block_Template{
	public function getCustomVars(){
		$model = Mage::getModel('shippingtime/custom_order');
		return $model->getByOrder($this->getOrder()->getId());
	}
	public function getOrder()
	{
		return Mage::registry('current_order');
	}
}