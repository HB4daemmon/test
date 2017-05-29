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
 * Sales order history block
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Sales_Block_Order_Recent extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();

        //TODO: add full name logic
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addAttributeToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->addFieldToFilter('parent_order_id', array('neq' => NULL))
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize('5')
            ->load()
        ;

        $this->setOrders($orders);
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
    }

    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', array('order_id' => $order->getId()));
    }

    protected function _toHtml()
    {
        if ($this->getOrders()->getSize() > 0) {
            return parent::_toHtml();
        }
        return '';
    }

    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', array('order_id' => $order->getId()));
    }

    public function getHistoryStatus($order){
        $res = Array();
        $status_name = "";
        $status_list = ['complete','in_progress','in_transit','delivered'];
        $index = 0;
        foreach($order->getStatusHistoryCollection() as $s){
            $index ++;
            $status = $s->getData();
            if ($status['status'] != $status_name){
                $status_name = $status['status'];
                $created_at = $status['created_at'];
                $d = strtotime($created_at);
//                $d1 = date("h:i a", $d);
//                $d2 = date("D, M j, Y", $d);
                $d1 = Mage::getSingleton('core/date')->date( "h:i a", $d );
                $d2 = Mage::getSingleton('core/date')->date( "D, M j, Y", $d );
                if ($index == 1){
                    $d3 = "orange";
                    $d4 = 4;
                }else{
                    $d3 = "green";
                    $d4 = 3;
                }
                $res[$status_name] = [$d1,$d2,$d3,$d4];
            }
        }

        $res_count = count($res);
        $res['confirmed'] = array_merge([],$res['complete']);
        if ($res_count == 1){
            $res['complete'][2] = "green";
            $res['complete'][3] = 3;
            $res['confirmed'][2] = "orange";
            $res['confirmed'][3] = 4;
        }

        foreach($status_list as $s){
            if (!isset($res[$s])){
                $res[$s] = ["&nbsp;","&nbsp;","grey",5];
            }
        }
        return $res;
    }

    public function getStandDataFormat($date){
//        return date("D, M j, Y", $d);
        $d = strtotime($date);
        return date("D, M j, Y", $d);
    }
}
