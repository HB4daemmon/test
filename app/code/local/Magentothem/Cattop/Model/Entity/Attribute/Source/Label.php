<?php
class Magentothem_Cattop_Model_Entity_Attribute_Source_Label extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const MOI = 'new';
	const HOT = 'hot';

    public function getAllOptions()
    {  	
        return array(
			array('value'=>self::MOI, 'label'=>Mage::helper('cattop')->__("New")),
            array('value'=>self::HOT, 'label'=>Mage::helper('cattop')->__("Hot"))
        );      
    }
}
