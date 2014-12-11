<?php

class Kantwait_Tips_Model_Mysql4_Custom_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tips/custom_order');
    }
}