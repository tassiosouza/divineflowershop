<?php
/**
 * Shortcode [nasa_products_masonry ...]
 * 
 * @global type $nasa_opt
 * @global int $nasa_sc
 * @param type $atts
 * @param type $content
 * @return type
 */
function nasa_sc_products_masonry($atts = array(), $content = null) {
    global $nasa_opt, $nasa_sc;
    
    if (!isset($nasa_sc) || !$nasa_sc) {
        $nasa_sc = 1;
    }
    $GLOBALS['nasa_sc'] = $nasa_sc + 1;
    
    if (!NASA_WOO_ACTIVED) {
        return $content;
    }
    
    $dfAttr = array(
        'cat' => '',
        'type' => 'recent_product',
        'layout' => '1',
        'loadmore' => 'no',
        'sc' => $nasa_sc,
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    if ($type == '') {
        return $content;
    }
    
    if (!in_array($layout, array('1', '2'))) {
        $layout = '1';
    }
    
    /**
     * Masonry isotope
     */
    if (!wp_script_is('jquery-masonry-isotope')) {
        wp_enqueue_script('jquery-masonry-isotope', NASA_CORE_PLUGIN_URL . 'assets/js/min/jquery.masonry-isotope.min.js', array('jquery'), null, true);
    }
    
    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_products_masonry', $dfAttr, $atts);
        
        if (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] != '') {
            $key .= '_btns-' . $nasa_opt['loop_layout_buttons'];
        }
        
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $limit = $layout == 2 ? 16 : 18;

        $loop = nasa_woo_query(array(
            'type' => $type,
            'post_per_page' => $limit,
            'paged' => 1,
            'cat' => $cat
        ));
        
        if ($loop->post_count) :
            $attributeWrap = '';
            
            if ($loadmore === 'yes') :
                $attributeWrap = 
                    'data-next_page="2" ' .
                    'data-layout="' . esc_attr($layout) . '" ' .
                    'data-product_type="' . esc_attr($type) . '" ' .
                    'data-limit="' . intval($limit) . '" ' .
                    'data-max_pages="' . intval($loop->max_num_pages) . '" ' .
                    'data-cat="' . esc_attr($cat) . '"';
            endif;
            
            $nasa_args = array(
                'nasa_opt' => $nasa_opt,
                'cat' => $cat,
                'type' => $type,
                'layout' => $layout,
                'loadmore' => $loadmore,
                'sc' => $sc,
                'el_class' => $el_class,
                'limit' => $limit,
                'loop' => $loop
            );

            ob_start();
            ?>
            <div class="nasa-wrap-products-masonry<?php echo ($el_class != '') ? ' ' . esc_attr($el_class) : ''; ?>">
                <div class="nasa-products-masonry products woocommerce"<?php echo $attributeWrap; ?>>
                    <?php nasa_template('products/nasa_products_masonry/masonry-' . $layout . '.php', $nasa_args); ?>
                </div>

                <?php if ($loadmore === 'yes' && $loop->max_num_pages > 1) :
                    echo '<div class="nasa-relative text-center desktop-margin-top-40 margin-bottom-20 nasa-clear-both">';
                    echo '<a class="load-more-masonry" href="javascript:void(0);" title="' . esc_attr__('LOAD MORE ...', 'nasa-core') . '" data-nodata="' . esc_attr__('ALL PRODUCTS LOADED', 'nasa-core') . '" rel="nofollow">' .
                        esc_html__('LOAD MORE ...', 'nasa-core') .
                    '</a>';
                    echo '</div>';
                endif; ?>
            </div>
            <?php
            $content = ob_get_clean();
        endif;
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: nasa products
// **********************************************************************//
function nasa_register_products_masonry(){
    vc_map(array(
        "name" => "Products Masonry",
        "base" => "nasa_products_masonry",
        'icon' => 'icon-wpb-nasatheme',
        'description' => __("Display products as masonry layout.", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "dropdown",
                "heading" => __("Type Show", 'nasa-core'),
                "param_name" => "type",
                "value" => array(
                    __('Recent', 'nasa-core') => 'recent_product',
                    __('Best Selling', 'nasa-core') => 'best_selling',
                    __('Featured', 'nasa-core') => 'featured_product',
                    __('Top Rate', 'nasa-core') => 'top_rate',
                    __('On Sale', 'nasa-core') => 'on_sale',
                    __('Recent Review', 'nasa-core') => 'recent_review',
                    __('Deals') => 'deals',
                    __('Quantity Stock - Descending') => 'stock_desc'
                ),
                'std' => 'recent_product',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Layout", 'nasa-core'),
                "param_name" => "layout",
                "value" => array(
                    __('Type 1 (Limit 18 items)', 'nasa-core') => '1',
                    __('Type 2 (Limit 16 items)', 'nasa-core') => '2'
                ),
                'std' => '1',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Load More", 'nasa-core'),
                "param_name" => "loadmore",
                "value" => array(
                    __('Yes', 'nasa-core') => 'yes',
                    __('No', 'nasa-core') => 'no'
                ),
                'std' => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => __("Product Category", 'nasa-core'),
                "param_name" => "cat",
                "admin_label" => true,
                "value" => nasa_get_cat_product_array()
            ),

            array(
                "type" => "textfield",
                "heading" => __("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    ));
}