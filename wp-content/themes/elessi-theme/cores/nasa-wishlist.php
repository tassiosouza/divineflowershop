<?php
defined('ABSPATH') or die(); // Exit if accessed directly

/**
 * Nasa Wishlist
 * 
 * Since 3.0
 */
if (!NASA_WISHLIST_ENABLE && NASA_WOO_ACTIVED) :

    class ELESSI_WOO_WISHLIST {

        /**
         * instance of the class
         */
        protected static $instance = null;

        /**
         * Support multi Languages
         */
        protected $multi_langs = false;

        /**
         * Current Language
         */
        public $current_lang = '';

        /**
         * List Languages
         */
        public $languages = array();

        /**
         * Cookie name
         */
        public $cookie_name = 'nasa_wishlist';

        /**
         * wishlist_list
         */
        public $wishlist_list = array();

        /**
         * expire time
         */
        public $expire = 0;

        /**
         * Init Class
         */
        public static function init() {
            global $nasa_opt;

            if (isset($nasa_opt['enable_nasa_wishlist']) && !$nasa_opt['enable_nasa_wishlist']) {
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
            global $nasa_opt;

            $in_mobile = isset($nasa_opt['nasa_in_mobile']) && $nasa_opt['nasa_in_mobile'] ? true : false;
            $mobile_app = $in_mobile && isset($nasa_opt['mobile_layout']) && $nasa_opt['mobile_layout'] == 'app' ? true : false;

            $siteurl = get_option('siteurl');
            $this->cookie_name .= $siteurl ? '_' . md5($siteurl) : '';

            $this->current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_option('WPLANG');
            if (trim($this->current_lang) == '') {
                $this->current_lang = 'default';
            }

            $this->languages = array($this->current_lang);

            /**
             * Support Multi Languages
             */
            if (function_exists('icl_get_languages')) {
                $this->multi_langs = true;
                $wpml_langs = icl_get_languages('skip_missing=0&orderby=code');

                if (!empty($wpml_langs)) {
                    foreach ($wpml_langs as $lang) {
                        if (isset($lang['language_code']) && !in_array($lang['language_code'], $this->languages)) {
                            $this->languages[] = $lang['language_code'];
                        }
                    }
                }
            }

            $this->wishlist_list = $this->get_wishlist_list();

            /**
             * Live 30 days
             */
            $this->expire = apply_filters('nasa_cookie_wishlist_live', NASA_TIME_NOW + (60 * 60 * 24 * 30));

            add_action('nasa_show_buttons_loop', array($this, 'btn_in_list'), 20);

            if ($mobile_app) {
                add_action('woocommerce_single_product_summary', array($this, 'btn_wishlist_with_label'), 31);
            } else {
                add_action('nasa_single_buttons', array($this, 'btn_in_detail'), 10);
            }
        }

        /**
         * get Cookie Name
         * 
         * @return type
         */
        public function get_cookie_name($_lang = null) {
            $lang = !$_lang ? $this->current_lang : $_lang;
            return $this->cookie_name . '_' . $lang;
        }

        /**
         * Get Wishlist items id
         */
        public function get_wishlist_list($_lang = null) {
            $wishlists = isset($_COOKIE[$this->get_cookie_name($_lang)]) ? json_decode($_COOKIE[$this->get_cookie_name($_lang)]) : array();

            if (!is_array($wishlists)) {
                $wishlists = array();
            }

            return $wishlists;
        }

        /**
         * Get Wishlist items id of Current language
         */
        public function get_current_wishlist() {
            return $this->wishlist_list;
        }

        /**
         * btn wishlist with label
         * 
         * @global type $product
         */
        public function btn_wishlist_with_label() {
            global $product;
            
            if (!$product) {
                return;
            }

            $variation = false;
            $productId = $product->get_id();
            $productType = $product->get_type();
            if ($productType == 'variation') {
                $variation_product = $product;
                $productId = wp_get_post_parent_id($productId);
                $parentProduct = wc_get_product($productId);
                $productType = $parentProduct->get_type();

                $GLOBALS['product'] = $parentProduct;
                $variation = true;
            }

            $class_btn = 'btn-wishlist btn-link wishlist-icon btn-nasa-wishlist nasa-flex ns-has-wrap';

            $ns_wishlist_stroke = apply_filters('elessi_wishlist_svg_stroke', '<svg class="nasa-icon ns-stroke" width="20" height="20" viewBox="0 0 32 32">
                <path d="M21.886 5.115c3.521 0 6.376 2.855 6.376 6.376 0 1.809-0.754 3.439-1.964 4.6l-10.297 10.349-10.484-10.536c-1.1-1.146-1.778-2.699-1.778-4.413 0-3.522 2.855-6.376 6.376-6.376 2.652 0 4.925 1.62 5.886 3.924 0.961-2.304 3.234-3.924 5.886-3.924zM21.886 4.049c-2.345 0-4.499 1.089-5.886 2.884-1.386-1.795-3.54-2.884-5.886-2.884-4.104 0-7.442 3.339-7.442 7.442 0 1.928 0.737 3.758 2.075 5.152l11.253 11.309 11.053-11.108c1.46-1.402 2.275-3.308 2.275-5.352 0-4.104-3.339-7.442-7.442-7.442v0z" fill="currentColor" />
            </svg>');

            $ns_wishlist_filled = apply_filters('elessi_wishlist_svg_filled', '<svg  class="nasa-icon ns-filled" width="20" height="20" viewBox="0 0 28 32">
                <path d="M19.886 4.049c-2.345 0-4.499 1.089-5.886 2.884-1.386-1.795-3.54-2.884-5.886-2.884-4.104 0-7.442 3.339-7.442 7.442 0 1.928 0.737 3.758 2.075 5.152l11.253 11.309 11.053-11.108c1.46-1.402 2.275-3.308 2.275-5.352 0-4.104-3.339-7.442-7.442-7.442z" fill="currentColor" />
            </svg>');
?>

            <a href="javascript:void(0);" class="<?php echo esc_attr($class_btn); ?>" data-prod="<?php echo (int) $productId; ?>" data-prod_type="<?php echo esc_attr($productType); ?>" title="<?php esc_attr_e('Wishlist', 'elessi-theme'); ?>" rel="nofollow">
                
                <?php echo $ns_wishlist_stroke; ?>
                <?php echo $ns_wishlist_filled; ?>
                

                <span class="nasa-icon-text-wrap">
                    <span class="margin-left-5 rtl-margin-left-0 rtl-margin-right-5 nasa-icon-text"><?php echo esc_html__('Add to wishlist', 'elessi-theme'); ?></span>
                    <span class="margin-left-5 rtl-margin-left-0 rtl-margin-right-5 nasa-icon-text"><?php echo esc_html__('Added to wishlist', 'elessi-theme'); ?></span>
                </span>
            </a>

            <?php
            if ($variation) {
                $GLOBALS['product'] = $variation_product;
            }
        }

        /**
         * btn wishlist in list
         * 
         * @global type $product
         */
        public function btn_wishlist($tip = 'left') {
            global $product;
            
            if (!$product) {
                return;
            }

            $variation = false;
            $productId = $product->get_id();
            $productType = $product->get_type();
            if ($productType == 'variation') {
                $variation_product = $product;
                $productId = wp_get_post_parent_id($productId);
                $parentProduct = wc_get_product($productId);
                $productType = $parentProduct->get_type();

                $GLOBALS['product'] = $parentProduct;
                $variation = true;
            }

            $class_btn = 'btn-wishlist btn-link wishlist-icon btn-nasa-wishlist nasa-tip';
            $class_btn .= ' nasa-tip-' . $tip;

            $ns_wishlist_stroke = apply_filters('elessi_wishlist_svg_stroke', '<svg class="nasa-icon ns-stroke" width="20" height="20" viewBox="0 0 32 32">
                <path d="M21.886 5.115c3.521 0 6.376 2.855 6.376 6.376 0 1.809-0.754 3.439-1.964 4.6l-10.297 10.349-10.484-10.536c-1.1-1.146-1.778-2.699-1.778-4.413 0-3.522 2.855-6.376 6.376-6.376 2.652 0 4.925 1.62 5.886 3.924 0.961-2.304 3.234-3.924 5.886-3.924zM21.886 4.049c-2.345 0-4.499 1.089-5.886 2.884-1.386-1.795-3.54-2.884-5.886-2.884-4.104 0-7.442 3.339-7.442 7.442 0 1.928 0.737 3.758 2.075 5.152l11.253 11.309 11.053-11.108c1.46-1.402 2.275-3.308 2.275-5.352 0-4.104-3.339-7.442-7.442-7.442v0z" fill="currentColor" />
            </svg>');

             $ns_wishlist_filled = apply_filters('elessi_wishlist_svg_filled', '<svg  class="nasa-icon ns-filled" width="20" height="20" viewBox="0 0 28 32">
                <path d="M19.886 4.049c-2.345 0-4.499 1.089-5.886 2.884-1.386-1.795-3.54-2.884-5.886-2.884-4.104 0-7.442 3.339-7.442 7.442 0 1.928 0.737 3.758 2.075 5.152l11.253 11.309 11.053-11.108c1.46-1.402 2.275-3.308 2.275-5.352 0-4.104-3.339-7.442-7.442-7.442z" fill="currentColor" />
            </svg>');

            ?>

            <a href="javascript:void(0);" class="<?php echo esc_attr($class_btn); ?>" data-prod="<?php echo (int) $productId; ?>" data-prod_type="<?php echo esc_attr($productType); ?>" data-icon-text="<?php esc_attr_e('Add to Wishlist', 'elessi-theme'); ?>" data-added="<?php esc_attr_e('Added to Wishlist', 'elessi-theme'); ?>" title="<?php esc_attr_e('Wishlist', 'elessi-theme'); ?>" rel="nofollow">
                <?php echo $ns_wishlist_stroke; ?>
                <?php echo $ns_wishlist_filled; ?>
            </a>

            <?php
            if ($variation) {
                $GLOBALS['product'] = $variation_product;
            }
        }

        /**
         * btn wishlist in list
         * 
         * @global type $product
         */
        public function btn_in_list() {
            $this->btn_wishlist('left');
        }

        /**
         * btn wishlist in detail
         * 
         * @global type $product
         */
        public function btn_in_detail() {
            $this->btn_wishlist('right');
        }

        /**
         * Add Wishlist
         */
        public function add_to_wishlist($_product_id) {
            $product_id = intval($_product_id);

            if (!$product_id) {
                return false;
            }

            if ($this->languages) {
                foreach ($this->languages as $lang) {
                    $wishlists = $this->get_wishlist_list($lang);

                    if ($this->current_lang == $lang) {
                        $wishlists[] = $product_id;
                        $this->wishlist_list = $wishlists;
                    }

                    /**
                     * Support Multi Languages
                     */
                    else {
                        if ($this->multi_langs) {
                            $product_langID = icl_object_id($product_id, 'product', true, $lang);
                            $wishlists[] = $product_langID;
                        }
                    }

                    $this->set_cookie_wishlist($lang, $wishlists);
                }
            }

            return true;
        }

        /**
         * Remove from Wishlist
         */
        public function remove_from_wishlist($_product_id) {
            $product_id = intval($_product_id);

            if (!$product_id) {
                return false;
            }

            if ($this->languages) {
                foreach ($this->languages as $lang) {
                    if ($this->current_lang == $lang) {
                        $wishlists = $this->wishlist_list;

                        if ($wishlists) {
                            foreach ($wishlists as $k => $v) {
                                if ($v == $product_id) {
                                    unset($wishlists[$k]);
                                }
                            }
                        }

                        $this->wishlist_list = $wishlists;
                    }

                    /**
                     * Support WPML
                     */
                    else {
                        $wishlists = $this->get_wishlist_list($lang);
                        if ($this->multi_langs) {
                            if ($wishlists) {
                                $product_langID = icl_object_id($product_id, 'product', true, $lang);

                                foreach ($wishlists as $k => $v) {
                                    if ($v == $product_langID) {
                                        unset($wishlists[$k]);
                                    }
                                }
                            }
                        }
                    }

                    $this->set_cookie_wishlist($lang, $wishlists);
                }
            }

            return true;
        }

        /**
         * Count wishlist items
         * 
         * @return type
         */
        public function count_items() {
            return count($this->wishlist_list);
        }

        /**
         * Set cookie wishlist
         */
        protected function set_cookie_wishlist($lang = null, $wishlists = array()) {
            setcookie($this->get_cookie_name($lang), json_encode(array_values($wishlists)), $this->expire, COOKIEPATH, COOKIE_DOMAIN, false, false);
        }

        /**
         * Wishlist html
         */
        public function wishlist_html() {
            global $nasa_opt;

            $wishlist_items = $this->wishlist_list;

            $file = ELESSI_CHILD_PATH . '/includes/nasa-sidebar-wishlist_html.php';
            include is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-sidebar-wishlist_html.php';
        }
    }

    /**
     * Init NasaTheme Wishlist
     */
    add_action('init', 'elessi_woo_wishlist');
    if (!function_exists('elessi_woo_wishlist')) :
        function elessi_woo_wishlist() {
            return ELESSI_WOO_WISHLIST::init();
        }
    endif;
endif;
