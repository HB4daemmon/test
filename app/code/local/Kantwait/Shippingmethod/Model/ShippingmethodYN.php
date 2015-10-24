<?php
    class Kantwait_Shippingmethod_Model_ShippingmethodYN{
        public function toOptionArray()
        {
            return array(
                array('value' => 1, 'label'=>Mage::helper('shippingtime')->__('Yes')),
                array('value' => 0, 'label'=>Mage::helper('shippingtime')->__('No')),
            );
        }
    }
?>