<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page common functionality block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Checkout_Block_Onepage_Abstract extends Mage_Core_Block_Template
{
    protected $_customer;
    protected $_checkout;
    protected $_quote;
    protected $_countryCollection;
    protected $_regionCollection;
    protected $_addressesCollection;

    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }

    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

/* */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }


    public function getRegionHtmlSelect($type)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(Mage::helper('checkout')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());

        return $select->getHtml();
    }

    public function getTipsOption(){
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

    public function getTipsDefaultOption(){
        $config = $this->getQuote()->getSelect();
        if(!isset($config)){
            $configstr = Mage::getStoreConfig('tips_options/tips_default');
            $config = $configstr['tips_default_column'];
        }

        return $config;
    }

    public function getTipsHtmlSelect($type)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[select]')
            ->setId($type.':select')
            ->setTitle(Mage::helper('tips')->__('Tips'))
            ->setValue($this->getTipsDefaultOption())
            ->setOptions($this->getTipsOption());
        return $select->getHtml();
    }

    public function getShippingtimeConfig($store,$method){
        $configstr = Mage::getStoreConfig("shippingtime_options/shippingtime_".$store."_label");
        $config_workday_str = $configstr["shippingtime_".$store."_workday_options"];
        $config_workday = explode(',',trim($config_workday_str));
        $config_weekend_str = $configstr["shippingtime_".$store."_weekend_options"];
        $config_weekend = explode(',',trim($config_weekend_str));
        if($method == 'workday'){
            return $config_workday;
        }else{
            return $config_weekend;
        }
    }

    public function getShippingtimeOption($store,$isWorkDay){
        $result = array();

        $config_workday = $this->getShippingtimeConfig($store,'workday');
        $config_weekend = $this->getShippingtimeConfig($store,'weekend');
        if($isWorkDay){
            foreach($config_workday as $w){
                $option = array('value'=>$w, 'label'=>Mage::helper('shippingtime')->__($w.":00 - ".($w+1).":00"));
                array_push($result,$option);
            }
        }else{
            foreach($config_weekend as $w){
                $option = array('value'=>$w, 'label'=>Mage::helper('shippingtime')->__($w.":00 - ".($w+1).":00"));
                array_push($result,$option);
            }
        }
        return $result;
    }

    public function getShippingtimeHtmlSelect($store,$isWorkDay,$value){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('shippingtime_'.$store.'[select]')
            ->setId('shippingtime_'.$store.':select')
            ->setTitle(Mage::helper('shippingtime')->__('Shipping Time'))
            ->setValue($value)
            ->setOptions($this->getShippingtimeOption($store,$isWorkDay));
        return $select->getHtml();
    }

    public function getShippingtimeDate($store){
        $current_date = strtotime("+3 hours");
        $numOfWeek = idate("w",$current_date);
        $hour = idate("H",$current_date);
        $config_workday = $this->getShippingtimeConfig($store,'workday');
        $config_weekend = $this->getShippingtimeConfig($store,'weekend');
        $result = array();
        if($numOfWeek == 0 || $numOfWeek == 6){
            if($hour<=$config_weekend[0]||array_search($hour,$config_weekend)){
                //This day
                for($i=0;$i<7;$i++){
                    if($i == 0){
                        $_date = strtotime("+3 hours");
                    }elseif($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date =  strtotime("+3 hours +".$i." days");
                    }
                    $_numOfWeek = idate("w",$_date);
                    $_dateTemp = date('Y-m-d',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                }

            }else{
                //The next day
                for($i=1;$i<8;$i++){
                    if($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date =  strtotime("+3 hours +".$i." days");
                    }
                    $_dateTemp = date('Y-m-d',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                }
            }
        }else{
            if($hour<=$config_workday[0]||array_search($hour,$config_workday)){
                //This day
                for($i=0;$i<7;$i++){
                    if($i == 0){
                        $_date = strtotime("+3 hours");
                    }elseif($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date = strtotime("+3 hours +".$i." days");
                    }
                    $_numOfWeek = idate("w",$_date);
                    $_dateTemp = date('Y-m-d',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                }

            }else{
                //The next day
                for($i=1;$i<8;$i++){
                    if($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date =  strtotime("+3 hours +".$i." days");
                    }
                    $_dateTemp = date('Y-m-d',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                }
            }
        }
        return $result;
    }

    public function getShippingtimeHtmlDate($store){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('shippingtime_'.$store.'[date]')
            ->setId('shippingtime_'.$store.':date')
            ->setTitle(Mage::helper('shippingtime')->__('Shipping Date'))
            ->setOptions($this->getShippingtimeDate($store));
        return $select->getHtml();
    }

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
    }


    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return true;
    }
/* */
}
