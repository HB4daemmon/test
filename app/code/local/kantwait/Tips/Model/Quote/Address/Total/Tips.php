<?php
class Kantwait_Tips_Model_Quote_Address_Total_Tips extends Mage_Sales_Model_Quote_Address_Total_Abstract{
    protected $_code = 'tips';

    public function __construct()
    {
        $this->setCode('tips');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }


        $quote = $address->getQuote();
        $custom = Mage::getModel("tips/custom_quote");
        $select_arr = $custom->getByQuote($quote->getId(),"select");
        $other_arr = $custom->getByQuote($quote->getId(),"other");
        $select = $select_arr["select"];
        $other = $other_arr["other"];
        if(is_numeric($other) && $other>0){
            $tips = $other;
        }else{
            $tips = $select;
        }
        //$amount = $address->getData("shippingmethod_amount");
        if ($tips) {

            $address->setData("tips_amount",$tips);
            $address->setData("base_tips_amount",$tips);
            $address->setGrandTotal($address->getGrandTotal() + $tips);
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $tips);
        }
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getTipsAmount();
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('tips')->__('Tips'),
            'value'=> $amt
        ));
        return $this;
    }
}