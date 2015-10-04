<?php
/**
 * 
 */

class Luciky_Sales_Model_Observer extends Mage_Sales_Model_Observer{

	/**
	 * 
	 *quoteitem保存之后操作：
	 * @param Varien_Event_Observer $observer
	 */
	
	public function CheckoutAddAfter(Varien_Event_Observer $observer){
	
		$_quote=$observer->getQuote();
		$_group=$_quote->getStore()->getGroup();
		$store_groups=Mage::getModel('sales/quote_storegroup')->getCollection();
		$store_groups->addFieldtoFilter('quote_id',$_quote->getId())
				->addFieldtoFilter('storegroup_id',$_group->getId());
		if (count($store_groups) == 0 ){
		
				$store_group = Mage::getModel('sales/quote_storegroup');
				$store_group->setQuote($_quote)
							->setStoregroupId($_group->getId())
							->setStoregroupName($_group->getName());
				try {
					$store_group->save();
				} catch (Exception $e) {
					Mage::log('store_group cannot be inserted ');
				}
		}
		
	}
	
	public function CheckoutDelAfter(Varien_Event_Observer  $observer)
	{
		$_quote=$observer->getQuote();
		$storegroups=Mage::getModel('sales/quote_storegroup')->getCollection();
		$storegroups->addFieldtoFilter('quote_id',$_quote->getId());
		$storeIds=array();
		foreach ($_quote->getAllItems() as $key => $item){
			$storeIds[]=$item->getStoreId();
		}
		foreach ($storegroups as $key => $storegroup){
			$groupstoreIds=array();
			$group= Mage::getModel('core/store_group')->load($storegroup->getStoregroupId());
			$groupstoreIds=$group->getStoreIds();
			$intersect = array_intersect($groupstoreIds, $storeIds);
			if (count($intersect) == 0){
			
				try {
				$storegroup->delete();	
				} catch (Exception $e) {
					Mage::log('delete your store_group after delete item ERROR AT OBSERVER');
				}
				
			
			}
		}
	
	
	}
	
// 	$base_discount_amount=0;//base_discount_amount
// 	$base_grand_total=0;//base_row_total
// 	$base_shipping_tax_amount=0;
// 	$base_subtotal=0;
// 	$subtotal=0;
// 	$base_tax_amount=0;//base_tax_amount
// 	$discount_amount=0;//discount_amount
// 	$grand_total=0;//row_total
// 	$shipping_amount=0;
// 	$shipping_tax_amount=0;
// 	/*store_to_base rate.....*/
		
// 	$tax_amount=0;//tax_amount
// 	$total_qty_ordered=0;//qty_ordered
// 	$base_shipping_discount_amount=0;
// 	$base_subtotal_incl_tax=0;//base_price_incl_tax?base_row_total_incl_tax
// 	$shipping_discount_amount=0;
// 	$subtotal_incl_tax=0;//price_incl_tax?row_total_incl_tax
// 	$weight=0;//row_weight
// 	$total_item_count=0;//$total_qty_ordered
// 	$hidden_tax_amount=0;//hidden_tax_amount
// 	$base_hidden_tax_amount=0;//base_hidden_tax_amount
// 	$shipping_hidden_tax_amount=0;
// 	$base_shipping_hidden_tax_amount=0;
// 	$shipping_incl_tax=0;
// 	$base_shipping_incl_tax=0;
		
	/**
	 * 
	 * 拆分订单
	 * @param Varien_Event_Observer $observer
	 */
	public function SaveOrdertoOrders(Varien_Event_Observer $observer){
	
		$_order=$observer->getOrder();
		$_quote=$observer->getQuote();

		$keys=array('state','status','shipping_description','quote_id','is_virtual','store_id','customer_id','customer_group_id','customer_gender','base_to_global_rate','base_to_order_rate','customer_is_guest','customer_note_notify','email_sent','store_currency_code',
		'base_currency_code','customer_email','customer_firstname','customer_lastname','global_currency_code','order_currency_code','remote_ip','shipping_method','shipping_incl_tax','base_shipping_incl_tax');

        $item_keys=array( 'parent_item_id', 'quote_item_id', 'store_id', 'created_at', 'updated_at', 'product_id', 'product_type', 'product_options', 'weight',
            'is_virtual', 'sku', 'name', 'description', 'applied_rule_ids', 'additional_data', 'free_shipping', 'is_qty_decimal', 'no_discount', 'qty_backordered', 'qty_canceled', 
            'qty_invoiced', 'qty_ordered', 'qty_refunded', 'qty_shipped', 'base_cost', 'price', 'base_price', 'original_price', 'base_original_price', 'tax_percent', 'tax_amount', 
            'base_tax_amount', 'tax_invoiced', 'base_tax_invoiced', 'discount_percent', 'discount_amount', 'base_discount_amount', 'discount_invoiced', 'base_discount_invoiced', 
            'amount_refunded', 'base_amount_refunded', 'row_total', 'base_row_total', 'row_invoiced', 'base_row_invoiced', 'row_weight', 'base_tax_before_discount',
            'tax_before_discount', 'ext_order_item_id', 'locked_do_invoice', 'locked_do_ship', 'price_incl_tax', 'base_price_incl_tax', 'row_total_incl_tax',
            'base_row_total_incl_tax', 'hidden_tax_amount', 'base_hidden_tax_amount', 'hidden_tax_invoiced', 'base_hidden_tax_invoiced', 'hidden_tax_refunded', 
            'base_hidden_tax_refunded', 'is_nominal', 'tax_canceled', 'hidden_tax_canceled', 'tax_refunded', 'base_tax_refunded', 'discount_refunded', 'base_discount_refunded',
            'gift_message_id', 'gift_message_available', 'base_weee_tax_applied_amount', 'base_weee_tax_applied_row_amnt', 'weee_tax_applied_amount', 'weee_tax_applied_row_amount', 
            'weee_tax_applied', 'weee_tax_disposition', 'weee_tax_row_disposition', 'base_weee_tax_disposition', 'base_weee_tax_row_disposition', 'sales_order_storegroup_id',
            'real_order_id', 'substitute', 'customer_message');

		$quote_storegroups=Mage::getModel('sales/quote_storegroup')->getCollection();
		$quote_storegroups->addFieldtoFilter('quote_id',$_quote->getId());
		$pkeys=array('storegroup_id','storegroup_name','time_range','date');

		$_count=count($quote_storegroups);

		foreach ($quote_storegroups as  $quote_storegroup){//创建子订单
			$_convert=Mage::getModel('sales/convert_quote');
            $order_items=Mage::getModel('sales/order_item')->getCollection();
            $order_items->addFieldtoFilter('order_id',$_order->getData('entity_id'));
			$new_order=Mage::getModel('sales/order');
			$new_order->setBillingAddress($_convert->addressToOrderAddress($_quote->getBillingAddress()));
			$new_order->setShippingAddress($_convert->addressToOrderAddress($_quote->getShippingAddress()));
			$new_order->setPayment($_convert->paymentToOrderPayment($_quote->getPayment()));
			foreach ($keys as  $value){
				$new_order->setData($value,$_order->getData($value));
			}
			$reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($_order->getStoreId());
			$new_order->setData('state','new');
			$new_order->setData('parent_order_id',$_order->getId());
			$new_order->setIncrementId($reservedOrderId);
			$new_order->setQuote($_quote);
			$new_order->save();
			$new_orderid=$new_order->getData('entity_id');
		    Mage::log('new order id'.$new_orderid);
			$order_storegroup=Mage::getModel('sales/order_storegroup');
			foreach ($pkeys as  $value){
				$order_storegroup->setData($value,$quote_storegroup->getData($value));
			}
            $order_storegroup->setData('order_id',$new_orderid);
			try{
                $order_storegroup->save();
            }catch (Exception $e){
                Mage::log($e);
            }
			$new_order->setData('sales_flat_storegroup_id',$order_storegroup->getId());
			$new_order->setData('storegroup_id',$order_storegroup->getStoregroupId());
            //$new_order->setData('sales_order_custom_id',$order_storegroup->getId());
			$new_order->save();
			$store_group=Mage::getModel('core/store_group')->load($order_storegroup->getStoregroupId());
			$storeids=$store_group->getStoreIds();
			$order_items->addFieldtoFilter('store_id',array('in'=>$storeids));

            //get each shipping method cost
            $store_name=($store_group->getName()=='Walmart')?'walmart':'fareast';
            $configstr = Mage::getStoreConfig("shippingmethod_options/shippingmethod_".$store_name."_label");
            $config_value_str = $configstr["shippingmethod_".$store_name."_value"];
            $config = explode(',',trim($config_value_str));
            $shippingmethod_amount=$config[0];
			//商品价格总计subtotal
			//计算税收总额tax_amount
			//计算折扣总额 discount amount
			//含税rowtotal
			//
			$orderData=array(
					'base_discount_amount'=>0,//base_discount_amount
					'base_grand_total'=>0,//base_row_total
					'base_shipping_tax_amount'=>0,
					'base_subtotal'=>0,
					'subtotal'=>0,
					'base_tax_amount'=>0,//base_tax_amount
					'discount_amount'=>0,//discount_amount
					'grand_total'=>0,//row_total
					'shipping_amount'=>0,
					'shipping_tax_amount'=>0,
					/*store_to_base rate.....*/
						
					'tax_amount'=>0,//tax_amount
					'total_qty_ordered'=>0,//qty_ordered
					'base_shipping_discount_amount'=>0,
					'base_subtotal_incl_tax'=>0,//base_price_incl_tax?base_row_total_incl_tax
					'shipping_discount_amount'=>0,
					'subtotal_incl_tax'=>0,//price_incl_tax?row_total_incl_tax
					'weight'=>0,//row_weight
					'total_item_count'=>0,//$total_qty_ordered
					'hidden_tax_amount'=>0,//hidden_tax_amount
					'base_hidden_tax_amount'=>0,//base_hidden_tax_amount
					'shipping_hidden_tax_amount'=>0,
					'base_shipping_hidden_tax_amount'=>0,
					'shipping_incl_tax'=>0,
					'base_shipping_incl_tax'=>0,
                    'shippingmethod_amount'=>0,
                    'base_shippingmethod_amount'=>0,
			);
			
			
			foreach ($order_items as $key => $order_item){
                Mage::log(json_encode($order_item));
				$orderData['base_discount_amount']+=$order_item->getData('base_discount_amount');
				$orderData['base_subtotal']+=$order_item->getData('base_row_total');
				$orderData['subtotal']+=$order_item->getData('row_total');
				$orderData['base_tax_amount']+=$order_item->getData('base_tax_amount');
				$orderData['discount_amount']+=$order_item->getData('discount_amount');
				$orderData['tax_amount']+=$order_item->getData('tax_amount');
				$orderData['total_qty_ordered']+=$order_item->getData('qty_ordered');
				$orderData['base_subtotal_incl_tax']+=$order_item->getData('base_row_total_incl_tax');
				$orderData['subtotal_incl_tax']+=$order_item->getData('row_total_incl_tax');
				$orderData['weight']+=$order_item->getData('row_weight');
				$orderData['total_item_count']+=$order_item->getData('$total_qty_ordered');
				$orderData['hidden_tax_amount']+=$order_item->getData('hidden_tax_amount');
				$orderData['base_hidden_tax_amount']+=$order_item->getData('base_hidden_tax_amount');

				$order_item->setData('sales_order_storegroup_id',$order_storegroup->getId());
				$order_item->setData('real_order_id',$new_orderid);
				$order_item->save();

                $new_item = Mage::getModel('sales/order_item');
                foreach($item_keys as $key){//create new order items
                    $new_item->setData($key,$order_item->getData($key));
                }
                $new_item->setData('order_id',$new_orderid);
                $new_item->save();
			}
				$orderData['base_shipping_tax_amount']=$_order->getData('base_shipping_tax_amount') / $_count;
				$orderData['base_shipping_amount']=$_order->getData('base_shipping_amount') / $_count;				
				$orderData['shipping_amount']=$_order->getData('shipping_amount') / $_count;
				$orderData['shipping_tax_amount']=$_order->getData('shipping_tax_amount') / $_count;				
				$orderData['base_shipping_discount_amount']=$_order->getData('base_shipping_discount_amount') / $_count;				
				$orderData['shipping_discount_amount']=$_order->getData('shipping_discount_amount') / $_count;				
				$orderData['shipping_hidden_tax_amount']=$_order->getData('shipping_hidden_tax_amount') / $_count;				
				$orderData['base_shipping_hidden_tax_amount']=$_order->getData('base_shipping_hidden_tax_amount') / $_count;
				$orderData['shipping_incl_tax']=$_order->getData('shipping_incl_tax') / $_count;
				$orderData['base_shipping_incl_tax']=$_order->getData('base_shipping_incl_tax') / $_count;
				$orderData['state']=$_order->getData('state');
				$orderData['status']=$_order->getData('status');

                $orderData['shippingmethod_amount']=$shippingmethod_amount;
                $orderData['base_shippingmethod_amount']=$shippingmethod_amount;
                $orderData['shipping_amount']= $orderData['shippingmethod_amount'];
                $orderData['base_shipping_amount']=$orderData['base_shippingmethod_amount'];

                $orderData['tips_amount']= $_order->getData('tips_amount')/ $_count;
                $orderData['base_tips_amount']=$_order->getData('base_tips_amount')/ $_count;

				$orderData['base_grand_total']=$orderData['base_subtotal']+ $orderData['base_shipping_amount']+$orderData['tips_amount'];
				$orderData['grand_total']=$orderData['subtotal']+$orderData['shipping_amount']+$orderData['base_tips_amount'];

			foreach ($orderData as $key => $value){
				$new_order->setData($key,$value);
			}

			$new_order->save();

		}
		
	}
	
	public function OrderCancelledAfter(Varien_Event_Observer $observer){
		
		$parentId=$observer->getOrder()->getId();
		$ChildrenOrders=Mage::getModel('sales/order')->getCollection();
		$ChildrenOrders->addFieldtoFilter('parent_order_id',$parentId);
		foreach ($ChildrenOrders as $key => $order){
			$order->setData('status',$observer->getData('status'));
			$order->save();
		}
		
	}


}