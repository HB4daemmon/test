<?php
class Excellence_Custom_Block_Checkout_Onepage_Excellence2 
	extends Mage_Checkout_Block_Onepage_Abstract
{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('excellence2', array(
            'label'     => Mage::helper('checkout')->__('OneDayIn2013 Second Post Review'),
            'is_show'   => $this->isShow()
		));
		parent::_construct();
	}
}