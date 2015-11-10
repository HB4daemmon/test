<?php
class Magentothem_Cattop_Block_Cattop extends Mage_Core_Block_Template
{

    protected $level = 1;
    public $cats = array();

    public function getCatStore()
    {
        $rootcatId= Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootcatId);
        return $categories;
    }

    public function getTopCfg($cfg)
    {
        return Mage::helper('cattop')->getTopCfg($cfg);
    }

    public function getCatRootId()
    {
        return $rootcatId= Mage::app()->getStore()->getRootCategoryId();
    }
    public function getCatListTop()
    {
        $collection = Mage::getModel('catalog/category')
                            ->getCollection()
                            ->addAttributeToSelect('entity_id')
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('thumbnail')
                            ->addAttributeToSelect('cattop_thumb')
                            ->addAttributeToSelect('url_path')
                            ->addFieldToFilter('parent_id', array('eq'=>$this->getCatRootId()))
                            ->addFieldToFilter('cattop', array('eq'=>'1'))
                            ->addFieldToFilter('is_active', array('eq'=>'1'));
        return $collection;
    }
	
	/*
    public function getCatByPath($parentId, $path)
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToSelect('name')
                        ->addAttributeToSelect('url_path')
                        ->addAttributeToFilter('entity_id', array('neq' => $parentId))
                        ->addFieldToFilter('path', array('like' => "$path%"))
                        //->addAttributeToSort('path', 'asc')
                        ->addAttributeToSort('level', 'asc')
                        ->addFieldToFilter('is_active', array('eq'=>'1'))
                        ->addFieldToFilter('cattop', array('eq'=>'1'))
                        //->getSelect()->limit(5)
                        //->load(5) // display SQL
                        ->load();
                        //->toArray();
        return $collection;
    }
	/*
	
    /*********** get & Resized image ***********/

    /*public function getImage($cat)
    {
        return $this->getCatResizedImage($cat, $this->getTopCfg('width_thumbnail'), $this->getTopCfg('height_thumbnail'),100 );
    }

    public function getImageHover($cat)
    {
        return $this->getCatResizedImageHover($cat, $this->getTopCfg('width_thumbnail'), $this->getTopCfg('height_thumbnail'),100 );
    }

    public function getImagePath()
    {
        $imagePath['original']     = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS;
        $imagePath['resized']      = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . "cache" . DS . "cat_resized" . DS;// Because clean Image cache function works in this folder only
        $imagePath['url_original'] = Mage::getBaseUrl ( 'media' ) ."/catalog/category/";
        $imagePath['url_resized']  = Mage::getBaseUrl ( 'media' ) ."/catalog/category/cache/cat_resized/";
        
        return $imagePath; // Directory Images
    }

    function getcatResizedImage($cat ,$width, $height, $quality)
    {
        if (! $cat->getThumbnail()) return false;

        $imageUrl              = $this->getImagePath();
        $imageUrl['original'] .= $cat->getThumbnail();
        $imageUrl['resized']  .= $cat->getThumbnail();
        if(!is_file ($imageUrl['original'])) return false;

        if( file_exists($imageUrl['resized']))
        {
            $imageResizedObj = new Varien_Image ( $imageUrl['resized'] );
            if( $width != $imageResizedObj->getOriginalWidth()
                || $height != $imageResizedObj->getOriginalHeight()
                || filemtime($imageUrl['url']) > filemtime($imageUrl['resized']) )
            {
                $this->ResizedImage($imageUrl['original'], $imageUrl['resized'], $width, $height, $quality);
            }            
        } else {
            if(file_exists($imageUrl['original']))
            {
                $this->ResizedImage($imageUrl['original'], $imageUrl['resized'], $width, $height, $quality);
            }

        }

        if(file_exists($imageUrl['resized'])){
            $imageUrl['url_resized'] .= $cat->getThumbnail();
            return $imageUrl['url_resized'];
        }else{
            $imageUrl['url_original'] .= $cat->getThumbnail();
            return $imageUrl['url_original'];
        }


    }

    function getcatResizedImageHover($cat ,$width, $height, $quality)
    {
        if (! $cat->getCattopThumb()) return false;

        $imageUrl              = $this->getImagePath();
        $imageUrl['original'] .= $cat->getCattopThumb();
        $imageUrl['resized']  .= $cat->getCattopThumb();
        if(!is_file ($imageUrl['original'])) return false;

        if( file_exists($imageUrl['resized']))
        {
            $imageResizedObj = new Varien_Image ( $imageUrl['resized'] );
            if( $width != $imageResizedObj->getOriginalWidth()
                || $height != $imageResizedObj->getOriginalHeight()
                || filemtime($imageUrl['url']) > filemtime($imageUrl['resized']) )
            {
                $this->ResizedImage($imageUrl['original'], $imageUrl['resized'], $width, $height, $quality);
            }            
        } else {
            if(file_exists($imageUrl['original']))
            {
                $this->ResizedImage($imageUrl['original'], $imageUrl['resized'], $width, $height, $quality);
            }

        }

        if(file_exists($imageUrl['resized'])){
            $imageUrl['url_resized'] .= $cat->getCattopThumb();
            return $imageUrl['url_resized'];
        }else{
            $imageUrl['url_original'] .= $cat->getCattopThumb();
            return $imageUrl['url_original'];
        }


    }

    function ResizedImage($imageOriginalUrl, $imageResizedlUrl=null , $width, $height=null , $quality=100)
    {
        if(! is_file ( $imageOriginalUrl )) return false;
        if(!$imageResizedlUrl) $imageResizedlUrl = $imageOriginalUrl;
        $imageObj = new Varien_Image ( $imageOriginalUrl );
        $imageObj->constrainOnly ( true );
        $imageObj->keepAspectRatio ( true );
        $imageObj->keepFrame ( true ); // force Frame
        $imageObj->quality ( $quality );
        $imageObj->keepTransparency(true);  // keep Transparency with image png
        $imageObj->backgroundColor(array(255,255,255));
        $imageObj->resize ( $width, $height );
        $imageObj->save ( $imageResizedlUrl );        
    }*/

    /*********** End get & Resized image ***********/

}