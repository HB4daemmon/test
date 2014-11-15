<?php
class Luciky_Checkout_Block_Onepage_Tips extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct()
    {
        $this->getCheckout()->setStepData('tips', array(
            'label'     => Mage::helper('checkout')->__('Tips'),
            'is_show'   => $this->isShow()
        ));

//        if ($this->isCustomerLoggedIn()) {
//            $this->getCheckout()->setStepData('shippingtime', 'allow', true);
//        }
        parent::_construct();
    }
}
