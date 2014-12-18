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
 * Sales Quote Item Model
 *
 * @method Mage_Sales_Model_Resource_Quote_Item _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Item getResource()
 * @method int getQuoteId()
 * @method Mage_Sales_Model_Quote_Item setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Quote_Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Quote_Item setUpdatedAt(string $value)
 * @method int getProductId()
 * @method Mage_Sales_Model_Quote_Item setProductId(int $value)
 * @method int getStoreId()
 * @method Mage_Sales_Model_Quote_Item setStoreId(int $value)
 * @method int getParentItemId()
 * @method Mage_Sales_Model_Quote_Item setParentItemId(int $value)
 * @method int getIsVirtual()
 * @method Mage_Sales_Model_Quote_Item setIsVirtual(int $value)
 * @method string getSku()
 * @method Mage_Sales_Model_Quote_Item setSku(string $value)
 * @method string getName()
 * @method Mage_Sales_Model_Quote_Item setName(string $value)
 * @method string getDescription()
 * @method Mage_Sales_Model_Quote_Item setDescription(string $value)
 * @method string getAppliedRuleIds()
 * @method Mage_Sales_Model_Quote_Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Quote_Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method Mage_Sales_Model_Quote_Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method Mage_Sales_Model_Quote_Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method Mage_Sales_Model_Quote_Item setNoDiscount(int $value)
 * @method float getWeight()
 * @method Mage_Sales_Model_Quote_Item setWeight(float $value)
 * @method float getBasePrice()
 * @method Mage_Sales_Model_Quote_Item setBasePrice(float $value)
 * @method float getCustomPrice()
 * @method float getDiscountPercent()
 * @method Mage_Sales_Model_Quote_Item setDiscountPercent(float $value)
 * @method float getDiscountAmount()
 * @method Mage_Sales_Model_Quote_Item setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseDiscountAmount(float $value)
 * @method float getTaxPercent()
 * @method Mage_Sales_Model_Quote_Item setTaxPercent(float $value)
 * @method Mage_Sales_Model_Quote_Item setTaxAmount(float $value)
 * @method Mage_Sales_Model_Quote_Item setBaseTaxAmount(float $value)
 * @method float getRowTotal()
 * @method Mage_Sales_Model_Quote_Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method Mage_Sales_Model_Quote_Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method Mage_Sales_Model_Quote_Item setRowTotalWithDiscount(float $value)
 * @method float getRowWeight()
 * @method Mage_Sales_Model_Quote_Item setRowWeight(float $value)
 * @method Mage_Sales_Model_Quote_Item setProductType(string $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method Mage_Sales_Model_Quote_Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method Mage_Sales_Model_Quote_Item setTaxBeforeDiscount(float $value)
 * @method float getOriginalCustomPrice()
 * @method Mage_Sales_Model_Quote_Item setOriginalCustomPrice(float $value)
 * @method string getRedirectUrl()
 * @method Mage_Sales_Model_Quote_Item setRedirectUrl(string $value)
 * @method float getBaseCost()
 * @method Mage_Sales_Model_Quote_Item setBaseCost(float $value)
 * @method float getPriceInclTax()
 * @method Mage_Sales_Model_Quote_Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method Mage_Sales_Model_Quote_Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method Mage_Sales_Model_Quote_Item setGiftMessageId(int $value)
 * @method string getWeeeTaxApplied()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxAppliedRowAmount(float $value)
 * @method float getWeeeTaxDisposition()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseHiddenTaxAmount(float $value)
 * @method null|bool getHasConfigurationUnavailableError()
 * @method Mage_Sales_Model_Quote_Item setHasConfigurationUnavailableError(bool $value)
 * @method Mage_Sales_Model_Quote_Item unsHasConfigurationUnavailableError()
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Luciky_Sales_Model_Order_Storegroup extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sales/order_storegroup');
  
    }

    
}
