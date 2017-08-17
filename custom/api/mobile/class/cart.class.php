<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.class.php');
require_once(dirname(__FILE__) . '/../../../vendor/stripe-php/init.php');
require_once(dirname(__FILE__) . '/../../../util/connection.php');
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);

class MobileCart{
    public static function get_cart($user_id){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }
            Mage::getSingleton('customer/session')->loginById($customer->getId());
            $quote = Mage::getSingleton('checkout/session')->getQuote();
//            print_r($quote->getId());
            $_items = $quote->getAllItems();
            $cart = Array();
            $items = Array();
            $number = 0;
            foreach($_items as $_item){
                $product = $_item->getProduct();
                $item = Array(
                    "cart_id"=>$quote->getId(),
                    "product_id"=>$product->getId(),
                    "name"=>$_item->getName(),
                    "upc"=>$_item->getSku(),
                    "qty"=>$_item->getQty(),
                    "volume"=>$product->getQuantity(),
                    "sub_option"=>($_item->getSubstitute() == 0)?"No":"Yes",
                    "note"=>$_item->getCustomerMessage(),
                    "price"=>number_format($_item->getPrice(),2),
                    "sales_tax"=>number_format($_item->getTaxAmount(),2),
                    "tax_percent"=>number_format($_item->getTaxPercent(),2),
                    "image"=>'http://www.cartgogogo.com/media/catalog/product'.$product->getSmallImage(),
                    "quantity" => $product->getQuantity(),
                    "substitute"=>$_item->getSubstitute(),
                );
                array_push($items,$item);
                $number += $_item->getQty();
            }

            $cart['items'] = $items;
            $cart['subtotal'] = number_format($quote->getSubtotal(),2);
            $cart['number'] = $number;
            Mage::getSingleton('customer/session')->logout();
            return $cart;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function update_cart($user_id,$products){
        try {
            MobileCart::clear_cart($user_id);
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }
            $storeId = 3;
            Mage::getSingleton('customer/session')->loginById($customer->getId());
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->setCustomerId($user_id);
            $addressid = $customer->getDefaultShipping();
            $address = Mage::getModel('customer/address')->load($addressid);
            if ($address){
                $quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($address))
                    ->setShippingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($address));
            }
            $products = json_decode($products,true);
            if (count($products) == 0){
                MobileCart::clear_cart($user_id);
            }else{
                foreach ($products as $product_setting)
                {
                    $productId = $product_setting['product_id'];
                    $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
                    if ($product->getId() == null){
                        continue;
                    }

                    if (isset($product_setting['sub_option'])){
                        if ($product_setting['sub_option'] == 'Yes'){
                            $substitute = 0;
                        }else{
                            $substitute = 1;
                        }
                    }else{
                        $substitute = $product_setting['substitute'];
                    }

                    try {
                        if ($product_setting['qty'] > 0){
                            $quote->addProduct($product, new Varien_Object(array('qty' => $product_setting['qty'])));
                            $quote->save();
                            $quote_item = $quote->getItemByProduct($product);
                            $quote_item->setQty($product_setting['qty'])
                                ->setSubstitute($substitute)
                                ->setCustomerMessage($product_setting['note'])
                                ->save();
                            $quote->addItem($quote_item);
                            $quote->save();
                        }
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }
                }
            }
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            Mage::getSingleton('customer/session')->logout();
            return MobileCart::get_cart($user_id);
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function clear_cart($user_id){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }
            $storeId = 3;
            Mage::getSingleton('customer/session')->loginById($customer->getId());
            $quote = Mage::getSingleton('checkout/session')->getQuote();
//            $quote->delete();
            $quote->removeAllItems();
            $quote->setSubtotal(0);
            $quote->setBaseSubtotal(0);

            $quote->setSubtotalWithDiscount(0);
            $quote->setBaseSubtotalWithDiscount(0);

            $quote->setGrandTotal(0);
            $quote->setBaseGrandTotal(0);
            $quote->save();
//            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();

            Mage::getSingleton('customer/session')->logout();
            return "success";
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
