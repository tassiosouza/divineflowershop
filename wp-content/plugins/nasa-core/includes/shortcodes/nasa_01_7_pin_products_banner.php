<?php
/**
 * Shortcode [nasa_pin_products_banner ...]
 * 
 * @global type $nasa_pin_sc
 * @global type $nasa_opt
 * @param type $atts
 * @param type $content
 * @return string
 */
function nasa_sc_pin_products_banner($atts = array(), $content = null) {
    global $nasa_opt, $nasa_pin_sc;
    
    if (!isset($nasa_pin_sc) || !$nasa_pin_sc) {
        $nasa_pin_sc = 1;
    }
    $GLOBALS['nasa_pin_sc'] = $nasa_pin_sc + 1;
    
    if (!NASA_WOO_ACTIVED) {
        return '';
    }
    
    $dfAttr = array(
        'pin_slug' => '',
        'marker_style' => 'price',
        'slide_pin_product' => 'no',
        'slide_pin_product_reverse' => 'no',
        'full_price_icon' => 'no',
        'price_rounding' => 'yes',
        'show_img' => 'no',
        'show_price' => 'no',
        'pin_effect' => 'default',
        'bg_icon' => '',
        'txt_color' => '',
        'border_icon' => '',
        'el_class' => '',
        'pin_desc' => '',
        'ovr_pin_name' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));

    if ($pin_slug === '') {
        return '';
    }
    
    $post_pin_product_banner = get_posts(array(
        'name'              => $pin_slug,
        'post_status'       => 'publish',
        'post_type'         => 'nasa_pin_pb',
        'numberposts'       => 1
    ));
    
    if (!$post_pin_product_banner) {
        return '';
    }
    
    /**
     * Pin Banner
     */
    wp_enqueue_script('jquery-easing', NASA_CORE_PLUGIN_URL . 'assets/js/min/jquery.easing.min.js', array('jquery'), null, true);
    wp_enqueue_script('jquery-easypin', NASA_CORE_PLUGIN_URL . 'assets/js/min/jquery.easypin.min.js', array('jquery'), null, true);
    
    $pin = $post_pin_product_banner[0];
    
    // $content = '';
    $product_slide = '';
    // Get current image.
    $attachment_id = get_post_meta($pin->ID, 'nasa_pin_pb_image_url', true);
    if ($attachment_id) {
        // Get image source.
        $image_src = wp_get_attachment_url($attachment_id);
        
        if (!$image_src) {
            return '';
        }
        
        $pin_rand_id = 'nasa_pin_' . $nasa_pin_sc;
        
        $data = array(
            $pin_rand_id => array()
        );
        
        $_width = get_post_meta($pin->ID, 'nasa_pin_pb_image_width', true);
        $_height = get_post_meta($pin->ID, 'nasa_pin_pb_image_height', true);
        $_options = get_post_meta($pin->ID, 'nasa_pin_pb_options', true);

        $_optionsArr = json_decode($_options);

        if (!isset($marker_style) || !in_array($marker_style, array('price', 'plus'))) {
            $marker_style = 'price';
        }

        $popover = '';
        $icon = '';
        $style = 'width:35px;height:35px;';
        $icon_style = '';
        if ($bg_icon != '' || $txt_color != '' || $border_icon != '') {
            $icon_style .= ' style="';
            $icon_style .= $bg_icon != '' ? 'background-color:' . $bg_icon . ';' : '';
            $icon_style .= $txt_color != '' ? 'color:' . $txt_color . ';' : '';
            $icon_style .= $border_icon != '' ? 'border-color:' . $border_icon . ';' : '';
            $icon_style .= '" ';
        }

        $effect_style = $bg_icon != '' ? ' style="background-color:' . $bg_icon . ';"' : '';

        switch ($marker_style) {
            case 'plus':
                $icon = '<i class="nasa-marker-icon nasa-flex"' . $icon_style . '>' .
                '<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none">' .
                    '<path d="M12 6V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />' .
                    '<path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />' .
                '</svg></i>';
                $popover = ' popover-plus-wrap';
                break;

            case 'price':
            default:
                $style = 'min-width:40px;height:40px;';
                break;
        }

        $k = 0;
        $price_html = array();
        
        if (is_array($_optionsArr) && !empty($_optionsArr)) {
            $pin_product_slide_class = $slide_pin_product == 'yes' ? 'nasa-marker-icon-product-slide':'';
            $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
            $description_info = apply_filters('nasa_loop_short_description_show', false);
            $image_size = apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail');
            $padding_slide = ($slide_pin_product == 'yes') ? ($slide_pin_product_reverse == 'yes' ? 'desktop-padding-right-40 rtl-desktop-padding-right-0 rtl-desktop-padding-left-40 tablet-padding-right-20 rtl-tablet-padding-right-0 rtl-tablet-padding-left-20' : 'desktop-padding-left-40 rtl-desktop-padding-left-0 rtl-desktop-padding-right-40 tablet-padding-left-20 rtl-tablet-padding-left-0 rtl-tablet-padding-right-20') : '';

            if ($slide_pin_product == 'yes') {
                $pin_title = isset($pin->post_title) ? $pin->post_title : esc_html__('Shop The Look', 'nasa-core');
                $pin_title = isset($content) && $content != '' ? $content : $pin_title;

                $layout_buttons_class = (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] != '') ?' nasa-' . $nasa_opt['loop_layout_buttons']: '';

                $slider_class = 'ns-items-gap nasa-slick-slider products grid' . $layout_buttons_class;

                $data_attrs = array();
                $data_attrs[] = 'data-columns="2"';
                $data_attrs[] = 'data-columns-small="2"';
                $data_attrs[] = 'data-columns-tablet="2"';
                $data_attrs[] = 'data-autoplay="false"';
                $data_attrs[] = 'data-loop="false"';
                $data_attrs[] = 'data-slides-all="false"';
                $data_attrs[] = 'data-delay="6000"';
                $data_attrs[] = 'data-height-auto="false"';
                $data_attrs[] = 'data-dot="true"';
                $data_attrs[] = 'data-switch-tablet="' . nasa_switch_tablet() . '"';
                $data_attrs[] = 'data-switch-desktop="' . nasa_switch_desktop() . '"';
            
                $attrs_str = !empty($data_attrs) ? ' ' . implode(' ', $data_attrs) : '';
                
                $class_slider = 'nasa_product_pin_slide_wrap ' . $padding_slide;
                
                $class_slider .= isset($pin_desc) && $pin_desc != '' ? ' has-desc' : '';

                $product_slide = '<div class="' . $class_slider . '" data-count-pin="' . (count($_optionsArr) - 1) . '">';
                $product_slide .= '<h3>' . $pin_title . '</h3>';
                $product_slide .= isset($pin_desc) && $pin_desc != '' ? '<p class="nasa-title-desc text-center margin-bottom-10">' . $pin_desc . '</p>' : '';
                $product_slide .='<div class="' . $slider_class . '" ' . $attrs_str . '>';
            }
            
            foreach ($_optionsArr as $option) {
                $product_id = $option->product_id;
                $product = wc_get_product($product_id);
                
                if (!isset($option->coords) || !$product || $product->get_status() !== 'publish') {
                    continue;
                }
                
                if ($slide_pin_product == 'yes') {

                    if ($product) :
                        $post_object = get_post($product_id);
                        setup_postdata($GLOBALS['post'] =& $post_object);
    
                        $GLOBALS['product'] = $product;

                        if (!empty($product) || $product->is_visible()) :
                            ob_start();
                            wc_get_template('content-product.php', array(
                                'is_deals' => true,
                                '_delay' => 0,
                                '_delay_item' => $_delay_item,
                                'wrapper' => 'div',
                                'show_in_list' => false,
                                'description_info' => $description_info
                            ));
                            $product_slide .= ob_get_clean();
                        endif;
                    endif;
                }
                
                $position_show = isset($option->position_show) ? $option->position_show : 'top';

                if ($marker_style == 'price') {
                    if ($full_price_icon == 'yes') {
                        $icon = '<span class="nasa-marker-icon-bg"' . $icon_style . '>' . $product->get_price_html() . '</span>';
                    } else {
                        if ($product->get_type() == 'variable') {
                            $price_sale = $product->get_variation_sale_price();
                            $price = !$price_sale ? $product->get_variation_regular_price() : $price_sale;
                        } else {
                            $price_sale = $product->get_sale_price();
                            $price = !$price_sale ? $product->get_regular_price() : $price_sale;
                        }

                        $args_price = $price_rounding == 'yes' ? array('decimals' => 0) : array();
                        $icon = '<span class="nasa-marker-icon-bg"' . $icon_style . '>' . wc_price($price, $args_price) . '</span>';
                    }
                }

                $data[$pin_rand_id][$k] = array(
                    'marker_pin' => $icon,
                    'position' => 'nasa-' . $position_show,
                    'id_product' => $product_id,
                    'title_product' => $product->get_name(),
                    'link_product' => esc_url($product->get_permalink()),
                    'img_product' => $product->get_image($image_size),
                    'coords' => $option->coords,
                    'key_post' => $k
                );

                if (!isset($price_html[$product_id])) {
                    $price_html[$product_id] = $product->get_price_html();
                }

                $k++;
            }

            if ($slide_pin_product == 'yes') {
                $product_slide .= '</div></div>';
            }
        }

        $canvas = array(
            'src' => $image_src,
            'width' => $_width,
            'height' => $_height
        );

        $data[$pin_rand_id]['canvas'] = $canvas;

        $data_pin = wp_json_encode($data);

        if ($pin_effect == 'default') {
            $effect_class = isset($nasa_opt['effect_pin_product_banner']) && $nasa_opt['effect_pin_product_banner'] ? ' nasa-has-effect' : '';
        } else {
            $effect_class = $pin_effect == 'yes' ? ' nasa-has-effect' : '';
        }

        $effect_class .= $el_class != '' ? ' ' . esc_attr($el_class) : '';

        $effect_class .= ($slide_pin_product == 'yes') ? ' nasa-pin-banner-product-slide':'';

        $effect_class .= ($slide_pin_product == 'yes' && $slide_pin_product_reverse == 'yes') ? ' banner-slide-reverse' : '';

        $padding_img = ($slide_pin_product == 'yes') ? ($slide_pin_product_reverse == 'yes' ? 'desktop-padding-left-40 rtl-desktop-padding-left-0 rtl-desktop-padding-right-40 tablet-padding-left-20 rtl-tablet-padding-left-0 rtl-tablet-padding-right-20' : 'desktop-padding-right-40 rtl-desktop-padding-right-0 rtl-desktop-padding-left-40 tablet-padding-right-20 rtl-tablet-padding-right-0 rtl-tablet-padding-left-20') : '';

        $contentpin = '<div class="nasa-inner-wrap nasa-pin-wrap nasa-pin-banner-wrap' . $effect_class . '" data-pin="' . esc_attr($data_pin) . '">';
        if (!empty($price_html)) {
            foreach ($price_html as $k => $price_product) {
                $contentpin .= '<div class="hidden-tag nasa-price-pin-' . $k . '">' . $price_product . '</div>';
            }
        }

        $contentpin .= '<span class="nasa-wrap-relative-image ' . $padding_img . '">' .
            '<div data-width="' . $_width . '" data-height="' . $_height . '" class="nasa_pin_pb_image" data-src="' . esc_url($image_src) . '" data-easypin_id="' . $pin_rand_id . '" data-alt="' . esc_attr($pin->post_title) . '"></div>' .
        '</span>';
        $contentpin .= $product_slide;
        $contentpin .= '<div style="display:none;" id="tpl-' . $pin_rand_id . '" class="nasa-easypin-tpl">';
        $contentpin .= 
        '<div class="nasa-popover-clone">' .
            '<div class="{[position]}' . $popover . ($show_img === 'yes' ? ' ns-pin-img-price' : '') . '">' .
                '<div class="nasa-product-pin text-center">' .
                    '<a title="{[title_product]}" href="{[link_product]}" class="pin-product-url">' .
                        ($show_img === 'yes' ? '<div class="image-wrap">{[img_product]}</div>' : '') .
                        '<h5 class="title-wrap">{[title_product]}</h5>'.
                    '</a>' .
                    ($show_price === 'yes' ? '<div class="price nasa-price-pin" data-product_id="{[id_product]}"></div>' : '') .
                '</div>' .
            '</div>' .
        '</div>' .
        '<div class="nasa-marker-clone">' .
            '<div style="' . $style . '">' .
                '<span class="nasa-marker-icon-wrap ' . $pin_product_slide_class . '" data-key-post="{[key_post]}">{[marker_pin]}<span class="nasa-action-effect"' . $effect_style . '></span></span>' .
            '</div>' .
        '</div>'; 
        $contentpin .= '</div>';
        $contentpin .= '</div>';
    }
    
    return $contentpin;
}

// **********************************************************************// 
// ! Register New Element: Products banner
// **********************************************************************//
function nasa_register_products_banner(){
    $products_banner_params = array(
        "name" => "Products Banner",
        "base" => "nasa_pin_products_banner",
        "icon" => "icon-wpb-nasatheme",
        'description' => __("Display products pin banner.", 'nasa-core'),
        "category" => "Nasa Core",
        "params" => array(

            array(
                "type" => "textarea_html",
                "holder" => "div",
                "heading" => __("Overwrite Pin Name", 'nasa-core'),
                "param_name" => "content",
                "value" => "",
            ),

            array(
                "type" => "textfield",
                "heading" => __("Pin description", 'nasa-core'),
                "param_name" => "pin_desc"
            ),

            array(
                "type" => "dropdown",
                "heading" => __('Select Pin', 'nasa-core'),
                "param_name" => 'pin_slug',
                "value" => nasa_get_pin_arrays('nasa_pin_pb'),
                "std" => '',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Marker Style", 'nasa-core'),
                "param_name" => "marker_style",
                "value" => array(
                    __('Price icon', 'nasa-core') => 'price',
                    __('Plus icon', 'nasa-core') => 'plus'
                ),
                "std" => 'price',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Marker Full Price", 'nasa-core'),
                "param_name" => "full_price_icon",
                "value" => array(
                    __('No', 'nasa-core') => 'no',
                    __('Yes', 'nasa-core') => 'yes'
                ),

                "dependency" => array(
                    "element" => "marker_style",
                    "value" => array('price')
                ),

                "std" => 'no',
                "admin_label" => true
            ),
                        
            array(
                "type" => "dropdown",
                "heading" => __("Layout", 'nasa-core'),
                "param_name" => "slide_pin_product",
                "value" => array(
                    __('Without Slide', 'nasa-core') => 'no',
                    __('With Slide', 'nasa-core') => 'yes'
                ),

                "std" => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Layout Reverse", 'nasa-core'),
                "param_name" => "slide_pin_product_reverse",
                "value" => array(
                    __('No', 'nasa-core') => 'no',
                    __('Yes', 'nasa-core') => 'yes'
                ),

                "std" => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Price Rounding", 'nasa-core'),
                "param_name" => "price_rounding",
                "value" => array(
                    __('No', 'nasa-core') => 'no',
                    __('Yes', 'nasa-core') => 'yes'
                ),

                "dependency" => array(
                    "element" => "marker_style",
                    "value" => array('price')
                ),

                "std" => 'yes',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Show Image", 'nasa-core'),
                "param_name" => "show_img",
                "value" => array(
                    __('No', 'nasa-core') => 'no',
                    __('Yes', 'nasa-core') => 'yes'
                ),
                "std" => 'no'
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Show Price", 'nasa-core'),
                "param_name" => "show_price",
                "value" => array(
                    __('No', 'nasa-core') => 'no',
                    __('Yes', 'nasa-core') => 'yes'
                ),
                "std" => 'no'
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Effect icons", 'nasa-core'),
                "param_name" => "pin_effect",
                "value" => array(
                    __('Default', 'nasa-core') => 'default',
                    __('Yes', 'nasa-core') => 'yes',
                    __('No', 'nasa-core') => 'no'
                ),
                "std" => 'default'
            ),

            array(
                "type" => "colorpicker",
                "heading" => __("Background icon", 'nasa-core'),
                "param_name" => "bg_icon",
                "value" => ""
            ),

            array(
                "type" => "colorpicker",
                "heading" => __("Text color icon", 'nasa-core'),
                "param_name" => "txt_color",
                "value" => ""
            ),

            array(
                "type" => "colorpicker",
                "heading" => __("Border color icon", 'nasa-core'),
                "param_name" => "border_icon",
                "value" => ""
            ),

            array(
                "type" => "textfield",
                "heading" => __("Extra Class", 'nasa-core'),
                "param_name" => "el_class",
                "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'nasa-core')
            )
        )
    );
    vc_map($products_banner_params);
}
