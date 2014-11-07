<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Themeoptions_Model_Config_Color
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'green_red', 'label'=>Mage::helper('adminhtml')->__('green_red')),
            array('value'=>'brown_red', 'label'=>Mage::helper('adminhtml')->__('brown_red')),
            array('value'=>'orange_green', 'label'=>Mage::helper('adminhtml')->__('orange_green')),
            array('value'=>'red_light_green', 'label'=>Mage::helper('adminhtml')->__('red_light_green')),
            array('value'=>'teal_warter_melon', 'label'=>Mage::helper('adminhtml')->__('teal_warter_melon')),
            array('value'=>'twitter_red', 'label'=>Mage::helper('adminhtml')->__('twitter_red'))
        );
    }

}
