<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/user.class.php');
require_once(dirname(__FILE__) . '/utils.class.php');

class MobileAddress{
    public static function create($user_id,$first_name,$last_name,$street,$postcode,$city,$telephone){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

            $address = Mage::getModel("customer/address");
            $address->setCustomerId($customer->getId());
            $address->setFirstname($first_name);
            $address->setLastname($last_name);
            $address->setCountryId("US");
            $address->setStreet($street);
            $address->setPostcode($postcode);
            $address->setCity($city);
            $address->setTelephone($telephone);

            $address->save();
            return 'success';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function get($user_id){
        try {
            $addresses = [];
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

            foreach ($customer->getAddresses() as $address)
            {
                $addr = [];
                $addr['id'] = $address->getId();
                $addr['first_name'] = $address->getFirstname();
                $addr['last_name'] = $address->getLastname();
                $addr['country_id'] = $address->getCountryId();
                $addr['street'] = $address->getStreet();
                $addr['postcode'] = $address->getPostcode();
                $addr['city'] = $address->getCity();
                $addr['telephone'] = $address->getTelephone();
                $addresses[] = $addr;
            }

            return $addresses;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function delete($id){
        try {
            $address = Mage::getModel('customer/address')->load($id);
            $_id = $address->getId();
            if ($_id == null){
                throw new Exception("Address doesn't exist");
            }
            $address->delete();

            return 'success';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function edit($id,$first_name,$last_name,$street,$postcode,$city,$telephone){
        try {
            $address = Mage::getModel('customer/address')->load($id);
            $_id = $address->getId();
            if ($_id == null){
                throw new Exception("Address doesn't exist");
            }

            $address->setFirstname($first_name);
            $address->setLastname($last_name);
            $address->setStreet($street);
            $address->setPostcode($postcode);
            $address->setCity($city);
            $address->setTelephone($telephone);

            $address->save();
            return 'success';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function getRegionCollection($countryCode)
    {
        $regionCollection = Mage::getModel('directory/region_api')->items($countryCode);
        return $regionCollection;
    }

    public static function getAddressConfig($method){
        if($method == 'city'){
            return MobileUtils::getCityList();
        }else if ($method == 'state'){
            return MobileUtils::getState();
        }else if ($method == 'zipcode'){
            return MobileUtils::getZipCode();
        }
    }

    public static function getDefaultAddress($user_id){
        try {
            $customer = Mage::getModel("customer/customer")->load($user_id);
            if ($customer->getId() == null){
                throw new Exception("User is not existed");
            }

            $addressid = $customer->getDefaultShipping();
            $address = Mage::getModel('customer/address')->load($addressid);
            $addr = [];
            if ($address->getId() != null){
                $addr['id'] = $address->getId();
                $addr['first_name'] = $address->getFirstname();
                $addr['last_name'] = $address->getLastname();
                $addr['country_id'] = $address->getCountryId();
                $addr['street'] = $address->getStreet();
                $addr['postcode'] = $address->getPostcode();
                $addr['city'] = $address->getCity();
                $addr['telephone'] = $address->getTelephone();
            }else{
                $addr = "";
            }
            return $addr;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

//$mc = new MobileAddress();
//print_r($mc->delete(8));
//print_r($mc->edit(24,'daemon','wang','Ningshuangload','1111','Beijing','1234321'));
//print_r($mc->get());
//print_r($mc->getRegionCollection('US'));