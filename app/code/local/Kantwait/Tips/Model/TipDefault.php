<?php
    class Kantwait_Tips_Model_TipDefault{
        public function toOptionArray()
        {
            $result = array();
            $configstr = Mage::getStoreConfig('tips_options/tips_label');
            $config = explode(',',trim($configstr['tips_options']));
            $length = count($config);
            for($i=0;$i<$length;$i++){
                $option = array('value'=>$i, 'label'=>Mage::helper('tips')->__($config[$i]));
                array_push($result,$option);
            }
            return $result;
        }
    }
?>