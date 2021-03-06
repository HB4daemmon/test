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
    * @package     Mage_Customer
    * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
    * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
    */

    /**
    * Customer sharing config model
    *
    * @category   Mage
    * @package    Mage_Customer
    * @author      Magento Core Team <core@magentocommerce.com>
    */
    class Bc_Deliverydate_Model_Config_Dateformat extends Mage_Core_Model_Config_Data
    {

        /**
        * Get possible sharing configuration options
        *
        * @return array
        */
        public function toOptionArray()
        {
            return array(
                'd/M/Y' => Mage::helper('deliverydate')->__('d/M/Y'),
                'M/d/y' => Mage::helper('deliverydate')->__('M/d/y'),
                'd-M-Y' => Mage::helper('deliverydate')->__('d-M-Y'),
                'M-d-y' => Mage::helper('deliverydate')->__('M-d-y'),
                'm.d.y' => Mage::helper('deliverydate')->__('m.d.y'),
                'd.M.Y' => Mage::helper('deliverydate')->__('d.M.Y'),
                'M.d.y' => Mage::helper('deliverydate')->__('M.d.y'),
                'F j ,Y'=> Mage::helper('deliverydate')->__('F j ,Y'),
                'D M j' => Mage::helper('deliverydate')->__('D M j'),
                'Y-m-d' => Mage::helper('deliverydate')->__('Y-m-d')
            );
        }

    }
