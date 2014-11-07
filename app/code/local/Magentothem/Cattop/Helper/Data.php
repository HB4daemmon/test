<?php
class Magentothem_Cattop_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getTopCfg($cfg)
	{
        $config = Mage::getStoreConfig('cattop/top_config');
        if(isset($config[$cfg])) return $config[$cfg];
	}

}