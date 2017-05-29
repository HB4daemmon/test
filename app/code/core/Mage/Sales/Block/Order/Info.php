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
 * Invoice view  comments form
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Order_Info extends Mage_Core_Block_Template
{
    protected $_links = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/info.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function addLink($name, $path, $label)
    {
        $this->_links[$name] = new Varien_Object(array(
            'name' => $name,
            'label' => $label,
            'url' => empty($path) ? '' : Mage::getUrl($path, array('order_id' => $this->getOrder()->getId()))
        ));
        return $this;
    }

    public function getLinks()
    {
        $this->checkLinks();
        return $this->_links;
    }

    private function checkLinks()
    {
        $order = $this->getOrder();
        if (!$order->hasInvoices()) {
            unset($this->_links['invoice']);
        }
        if (!$order->hasShipments()) {
            unset($this->_links['shipment']);
        }
        if (!$order->hasCreditmemos()) {
            unset($this->_links['creditmemo']);
        }
    }

    /**
     * Get url for reorder action
     *
     * @deprecated after 1.6.0.0, logic moved to new block
     * @param Mage_Sales_Order $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->getUrl('sales/guest/reorder', array('order_id' => $order->getId()));
        }
        return $this->getUrl('sales/order/reorder', array('order_id' => $order->getId()));
    }

    /**
     * Get url for printing order
     *
     * @deprecated after 1.6.0.0, logic moved to new block
     * @param Mage_Sales_Order $order
     * @return string
     */
    public function getPrintUrl($order)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->getUrl('sales/guest/print', array('order_id' => $order->getId()));
        }
        return $this->getUrl('sales/order/print', array('order_id' => $order->getId()));
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
}
