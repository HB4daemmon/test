<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');

class MobileCategory{
    public function getAll(){
        try {
            $_helper = Mage::helper('catalog/category');
            $_categories = $_helper->getStoreCategories();
            $cate = array();
            if (count($_categories) > 0){
                foreach($_categories as $_category){
                    $_cate = array();
                    $_category = Mage::getModel('catalog/category')->load($_category->getId());
                    $_subcategories = $_category->getChildrenCategories();
                    $sub = array();
                    if (count($_subcategories) > 0){
                        foreach($_subcategories as $_subcategory){
                            $_sub = array();
                            $_sub['name'] = $_subcategory->getName();
                            $_sub['id'] = $_subcategory->getId();
                            array_push($sub,$_sub);
                        }
                    }
                    $_cate['name'] = $_category->getName();
                    $_cate['id'] = $_category->getId();
                    $_cate['subcategories'] = $sub;
                    array_push($cate,$_cate);
                }
            }
            return $cate;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getPage(){
        try {
            $cms_page = Mage::getModel('cms/page')
                ->getCollection()
                ->addFieldToFilter("identifier",'walmart')
                ->getFirstItem()
                ->getData();
            $content = $cms_page['content'];
            $categories = explode("{{widget type=\"categorytabs/advanced\"",$content);
            $result = [];
            for ($i=1;$i<count($categories);$i++){
                $category = $categories[$i];
                $res = [];
                $res['title'] = explode("\"",explode("title=\"",$category)[1])[0];
                $res['identify'] = explode("\"",explode("identify=\"",$category)[1])[0];
                $res['products_count'] = explode("\"",explode("products_count=\"",$category)[1])[0];
                $res['products_limit'] = explode("\"",explode("products_limit=\"",$category)[1])[0];
                $cat_ids = explode(',',explode("\"",explode("cat_ids=\"",$category)[1])[0]);
                foreach($cat_ids as $c){
                    $res['categories'][] = MobileCategory::getProducts($c,0,$res['products_limit']);
                }
                $result[] = $res;
            }
            return $result;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getProducts($category_id,$page=0,$page_size=20){
        try {
            $_category = Mage::getModel('catalog/category')->load($category_id);
            if($_category->getId() == null){
                throw new Exception("Category doesn't exist");
            }
            $category = [];
            $category['category_id'] = $_category->getId();
            $category['parent_id'] = $_category->getParentId();
            $category['created_at'] = $_category->getCreatedAt();
            $category['path'] = $_category->getPath();
            $category['name'] = $_category->getName();
            $category['url_key'] = $_category->getUrlKey();
            $category['display_mode'] = $_category->getDisplayMode();
            $category['url_path'] = $_category->getUrlPath();

            $products_collection = $_category->getProductCollection()->setPage($page,$page_size);
            foreach ($products_collection as $p){
                $product = MobileCategory::getProduct($p->getId());
                $category['products'][] = $product;
            }

            return $category;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getProduct($product_id){
        try {
            $product_model = Mage::getModel('catalog/product');
            $_product = $product_model->load($product_id);
            $product = [];
            $product['product_id'] = $_product->getId();
            $product['sku'] = $_product->getSku();
            $product['created_at'] = $_product->getCreatedAt();
            $product['updated_at'] = $_product->getUpdateAt();
            $product['name'] = $_product->getName();
            $product['meta_title'] = $_product->getMetaTitle();
            $product['meta_description'] = $_product->getMetaDescription();
            $product['image'] = $_product->getImage();
            $product['small_image'] = $_product->getSmallImage();
            $product['thumbnail'] = $_product->getThumbnail();
            $product['url_key'] = $_product->getUrlKey();
            $product['url_path'] = $_product->getUrlPath();
            $product['quantity'] = $_product->getQuantity();
            $product['price'] = $_product->getPrice();
            $product['special_price'] = $_product->getSpecialPrice();
            $product['weight'] = $_product->getWeight();
            $product['shop_price'] = $_product->getShopPrice();
            $product['description'] = $_product->getDescription();
            $product['short_description'] = $_product->getShortDescription();
            $product['special_from_date'] = $_product->getSpecialFromDate();
            $product['special_to_date'] = $_product->getSpecialToDate();
            return $product;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

$mc = new MobileCategory();
print_r($mc->getPage());