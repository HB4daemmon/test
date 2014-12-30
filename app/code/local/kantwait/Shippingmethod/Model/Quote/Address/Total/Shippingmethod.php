<?php
class Kantwait_Shippingmethod_Model_Quote_Address_Total_Shippingmethod extends Mage_Sales_Model_Quote_Address_Total_Abstract{
    protected $_code = 'shippingmethod';

    public function __construct()
    {
        $this->setCode('shippingmethod');
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
        $amount_arr = $custom->getByQuote($quote->getId(),"shippingmethod");
        $amount = $amount_arr["shippingmethod"];
        //$amount = $address->getData("shippingmethod_amount");
        if ($amount) {
            $exist_amount=$address->getData("shippingmethod_amount");
            $balance = $amount-$exist_amount;

            $address->setData("shippingmethod_amount",$amount);
            $address->setData("base_shippingmethod_amount",$amount);
            $address->setGrandTotal($address->getGrandTotal() + $balance);
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $balance);
        }
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getShippingmethodAmount();
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('shippingmethod')->__('Shipping & Handle'),
            'value'=> $amt
        ));
        return $this;
    }
}