<?php
    class kantwait_Tips_Model_TipYN{
        public function toOptionArray()
        {
            return array(
                array('value' => 1, 'label'=>Mage::helper('tips')->__('Yes')),
                array('value' => 0, 'label'=>Mage::helper('tips')->__('No')),
            );
        }
    }
?>