<?php
function nasa_wpb_retail() {
    $imgs_1 = elessi_import_upload('/wp-content/uploads/2019/10/retail-banner-top.jpg', '0', array(
        'post_title' => 'retail-banner-top',
        'post_name' => 'retail-banner-top',
    ));
    $imgs_1_src = $imgs_1 ? wp_get_attachment_image_url($imgs_1, 'full') : 'https://dummyimage.com/1920x497';
    
    $imgs_2 = elessi_import_upload('/wp-content/uploads/2019/10/banner-1-handtools.jpg', '3136', array(
        'post_title' => 'banner-1-handtools',
        'post_name' => 'banner-1-handtools',
    ));
    
    $imgs_3 = elessi_import_upload('/wp-content/uploads/2019/10/banner-2-handtools.jpg', '3136', array(
        'post_title' => 'banner-2-handtools',
        'post_name' => 'banner-2-handtools',
    ));
    
    $imgs_4 = elessi_import_upload('/wp-content/uploads/2019/10/banner-3-handtools.jpg', '3136', array(
        'post_title' => 'banner-3-handtools',
        'post_name' => 'banner-3-handtools',
    ));
    
    return array(
        'post' => array(
            'post_title' => 'WPB Retail',
            'post_name' => 'wpb-retail',
            'post_content' => '[vc_row el_class="padding-top-60 padding-bottom-60" css=".vc_custom_1571198177116{border-top-width: 1px !important;border-bottom-width: 1px !important;background-image: url(' . $imgs_1_src . ') !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;border-top-color: #eeeeee !important;border-top-style: solid !important;border-bottom-color: #eeeeee !important;border-bottom-style: solid !important;}"][vc_column][nasa_title title_text="Top Categories" title_desc="Lorem Ipsum has been the industry’s standard dummy text ever since the 1500s" title_type="h3" font_size="l" title_align="text-center" el_class="margin-bottom-20"][nasa_product_categories list_cats="grooming-men, shoes-men, clothing-men" disp_type="grid" hide_empty="0" columns_number="3" columns_number_small="1" columns_number_tablet="3"][/vc_column][/vc_row][vc_row el_class="margin-top-30"][vc_column width="1/4" offset="vc_col-xs-6"][nasa_service_box service_style="style-3" service_hover="buzz_effect" service_title="Free Shipping" service_icon="pe-7s-plane" service_desc="Free Shipping for all US order"][/vc_column][vc_column width="1/4" offset="vc_col-xs-6"][nasa_service_box service_style="style-3" service_hover="buzz_effect" service_title="Support 24/7" service_icon="pe-7s-headphones" service_desc="We support 24h a day"][/vc_column][vc_column width="1/4" offset="vc_col-xs-6"][nasa_service_box service_style="style-3" service_hover="buzz_effect" service_title="100% Money Back" service_icon="pe-7s-refresh-2" service_desc="You have 30 days to Return"][/vc_column][vc_column width="1/4" offset="vc_col-xs-6"][nasa_service_box service_style="style-3" service_hover="buzz_effect" service_title="Payment Secure" service_icon="pe-7s-gift" service_desc="We ensure secure payment"][/vc_column][/vc_row][vc_row el_class="margin-top-50"][vc_column width="5/12" el_class="desktop-padding-right-20"][vc_row_inner][vc_column_inner css=".vc_custom_1571051923677{border-top-width: 2px !important;border-right-width: 2px !important;border-bottom-width: 2px !important;border-left-width: 2px !important;padding-top: 20px !important;padding-right: 30px !important;padding-bottom: 5px !important;padding-left: 30px !important;border-left-color: #d69200 !important;border-left-style: solid !important;border-right-color: #d69200 !important;border-right-style: solid !important;border-top-color: #d69200 !important;border-top-style: solid !important;border-bottom-color: #d69200 !important;border-bottom-style: solid !important;}"][nasa_products_special_deal limit="2" columns_number="1" columns_number_small="1" columns_number_tablet="1" arrows="1" auto_slide="false" title="Special"][/vc_column_inner][/vc_row_inner][/vc_column][vc_column width="7/12" el_class="mobile-margin-top-30"][nasa_products title_shortcode="Digital" style="carousel" style_row="2" shop_url="0" arrows="1" columns_number="3" columns_number_small="2" columns_number_tablet="2"][/vc_column][/vc_row][vc_row css=".vc_custom_1571067311028{background-color: #d69200 !important;background-position: 0 0 !important;background-repeat: repeat !important;}" el_class="mobile-margin-bottom-60 desktop-margin-top-40 desktop-margin-bottom-80 padding-top-15 padding-bottom-15"][vc_column][nasa_slider bullets="false" navigation="false" autoplay="true"][vc_column_text]
    <p class="text-center" style="color: #fff;"><strong>UP TO 70% OFF THE ENTRIRE STORE! - MADE WITH LOVE by <span class="nasa-underline">Nasa studio</span></strong></p>
    [/vc_column_text][vc_column_text]
    <p class="text-center" style="color: #fff;"><strong>UP TO 70% OFF THE ENTRIRE STORE! - MADE WITH LOVE by <span class="nasa-underline">Nasa studio</span></strong></p>
    [/vc_column_text][/nasa_slider][/vc_column][/vc_row][vc_row el_class="margin-top-50"][vc_column width="7/12"][nasa_products title_shortcode="Furniture" style="carousel" style_row="2" shop_url="0" arrows="1" columns_number="3" columns_number_small="2" columns_number_tablet="2"][/vc_column][vc_column width="5/12" el_class="desktop-padding-left-20"][vc_row_inner][vc_column_inner css=".vc_custom_1571051936139{border-top-width: 2px !important;border-right-width: 2px !important;border-bottom-width: 2px !important;border-left-width: 2px !important;padding-top: 20px !important;padding-right: 30px !important;padding-bottom: 5px !important;padding-left: 30px !important;border-left-color: #d69200 !important;border-left-style: solid !important;border-right-color: #d69200 !important;border-right-style: solid !important;border-top-color: #d69200 !important;border-top-style: solid !important;border-bottom-color: #d69200 !important;border-bottom-style: solid !important;}"][nasa_products_special_deal limit="2" columns_number="1" columns_number_small="1" columns_number_tablet="1" arrows="1" auto_slide="false" title="Special"][/vc_column_inner][/vc_row_inner][/vc_column][/vc_row][vc_row el_class="margin-top-60"][vc_column width="1/3"][nasa_banner move_x="5%" valign="bottom" hover="zoom" img_src="' . $imgs_2 . '"]
    <h5 class="primary-color">New Series</h5>
    <h4>Power Tools</h4>
    [/nasa_banner][/vc_column][vc_column width="1/3"][nasa_banner move_x="5%" valign="middle" effect_text="fadeInLeft" hover="zoom" img_src="' . $imgs_3 . '"]
    <h5 style="color: #666;">Special Sale</h5>
    <h4><span class="primary-color">25%</span> OFF</h4>
    [/nasa_banner][/vc_column][vc_column width="1/3"][nasa_banner move_x="5%" effect_text="fadeInLeft" hover="zoom" img_src="' . $imgs_4 . '"]
    <h4>Power Tools</h4>
    <h5 class="primary-color">&amp; Accessories</h5>
    [/nasa_banner][/vc_column][/vc_row][vc_row el_class="margin-top-40"][vc_column][nasa_title title_text="Handtools" title_desc="Lorem Ipsum has been the industry’s standard dummy text ever since the 1500s" title_type="h3" font_size="xl" title_align="text-center" el_class="margin-bottom-20"][nasa_products style="carousel" shop_url="0" arrows="0" number="5" columns_number="4" columns_number_small="2" columns_number_tablet="3"][/vc_column][/vc_row][vc_row el_class="margin-top-50 padding-bottom-80"][vc_column width="1/2" el_class="rtl-right"][vc_video link="https://www.youtube.com/watch?v=DmFtQrnBSe0"][/vc_column][vc_column width="1/2" el_class="desktop-padding-left-40 rtl-right rtl-desktop-padding-left-0 rtl-desktop-padding-right-40"][vc_row_inner el_class="mobile-margin-top-35"][vc_column_inner][nasa_title title_text="About Us" title_type="h3" font_size="xl"][/vc_column_inner][/vc_row_inner][vc_row_inner][vc_column_inner][vc_column_text]

    Our mission is to bring together a diverse, curated collection of beautiful furniture and homewares from around the world. I was popularised in the 1960s with the release.

    [/vc_column_text][/vc_column_inner][/vc_row_inner][vc_row_inner el_class="margin-top-35"][vc_column_inner width="1/2"][vc_single_image image="2914" el_class="left rtl-right margin-right-20 rtl-margin-left-20 rtl-margin-right-0 margin-top-5"][nasa_title title_text="We work in Global" title_type="h5" title_desc="Lorem ipsum" el_class="nasa-clear-none"][/vc_column_inner][vc_column_inner el_class="mobile-margin-top-35" width="1/2"][vc_single_image image="2915" el_class="left rtl-right margin-right-20 rtl-margin-left-20 rtl-margin-right-0 margin-top-5"][nasa_title title_text="Our guarantee" title_type="h5" title_desc="From 2 - 5 years" el_class="nasa-clear-none"][/vc_column_inner][/vc_row_inner][vc_row_inner el_class="margin-top-35"][vc_column_inner width="1/2"][vc_single_image image="2916" el_class="left rtl-right margin-right-20 rtl-margin-left-20 rtl-margin-right-0 margin-top-10"][nasa_title title_text="On the market" title_type="h5" title_desc="12 years" el_class="nasa-clear-none"][/vc_column_inner][vc_column_inner el_class="mobile-margin-top-35" width="1/2"][vc_single_image image="2917" el_class="left rtl-right margin-right-20 rtl-margin-left-20 rtl-margin-right-0"][nasa_title title_text="Best quality" title_type="h5" title_desc="Lorem ipsum" el_class="nasa-clear-none"][/vc_column_inner][/vc_row_inner][/vc_column][/vc_row]'
        ),
        
        'post_meta' => array(
            // '_nasa_pri_color_flag' => 'on',
            // '_nasa_pri_color' => '#d69200',
            '_wpb_shortcodes_custom_css' => '.vc_custom_1571198177116{border-top-width: 1px !important;border-bottom-width: 1px !important;background-image: url(' . $imgs_1_src . ') !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;border-top-color: #eeeeee !important;border-top-style: solid !important;border-bottom-color: #eeeeee !important;border-bottom-style: solid !important;}.vc_custom_1571067311028{background-color: #d69200 !important;background-position: 0 0 !important;background-repeat: repeat !important;}.vc_custom_1571051923677{border-top-width: 2px !important;border-right-width: 2px !important;border-bottom-width: 2px !important;border-left-width: 2px !important;padding-top: 20px !important;padding-right: 30px !important;padding-bottom: 5px !important;padding-left: 30px !important;border-left-color: #d69200 !important;border-left-style: solid !important;border-right-color: #d69200 !important;border-right-style: solid !important;border-top-color: #d69200 !important;border-top-style: solid !important;border-bottom-color: #d69200 !important;border-bottom-style: solid !important;}.vc_custom_1571051936139{border-top-width: 2px !important;border-right-width: 2px !important;border-bottom-width: 2px !important;border-left-width: 2px !important;padding-top: 20px !important;padding-right: 30px !important;padding-bottom: 5px !important;padding-left: 30px !important;border-left-color: #d69200 !important;border-left-style: solid !important;border-right-color: #d69200 !important;border-right-style: solid !important;border-top-color: #d69200 !important;border-top-style: solid !important;border-bottom-color: #d69200 !important;border-bottom-style: solid !important;}'
        ),
        
        'globals' => array(
            'header-type' => '1',
            
            // 'fixed_nav' => '0',
            
            // 'plus_wide_width' => '400',
            'color_primary' => '#d69200',
            
            // 'bg_color_topbar' => '28aeb1',
            // 'text_color_topbar' => '#ffffff',
            
            // 'fullwidth_main_menu' => '1',
            
            // 'bg_color_main_menu' => '#e4272c',
            // 'text_color_main_menu' => '#ffffff',
            
            // 'v_root' => '1',
            // 'v_root_limit' => '10',
            
            // 'bg_color_v_menu' => '#000000',
            // 'text_color_v_menu' => '#ffffff',
            
            'footer_mode' => 'builder',
            'footer-type' => 'footer-light-2',
            'footer-mobile' => 'footer-mobile',
            
            // 'category_sidebar' => 'left-classic',
            
            'product_detail_layout' => 'modern-1',
            // 'product_image_layout' => 'single',
            // 'product_image_style' => 'slide',
            // 'sp_bgl' => '#f6f6f6',
            'tab_style_info' => 'scroll-down',
            
            // 'single_product_thumbs_style' => 'hoz',
            
            // 'loop_layout_buttons' => 'modern-5',
            
            // 'animated_products' => 'hover-carousel',
            
            // 'nasa_attr_image_style' => 'square',
            // 'nasa_attr_image_single_style' => 'extends',
            // 'nasa_attr_color_style' => 'round',
            // 'nasa_attr_label_style' => 'small-square-1',
            
            'breadcrumb_row' => 'single',
            'breadcrumb_type' => 'default',
            'breadcrumb_bg_color' => '#f8f8f8',
            'breadcrumb_align' => 'text-left',
            'breadcrumb_height' => '60',
        ),
    );
}
