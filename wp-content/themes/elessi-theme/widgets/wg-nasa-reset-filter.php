<?php
defined('ABSPATH') or die(); // Exit if accessed directly

/**
 * Widget Nasa Reset Filter
 */
if (NASA_WOO_ACTIVED) {

    /**
     * Register Widget
     */
    add_action('widgets_init', 'elessi_reset_filter_widget');
    function elessi_reset_filter_widget() {
        register_widget('Elessi_WC_Widget_Reset_Filters');
    }

    /**
     * Reset Filter Widget and related functions
     *
     * @author   NasaThemes
     * @category Widgets
     * @version  1.0.0
     * @extends  WC_Widget
     */
    class Elessi_WC_Widget_Reset_Filters extends WC_Widget {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->widget_cssclass = 'woocommerce widget_reset_filters nasa-slick-remove nasa-no-toggle nasa-widget-has-active nasa-widget-hidden';
            $this->widget_description = __('Display button reset filter.', 'elessi-theme');
            $this->widget_id = 'nasa_woocommerce_reset_filter';
            $this->widget_name = 'Nasa - Reset Filters';
            $this->settings = array(
                'title' => array(
                    'type' => 'text',
                    'std' => '',
                    'label' => __('Title', 'elessi-theme'),
                ),
            );

            parent::__construct();
        }

        /**
         * Output widget.
         *
         * @see WP_Widget
         * @param array $args     Arguments.
         * @param array $instance Widget instance.
         */
        public function widget($args, $instance) {
            if (!is_shop() && !is_product_taxonomy()) {
                return;
            }

            $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            $min_price = isset($_GET['min_price']) ? wc_clean(wp_unslash($_GET['min_price'])) : 0;
            $max_price = isset($_GET['max_price']) ? wc_clean(wp_unslash($_GET['max_price'])) : 0;
            $rating_filter = isset($_GET['rating_filter']) ? array_filter(array_map('absint', explode(',', wp_unslash($_GET['rating_filter'])))) : array();
            $status_filter = false;
            if (class_exists('Elessi_WC_Widget_Status_Filter')) {
                foreach (Elessi_WC_Widget_Status_Filter::$_status as $status) {
                    if (isset($_REQUEST[$status]) && $_REQUEST[$status] === '1') {
                        $status_filter = true;
                        break;
                    }
                }
            }

            $alphabet_filter = false;
            if (class_exists('Elessi_WC_Widget_Alphabet_Filter')) {
                if (isset($_REQUEST[Elessi_WC_Widget_Alphabet_Filter::$_request_name]) && trim($_REQUEST[Elessi_WC_Widget_Alphabet_Filter::$_request_name]) !== '') {
                    $alphabet_filter = true;
                }
            }
            
            $tags_filter = false;
            if (class_exists('Elessi_WC_Widget_Tags_Filter')) {
                if (isset($_REQUEST[Elessi_WC_Widget_Tags_Filter::$_request_name]) && !empty($_REQUEST[Elessi_WC_Widget_Tags_Filter::$_request_name])) {
                    $tags_filter = true;
                }
            }


            $cat_filter = false;
            if (class_exists('Elessi_WC_Widget_Multi_Categories_Filter')) {
                if (isset($_REQUEST[Elessi_WC_Widget_Multi_Categories_Filter::$_request_name]) && !empty($_REQUEST[Elessi_WC_Widget_Multi_Categories_Filter::$_request_name])) {
                    $cat_filter = true;
                }
            }

            if (0 < count($_chosen_attributes) || 0 < $min_price || 0 < $max_price || !empty($rating_filter) || $status_filter || $alphabet_filter || $tags_filter || $cat_filter) {
                global $wp_query;
                
                $title = isset($instance['title']) && $instance['title'] ? $instance['title'] : esc_html__('Reset', 'elessi-theme');
                
                $nasa_href_page = elessi_get_origin_url_paging(array('s', 'post_type'));
                
                // Reset Price, Rating Filter
                $reset_arrays = array('min_price', 'max_price', 'rating_filter');
                
                // Reset Status Filter
                if ($status_filter) {
                    $reset_arrays = array_merge($reset_arrays, Elessi_WC_Widget_Status_Filter::$_status);
                }

                // Reset Alphabet Filter
                if ($alphabet_filter) {
                    $reset_arrays[] = Elessi_WC_Widget_Alphabet_Filter::$_request_name;
                }
                
                // Reset Multi Tags Filter
                if ($tags_filter) {
                    $reset_arrays[] = Elessi_WC_Widget_Tags_Filter::$_request_name;
                }

                 // Reset Multi Categories Filter
                 if ($cat_filter) {
                    $reset_arrays[] = Elessi_WC_Widget_Multi_Categories_Filter::$_request_name;
                }
                
                // Reset Attributes Filter
                $array_add = array();
                if (!empty($_GET) && count($_chosen_attributes)) {
                    foreach ($_GET as $key => $value) {
                        if (0 === strpos($key, 'filter_')) {
                            $attribute = wc_sanitize_taxonomy_name(str_replace('filter_', '', $key));
                            $reset_arrays[] = $key;
                            $reset_arrays[] = 'query_type_' . $attribute;
                        } else {
                            if (!in_array($key, $reset_arrays)) {
                                $array_add[$key] = $value;
                            }
                        }
                    }
                }
                
                $nasa_href_page = remove_query_arg($reset_arrays, add_query_arg($array_add, $nasa_href_page));
                $_close_svg = '<svg class="nasa-close-fillter" width="20px" height="12px" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.207 6.207a1 1 0 0 0-1.414-1.414L12 10.586 6.207 4.793a1 1 0 0 0-1.414 1.414L10.586 12l-5.793 5.793a1 1 0 1 0 1.414 1.414L12 13.414l5.793 5.793a1 1 0 0 0 1.414-1.414L13.414 12l5.793-5.793z" fill="Curentcolor" /></svg>';

                $this->widget_start($args, $instance);

                echo '<a class="nasa-reset-filters-btn" href="' . esc_url($nasa_href_page) . '" title="' . $title . '">' . $_close_svg  . $title . '</a>';

                $this->widget_end($args);
            }
        }

    }

}
