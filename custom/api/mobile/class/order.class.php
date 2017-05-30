<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.class.php');
require_once(dirname(__FILE__) . '/../../../vendor/stripe-php/init.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);

class MobileOrder{
    public static function create($user_id,$token,$products,$address_id,$delivery_date,$delivery_range){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

//            $transaction = Mage::getModel('core/resource_transaction');
            $storeId = $customer->getStoreId();
            $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);

            $order = Mage::getModel('sales/order')
                ->setIncrementId($reservedOrderId)
                ->setStoreId($storeId)
                ->setQuoteId(0)
                ->setGlobal_currency_code('USD')
                ->setBase_currency_code('USD')
                ->setStore_currency_code('USD')
                ->setOrder_currency_code('USD');
            //Set your store currency USD or any other

            // set Customer data
            $order->setCustomer_email($customer->getEmail())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerLastname($customer->getLastname())
                ->setCustomerGroupId($customer->getGroupId())
                ->setCustomer_is_guest(0)
                ->setCustomer($customer)
                ->setState('new')
                ->setStatus('pending')
                ->setIs('pending')
                ->setStoregroupId(5);

//            $billing = $customer->getDefaultShippingAddress();
//            $billingAddress = Mage::getModel('sales/order_address')
//                ->setStoreId($storeId)
//                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
//                ->setCustomerId($customer->getId())
////                ->setCustomerAddressId($customer->getDefaultShipping())
//                ->setCustomerAddressId($address_id)
//                //->setCustomer_address_id($billing->getEntityId())
//                ->setPrefix($billing->getPrefix())
//                ->setFirstname($billing->getFirstname())
//                ->setMiddlename($billing->getMiddlename())
//                ->setLastname($billing->getLastname())
//                ->setSuffix($billing->getSuffix())
//                ->setCompany($billing->getCompany())
//                ->setStreet($billing->getStreet())
//                ->setCity($billing->getCity())
//                ->setCountryId($billing->getCountryId())
//                ->setRegion($billing->getRegion())
//                ->setRegionId($billing->getRegionId())
//                ->setPostcode($billing->getPostcode())
//                ->setTelephone($billing->getTelephone())
//                ->setFax($billing->getFax());
//            $order->setBillingAddress($billingAddress);

            $shipping = Mage::getModel('customer/address')->load($address_id);
            $shippingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($customer->getId())
                ->setCustomerAddressId($address_id)
                ->setCustomer_address_id($shipping->getEntityId())
                ->setPrefix($shipping->getPrefix())
                ->setFirstname($shipping->getFirstname())
                ->setMiddlename($shipping->getMiddlename())
                ->setLastname($shipping->getLastname())
                ->setSuffix($shipping->getSuffix())
                ->setCompany($shipping->getCompany())
                ->setStreet($shipping->getStreet())
                ->setCity($shipping->getCity())
                ->setCountryId($shipping->getCountryId())
                ->setRegion($shipping->getRegion())
                ->setRegionId($shipping->getRegionId())
                ->setPostcode($shipping->getPostcode())
                ->setTelephone($shipping->getTelephone())
                ->setFax($shipping->getFax());
            $order->setShippingAddress($shippingAddress)
                ->setShipping_method('flatrate_flatrate');
            /*->setShippingDescription($this->getCarrierName('flatrate'));*/
            /*some error i am getting here need to solve further*/

            //you can set your payment method name here as per your need
            $orderPayment = Mage::getModel('sales/order_payment')
                ->setStoreId($storeId)
//                ->setCustomerPaymentId(0)
                ->setCountryId($shipping->getCountryId())
                ->setMethod('cryozonic_stripe');
            $order->setPayment($orderPayment);
//            error_log(print_r(Mage::getModel('payment/config')->getAllMethods()));
//            $order->getPayment()->importData(array('method' => 'checkmo'));

            // let say, we have 2 products
            //check that your products exists
            //need to add code for configurable products if any
            $subTotal = 0;
            $products = json_decode($products,true);
//            return $products;
            foreach ($products as $productId=>$product) {
                $_product = Mage::getModel('catalog/product')->load($productId);
                $rowTotal = $_product->getPrice() * $product['qty'];
                $orderItem = Mage::getModel('sales/order_item')
                    ->setStoreId($storeId)
                    ->setQuoteItemId(0)
                    ->setQuoteParentItemId(NULL)
                    ->setProductId($productId)
                    ->setProductType($_product->getTypeId())
                    ->setQtyBackordered(NULL)
                    ->setTotalQtyOrdered($product['rqty'])
                    ->setQtyOrdered($product['qty'])
                    ->setName($_product->getName())
                    ->setSku($_product->getSku())
                    ->setPrice($_product->getPrice())
                    ->setBasePrice($_product->getPrice())
                    ->setOriginalPrice($_product->getPrice())
                    ->setRowTotal($rowTotal)
                    ->setBaseRowTotal($rowTotal);

                $subTotal += $rowTotal;
                $order->addItem($orderItem);
            }
            $order->setSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setGrandTotal($subTotal)
                ->setBaseGrandTotal($subTotal);
            $order->save();
            $order_id = $order->getId();
            $store_groups=Mage::getModel('sales/order_storegroup')
                         ->setOrderId($order_id)
                         ->setStoregroupId(5)
                         ->setStoregroupName('Walmart')
                         ->setDate($delivery_date)
                         ->setTimeRange($delivery_range);
            $store_groups->save();


            $order->setParentOrderId($order_id)
                  ->setSalesFlatStoregroupId($store_groups->getId());

            $stripe_res = MobileOrder::stripe_pay($token,$subTotal*100);
            if ($stripe_res['status'] == 'succeeded'){
                $order->setStatus("in_progress");
                $history = $order->addStatusHistoryComment('Manually set order to In Progress.', false);
                $history->setIsCustomerNotified(false);
                $order->save();
            }
            return $customer;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function stripe_pay($token,$amount){
        \Stripe\Stripe::setApiKey("sk_test_v0EvK0JYd9buXKSIxqZXMpgm");

        try {
            $charge = \Stripe\Charge::create(array(
                "amount" => $amount,
                "currency" => "usd",
                "description" => "Example charge",
                "source" => $token,
            ));
            return $charge;
        }catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];
            throw new Exception($e->getMessage($e->getMessage()));
//            print('Status is:' . $e->getHttpStatus() . "\n");
//            print('Type is:' . $err['type'] . "\n");
//            print('Code is:' . $err['code'] . "\n");
//            print('Param is:' . $err['param'] . "\n");
//            print('Message is:' . $err['message'] . "\n");
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            throw new Exception("Too many requests made to the API too quickly");
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new Exception("Invalid parameters were supplied to Stripe's API");
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            throw new Exception("Authentication with Stripe's API failed");
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            throw new Exception("Network communication with Stripe failed");
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            throw new Exception("Display a very generic error to the user, and maybe send yourself an email");
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            throw new Exception("other error");
        }

    }

    public static function get_order_list($user_id,$page=0,$page_size=20){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

            $orders_collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter("customer_id", $user_id)
                ->addAttributeToSort('created_at', 'desc')
                ->setPage($page,$page_size);

            $orders = [];
            foreach ($orders_collection as $order) {
                $items=array();
                foreach ($order->getAllItems() as $item) {
                    $items[] = array(
                        'item_id'            => $item->getId(),
                        'name'          => $item->getName(),
                        'sku'           => $item->getSku(),
                        'Price'         => $item->getPrice(),
                        'Ordered Qty'   => $item->getQtyOrdered(),
                        'customer_message' => $item->getCustomerMessage(),
                    );}
                $orders['orders'][] = array(
                    'order_id'            => $order->getId(),
                    'status'        => $order->getStatus(),
                    'name'          => $order->getCustomerName(),
                    'email'         => $order->getCustomerEmail(),
                    'telephone'     => $order->getShippingAddress()->getTelephone(),
                    'street'        => $order->getShippingAddress()->getStreet(),
                    'pincode'       => $order->getShippingAddress()->getPostcode(),
                    'city'          => $order->getShippingAddress()->getCity(),
                    'weight'        => $order->getWeight(),
                    'items'        => $items,
                    'created_at'  => $order->getCreatedAt(),
                );
            }
            $pager['page'] = $orders_collection->getCurPage();
            $pager['page_size'] = $orders_collection->getPageSize();
            $pager['last_page_number'] = $orders_collection->getLastPageNumber();

            $res['pager'] = $pager;
            $res['orders'] = $orders;
            return $res;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function getOrderDetail($order_id){
        try {
            $conn = db_connect();
            $sql = "select o.increment_id,oi.item_id,oi.product_id,oi.store_id,oi.product_id,oi.weight,oi.sku,oi.name,oi.qty_ordered,
                oi.price,cped.value as store_price,oi.base_price,oi.base_original_price,oi.row_total,oi.price_incl_tax,
                oi.base_price_incl_tax,oi.row_total_incl_tax,oi.base_row_total_incl_tax,
                if(oi.substitute=1,'Y','N') as substitute,oi.customer_message,oi.item_status,oi.sub_price,oi.sub_volume,
                oi.tax_percent,oi.tax_amount
                 from sales_flat_order_item oi,sales_flat_order o,catalog_product_entity_varchar cped,eav_attribute ea
                where oi.order_id = o.entity_id
                and oi.product_id = cped.entity_id
                and cped.attribute_id = ea.attribute_id
				and attribute_code = 'store_price'
                and ea.entity_type_id = 4
                and o.entity_id = $order_id;
                    ";
            $res = $conn->query($sql);
            $order_items = array();
            while($row = $res->fetch_assoc()){
                array_push($order_items,$row);
            }

            $out_of_stock = 0;
            $substitute = 0;
            $tax = 0;

            foreach($order_items as $i){
                if ($i['item_status'] == 'out_of_stock'){
                    $out_of_stock -= $i['row_total'];
                }else if ($i['item_status'] == 'substitute'){
                    $substitute += -$i['row_total'] + $i['sub_price'] * $i['sub_volume'];
                    $tax += round($i['sub_price'] * $i['sub_volume'] * $i['tax_percent']) / 100;
                }else{
                    $tax += floatval($i['tax_amount']);
                }

                if ($i['store_price'] == null || $i['store_price'] == ''){
                    $i['store_price'] = $i['price'];
                }

            }

            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter("entity_id", $order_id)
                ->getFirstItem();

            $order['subtotal'] = number_format($orders->getData('subtotal'),2);
            $order['shipping_amount'] = number_format($orders->getData('shipping_amount'),2);
            $order['tips'] = number_format($orders->getTipsAmount(),2);
            $order['original_tax'] = number_format($orders->getData('tax_amount'),2);
            $order['out_of_stock'] = number_format($out_of_stock,2);
            $order['substitute'] = number_format($substitute,2);
            $order['tax'] = number_format($tax,2);

            $order['total'] = number_format($orders->getData('grand_total') - $orders->getData('tax_amount') + $out_of_stock + $substitute + $tax,2);
            $order['original_grand_total'] = number_format($orders->getData('grand_total'),2);
            $order['change'] = number_format($tax - $orders->getData('tax_amount') + $substitute + $out_of_stock ,2);

            $order['items'] = $order_items;

            return $order;

        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
