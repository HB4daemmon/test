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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Quote Order Model
 */
class Luciky_Sales_Model_Quote_Storegroup extends Mage_Core_Model_Abstract
{
	protected $_quote;
	
	protected $_store;
	
	

    protected function _construct()
    {
        $this->_init('sales/quote_storegroup');
  
    }
    
    /**
     * Declare quote model object
     *
     * @param   Luciky_Sales_Model_Quote $quote
     * @return  Luciky_Sales_Model_Quote_Store
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }
    
    
    

    
    /**
     * 
     * @param Mage_Core_Model_Store $store
     * @return Luciky_Sales_Model_Quote_Store
     */

    public function setStoregroupfromStore(Mage_Core_Model_Store $store)
    {
    	$this->_store = $store;
    	$store_group=$store->getGroup();
    	$this->setStoregroupId($store_group->getId());
    	$this->setStoregroupName($store_group->getName());
		return  $this;
    	
    }
    
    public function getItems(){
    	$items = Mage::getModel('sales/quote_item')->getCollection();
    	$items ->addFieldtoFilter('sales_quote_storegroup_id',$this->getId());
    	return $items;
    }
    
    public function setDate(){
    	
    }
    
    public function setTimeRange(){
    	
    }

    
}
