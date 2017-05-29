<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');
require_once(dirname(__FILE__) . '/utils.class.php');

class MobileCategory{
    public static function getCategoryList($type="default"){
        try {
            $_helper = Mage::helper('catalog/category');
            $_categories = $_helper->getStoreCategories();
            $_sub_cates = array();
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
                            array_push($_sub_cates,$_sub);
                        }
                    }
                    $_cate['name'] = $_category->getName();
                    $_cate['id'] = $_category->getId();
                    $_cate['subcategories'] = $sub;
                    array_push($cate,$_cate);
                }
            }
            if($type == 'default'){
                return $cate;
            }else if ($type == 'sub'){
                return $_sub_cates;
            }else{
                return null;
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function queryCategory($cate_id){
        try {
            $res = array();
            $_category = Mage::getModel('catalog/category')->load($cate_id);
            $res['name'] = $_category->getName();
            $res['id'] = $_category->getId();
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
            $res['subcategories'] = $sub;
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function getPage(){
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
                $res['most_popular'] = [];
                $category_id = 0;
                foreach($cat_ids as $c){
                    $r = MobileCategory::getProducts($c,0,$res['products_limit']);
                    $category_id = $r['parent_id'];
                    if (strtolower($r['name']) == "most popular"){
                        $res['most_popular'] = $r;
                        break;
                    }
                }
                $res['category_id'] = $category_id;
                $result[] = $res;
            }
            return $result;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
//            $category['getSize'] = $_category->getProductCollection()->getSize();
//            $category['getLastPageNumber'] = $_category->getProductCollection()->getLastPageNumber();
//            $category['getPageSize'] = $_category->getProductCollection()->getPageSize();
//            $category['getCurPage'] = $_category->getProductCollection()->getCurPage();
            $category['count'] = $_category->getProductCollection()->count();

//            $category['all_products'] = $_category->getProductCollection()->getData();

            $products_collection = $_category->getProductCollection()->setPage($page,$page_size);
            $category['page'] = $products_collection->getCurPage();
            $category['page_size'] = $products_collection->getPageSize();
            $category['last_page_number'] = $products_collection->getLastPageNumber();
            foreach ($products_collection as $p){
                $product = MobileCategory::getProduct($p->getId());
                $category['products'][] = $product;
            }

            return $category;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
            throw new Exception($e->getMessage());
        }
    }

    public static function queryProduct($query_text,$page,$page_size){
        try {
            $page = intval($page);
            $page_size = intval($page_size);

            $query = Mage::getModel('catalogsearch/query')->setQueryText($query_text)->prepare();
            Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
                Mage::getModel('catalogsearch/fulltext'),
                $query_text,
                $query
            );

            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->getSelect()->joinInner(
                array('search_result' => $collection->getTable('catalogsearch/result')),
                $collection->getConnection()->quoteInto(
                    'search_result.product_id=e.entity_id AND search_result.query_id=?',
                    $query->getId()
                ),
                array('relevance' => 'relevance')
            );
            $productIds = $collection->getAllIds();
            $length = count($productIds);
            $pager = MobileUtils::count_page($length,$page,$page_size);
            if($pager['skip'] <= $length){
                $skip = $pager['skip'];
            }else{
                $skip = $length;
            }

            if($pager['skip'] + $page_size <= $length){
                $end = $pager['skip']+ $page_size;
            }else{
                $end = $length;
            }

            $data = array();
            if($skip != $end){
                for($i=$skip;$i<$end;$i++){
                    $id = $productIds[$i];
                    $product = MobileCategory::getProduct($id);
                    array_push($data,$product);
                }
            }
            $products = array("pager"=>$pager,"data"=>$data);
            return $products;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

//$mc = new MobileCategory();
//print_r($mc->getPage());