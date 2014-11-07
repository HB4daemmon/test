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
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Luciky_Checkout_Block_Onepage_Shippingtime extends Mage_Checkout_Block_Onepage_Abstract
{
    
    protected function _construct()
    {
        $this->getCheckout()->setStepData('shipping_time', array(
            'label'     => Mage::helper('checkout')->__('Deliver Time'),
            'is_show'   => $this->isShow()
        ));

//        if ($this->isCustomerLoggedIn()) {
//            $this->getCheckout()->setStepData('shippingtime', 'allow', true);
//        }
        parent::_construct();
    }



    /**
     * Return Storegroups
     *
     * @return array
     */
    public function getStoregroups()
    {
        $store_groups=Mage::getModel('sales/quote_storegroup')->getCollection();
        $store_groups->addFieldtoFilter('quote_id',$this->getQuote()->getId());
        return $store_groups;
       }

   
}
