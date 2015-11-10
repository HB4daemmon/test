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

    public function getStoreName(){
        return array('fareast','walmart');
    }

    public function getTipsOption(){
        $result = array();
        $configstr = Mage::getStoreConfig('tips_options/tips_label');
        $config = explode(',',trim($configstr['tips_options']));
        $length = count($config);
        for($i=0;$i<$length;$i++){
            $option = array('value'=>$config[$i], 'label'=>Mage::helper('tips')->__($config[$i]));
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

    public function getShippingtimeOption(){
        $result = array();
        foreach($this->getStoreName() as $store){
            $date = array();
            $_range = $this->getShippingtimeDate($store,'range');
            $_date = $this->getShippingtimeDate($store,'date');
            $i=0;
            foreach($_date as $d){
                $range = array();
                foreach($_range[$i] as $r){
                    $option = array('value'=>$r, 'label'=>Mage::helper('shippingtime')->__($this->to12Hour($r)." - ".$this->to12Hour($r+1)));
                    array_push($range,$option);
                }
                $date[$d]=$range;
                $i++;
            }
            $result[$store] = $date;
        }
        return json_encode($result);
    }

    public function getDefaultShippingtimeRange($store){
        $result = array();
        $_range = $this->getShippingtimeDate($store,'range');
        $range = $_range[0];
        foreach($range as $r){
            $option = array('value'=>$r, 'label'=>Mage::helper('shippingtime')->__(($this->to12Hour($r)." - ".$this->to12Hour($r+1))));
            array_push($result,$option);
        }
        return $result;
    }

    public function to12Hour($r){
        if($r==0){
            return "12:00 midnight";
        }else if($r == 12){
            return "12:00 noon";
        }else if($r>0 &&$r<12){
            return $r.":00 am";
        }else{
            return ($r-12).":00 pm";
        }
    }

    public function getShippingtimeHtmlTime($store,$store_groupid){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('shippingtime['.$store_groupid.'][time]')
            ->setId('shippingtime_'.$store.':time')
            ->setTitle(Mage::helper('shippingtime')->__('Shipping Time'))
            ->setOptions($this->getDefaultShippingtimeRange($store));
        return $select->getHtml();
    }

    //get date & range array
    public function getShippingtimeDate($store,$type){
        $current_date = strtotime("+2 hours");
        $numOfWeek = idate("w",$current_date);
        $hour = idate("H",$current_date);
        $config_workday = $this->getShippingtimeConfig($store,'workday');
        $config_weekend = $this->getShippingtimeConfig($store,'weekend');
        $result = array();
        $rangeResult =  array();
        $dateResult = array();
        if($numOfWeek == 0 || $numOfWeek == 6){ //weekend
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
                    $_dateTemp = date('m-d-Y',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                    array_push($dateResult,$_dateTemp);

                    if($i==0){
                        if($index = array_search($hour,$config_weekend)){
                            $_range = array_slice($config_weekend,$index);
                        }else{
                            $_range = $config_weekend;
                        }
                    }else{
                        if($_numOfWeek == 0 || $_numOfWeek ==6){
                            $_range = $config_weekend;
                        }else{
                            $_range = $config_workday;
                        }
                    }
                    array_push($rangeResult,$_range);

                }

            }else{
                //The next day
                for($i=1;$i<8;$i++){
                    if($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date =  strtotime("+3 hours +".$i." days");
                    }
                    $_numOfWeek = idate("w",$_date);
                    $_dateTemp = date('m-d-Y',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                    array_push($dateResult,$_dateTemp);

                    if($_numOfWeek == 0 || $_numOfWeek ==6){
                        $_range = $config_weekend;
                    }else{
                        $_range = $config_workday;
                    }
                    array_push($rangeResult,$_range);
                }
            }
        }else{ //workday
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
                    $_dateTemp = date('m-d-Y',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                    array_push($dateResult,$_dateTemp);

                    if($i==0){
                        if($index = array_search($hour,$config_workday)){
                            $_range = array_slice($config_workday,$index);
                        }else{
                            $_range = $config_weekend;
                        }
                    }else{
                        if($_numOfWeek == 0 || $_numOfWeek ==6){
                            $_range = $config_weekend;
                        }else{
                            $_range = $config_workday;
                        }
                    }
                    array_push($rangeResult,$_range);
                }

            }else{
                //The next day
                for($i=1;$i<8;$i++){
                    if($i==1){
                        $_date = strtotime("+3 hours +1 day");
                    }else{
                        $_date =  strtotime("+3 hours +".$i." days");
                    }
                    $_numOfWeek = idate("w",$_date);
                    $_dateTemp = date('m-d-Y',$_date);
                    $option = array('value'=>$_dateTemp, 'label'=>Mage::helper('shippingtime')->__($_dateTemp));
                    array_push($result,$option);
                    array_push($dateResult,$_dateTemp);

                    if($_numOfWeek == 0 || $_numOfWeek ==6){
                        $_range = $config_weekend;
                    }else{
                        $_range = $config_workday;
                    }
                    array_push($rangeResult,$_range);
                }
            }
        }

        if($type == 'date'){
            return $this->validateOrderCount($dateResult,$rangeResult,'date');
        }elseif($type == 'range'){
            return $this->validateOrderCount($dateResult,$rangeResult,'range');
        }else{
            return $result;
        }

    }

    public function validateOrderCount($date,$time,$type){
        try{
            $count = count($date);
            $conn = $this->db_connect();
            $new_date = array();
            $test = array();

            for($i = 0; $i<$count;$i++){
                $d = $date[$i];
                $ts = $time[$i];
                $new_time[$i] = array();
                //$format_date = date('m-d-Y',strtotime($d));
                foreach($ts as $t){
                    $sql = "select count(1) as order_num from sales_flat_order_storegroup s, sales_flat_order o
                    where s.order_id = o.entity_id
                    and o.status = 'processing'
                    and s.date = '$d'
                    and s.time_range = '$t';";
                    $sqlres = $conn->query($sql);
                    if(!$sqlres){
                        throw new Exception("select order num error");
                    }
                    $row = $sqlres->fetch_assoc();
                    if($row['order_num'] <= 3){
                        array_push($new_time[$i],$t);
                    }
                    array_push($test,$sql);
                }
                if(count($new_time)>0){
                    array_push($new_date,$d);
                }

            }



            if($type == 'date'){
                return $new_date;
            }else if($type == 'range'){
                return $new_time;
            }
            $conn->close();
        }catch (Exception $e){
            $conn->close();
            return false;
        }

    }

    public function db_connect() {
        $xml_array = simplexml_load_file(dirname(__FILE__).'/../../../../../../etc/local.xml');
        $host = $xml_array->global->resources->default_setup->connection->host;
        $username = $xml_array->global->resources->default_setup->connection->username;
        $password = $xml_array->global->resources->default_setup->connection->password;
        $db_name = $xml_array->global->resources->default_setup->connection->dbname;

        $res = new mysqli($host, $username, $password, $db_name);
        if ($res->connect_errno) {
            throw new Exception("Failed to connect database");
        }
        $res->query("SET NAMES utf8");
        return $res;
    }

    public function getShippingtimeHtmlDate($store,$store_groupid){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('shippingtime['.$store_groupid.'][date]')
            ->setId('shippingtime_'.$store.':date')
            ->setTitle(Mage::helper('shippingtime')->__('Shipping Date'))
            ->setOptions($this->getShippingtimeDate($store,''))
            ->setExtraParams('onchange="changeTimeRange(\''.$store.'\')"');
        return $select->getHtml();
    }

    public function getShippingmethodConfig($store,$method){
        $configstr = Mage::getStoreConfig("shippingmethod_options/shippingmethod_".$store."_label");
        if($method == 'value'){
            $config_value_str = $configstr["shippingmethod_".$store."_value"];
            $config = explode(',',trim($config_value_str));
        }elseif($method == 'name'){
            $config_name_str = $configstr["shippingmethod_".$store."_name"];
            $config = explode(',',trim($config_name_str));
        }
        return $config;
    }

    public function getShippingmethodHtml($store){
        $config_name=$this->getShippingmethodConfig($store,'name');
        $config_value=$this->getShippingmethodConfig($store,'value');
        //return $config_name[0]." : $".$config_value[0];
        return $config_name[0];
    }

    public function getTotalShippingFee($store_groups){
        $total = 0;
        foreach ($store_groups as $key => $store_group){
            $store_name =  ($store_group->getStoregroupName()=='Walmart')?'walmart':'fareast';
            $fee = $this->getShippingmethodConfig($store_name,'value')[0];
            $total = $total + floatval($fee);
        }
        return $total;
    }

    public function getTotalShippingFeeHtml($store_groups){
        $total = $this->getTotalShippingFee($store_groups);
        return "Delivery Fee: $".number_format($total,2);
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
