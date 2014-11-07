<?php
    header('Content-type: text/css; charset: UTF-8');
    header('Cache-Control: must-revalidate');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    $url = $_REQUEST['url'];
?>


.header .form-search, .top-cart-wrapper, .drop-currency .sub-currency, .drop-lang .sub-lang, .drop-currency .sub-currency a, .drop-lang .sub-lang a, .top-cart-content, .bx-wrapper .bx-controls a, .ma-brand-slider-contain .bx-wrapper .item a, .product-view .product-img-box .more-views li a, .product-options dd select, .add-to-cart .qty, .cart .discount input.input-text, .cart .shipping .shipping-form .form-list .input-box select, .cart .shipping .shipping-form .form-list .input-box input, .link a, .cattop .cat_level_top a img, .item-container .item-inner:hover .add-to-links li a {
    border-radius: 2px;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
}

button.button span, .block-tags .block-content .tags-list a, .block-tags .actions a, .tab_container .item, .banner-static-home2 .banner-content, .onsale-products a.product-image, .block-layered-nav #search input, .products-list li.item .images-container, .product-view .product-img-box .product-image, .product-view .product-shop .availability span, .product-view .box-tags .form-add input.input-text, .opc li.section, .opc .step-title .number, .multiple-checkout .col2-set, .multiple-checkout .col3-set, .wrapper_box p.info, .wrapper_box #shopping_cart, .wrapper_box #continue_shopping {
    border-radius: 3px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
}

.pt_vmegamenu, .col-left .block, .ma-onsale-products, .ma-relatedslider-container {
    border-top-left-radius: 4px;
    -webkit-border-top-left-radius: 4px;
    -moz-border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
    -webkit-border-bottom-left-radius: 4px;
    -moz-border-bottom-left-radius: 4px;
}
.item-container, .product-collateral .product-tabs-container, .box-account, .fieldset, .cart .crosssell, .cart .discount, .cart .shipping, .cart .totals, .cart .crosssell li.item .item-inner, .wrapper_box {
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
}

#catslider li, #catslider li div.catslider-item, .account-login .content, .block-progress dt.complete {
    border-top-left-radius: 4px;
    -webkit-border-top-left-radius: 4px;
    -moz-border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    -webkit-border-top-right-radius: 4px;
    -moz-border-top-right-radius: 4px;
}

.account-login .buttons-set, .block-progress dd.complete{
    border-bottom-left-radius: 4px;
    -webkit-border-bootom-left-radius: 4px;
    -moz-border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
    -webkit-border-bottom-right-radius: 4px;
    -moz-border-bottom-right-radius: 4px;
}

.ma-demo-wrap, .ma-demo-wrap .control.active {
    border-radius: 0 4px 4px 0;
    -webkit-border-radius: 0 4px 4px 0;
    -moz-border-radius: 0 4px 4px 0;
}
.footer-static .f-col3 li span{
    border-radius: 20px;
    -webkit-border-radius: 20px;
    -moz-border-radius: 20px;
}
.banner-home-content .banner-box img, .banner-home-content-2 .banner-box img, .item-container .item-inner .des-inbox, .item-container .item-inner .item-inner-top, .item-container .item-inner .add-to-links li a {
    transition: all 0.3s ease-out;
    -webkit-transition: all 0.3s ease-out;
    -moz-transition: all 0.3s ease-out;
}
.banner-home-content .banner-box:hover img, .banner-home-content-2 .banner-box:hover img {
    opacity: 0.8;
    -webkit-opacity: 0.8;
    -moz-opacity: 0.8;
    
}

.select-sort-by:before, .select-limiter:before {
    width: 22px;
    height: 22px;
    background: #fff url(../images/select_lang.png) no-repeat scroll 50% 50%;
    content: "";
    display: block;
    position: absolute;
    top: 5px;
    right: 1px;
    pointer-events: none;
}


.header .form-search .select-search:before {
    background: url("../images/select_lang.png") no-repeat scroll 50% 50% #F5F5F5;
    content: "";
    display: block;
    height: 44px;
    pointer-events: none;
    position: absolute;
    right: 0;
    top: 0px;
    width: 22px;
}

.product-options dd .input-box:before, .cart .shipping .shipping-form .form-list .input-box:before {
    width: 29px;
    height: 29px;
    background: #efefef url(../images/select_lang.png) no-repeat scroll 50% 50%;
    content: "";
    display: block;
    position: absolute;
    top: 3px;
    right: 3px;
    pointer-events: none;
    
    border-radius: 2px;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
}

#back-top {
    border-radius: 40px;
}


