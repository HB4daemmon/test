<?php
class kantwait_Tips_Block_Custom_Order extends Mage_Core_Block_Template{
	public function getCustomVars(){
		$model = Mage::getModel('tips/custom_order');
		return $model->getByOrder($this->getOrder()->getId());
	}
	public function getOrder()
	{
		return Mage::registry('current_order');
	}
}