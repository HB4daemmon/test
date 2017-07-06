<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.class.php');
require_once(dirname(__FILE__) . '/../../../vendor/stripe-php/init.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);

class MobileOrder{
    public static function create($user_id,$token,$products,$address_id,$delivery_date,$delivery_range,$tips){
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

            $billing = Mage::getModel('customer/address')->load($address_id);
            $billingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($customer->getId())
//                ->setCustomerAddressId($customer->getDefaultShipping())
                ->setCustomerAddressId($address_id)
                //->setCustomer_address_id($billing->getEntityId())
                ->setPrefix($billing->getPrefix())
                ->setFirstname($billing->getFirstname())
                ->setMiddlename($billing->getMiddlename())
                ->setLastname($billing->getLastname())
                ->setSuffix($billing->getSuffix())
                ->setCompany($billing->getCompany())
                ->setStreet($billing->getStreet())
                ->setCity($billing->getCity())
                ->setCountryId($billing->getCountryId())
                ->setRegion($billing->getRegion())
                ->setRegionId($billing->getRegionId())
                ->setPostcode($billing->getPostcode())
                ->setTelephone($billing->getTelephone())
                ->setFax($billing->getFax());
            $order->setBillingAddress($billingAddress);

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
            $order->save();
            $order_id = $order->getId();
            $store_groups=Mage::getModel('sales/order_storegroup')
                ->setOrderId($order_id)
                ->setStoregroupId(5)
                ->setStoregroupName('Walmart')
                ->setDate($delivery_date)
                ->setTimeRange($delivery_range);
            $store_groups->save();
            $store_groups_id = $store_groups->getId();

            $subTotal = 0;
            $products = json_decode($products,true);
//            return $products;
            foreach ($products as $productId=>$product) {
                $_product = Mage::getModel('catalog/product')->load($productId);
                $rowTotal = $_product->getPrice() * $product['qty'];
                if ($product['substitute'] == "Y"){
                    $sub = 1;
                }else{
                    $sub = 0;
                }
                $orderItem = Mage::getModel('sales/order_item')
                    ->setStoreId($storeId)
                    ->setQuoteItemId(0)
                    ->setQuoteParentItemId(NULL)
                    ->setProductId($productId)
                    ->setProductType($_product->getTypeId())
                    ->setQtyBackordered(NULL)
                    ->setTotalQtyOrdered($product['qty'])
                    ->setQtyOrdered($product['qty'])
                    ->setName($_product->getName())
                    ->setSku($_product->getSku())
                    ->setPrice($_product->getPrice())
                    ->setBasePrice($_product->getPrice())
                    ->setOriginalPrice($_product->getPrice())
                    ->setRowTotal($rowTotal)
                    ->setBaseRowTotal($rowTotal)
                    ->setCustomerMessage($product['note'])
                    ->setSubstitute($sub)
                    ->setRealOrderId($store_groups_id)
                    ->setIsVirtual(0)
                    ->setIsQtyDecimal(0);
                $subTotal += $rowTotal;
                $order->addItem($orderItem);
            }
            $order->setSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setGrandTotal($subTotal)
                ->setBaseGrandTotal($subTotal);
            $order->setParentOrderId($order_id)
                  ->setSalesFlatStoregroupId($store_groups->getId());
            $order->save();

            $stripe_res = MobileOrder::stripe_pay($token,$subTotal*100);
            if ($stripe_res['status'] == 'succeeded'){
                $order->setStatus("confirmed");
                $history = $order->addStatusHistoryComment('Manually set order to Confirmed.', false);
                $history->setIsCustomerNotified(false);
            }

            return $customer;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function place($user_id,$token,$products,$address_id,$delivery_date,$delivery_range,$tips){
        try{
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }
            $storeId = 3;
            // Start New Sales Order Quote
            $quote = Mage::getModel('sales/quote')->setStoreId($storeId);
            $quote->setCurrency("USD");
            $quote->assignCustomer($customer);

            $products = json_decode($products,true);
            foreach ($products as $productId=>$product)
            {
                $product = Mage::getModel('catalog/product')->load($productId);
                if ($product['substitute'] == "Y"){
                    $sub = 1;
                }else{
                    $sub = 0;
                }
                $quote->addProduct($product, new Varien_Object(array('qty' => $product['qty'],
                    'substitute'=>$sub,'customer_message'=>$product['note'])));
            }


            $address_entity = Mage::getModel('customer/address')->load($address_id);
            $address = array(
                'customer_address_id' => $address_id,
                'prefix' => $address_entity->getPrefix(),
                'firstname' => $address_entity->getFirstname(),
                'middlename' => $address_entity->getMiddlename(),
                'lastname' => $address_entity->getLastname(),
                'suffix' => $address_entity->getSuffix(),
                'company' => $address_entity->getCompany(),
                'street' => $address_entity->getStreet()[0]+$address_entity->getStreet()[1],
                'city' => $address_entity->getCity(),
                'country_id' => $address_entity->getCountryId(),
                'region' => $address_entity->getRegion(),
                'region_id' => $address_entity->getRegionId(),
                'postcode' => $address_entity->getPostcode(),
                'telephone' => $address_entity->getTelephone(),
                'fax' => $address_entity->getFax(),
            );

//            $quote->setShippingAddress($address_entity);
//            $quote->setBillingAddress($address_entity);
            $shippingAddress = $quote->getShippingAddress()->addData($address);
            $billingAddress  = $quote->getBillingAddress()->addData($address);
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('flatrate_flatrate')
//                ->setPaymentMethod('cryozonic_stripe')
                ->setPaymentMethod('checkmo')
                ->save();
            $quote->getPayment()->importData(array('method' => 'checkmo','cc_stripejs_token'=>$token));
            $quote->collectTotals()->save();

            try {
                // Create Order From Quote
                $service = Mage::getModel('sales/service_quote', $quote);
                $service->submitAll();
                $order = $service->getOrder();

                $order_id = $order->getId();
                $store_groups=Mage::getModel('sales/order_storegroup')
                    ->setOrderId($order_id)
                    ->setStoregroupId(5)
                    ->setStoregroupName('Walmart')
                    ->setDate($delivery_date)
                    ->setTimeRange($delivery_range);
                $store_groups->save();

                $order->setParentOrderId($order_id);
                $order->setStoregroupId(5);
                $order->setSalesFlatStoregroupId($store_groups->getId());
                $order->save();
            }
            catch (Exception $ex) {
                echo $ex->getMessage();
            }
            catch (Mage_Core_Exception $e) {
                echo $e->getMessage();
            }
            return $order->getRealOrderId();
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function place2($user_id,$token,$products,$address_id,$delivery_date,$delivery_range,$tips,$brand="",$last4=""){
        try{
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }
            Mage::getSingleton('customer/session')->loginById($customer->getId());
            $storeId = 3;
            // Start New Sales Order Quote
            $address_entity = Mage::getModel('customer/address')->load($address_id);
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->setStoreId($storeId)
                   ->setCurrency("USD")
                   ->assignCustomer($customer);
            $quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($address_entity))
                ->setShippingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($address_entity));
            $cart = Mage::getSingleton('checkout/cart');
            $cart->setQuote($quote);
            $cart->truncate();
            $products = json_decode($products,true);
            foreach ($products as $productId=>$product_setting)
            {
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
                if ($product->getId() == null){
                    continue;
                }
                if ($product_setting['substitute'] == "Y"){
                    $sub = 1;
                }else{
                    $sub = 0;
                }
                try {
                    $quote->addProduct($product, new Varien_Object(array('qty' => $product_setting['qty'],
                        'substitute'=>$sub,'customer_message'=>$product_setting['note'])));
                    $quote->save();
                    $quote_item = $quote->getItemByProduct($product);
                    $quote_item->setQty($product_setting['qty'])
                                ->setSubstitute($sub)
                                ->setCustomerMessage($product_setting['note'])
                                ->save();
                    $quote->addItem($quote_item);
                    $quote->save();
//                    print_r($quote->getItemsCollection()->getFirstItem()->getData());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }
            $quote->setOther($tips);
            $quote->save();
            $quote->getShippingAddress()->collectTotals();
//            print_r($quote->getItemsCollection()->getFirstItem()->getData());
            $checkout = Mage::getSingleton('checkout/type_onepage');
            $checkout->initCheckout();
            $checkout->setQuote($quote);
            $checkout->saveCheckoutMethod('register');
            $checkout->saveShippingMethod('5');
            $checkout->savePayment(array('method' => 'cryozonic_stripe','cc_stripejs_token'=>$token));

            $quote_store_groups = Mage::getModel('sales/quote_storegroup')
                ->getCollection()
                ->addFieldToFilter('quote_id',$quote->getId());
            if ($quote_store_groups->count() > 0){
                foreach($quote_store_groups as $_quote){
                    $_quote -> delete();
                }
            }

            $quote_store_groups=Mage::getModel('sales/quote_storegroup')
                ->setQuoteId($quote->getId())
                ->setStoregroupId(5)
                ->setStoregroupName('Walmart')
                ->setDate($delivery_date)
                ->setTimeRange($delivery_range);
            $quote_store_groups->save();

            $checkout->saveOrder();
            $cart->truncate();
            $cart->save();
            $cart->getItems()->clear()->save();
            Mage::getSingleton('customer/session')->logout();


            return "success";
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

    public static function get_order_list($user_id,$email,$password,$page=0,$page_size=20){
        try {
            $user = MobileUser::login($email,$password);
            if (!isset($user['id']) or $user['id'] != $user_id){
                throw new Exception("Authorization fail");
            }
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
                    'increment_id' => $order->getIncrementId(),
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
//            $conn = db_connect();
//            $sql = "select o.increment_id,oi.item_id,oi.product_id,oi.store_id,oi.product_id,oi.weight,oi.sku,oi.name,oi.qty_ordered,
//                oi.price,cped.value as store_price,oi.base_price,oi.base_original_price,oi.row_total,oi.price_incl_tax,
//                oi.base_price_incl_tax,oi.row_total_incl_tax,oi.base_row_total_incl_tax,
//                if(oi.substitute=1,'Y','N') as substitute,oi.customer_message,oi.item_status,oi.sub_price,oi.sub_volume,
//                oi.tax_percent,oi.tax_amount
//                 from sales_flat_order_item oi,sales_flat_order o,catalog_product_entity_varchar cped,eav_attribute ea
//                where oi.order_id = o.entity_id
//                and oi.product_id = cped.entity_id
//                and cped.attribute_id = ea.attribute_id
//				and attribute_code = 'store_price'
//                and ea.entity_type_id = 4
//                and o.entity_id = $order_id;
//                    ";
//            $res = $conn->query($sql);
//            $order_items = array();
//            while($row = $res->fetch_assoc()){
//                array_push($order_items,$row);
//            }
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter("entity_id", $order_id)
                ->getFirstItem();

            $order_items = Array();
            foreach($orders->getAllItems() as $_item){
                $product = $_item->getProduct();
                $price_change = 0;
                $tax_change = 0;
                if ($_item->getItemStatus() == 'out_of_stock'){
                    $status = "Out of stock";
                    $price_change = -number_format($_item->getPrice(),2);
                    $tax_change = -number_format($_item->getTaxAmount(),2);
                }else if ($_item->getItemStatus() == 'substitute'){
                    $status = "Substitute";
                    $price_change = number_format(-$_item->getPrice() + $_item->getSubPrice() * $_item->getSubVolume(),2);
                    $tax_change = number_format(-$_item->getTaxAmount() + round($_item->getSubPrice() * $_item->getSubVolume() * $_item->getTaxPercent()) / 100,2);
                }else{
                    $status = "Pick up";
                }
                $item = Array(
                    "name"=>$_item->getName(),
                    "upc"=>$_item->getSku(),
                    "qty_ordered"=>$_item->getQtyOrdered(),
                    "volume"=>$product->getQuantity(),
                    "sub_option"=>($_item->getSubstitute() == 0)?"No":"Yes",
                    "note"=>$_item->getCustomerMessage(),
                    "price"=>number_format($_item->getPrice(),2),
                    "sales_tax"=>number_format($_item->getTaxAmount(),2),
                    "tax_percent"=>number_format($_item->getTaxPercent(),2),
                    "status"=>$status,
                    "sub_item"=>($status=='Substitute')?"name:".$_item->getSubName()." ,unit price:".$_item->getSubPrice()." ,quantity:".$_item->getSubVolume():"",
                    "sub_price"=>$_item->getSubPrice(),
                    "sub_quantity"=>$_item->getSubVolume(),
                    "price_adj"=>$price_change,
                    "tax_adj"=>$tax_change,
                    "image"=>'http://www.cartgogogo.com/media/catalog/product'.$product->getImage()
                );
                array_push($order_items,$item);
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

            $storegroups=Mage::getModel('sales/order_storegroup')->getCollection()
                                       ->addFieldtoFilter('order_id',$order_id)->getData();
            if (count($storegroups) == 0){
                throw new Exception("Can't get delivery message");
            }
            $storegroup = $storegroups[0];

            $payment = $orders->getPayment();



            $created_at = strtotime($orders->getCreatedAt());
            $address = $orders->getShippingAddress();
            $order['order_id'] = $orders->getId();
            $order['order_no'] = $orders->getIncrementId();
            $order['status'] = MobileOrder::getHistoryStatus($orders);
            $order['order_date'] = date("m/d/Y", $created_at);
            $order['order_time'] = date("h:i a", $created_at);
            $order['username'] = $orders->getCustomerEmail();
            $order['name'] = $orders->getCustomerFirstname()." ".$orders->getCustomerLastname();
            $order['phone'] = $address->getTelephone();
            $order['delivery_date'] = $storegroup['date'];
            $order['delivery_time'] = $storegroup['time_range'];
            $order['payment'] = $payment->getCcType().' end in '.$payment->getCcLast4();
            $order['delivery_address'] = $address->getStreet();
            $order['delivery_city'] = $address->getCity();
            $order['delivery_state'] = $address->getRegion();
            $order['delivery_zipcode'] = $address->getPostcode();
            $order['delivery_note'] = "";
            $order['subtotal'] = number_format($orders->getData('subtotal'),2);
            $order['discount'] = number_format($orders->getData('discount'),2);
            $order['tips'] = number_format($orders->getTipsAmount(),2);
            $order['tax'] = number_format($orders->getTaxAmount(),2);
            $order['delivery_fee'] = number_format($orders->getShippingAmount(),2);
            $order['total'] = number_format($orders->getGrandTotal(),2);
            $order['subtotal_ofs_adj'] = number_format($out_of_stock,2);
            $order['subtotal_sub_adj'] = number_format($substitute,2);
            $order['tax_adj'] = number_format($tax-$order['tax'],2);
            $order['total_adj'] = number_format($tax - $orders->getData('tax_amount') + $substitute + $out_of_stock ,2);
            $order['subtotal_rev'] = number_format($orders->getData('grand_total') - $orders->getData('tax_amount') + $out_of_stock + $substitute,2);
            $order['tax_rev'] = number_format($tax,2);
            $order['total_rev'] = number_format($order['subtotal_rev']+$order['tax_rev'],2);
            $order['delivered_by'] = "";
            $order['user_rating'] = 5;


//            $order['shipping_amount'] = number_format($orders->getData('shipping_amount'),2);
//            $order['original_tax'] = number_format($orders->getData('tax_amount'),2);
//            $order['out_of_stock'] = number_format($out_of_stock,2);
//            $order['substitute'] = number_format($substitute,2);
//            $order['tax_ofs_adj'] = number_format($tax,2);
//            $order['total'] = number_format($orders->getData('grand_total') - $orders->getData('tax_amount') + $out_of_stock + $substitute + $tax,2);
//            $order['original_grand_total'] = number_format($orders->getData('grand_total'),2);
//            $order['change'] = number_format($tax - $orders->getData('tax_amount') + $substitute + $out_of_stock ,2);

            $order['items'] = $order_items;

            return $order;

        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function getHistoryStatus($order){
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
                $d1 = date("m/d/Y h:i a", $d);
                array_push($res,Array($d1=>$status_name));
//                $res[$d1] = $status_name;
            }
        }

        return $res;
    }

}
