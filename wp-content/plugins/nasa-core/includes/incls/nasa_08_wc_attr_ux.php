<?php
defined('ABSPATH') or die(); // Exit if accessed directly

/**
 * Instantiate Class Nasa_WC_Attr_UX
 */
add_action('init', array('Nasa_WC_Attr_UX', 'getInstance'));

/**
 * Class Nasa Woocommerce attributies UX
 */
class Nasa_WC_Attr_UX extends Nasa_Abstract_WC_Attr_UX {
    /**
     * Init static
     * @var type 
     */
    protected static $instance = null;
    
    /**
     * Limit show number of 1 variation of product
     * @var type 
     */
    protected $_max_show = 0;
    
    /**
     * Less More
     * @var type 
     */
    protected $_less_more = false;

    /**
     * Output Content
     * @var type 
     */
    protected $_outputs;
    
    /**
     * Product ID
     * @var type 
     */
    protected $_product_id;
    
    /**
     * terms
     * @var type 
     */
    protected $_nasa_termmeta;
    
    /**
     * HTML variations
     * @var type 
     */
    protected $_content = '';
    
    /**
     * Support Multi Currencies
     * @var type 
     */
    protected $_currency = '';

    /**
     * style array check
     * @var type 
     */
    public $style_image = array('round', 'square');
    public $style_image_single = array('extends', 'square-caption');
    public $style_color = array('radio', 'round', 'small-square', 'big-square');
    public $style_label = array('radio', 'round', 'small-square-1', 'small-square-2', 'big-square');
    public $loop_layout_buttons_8 = false;
    
    /**
     * Event Load
     * @var type
     */
    public $events_click = array('select', 'badge', 'modern-8');

    /**
     * Instance
     */
    public static function getInstance() {
        global $nasa_opt;
        
        if (isset($nasa_opt['enable_nasa_variations_ux']) && !$nasa_opt['enable_nasa_variations_ux']) {
            return null;
        }

        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        if (!NASA_WOO_ACTIVED) {
            return null;
        }
        
        global $nasa_opt;
        
        parent::__construct();
        
        // Add class Round | Square in body
        add_filter('body_class', array($this, 'add_body_classes'));
        
        if (isset($nasa_opt['limit_nasa_variations_ux']) && $nasa_opt['limit_nasa_variations_ux'] === '') {
            $this->_max_show = 999;
        } else {
            $this->_max_show = (!isset($nasa_opt['limit_nasa_variations_ux']) || !(int) $nasa_opt['limit_nasa_variations_ux']) ? 3 : (int) $nasa_opt['limit_nasa_variations_ux'];
        }
        
        $this->_less_more = (isset($nasa_opt['nasa_ux_less_more']) && $nasa_opt['nasa_ux_less_more']) ? true : false;
        
        add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'get_nasa_attr_ux_html'), 100, 2);
        add_filter('nasa_attr_ux_html', array($this, 'nasa_attr_ux_html'), 5, 4);
        
        add_action('nasa_static_content', array($this, 'nasa_static_enable_attr_ux'), 99);

        if (isset($nasa_opt['pre_option_nasa_hide_out_of_stock_items']) && $nasa_opt['pre_option_nasa_hide_out_of_stock_items']) {
            add_filter('woocommerce_variation_is_visible', array($this, 'hide_out_of_stock_variations'), 10, 4);
        } 

        add_filter('woocommerce_available_variation', array($this, 'nasa_custom_variation'), 10, 3);
        
        /**
         * Style for Color - Label in Single or Quick view
         */
        add_action('woocommerce_before_variations_form', array($this, 'open_wrap_variations_ux'));
        add_action('woocommerce_after_variations_form', array($this, 'close_wrap_variations_ux'));
        
        if (!isset($nasa_opt['nasa_variations_ux_item']) || $nasa_opt['nasa_variations_ux_item']) {
            /**
             * Get currency product
             */
            $this->_currency = get_woocommerce_currency();

            /**
             * Custom add to cart class in grid
             */
            add_filter('nasa_filter_add_to_cart_class', array($this, 'nasa_custom_add_to_cart_class_loop'));

            /**
             * Render variations in grid product
             */
            if (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] == 'modern-8') {
                add_action('nasa_after_show_buttons_loop', array($this, 'product_content_variations_ux_loop'), 14, 0);
            } else {
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'product_content_variations_ux_loop'), 14, 0);
            }
        
            /**
             * Filter for Select options in grid
             */
            add_action('nasa_filter_add_to_cart_class', array($this, 'select_option_class_before_click'));

            /**
             * Filter for Variants Badge in grid
             */
            add_filter('nasa_badges', array($this, 'badge_variants'));
            
            /**
             * Hide first item Select | Custom
             */
            if (isset($nasa_opt['show_nasa_ux_select_first']) && !$nasa_opt['show_nasa_ux_select_first']) {
                add_filter('nasa_toggle_first_variants', array($this, 'hide_first_item_select_custom'));
            }
        }
    }

    /**
     * 
     * @global type $nasa_opt
     * @param string $classes
     * @return string
     */
    public function select_option_class_before_click($classes) {
        global $nasa_opt;
        
        if (isset($nasa_opt['nasa_variations_after']) && $nasa_opt['nasa_variations_after'] == 'select') {
            $classes .= ' nasa-before-click';
        }

        if (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] == 'modern-8') {
            $classes .= ' nasa-quick-add';
        }
        
        return $classes;
    }
    
    /**
     * Variant Badge
     * 
     * @global type $nasa_opt
     * @global type $product
     * @param string $badges
     * @return string
     */
    public function badge_variants($badges) {
        global $nasa_opt, $product;
        if (
            !isset($nasa_opt['nasa_variations_after']) ||
            $nasa_opt['nasa_variations_after'] !== 'badge' ||
            'variable' !== $product->get_type()
        ) {
            return $badges;
        }
        
        $label = apply_filters('nasa_variants_badge_label', esc_html__('Variants', 'nasa-core'));
        
        $badges .= '<a class="badge nasa-variants nasa-variants-before-click" href="javascript:void(0);" data-product_id="' . (int) $product->get_id() . '" rel="nofollow">' . $label . '</a>';
        
        return $badges;
    }

    /**
     * Ajax inputs value
     */
    public function nasa_static_enable_attr_ux() {
        // echo '<input type="hidden" name="nasa_attr_ux" value="1" />';
        echo '<input type="hidden" name="add_to_cart_text" value="' . esc_html__('Add to cart', 'nasa-core') . '" />';
        echo '<input type="hidden" name="nasa_no_matching_variations" value="' . esc_html__('Sorry, no products matched your selection. Please choose a different combination.', 'nasa-core') . '" />';
    }
    
    /**
     * Image size for variations
     * Schedule time sale for variation
     * 
     * @param type $variation
     * @return type
     */
    public function nasa_custom_variation($variation, $variable_product, $variation_product) {
        /**
         * Add To Cart Text
         */
        $variation['add_to_cart_txt'] = $variation_product->add_to_cart_text();
        
        /**
         * Image Catalog
         */
        if (!isset($variation['image_catalog'])) {
            $image_size = apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail');
            $image = wp_get_attachment_image_src($variation['image_id'], $image_size);
            $variation['image_catalog'] = isset($image[0]) ? esc_url($image[0]) : '';
        }
        
        /**
         * Deal Time
         */
        if (!isset($variation['deal_time'])) {
            $time_from = get_post_meta($variation['variation_id'], '_sale_price_dates_from', true);
            $time_to = get_post_meta($variation['variation_id'], '_sale_price_dates_to', true);
            
            $array_time = array();
            if ($time_to) {
                $array_time['to'] = $time_to * 1000;
                $array_time['html'] = nasa_time_sale($time_to);
            }
            
            if ($time_from) {
                $array_time['from'] = $time_from * 1000;
            }
            
            $variation['deal_time'] = $array_time ? $array_time : false;
        }
        
        /**
         * Out of Stock - Badge
         */
        if ("outofstock" === $variation_product->get_stock_status()) {
            $variation['outofstock_badge'] = function_exists('elessi_badge_outofstock') ? elessi_badge_outofstock() :
                apply_filters('nasa_badge_outofstock', '<span class="badge out-of-stock-label">' . esc_html__('Sold Out', 'nasa-core') . '</span>');
        }

        return $variation;
    }
    
    /**
     * Custom class add to cart in grid
     * 
     * @global type $nasa_opt
     * @global type $product
     * @param string $class_btn
     * @return string
     */
    public function nasa_custom_add_to_cart_class_loop($class_btn) {
        global $nasa_opt, $product;
        
        if (
            (isset($nasa_opt['nasa_variations_ux_add_to_cart_grid']) && !$nasa_opt['nasa_variations_ux_add_to_cart_grid']) ||
            'variable' !== $product->get_type()
        ) {
            return $class_btn;
        }

        if (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] == 'modern-8') {
            $class_btn .= ' nasa-quick-add';
        }
        
        $class_btn .= ' nasa-variable-add-to-cart-in-grid';
        
        return $class_btn;
    }

    /**
     * Class Type display to body
     * 
     * @param array $classes
     * @return $classes
     */
    public function add_body_classes($classes) {
        global $nasa_opt;
        
        if (isset($nasa_opt['label_attribute_single']) && $nasa_opt['label_attribute_single']) {
            $classes[] = 'nasa-label-attr-single';
        }
        
        /**
         * Image style
         */
        $classes[] = $this->get_display_style_image();
        
        return $classes;
    }

    /**
     * 
     * @global type $product
     * @global type $nasa_opt
     * @global type $nasa_termmeta
     * @param type $return
     * @param type $callAjax
     * @return type
     */
    public function product_content_variations_ux_loop($return = false, $callAjax = false) {
        global $product, $nasa_opt;
        
        if (!$product || $product->get_type() != 'variable') {
            return;
        }
        
        $this->loop_layout_buttons_8 = (isset($nasa_opt['loop_layout_buttons']) && $nasa_opt['loop_layout_buttons'] == 'modern-8') ? true : false;
        
        /**
         * After click
         */
        $this->_product_id = (int) $product->get_id();
        if (
            !$callAjax &&
            isset($nasa_opt['nasa_variations_after']) &&
            in_array($nasa_opt['nasa_variations_after'], $this->events_click)
        ) {
            $this->_content = '<div class="nasa-variations-ux-after nasa-product-' . $this->_product_id . ' hidden-tag"></div>';
            
            if ($return) {
                return $this->_content;
            }

            echo $this->_content;
            
            return;
        }
        
        /**
         * Render HTML variations UX
         */
        $this->_content = $this->get_cache_content($this->_product_id);
        
        if (!$this->_content) {
            $nasa_colors = self::get_tax_color();
            $nasa_labels = self::get_tax_labels();
            $nasa_images = self::get_tax_images();
            $nasa_selects = $nasa_custom = $nasa_private_attr = null;

            if (!isset($nasa_opt['enable_nasa_ux_select']) || $nasa_opt['enable_nasa_ux_select']) {
                $nasa_selects = self::get_tax_selects();
                $nasa_private_attr = get_post_meta($this->_product_id, '_product_attributes', true);
                
                if ($nasa_private_attr) {
                    foreach ($nasa_private_attr as $objs) {
                        if (
                            isset($objs['is_taxonomy']) &&
                            !$objs['is_taxonomy'] &&
                            isset($objs['is_variation']) &&
                            $objs['is_variation']
                        ) {
                            if (!is_array($nasa_custom)) {
                                $nasa_custom = array();
                            }

                            $nasa_custom[] = $objs['name'];
                        }
                    }
                }
            }

            if (
                empty($nasa_colors) &&
                empty($nasa_labels) &&
                empty($nasa_images) &&
                empty($nasa_selects) &&
                empty($nasa_custom)
            ) {
                return null;
            }
        
            global $nasa_termmeta;
            
            $this->_content = '';
            
            $available_variations = $product->get_available_variations();
            if (empty($available_variations) && false !== $available_variations) {
                return;
            }

            $attributes = $product->get_variation_attributes();

            $this->_outputs = array(
                self::_NASA_COLOR => array(),
                self::_NASA_LABEL => array(),
                self::_NASA_IMAGE => array(),
                self::_NASA_SELECT => array(),
                self::_NASA_ATTR_CUSTOM => array()
            );

            if (!isset($nasa_termmeta)) {
                $nasa_termmeta = array();
            }

            if (!isset($nasa_termmeta[$this->_product_id])) {
                $nasa_termmeta[$this->_product_id] = $this->_outputs;
            }
            
            $this->_nasa_termmeta = $nasa_termmeta;

            foreach ($attributes as $attribute_name => $options) {
                $attr_name = 0 === strpos($attribute_name, 'pa_') ? substr($attribute_name, 3) : $attribute_name;
                $default = $product->get_variation_default_attribute($attribute_name);
                
                /**
                 * Init labels variations
                 */
                $this->init_label_output($nasa_labels, $options, $attr_name, $default);

                /**
                 * Init images variations
                 */
                $this->init_image_output($nasa_images, $options, $attr_name, $default);
                
                /**
                 * Init colors variations
                 */
                $this->init_color_output($nasa_colors, $options, $attr_name, $default);

                /**
                 * Init select variations
                 */
                $this->init_select_output($nasa_selects, $options, $attr_name, $default);
                
                /**
                 * Init custom variations
                 */
                $this->init_custom_output($nasa_custom, $options, $attr_name, $default);
            }

            $GLOBALS['nasa_termmeta'] = $this->_nasa_termmeta;

            $class_content_variable_warp_modern_8 = $this->loop_layout_buttons_8 ? 'ns-modern-8-content-variable-warp' : '';

            /**
             * Open Wrap variations
             */
            $this->_content .= '<div class="nasa-product-content-variable-warp ' .  $class_content_variable_warp_modern_8 . '" data-product_id="' . $this->_product_id . '" data-product_variations="' . htmlspecialchars(wp_json_encode($available_variations)) . '">';
            
            if (!empty($this->_outputs[self::_NASA_COLOR]) || !empty($this->_outputs[self::_NASA_IMAGE])) {
                $class_wrap_color_image = 'nasa-product-content-color-image-wrap';
                $class_wrap_color_image .= !empty($this->_outputs[self::_NASA_COLOR]) && !empty($this->_outputs[self::_NASA_IMAGE]) ? ' nasa-full-color-image-attr' : '';
                
                $this->_content .= '<div class="' . $class_wrap_color_image . '">';
                
                /**
                 * Images variations
                 */
                $this->image_render();

                /**
                 * Colors variations
                 */
                $this->color_render();
                
                /**
                 * Wrap close Color - Image
                 */
                $this->_content .= '</div>';
            }
            
            /**
             * Labels variations
             */
            $this->label_render();

            /**
             * Default WooCommerce Variations
             */
            if (!empty($this->_outputs[self::_NASA_SELECT]) || !empty($this->_outputs[self::_NASA_ATTR_CUSTOM])) {
                /**
                 * Wrap open Select Options
                 */
                $this->_content .= '<div class="nasa-product-content-' . self::_NASA_SELECT . '-wrap">';
                
                /**
                 * Selects variations
                 */
                $this->select_render();

                /**
                 * Custom variations
                 */
                $this->custom_render();
                
                /**
                 * Wrap close Select Options
                 */
                $this->_content .= '</div>';
            }

            if (
                (!isset($nasa_opt['loop_add_to_cart']) || $nasa_opt['loop_add_to_cart']) &&
                (!isset($nasa_opt['disable-cart']) || !$nasa_opt['disable-cart']) &&
                $this->loop_layout_buttons_8
            ) {
                $this->_content .= '<a href="javascript:void(0);" title="' . esc_attr__('Close','nasa-core') . '" class="ns-close-quickadd" rel="nofollow"><svg width="30" height="30" viewBox="0 0 32 32" fill="currentColor"><path d="M15.233 19.175l0.754 0.754 6.035-6.035-0.754-0.754-5.281 5.281-5.256-5.256-0.754 0.754 3.013 3.013z" /></svg></a>';
                
                $this->_content .= '<div class="ns-modern-8-variations_button"><div class="quantity">' .
                    '<a href="javascript:void(0);" class="plus" rel="nofollow">' .
                        '<svg width="13" height="20" stroke-width="2" viewBox="0 0 24 24" fill="currentColor">' .
                            '<path d="M12 6V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />' .
                            '<path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />' .
                        '</svg>' .
                    '</a>' .
                    '<input type="number" class="input-text qty text" name="quantity" aria-label="Product quantity" value="1" data-old="1" size="4" min="1" max="" step="1" placeholder="" inputmode="numeric" autocomplete="off" />' .
                    '<a href="javascript:void(0);" class="minus" rel="nofollow">' .
                        '<svg width="13" height="20" stroke-width="2" viewBox="0 0 24 24" fill="currentColor">' .
                            '<path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />' .
                        '</svg>' .
                    '</a>' .
                '</div>';
                
                ob_start();
                woocommerce_template_loop_add_to_cart(array(
                    'modern-8-add' => 1
                ));
                $this->_content .= ob_get_clean();
                $this->_content .= '</div>';
            }

            /**
             * Close Wrap variations
             */
            $this->_content .= '</div>';
            
            /**
             * Cache file
             */
            $this->set_cache_content($this->_product_id, $this->_content);
        }
        
        if ($return) {
            return $this->_content;
        }
        
        echo $this->_content;
    }
    
    /**
     * init Colors variations
     */
    protected function init_color_output($nasa_colors, $options, $attr_name, $default) {
        if (!empty($options) && $nasa_colors && in_array($attr_name, $nasa_colors)) {
            $k = 1;
            
            foreach ($options as $option) {
                if (!isset($this->_nasa_termmeta[$this->_product_id][self::_NASA_COLOR][$attr_name][$option])) {
                    $term = get_term_by('slug', $option, 'pa_' . $attr_name);
                    if ($term) {
                        $active = $term->slug == $default ? true : false;
                        $term_meta = get_term_meta($term->term_id, self::_NASA_COLOR, true);
                        $term_meta = $term_meta ? $term_meta : $term->name;
                        $this->_nasa_termmeta[$this->_product_id][self::_NASA_COLOR][$attr_name][$option] = $this->_outputs[self::_NASA_COLOR][$attr_name][$option] = array('name' => $term->name, 'value' => $term_meta, 'active' => $active);

                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }

                        $k++;
                    }
                } else {
                    $this->_outputs[self::_NASA_COLOR][$attr_name][$option] = $this->_nasa_termmeta[$this->_product_id][self::_NASA_COLOR][$attr_name][$option];
                }
            }
        }
    }
    
    /**
     * init Labels variations
     */
    protected function init_label_output($nasa_labels, $options, $attr_name, $default) {
        if ($nasa_labels && in_array($attr_name, $nasa_labels)) {
            $k = 1;
            
            foreach ($options as $option) {
                if (!isset($this->_nasa_termmeta[$this->_product_id][self::_NASA_LABEL][$attr_name][$option])) {
                    $term = get_term_by('slug', $option, 'pa_' . $attr_name);
                    if ($term) {
                        $active = $term->slug == $default ? true : false;
                        $term_meta = get_term_meta($term->term_id, self::_NASA_LABEL, true);
                        $term_meta = $term_meta ? $term_meta : $term->name;
                        $this->_nasa_termmeta[$this->_product_id][self::_NASA_LABEL][$attr_name][$option] = $this->_outputs[self::_NASA_LABEL][$attr_name][$option] = array('name' => $term->name, 'value' => $term_meta, 'active' => $active);

                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }

                        $k++;
                    }
                } else {
                    $this->_outputs[self::_NASA_LABEL][$attr_name][$option] = $this->_nasa_termmeta[$this->_product_id][self::_NASA_LABEL][$attr_name][$option];
                }
            }
        }
    }
    
    /**
     * init Images variations
     */
    protected function init_image_output($nasa_images, $options, $attr_name, $default) {
        if ($nasa_images && in_array($attr_name, $nasa_images)) {
            $k = 1;
            
            foreach ($options as $option) {
                if (!isset($this->_nasa_termmeta[$this->_product_id][self::_NASA_IMAGE][$attr_name][$option])) {
                    $term = get_term_by('slug', $option, 'pa_' . $attr_name);
                    if ($term) {
                        $active = $term->slug == $default ? true : false;
                        $term_meta = get_term_meta($term->term_id, self::_NASA_IMAGE, true);
                        $this->_nasa_termmeta[$this->_product_id][self::_NASA_IMAGE][$attr_name][$option] = $this->_outputs[self::_NASA_IMAGE][$attr_name][$option] = array(
                            'name' => $term->name,
                            'value' => $term_meta,
                            'active' => $active
                        );

                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }

                        $k++;
                    }
                } else {
                    $this->_outputs[self::_NASA_IMAGE][$attr_name][$option] = $this->_nasa_termmeta[$this->_product_id][self::_NASA_IMAGE][$attr_name][$option];
                }
            }
        }
    }
    
    /**
     * init Selects variations
     */
    protected function init_select_output($nasa_selects, $options, $attr_name, $default) {
        if ($nasa_selects && in_array($attr_name, $nasa_selects)) {
            foreach ($options as $option) {
                if (!isset($this->_nasa_termmeta[$this->_product_id][self::_NASA_SELECT][$attr_name][$option])) {
                    $term = get_term_by('slug', $option, 'pa_' . $attr_name);
                    if ($term) {
                        $active = $term->slug == $default ? true : false;
                        $term_meta = get_term_meta($term->term_id, self::_NASA_SELECT, true);
                        $term_meta = $term_meta ? $term_meta : $term->name;
                        $this->_nasa_termmeta[$this->_product_id][self::_NASA_SELECT][$attr_name][$option] = $this->_outputs[self::_NASA_SELECT][$attr_name][$option] = array('name' => $term->name, 'value' => $term_meta, 'active' => $active);
                    }
                } else {
                    $this->_outputs[self::_NASA_SELECT][$attr_name][$option] = $this->_nasa_termmeta[$this->_product_id][self::_NASA_SELECT][$attr_name][$option];
                }
            }
        }
    }
    
    /**
     * init Custom variations
     */
    protected function init_custom_output($nasa_custom, $options, $attr_name, $default) {
        if ($nasa_custom && in_array($attr_name, $nasa_custom)) {
            foreach ($options as $option) {
                if (!isset($this->_nasa_termmeta[$this->_product_id][self::_NASA_ATTR_CUSTOM][$attr_name][$option])) {
                    $active = $option == $default ? true : false;
                    $this->_nasa_termmeta[$this->_product_id][self::_NASA_ATTR_CUSTOM][$attr_name][$option] = $this->_outputs[self::_NASA_ATTR_CUSTOM][$attr_name][$option] = array('name' => $option, 'value' => $option, 'active' => $active);
                } else {
                    $this->_outputs[self::_NASA_ATTR_CUSTOM][$attr_name][$option] = $this->_nasa_termmeta[$this->_product_id][self::_NASA_ATTR_CUSTOM][$attr_name][$option];
                }
            }
        }
    }
    
    /**
     * Labels variations
     */
    protected function label_render() {
        if (!empty($this->_outputs[self::_NASA_LABEL])) {
            $this->_content .= '<div class="nasa-product-content-' . self::_NASA_LABEL . '-wrap">';

            foreach ($this->_outputs[self::_NASA_LABEL] as $attr_name => $objs) {
                $terms = wc_get_product_terms($this->_product_id, 'pa_' . $attr_name, apply_filters('nasa_attr_ux_agrs_terms', array('fields' => 'all')));
                $array_keys = array_keys($objs);
                
                $this->_content .= '<div class="nasa-product-content-child nasa-product-content-' . sanitize_title($attr_name) . '-wrap-child" data-pa_name="' . sanitize_title($attr_name) . '">';

                if ($this->loop_layout_buttons_8) {
                    $taxonomy = get_taxonomy( 'pa_'.$attr_name );
                    $this->_content .= '<span class="nasa-bold ns-attr-ux-title" data-text="' . $taxonomy->labels->singular_name . '">'.$taxonomy->labels->singular_name.'</span>';
                }

                $k = 1;
                foreach ($terms as $term) {
                    if (in_array($term->slug, $array_keys)) {
                        $active_curr = isset($objs[$term->slug]['active']) && $objs[$term->slug]['active'] ? true : false;
                        $value_curr = isset($objs[$term->slug]['value']) && $objs[$term->slug]['value'] ? $objs[$term->slug]['value'] : $term->slug;
                        $class = 'nasa-attr-ux-item';
                        
                        if ($this->_max_show) {
                            if ($k == $this->_max_show + 1) {
                                $this->_content .= '<a href="javascript:void(0);" class="nasa-attr-ux-more nasa-bold nasa-attr-ux-' . self::_NASA_LABEL . '" rel="nofollow"><svg width="8" height="8" stroke-width="3" viewBox="4 4 17 17" fill="currentColor"><path d="M12 6V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /><path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /></svg>' . (count($terms) - $this->_max_show) . '</a>';
                            }
                            
                            if ($k > $this->_max_show) {
                                $class .= ' ux-item-more';
                            }
                        }
                        
                        $this->_content .= sprintf(
                            '<a href="javascript:void(0);" class="' . $class . ' nasa-attr-ux-' . self::_NASA_LABEL . ' nasa-attr-ux-%s %s" data-name="%s"  title="%s" data-value="%s" data-pa="%s" data-act="%s" rel="nofollow">%s</a>',
                            esc_attr($term->slug),
                            $active_curr ? 'nasa-active' : '',
                            esc_attr($term->name),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            sanitize_title($attr_name),
                            $active_curr ? '1' : '0',
                            $value_curr
                        );

                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }
                        
                        $k++;
                    }
                }

                $this->_content .= '</div>';
            }

            $this->_content .= '</div>';
        }
    }

    /**
     * Colors variations
     */
    protected function color_render() {
        if (!empty($this->_outputs[self::_NASA_COLOR])) {
            foreach ($this->_outputs[self::_NASA_COLOR] as $attr_name => $objs) {
                $terms = wc_get_product_terms($this->_product_id, 'pa_' . $attr_name, apply_filters('nasa_attr_ux_agrs_terms', array('fields' => 'all')));
                $array_keys = array_keys($objs);
                
                $this->_content .= '<div class="nasa-product-content-child nasa-product-content-' . sanitize_title($attr_name) . '-wrap-child nasa-color-wrap-child" data-pa_name="' . sanitize_title($attr_name) . '">';
                
                if ( $this->loop_layout_buttons_8) {
                    $taxonomy = get_taxonomy( 'pa_'.$attr_name );
                    $this->_content .= '<span class="nasa-bold ns-attr-ux-title" data-text="' . $taxonomy->labels->singular_name . '">' . $taxonomy->labels->singular_name . '</span>';
                }

                $k = 1;
                
                foreach ($terms as $term) {
                    if (in_array($term->slug, $array_keys)) {
                        $active_curr = isset($objs[$term->slug]['active']) && $objs[$term->slug]['active'] ? true : false;
                        $value_curr = isset($objs[$term->slug]['value']) && $objs[$term->slug]['value'] ? $objs[$term->slug]['value'] : $term->slug;
                        $colors = $value_curr ? explode(',', $value_curr) : array();
                        $colors = apply_filters('ns_attr_color_arr', $colors);
                        
                        $color_html = '<span class="nasa-flex ns-colors-wrap">';
                        
                        if (isset($colors[0])) {
                            $color_html .= '<span style="background-color:' . esc_attr($colors[0]) . ';"></span>';
                        }
                        
                        if (isset($colors[1])) {
                            $color_html .= '<span style="background-color:' . esc_attr($colors[1]) . ';"></span>';
                        }
                        
                        $color_html .= '</span>';
                        
                        $class = 'nasa-attr-ux-item nasa-tip nasa-tip-top';
                        
                        if ($this->_max_show) {
                            if ($k == $this->_max_show + 1) {
                                $this->_content .= '<a href="javascript:void(0);" class="nasa-attr-ux-more nasa-bold nasa-attr-ux-' . self::_NASA_COLOR . '" rel="nofollow"><svg width="8" height="8" stroke-width="3" viewBox="4 4 17 17" fill="currentColor"><path d="M12 6V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /><path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /></svg>' . (count($terms) - $this->_max_show) . '</a>';
                            }
                            
                            if ($k > $this->_max_show) {
                                $class .= ' ux-item-more';
                            }
                        }
                        
                        $this->_content .= sprintf(
                            '<a href="javascript:void(0);" class="' . $class . ' nasa-attr-ux-' . self::_NASA_COLOR . ' nasa-attr-ux-%s %s" data-name="%s" title="%s" data-value="%s" data-pa="%s" data-act="%s" rel="nofollow">%s</a>',
                            esc_attr($term->slug),
                            $active_curr ? 'nasa-active' : '',
                            esc_attr($term->name),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            sanitize_title($attr_name),
                            $active_curr ? '1' : '0',
                            $color_html
                        );
                        
                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }
                        
                        $k++;
                    }
                }
                
                $this->_content .= '</div>';
            }
        }
    }
    
    /**
     * Images variations
     */
    protected function image_render() {
        if (!empty($this->_outputs[self::_NASA_IMAGE])) {
            foreach ($this->_outputs[self::_NASA_IMAGE] as $attr_name => $objs) {
                $terms = wc_get_product_terms($this->_product_id, 'pa_' . $attr_name, apply_filters('nasa_attr_ux_agrs_terms', array('fields' => 'all')));
                $array_keys = array_keys($objs);
                
                $this->_content .= '<div class="nasa-product-content-child nasa-product-content-' . sanitize_title($attr_name) . '-wrap-child nasa-image-wrap-child" data-pa_name="' . sanitize_title($attr_name) . '">';
               
                if ($this->loop_layout_buttons_8) {
                    $taxonomy = get_taxonomy( 'pa_'.$attr_name );
                    $this->_content .= '<span class="nasa-bold ns-attr-ux-title" data-text="' . $taxonomy->labels->singular_name . '">' . $taxonomy->labels->singular_name . '</span>';
                }
                
                $k = 1;
                
                foreach ($terms as $term) {
                    if (in_array($term->slug, $array_keys)) {
                        $active_curr = isset($objs[$term->slug]['active']) && $objs[$term->slug]['active'] ? true : false;
                        $value_curr = isset($objs[$term->slug]['value']) && $objs[$term->slug]['value'] ? $objs[$term->slug]['value'] : false;
                        
                        $class = 'nasa-attr-ux-item nasa-tip nasa-tip-top';
                        
                        if ($this->_max_show) {
                            if ($k == $this->_max_show + 1) {
                                $this->_content .= '<a href="javascript:void(0);" class="nasa-attr-ux-more nasa-bold nasa-attr-ux-' . self::_NASA_IMAGE . '" rel="nofollow"><svg width="8" height="8" stroke-width="3" viewBox="4 4 17 17" fill="currentColor"><path d="M12 6V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /><path d="M6 12H18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" /></svg>' . (count($terms) - $this->_max_show) . '</a>';
                            }
                            
                            if ($k > $this->_max_show) {
                                $class .= ' ux-item-more';
                            }
                        }
                        
                        $image = $this->get_image_preview($value_curr, false, 20, 20, $objs[$term->slug]['name']);
                        $this->_content .= sprintf(
                            '<a href="javascript:void(0);" class="' . $class . ' nasa-attr-ux-' . self::_NASA_IMAGE . ' nasa-attr-ux-%s %s" data-name="%s" title="%s" data-value="%s" data-pa="%s" data-act="%s" rel="nofollow">%s</a>',
                            esc_attr($term->slug),
                            $active_curr ? 'nasa-active' : '',
                            esc_attr($term->name),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            sanitize_title($attr_name),
                            $active_curr ? '1' : '0',
                            $image
                        );
                        
                        if ($this->_max_show && $k >= $this->_max_show) {
                            if (!$this->_less_more) {
                                break;
                            }
                        }
                        
                        $k++;
                    }
                }
                
                $this->_content .= '</div>';
            }
        }
    }
    
    /**
     * Selects variations
     */
    protected function select_render() {
        if (!empty($this->_outputs[self::_NASA_SELECT])) {
            $k = 0;
            
            foreach ($this->_outputs[self::_NASA_SELECT] as $attr_name => $objs) {
                $label = wc_attribute_label('pa_' . $attr_name);
                $terms = wc_get_product_terms($this->_product_id, 'pa_' . $attr_name, apply_filters('nasa_attr_ux_agrs_terms', array('fields' => 'all')));
                $array_keys = array_keys($objs);
                
                $this->_content .= '<div class="nasa-product-content-child nasa-inactive nasa-product-content-' . sanitize_title($attr_name) . '-wrap-child" data-pa_name="' . sanitize_title($attr_name) . '">';
                
                $class_toggle = '';
                if ($k == 0) {
                    $class_toggle = apply_filters('nasa_toggle_first_variants', ' nasa-show');
                }

                $class_attr_ux_title = $this->loop_layout_buttons_8 ? ' ns-attr-ux-title':'';

                $k++;
                $this->_content .= '<a class="nasa-toggle-attr-select' . $class_toggle . $class_attr_ux_title . '" href="javascript:void(0);" data-text="' . $attr_name . '" rel="nofollow">' . $label . '</a>';
                $this->_content .= '<div class="nasa-toggle-content-attr-select' . $class_toggle . '">';
                
                foreach ($terms as $term) {
                    if (in_array($term->slug, $array_keys)) {
                        $active_curr = isset($objs[$term->slug]['active']) && $objs[$term->slug]['active'] ? true : false;
                        $value_curr = isset($objs[$term->slug]['value']) && $objs[$term->slug]['value'] ? $objs[$term->slug]['value'] : $objs[$term->slug];
                        
                        $this->_content .= sprintf(
                            '<a href="javascript:void(0);" class="nasa-attr-ux-item nasa-attr-ux-' . self::_NASA_SELECT . ' nasa-attr-ux-%s %s" data-name="%s" title="%s" data-value="%s" data-pa="%s" data-act="%s" rel="nofollow">%s</a>',
                            esc_attr($term->slug),
                            $active_curr ? 'nasa-active' : '',
                            esc_attr($term->name),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            sanitize_title($attr_name),
                            $active_curr ? '1' : '0',
                            $value_curr
                        );
                    }
                }
                
                $this->_content .= '</div></div>';
            }
        }
    }
    
    /**
     * Custom variations
     */
    protected function custom_render() {
        if (!empty($this->_outputs[self::_NASA_ATTR_CUSTOM])) {
            $k = 0;
            
            foreach ($this->_outputs[self::_NASA_ATTR_CUSTOM] as $attr_name => $objs) {
                $this->_content .= '<div class="nasa-product-content-child nasa-inactive nasa-product-content-' . sanitize_title($attr_name) . '-wrap-child nasa-attr_type_custom" data-pa_name="' . sanitize_title($attr_name) . '">';
                
                $class_toggle = '';
                if ($k == 0) {
                    $class_toggle = apply_filters('nasa_toggle_first_variants', ' nasa-show');
                }

                $class_attr_ux_title = $this->loop_layout_buttons_8 ? ' ns-attr-ux-title':'';
                
                $k++;
                $this->_content .= '<a class="nasa-toggle-attr-select' . $class_toggle . $class_attr_ux_title . '" href="javascript:void(0);" data-text="' . $attr_name . '" rel="nofollow">' . $attr_name . '</a>';
                $this->_content .= '<div class="nasa-toggle-content-attr-select' . $class_toggle . '">';
                
                foreach ($objs as $key => $term) {
                    $this->_content .= sprintf(
                        '<a href="javascript:void(0);" class="nasa-attr-ux-item nasa-attr-ux-' . self::_NASA_ATTR_CUSTOM . ($term['active'] ? ' nasa-active' : '') . '" data-value="%s" data-pa="%s" data-act="%s" rel="nofollow">%s</a>',
                        esc_attr($term['value']),
                        sanitize_title($attr_name),
                        $term['active'] ? '1' : '0',
                        $term['value']
                    );
                }
                
                $this->_content .= '</div></div>';
            }
        }
    }
    
    /**
     * Hide first item Select | Custom
     */
    public function hide_first_item_select_custom($class) {
        return '';
    }
    
    /**
     * Filter function to add swatches bellow the default selector
     *
     * @param $html
     * @param $args
     *
     * @return string
     */
    public function get_nasa_attr_ux_html($html, $args) {
        if ((!isset($_REQUEST['wc-ajax']) || $_REQUEST['wc-ajax'] !== 'nasa_quick_view') && !is_product()) {
            return $html;
        }
        
        $attr = self::get_tax_attribute($args['attribute']);

        // Return if this is normal attribute
        if (empty($attr)) {
            return $html;
        }

        if (!array_key_exists($attr->attribute_type, $this->types)) {
            return $html;
        }

        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $class = 'variation-selector variation-select-' . $attr->attribute_type;
        $nasa_attr_ux = '';

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        if (!empty($options) && $product && taxonomy_exists($attribute)) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms($product->get_id(), $attribute, apply_filters('nasa_attr_ux_agrs_terms', array('fields' => 'all')));

            foreach ($terms as $term) {
                if (in_array($term->slug, $options)) {
                    $nasa_attr_ux .= apply_filters('nasa_attr_ux_html', '', $term, $attr, $args);
                }
            }
        }

        if (!empty($nasa_attr_ux)) {
            $class .= ' hidden-tag nasa-not-in-sticky';

            $nasa_attr_ux = '<div class="nasa-attr-ux_wrap type-' . esc_attr($attr->attribute_type) . '" data-attribute_name="attribute_' . sanitize_title($attribute) . '">' . $nasa_attr_ux . '</div>';
            $html = '<div class="' . esc_attr($class) . '">' . $html . '</div>' . $nasa_attr_ux;
        }

        return $html;
    }
    
    /**
     * Add product type
     */
    public function add_product_type() {
        global $product;
        if ($product->get_type() != 'variable') {
            return;
        }
        
        $product_id = $product->get_id();
        $content = $this->get_cache_content($product_id);
        
        echo $content ? $content : '<div class="nasa-product-variable-call-ajax no-process nasa-product-variable-' . esc_attr($product_id) . ' hidden-tag" data-product_id="' . esc_attr($product_id) . '"></div>';
    }
    
    /**
     * Render variable string to product
     */
    public function render_variables($pids = array()) {
        global $nasa_opt;
        
        $products = array();
        
        if (isset($nasa_opt['nasa_variations_ux_item']) && !$nasa_opt['nasa_variations_ux_item']) {
            return $products;
        }
        
        if (empty($pids)) {
            return $products;
        }
        
        foreach ($pids as $pid) {
            $product = wc_get_product($pid);
            
            if ($product && $product->get_type() == 'variable') {
                $GLOBALS['product'] = $product;
                
                $products[$pid] = $this->product_content_variations_ux_loop(true);
            }
        }
        
        return $products;
    }

    /**
     * Print HTML swatches of a single product page
     *
     * @param $html
     * @param $term
     * @param $attr
     * @param $args
     *
     * @return string
     */
    public function nasa_attr_ux_html($html, $term, $attr, $args) {
        global $nasa_opt;
        $selected = sanitize_title($args['selected']) == $term->slug ? ' selected' : '';
        $name = esc_html(apply_filters('woocommerce_variation_option_name', $term->name));
        $term_meta = get_term_meta($term->term_id, $attr->attribute_type, true);
        $badge = get_term_meta($term->term_id, 'nasa_fomo', true);
        $badge_bg = get_term_meta($term->term_id, 'nasa_fomo_bg', true);
        $badge_color = get_term_meta($term->term_id, 'nasa_fomo_color', true);


        $style_parts = [];

        if ($badge_color) {
            $style_parts[] = 'color:' . esc_attr($badge_color);
        }

        if ($badge_bg) {
            $style_parts[] = 'background-color:' . esc_attr($badge_bg);
        }

        $style_attr = $style_parts ? 'style="' . implode(';', $style_parts) . ';"' : '';
        $badge_html = $badge != '' && isset($nasa_opt['attribute_single_fomo']) && $nasa_opt['attribute_single_fomo'] ? '<span class="badge" ' . $style_attr . '>'. esc_attr($badge) .'</span>' : '';
        
        $colors = $term_meta ? explode(',', $term_meta) : array();
        $colors = apply_filters('ns_attr_color_arr', $colors);
        
        $color_html = '<span class="nasa-attr-bg nasa-flex ns-colors-wrap">';

        if (isset($colors[0])) {
            $color_html .= '<span style="background-color:' . esc_attr($colors[0]) . ';"></span>';
        }

        if (isset($colors[1])) {
            $color_html .= '<span style="background-color:' . esc_attr($colors[1]) . ';"></span>';
        }

        $color_html .= '</span>';
        
        switch ($attr->attribute_type) {
            
            /**
             * Color Swatch
             */
            case self::_NASA_COLOR:
                $html = sprintf(
                    '<a href="javascript:void(0);" class="nasa-attr-ux nasa-tip nasa-tip-top nasa-attr-ux-color nasa-attr-ux-%s' . $selected . '" data-value="%s" rel="nofollow">%s<span class="nasa-attr-text">%s</span>%s</a>',
                    esc_attr($term->slug),
                    esc_attr($term->slug),
                    $color_html,
                    $name,
                    in_array($this->get_color_style_value(), array('small-square', 'big-square')) ? $badge_html : ''
                );
                break;

            /**
             * Label Swatch
             */
            case self::_NASA_LABEL:
                $label = $term_meta ? $term_meta : $name;
                $html = sprintf(
                    '<a href="javascript:void(0);" class="nasa-attr-ux nasa-attr-ux-label nasa-attr-ux-%s' . $selected . '" data-value="%s" rel="nofollow"><span class="nasa-attr-bg"></span><span class="nasa-attr-text">%s</span>%s</a>',
                    esc_attr($term->slug),
                    esc_attr($term->slug),
                    esc_html($label),
                    in_array($this->get_label_style_value(), array('small-square-1', 'big-square')) ? $badge_html : ''
                );
                break;
            
            /**
             * Image Swatch
             */
            case self::_NASA_IMAGE:
                $image = $this->get_image_preview($term_meta, false, 50, 50, $name);
                $html = sprintf(
                    '<a href="javascript:void(0);" class="nasa-attr-ux nasa-tip nasa-tip-top nasa-attr-ux-image nasa-attr-ux-%s' . $selected . '" data-value="%s"  title="%s" rel="nofollow"><span class="nasa-attr-bg-img">%s</span><span class="nasa-attr-text">%s</span>%s</a>',
                    esc_attr($term->slug),
                    esc_attr($term->slug),
                    esc_attr($name),
                    $image,
                    $name,
                    $this->get_image_single_style_value() === 'square-caption' ? $badge_html : ''
                );
                break;
            
            default:
                
                break;
        }

        return $html;
    }
    
    /**
     * Display type Attribute Round | Square
     */
    protected function get_display_style_image() {
        $image_style = $this->get_image_style_value();
        
        return in_array($image_style, $this->style_image) ? 'nasa-image-' . $image_style : 'nasa-image-round';
    }
    
    /**
     * Call value Attribute Image Round | Square
     */
    protected function get_image_style_value() {
        global $nasa_opt, $wp_query;

        $type = isset($nasa_opt['nasa_attr_image_style']) && in_array($nasa_opt['nasa_attr_image_style'], $this->style_image) ? $nasa_opt['nasa_attr_image_style'] : 'round';
        
        $nasa_root_term_id = $this->get_root_term_id();
        
        /**
         * Root Category
         */
        if ($nasa_root_term_id) {
            $type_override = get_term_meta($nasa_root_term_id, 'cat_attr_image_style', true);
            $type = $type_override ? $type_override : $type;
        } else {
            $page_id = false;
            $is_shop = is_shop();
            $is_product_taxonomy = is_product_taxonomy();
            
            /**
             * Shop
             */
            if ($is_shop || $is_product_taxonomy) {
                $pageShop = wc_get_page_id('shop');
                
                if ($pageShop > 0) {
                    $page_id = $pageShop;
                }
            }
            
            /**
             * Page
             */
            else {
                $page_id = $wp_query->get_queried_object_id();
            }
            
            /**
             * Swith header structure
             */
            if ($page_id) {
                $type_override = get_post_meta($page_id, '_nasa_attr_image_style', true);
                if (!empty($type_override)) {
                    $type = $type_override;
                }
            }
        }
        
        return $type;
    }
    
    /**
     * Wrap color - label variation
     * hook woocommerce_before_variations_form
     */
    public function open_wrap_variations_ux() {
        $class = array();
        $class[] = $this->get_display_stype_color();
        $class[] = $this->get_display_stype_label();
        $class[] = $this->get_display_stype_image_single();
        
        echo '<div class="' . implode(' ', $class) . '">';
    }
    
    /**
     * Wrap color - label variation
     * hook woocommerce_after_variations_form
     */
    public function close_wrap_variations_ux() {
        echo '</div>';
    }
    
    /**
     * Display type Attribute Color
     * 
     * Only for Single product
     */
    protected function get_display_stype_image_single() {
        return 'nasa-image-' . $this->get_image_single_style_value();
    }
    
    /**
     * Call value Attribute Color
     * 
     * Only for Single product
     */
    protected function get_image_single_style_value() {
        global $nasa_opt;

        $type = isset($nasa_opt['nasa_attr_image_single_style']) && in_array($nasa_opt['nasa_attr_image_single_style'], $this->style_image_single) ? $nasa_opt['nasa_attr_image_single_style'] : 'extends';
        
        $nasa_root_term_id = $this->get_root_term_id();
        
        /**
         * Root Category
         */
        if ($nasa_root_term_id) {
            $type_override = get_term_meta($nasa_root_term_id, 'cat_attr_image_single_style', true);
            $type = $type_override ? $type_override : $type;
        }

        return apply_filters('nasa_attr_image_single_style', $type);
    }

    /**
     * Display type Attribute Color
     * 
     * Only for Single product
     */
    protected function get_display_stype_color() {
        return 'nasa-color-' . $this->get_color_style_value();
    }
    
    /**
     * Call value Attribute Color
     * 
     * Only for Single product
     */
    protected function get_color_style_value() {
        global $nasa_opt;

        $type = isset($nasa_opt['nasa_attr_color_style']) && in_array($nasa_opt['nasa_attr_color_style'], $this->style_color) ? $nasa_opt['nasa_attr_color_style'] : 'radio';
        
        $nasa_root_term_id = $this->get_root_term_id();
        
        /**
         * Root Category
         */
        if ($nasa_root_term_id) {
            $type_override = get_term_meta($nasa_root_term_id, 'cat_attr_color_style', true);
            $type = $type_override ? $type_override : $type;
        }

        return apply_filters('nasa_attr_color_style', $type);
    }
    
    /**
     * Display type Attribute Label
     * 
     * Only for Single product
     */
    protected function get_display_stype_label() {
        return 'nasa-label-' . $this->get_label_style_value();
    }
    
    /**
     * Call value Attribute Color
     * 
     * Only for Single product
     */
    protected function get_label_style_value() {
        global $nasa_opt;

        $type = isset($nasa_opt['nasa_attr_label_style']) && in_array($nasa_opt['nasa_attr_label_style'], $this->style_label) ? $nasa_opt['nasa_attr_label_style'] : 'radio';
        
        $nasa_root_term_id = $this->get_root_term_id();
        
        /**
         * Root Category
         */
        if ($nasa_root_term_id) {
            $type_override = get_term_meta($nasa_root_term_id, 'cat_attr_label_style', true);
            $type = $type_override ? $type_override : $type;
        }
        
        return apply_filters('nasa_attr_label_style', $type);
    }

    /**
     * get root term id - root category
     */
    protected function get_root_term_id() {
        return nasa_root_term_id();
    }

    /**
     * Set cache file for variation of Product
     */
    protected function set_cache_content($product_id, $content) {
        $cache_obj = nasa_cache_obj();
        
        return null !== $cache_obj ? $cache_obj->set_content($this->_key_cache($product_id), $content, 'products') : false;
    }
    
    /**
     * Get cache variation of Product
     */
    protected function get_cache_content($product_id) {
        global $nasa_opt;
        
        if (isset($nasa_opt['nasa_cache_variables']) && !$nasa_opt['nasa_cache_variables']) {
            return false;
        }
        
        $cache_obj = nasa_cache_obj();
        
        return null !== $cache_obj ? $cache_obj->get_content($this->_key_cache($product_id), 'products') : false;
    }
    
    /**
     * Build key Cache
     */
    protected function _key_cache($product_id) {
        global $nasa_opt;
        
        $key = (isset($nasa_opt['loop_layout_buttons']) && in_array($nasa_opt['loop_layout_buttons'], $this->events_click)) ?
            $product_id . '_nsm_8' : $product_id;
        
        return $key . '_' . $this->_currency;
    }

    /**
     * hide_out_of_stock_variations
     */
    public function hide_out_of_stock_variations($is_visible, $variation_id, $product_id, $variation) {
        if (!$variation->is_in_stock()) {
            $is_visible = false;
        }
        
        return $is_visible;
    }

    /**
     * Destructor
     */
    public function __destruct() {
        if (isset($GLOBALS['nasa_termmeta'])) {
            unset($GLOBALS['nasa_termmeta']);
        }
    }
}
