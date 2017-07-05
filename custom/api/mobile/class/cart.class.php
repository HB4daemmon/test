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
            foreach($_items as $_item){
                $product = $_item->getProduct();

                $item = Array(
                    "name"=>$_item->getName(),
                    "upc"=>$_item->getSku(),
                    "qty"=>$_item->getQty(),
                    "volume"=>$product->getQuantity(),
                    "sub_option"=>($_item->getSubstitute() == 0)?"No":"Yes",
                    "note"=>$_item->getCustomerMessage(),
                    "price"=>number_format($_item->getPrice(),2),
//                    "sales_tax"=>number_format($_item->getTaxAmount(),2),
//                    "tax_percent"=>number_format($product->getTaxPercent(),2),
                    "image"=>'http://www.cartgogogo.com/media/catalog/product'.$product->getThumbnail()
                );
                array_push($items,$item);
            }

            $cart['items'] = $items;
            $cart['subtotal'] = number_format($quote->getSubtotal(),2);
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
            $products = json_decode($products,true);
            foreach ($products as $productId=>$product_setting)
            {
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
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
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }
            $quote->collectTotals()->save();
//            print_r($quote->getId());
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
            $quote->delete();
            Mage::getSingleton('customer/session')->logout();
            return "success";
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
