<?php
/**
 * Exit if accessed directly
 */
defined('ABSPATH') or die();

/**
 * If WooCommerce Active
 */
if (NASA_WOO_ACTIVED) {

    add_action('widgets_init', 'elessi_product_categories_widget');
    function elessi_product_categories_widget() {
        register_widget('Elessi_Product_Categories_Widget');
    }

    class Elessi_Product_Categories_Widget extends WC_Widget {

        /**
         * Category ancestors
         *
         * @var array
         */
        public $cat_ancestors;

        /**
         * Current Category
         *
         * @var bool
         */
        public $current_cat;
        
        /**
         * Icons Support
         */
        protected $_icons_sp;

        /**
         * Constructor
         */
        public function __construct() {
            global $wp_version, $nasa_opt;
            
            $this->widget_cssclass = 'woocommerce widget_product_categories';
            $this->widget_description = __('Display product categories with Accordion.', 'elessi-theme');
            $this->widget_id = 'nasa_product_categories';
            $this->widget_name = 'Nasa - Product Categories';
            
            $this->_icons_sp = true;
            
            if (
                isset($wp_version) &&
                version_compare($wp_version, '5.8', ">=") &&
                isset($nasa_opt['block_editor_widgets']) &&
                $nasa_opt['block_editor_widgets']
            ) {
                $this->_icons_sp = apply_filters('nasa_block_editor_wg_cat_icons_sp', false);
            }
            
            $this->settings = array(
                'title' => array(
                    'type' => 'text',
                    'std' => 'Product Categories',
                    'label' => __('Title', 'elessi-theme')
                ),
                'orderby' => array(
                    'type' => 'select',
                    'std' => 'name',
                    'label' => __('Order by', 'elessi-theme'),
                    'options' => array(
                        'order' => __('Category Order', 'elessi-theme'),
                        'name' => __('Name', 'elessi-theme')
                    )
                ),
                'count' => array(
                    'type' => 'checkbox',
                    'std' => 0,
                    'label' => __('Show product counts', 'elessi-theme')
                ),
                'hierarchical' => array(
                    'type' => 'checkbox',
                    'std' => 1,
                    'label' => __('Show hierarchy', 'elessi-theme')
                ),
                'show_children_only' => array(
                    'type' => 'checkbox',
                    'std' => 0,
                    'label' => __('Only show children of the current category', 'elessi-theme')
                ),
                'hide_empty' => array(
                    'type'  => 'checkbox',
                    'std'   => 0,
                    'label' => __('Hide empty categories', 'elessi-theme'),
                ),
                'accordion' => array(
                    'type' => 'checkbox',
                    'std' => 1,
                    'label' => __('Show as Accordion', 'elessi-theme')
                ),
                'show_items' => array(
                    'type' => 'text',
                    'std' => 'All',
                    'label' => __('Show default numbers items', 'elessi-theme')
                )
            );
            
            parent::__construct();
        }

        /**
         * Updates a particular instance of a widget.
         *
         * @see WP_Widget->update
         *
         * @param array $new_instance
         * @param array $old_instance
         *
         * @return array
         */
        public function update($new_instance, $old_instance) {
            if ($this->_icons_sp) {
                $this->nasa_settings($new_instance);
            }
            
            return parent::update($new_instance, $old_instance);
        }

        /**
         * form function.
         *
         * @see WP_Widget->form
         * @param array $instance
         */
        public function form($instance) {
            if ($this->_icons_sp) {
                $this->nasa_settings($instance);
            }
            
            if (empty($this->settings)) {
                return;
            }

            foreach ($this->settings as $key => $setting) {
                $value = isset($instance[$key]) ? $instance[$key] : (isset($setting['std']) ? $setting['std'] : '');
                $_id = $this->get_field_id($key);
                $_name = $this->get_field_name($key);

                switch ($setting['type']) {

                    case 'text' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <input class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="text" value="<?php echo esc_attr($value); ?>" />
                        </p>
                        <?php
                        break;

                    case 'number' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <input class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="number" step="<?php echo esc_attr($setting['step']); ?>" min="<?php echo esc_attr($setting['min']); ?>" max="<?php echo esc_attr($setting['max']); ?>" value="<?php echo esc_attr($value); ?>" />
                        </p>
                        <?php
                        break;

                    case 'select' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <select class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>">
                                <?php foreach ($setting['options'] as $o_key => $o_value): ?>
                                    <option value="<?php echo esc_attr($o_key); ?>" <?php selected($o_key, $value); ?>><?php echo esc_html($o_value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <?php
                        break;

                    case 'checkbox' :
                        ?>
                        <p>
                            <input id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="checkbox" value="1" <?php checked($value, 1); ?> />
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                        </p>
                        <?php
                        break;
                    
                    case 'toggle-show-icon' :
                        ?>
                        <p>
                            <a class="toggle-choose-icon-btn" href="javascript:void(0);" rel="nofollow">
                                <?php echo esc_html__('Add icons for categories.', 'elessi-theme'); ?>
                            </a>
                        </p>
                        <?php
                        break;
                    
                    // Button chosen icon font
                    case 'icons':
                        echo $this->get_template_admin_icon($_name, $_id, $setting['label'], $value);
                        break;
                    
                    default :
                        
                        break;
                }
            }
        }
        
        /**
         * Add Icons
         * 
         * @param type $_name
         * @param type $_id
         * @param type $label
         * @param type $value
         * @return string
         */
        public function get_template_admin_icon($_name, $_id, $label, $value) {
            $content = '<p class="toggle-choose-icon hidden-tag">';
            $content .= '<a class="nasa-chosen-icon" data-fill="' . esc_attr($_id) . '">' . esc_html__('Click select icon for ', 'elessi-theme') . '</a>';
            $content .= '<span id="ico-' . esc_attr($_id) . '">';
            if ($value):
                $content .= '<i class="' . esc_attr($value) . '"></i>';
                $content .= '<a href="javascript:void(0);" class="nasa-remove-icon" data-id="' . esc_attr($_id) . '" rel="nofollow">';
                $content .= '<i class="fa fa-remove"></i>';
                $content .= '</a>';
            endif;
            $content .= '</span>';
            $content .= '<label for="' . $_id . '">' . $label . '</label><br />';
            $content .= '<input class="widefat" id="' . esc_attr($_id) . '" name="' . $_name . '" type="hidden" readonly="true" value="' . esc_attr($value) . '" />';
            $content .= '</p>';

            return $content;
        }

        /**
         * Init settings after post types are registered.
         */
        public function nasa_settings($instance) {
            // Default settings
            if (empty($instance)) {
                $default = get_option('widget_' . $this->widget_id, true);
                if ($default) {
                    foreach ($default as $value) {
                        $instance = $value;
                        break;
                    }
                }
            }
            
            $args = array(
                'taxonomy' => 'product_cat',
                'hierarchical' => true,
                'hide_empty' => false
            );
            
            $top = apply_filters('nasa_admin_top_level_set_icon', true);
            if ($top) {
                $args['parent'] = 0;
            }

            $cats = get_terms(apply_filters('woocommerce_product_attribute_terms', $args));
            
            if ($cats) {
                $this->settings['toggle'] = array(
                    'type' => 'toggle-show-icon',
                    'std' => ''
                );
                
                foreach ($cats as $cat) {
                    // Change settings
                    $this->settings['cat_' . $cat->slug] = array(
                        'type' => 'icons',
                        'std' => isset($instance['cat_' . $cat->slug]) ? $instance['cat_' . $cat->slug] : '',
                        'label' => '<b>' . $cat->name . '</b>'
                    );
                }
            }
        }

        /**
         * widget function.
         *
         * @see WP_Widget
         *
         * @param array $args
         * @param array $instance
         *
         * @return void
         */
        public function widget($args, $instance) {
            global $wp_query, $post, $nasa_opt;
            
            $accordion = isset($instance['accordion']) ? $instance['accordion'] : $this->settings['accordion']['std'];
            $count = isset($instance['count']) ? $instance['count'] : $this->settings['count']['std'];
            $hierarchical = isset($instance['hierarchical']) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
            
            $orderby = isset($instance['orderby']) ? $instance['orderby'] : $this->settings['orderby']['std'];
            $hide_empty = isset($instance['hide_empty']) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
            $show_items = isset($instance['show_items']) ? (int) $instance['show_items'] : 0;
            
            $show_children_only = isset($instance['show_children_only']) && $instance['show_children_only'] == '1' ? true : false;
            
            $get_sidebar = false;
            $is_product = is_singular('product');
            
            if (!$is_product) {
                $get_sidebar = isset($nasa_opt['category_sidebar']) ? $nasa_opt['category_sidebar'] : 'top';
                $get_sidebar = isset($_GET['sidebar']) ? $_GET['sidebar'] : $get_sidebar;
                $get_sidebar = in_array($get_sidebar, array('left', 'right', 'left-classic', 'right-classic', 'top-2', 'top-3')) ? $get_sidebar : 'top';
            }
            
            /**
             * Show all items
             */
            $list_args = array(
                'show_count'    => $count,
                'hierarchical'  => $hierarchical,
                'taxonomy'      => 'product_cat',
                'hide_empty'    => $hide_empty
            );

            // Menu Order
            $list_args['menu_order'] = false;
            if ($orderby === 'order') {
                $list_args['orderby'] = 'meta_value_num';
                $list_args['meta_key'] = 'order';
            } else {
                $list_args['orderby'] = 'title';
            }

            // Setup Current Category
            $this->current_cat = false;
            $this->cat_ancestors = array();
            $root_id = 0;
            
            if (is_tax('product_cat')) {
                $this->current_cat = $wp_query->queried_object;
                $this->cat_ancestors = get_ancestors($this->current_cat->term_id, 'product_cat');
            }
            
            elseif ($is_product) {
                $terms = wc_get_product_terms(
                    $post->ID,
                    'product_cat',
                    apply_filters(
                        'woocommerce_product_categories_widget_product_terms_args',
                        array(
                            'orderby' => 'parent',
                            'order'   => 'DESC',
                        )
                    )
                );
                
                if ($terms) {
                    $main_term = apply_filters('woocommerce_product_categories_widget_main_term', $terms[0], $terms);
                    $this->current_cat = $main_term;
                    $this->cat_ancestors = get_ancestors($main_term->term_id, 'product_cat');
                }
            }
            
            /**
             * Only Show Children
             */
            if ($show_children_only && $this->current_cat) {
                if ($hierarchical) {
                    $include = array_merge(
                        $this->cat_ancestors,
                        array($this->current_cat->term_id),
                        get_terms(
                            'product_cat',
                            array(
                                'fields' => 'ids',
                                'parent' => 0,
                                'hierarchical' => true,
                                'hide_empty' => false,
                            )
                        ),
                        get_terms(
                            'product_cat',
                            array(
                                'fields' => 'ids',
                                'parent' => $this->current_cat->term_id,
                                'hierarchical' => true,
                                'hide_empty' => false,
                            )
                        )
                    );
                    
                    // Gather siblings of ancestors.
                    if ($this->cat_ancestors) {
                        foreach ($this->cat_ancestors as $ancestor) {
                            $include = array_merge(
                                $include,
                                get_terms(
                                    'product_cat',
                                    array(
                                        'fields' => 'ids',
                                        'parent' => $ancestor,
                                        'hierarchical' => false,
                                        'hide_empty' => false,
                                    )
                                )
                            );
                        }
                    }
                } else {
                    // Direct children.
                    $include = get_terms(
                        'product_cat',
                        array(
                            'fields' => 'ids',
                            'parent' => $this->current_cat->term_id,
                            'hierarchical' => true,
                            'hide_empty' => false,
                        )
                    );
                }

                $list_args['include'] = implode(',', $include);
                
                if (empty($include)) {
                    return;
                }
            }
            
            elseif ((isset($nasa_opt['disable_top_level_cat']) && $nasa_opt['disable_top_level_cat'])) {
                $root_id = $this->cat_ancestors ? end($this->cat_ancestors) :
                    ($this->current_cat ? $this->current_cat->term_id : $root_id);
                $list_args['child_of'] = $root_id;
            }
            
            elseif ($show_children_only) {
                $list_args['child_of'] = 0;
                $list_args['depth'] = 1;
                $list_args['hierarchical'] = 1;
            }
            
            $this->widget_start($args, $instance);
            
            $menu_cat = new Elessi_Product_Cat_List_Walker();
            $menu_cat->set_icons($instance);
            $menu_cat->set_show_default($show_items);
            
            $list_args['walker']                        = $menu_cat;
            $list_args['title_li']                      = '';
            $list_args['pad_counts']                    = 1;
            $list_args['show_option_none']              = esc_html__('No product categories exist.', 'elessi-theme');
            $list_args['current_category']              = $this->current_cat ? $this->current_cat->term_id : '';
            $list_args['current_category_ancestors']    = $this->cat_ancestors;
            $list_args['max_depth']                     = '';
            
            if (!isset($nasa_opt['show_uncategorized']) || !$nasa_opt['show_uncategorized']) {
                $uncategorized = get_option('default_product_cat');
                
                if ($uncategorized) {
                    $list_args['exclude'] = $uncategorized;
                }
            }

            $top3 = isset($nasa_opt['category_sidebar']) ? $nasa_opt['category_sidebar'] : 'top';
            $top3 = isset($_GET['sidebar']) ? $_GET['sidebar'] : $top3;

            if ($top3 === 'top-3') {
                echo '<a data-show="1" class="nasa-tab-filter-topbar hidden-tag nasa-tab-push-cats" href="javascript:void(0);" data-text="' . esc_attr__('- Show less', 'elessi-theme') . '" rel="nofollow">' . esc_html__('+ Show more', 'elessi-theme') . '</a>';
            }
            
            $accordion_class = $accordion ? ' nasa-accordion' : ' ns-ctree';

            if ($get_sidebar == 'top') {
                $class_wrap_cat_top = 'nasa-widget-filter-cats-topbar';
                
                if (isset($nasa_opt['top_bar_cat_pos']) && $nasa_opt['top_bar_cat_pos'] == 'top') {
                    $accordion_class .= ' nasa-top-cat-filter';
                    $class_wrap_cat_top .= ' nasa-top-cat-filter-wrap';
                }
                
                echo '<div class="' . esc_attr($class_wrap_cat_top) . '">';
            }
            
            echo '<ul class="nasa-product-categories-widget nasa-product-taxs-widget nasa-root-tax nasa-root-cat product-categories' . $accordion_class . '">';
            
            wp_list_categories(apply_filters('woocommerce_product_categories_widget_args', $list_args));

            if ($show_items && ($menu_cat->get_total_root() > $show_items)) {
                echo '<li class="nasa_show_manual"><a data-show="1" class="nasa-show" href="javascript:void(0);" data-text="' . esc_attr__('- Show less', 'elessi-theme') . '" rel="nofollow">' . esc_html__('+ Show more', 'elessi-theme') . '</a></li>';
            }
            
            echo '<li class="nasa-current-note"></li>';

            echo '</ul>';
            
            if ($get_sidebar == 'top') {
                echo '</div>';
            }
            
            $this->widget_end($args);
        }
    }

    if (!class_exists('WC_Product_Cat_List_Walker')) {
        require_once WC()->plugin_path() . '/includes/walkers/class-product-cat-list-walker.php';
    }

    /**
     * Elessi_Product_Cat_List_Walker
     * 
     * Extends from WC_Product_Cat_List_Walker
     */
    class Elessi_Product_Cat_List_Walker extends WC_Product_Cat_List_Walker {

        protected $_icons = array();
        protected $_k = 0;
        protected $_show_default = 0;
        protected $_max_depth = 0;

        public function __construct($max_depth = 0) {
            $this->_max_depth = (int) $max_depth;
        }

        public function set_icons($instance) {
            $this->_icons = $instance;
        }

        public function set_show_default($show) {
            $this->_show_default = (int) $show;
        }

        public function get_total_root() {
            return $this->_k;
        }

        /**
         * @see Walker::start_el()
         * @since 2.1.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param int $depth Depth of category in reference to parents.
         * @param integer $current_object_id
         */
        public function start_el(&$output, $cat, $depth = 0, $args = array(), $current_object_id = 0) {
            // global $nasa_opt;

            $output .= '<li class="nasa-tax-item cat-item cat-item-' . $cat->term_id . ' cat-item-' . $cat->slug;
            $nasa_active = $accordion = $icon = '';
            if ($depth == 0) {
                $output .= ' root-item';
                if ($this->_show_default && ($this->_k >= $this->_show_default)) {
                    $output .= ' nasa-show-less';
                }
                $this->_k++;
            }
            if (isset($this->_icons['cat_' . $cat->slug]) && trim($this->_icons['cat_' . $cat->slug]) != '') {
                $icon = '<i class="nasa-icon ' . esc_attr($this->_icons['cat_' . $cat->slug]) . '"></i>&nbsp;&nbsp;';
            }

            if ($args['current_category'] == $cat->term_id) {
                $output .= ' current-cat current-tax-item active';
                $nasa_active = ' nasa-active';
            }

            if ($args['has_children'] && $args['hierarchical'] && (!$this->_max_depth || (($depth + 1) < $this->_max_depth))) {
                $output .= ' cat-parent nasa-tax-parent li_accordion';
                $accordion = '<a href="javascript:void(0);" class="accordion" rel="nofollow"></a>';
            }

            if ($args['current_category_ancestors'] && $args['current_category'] && in_array($cat->term_id, $args['current_category_ancestors'])) {
                $output .= ' nasa-current-tax-parent current-cat-parent active';
            }

            $output .= '">' . $accordion;

            $output .= '<a ' .
                'href="' . get_term_link($cat, $this->tree_type) . '" ' .
                'title="' . esc_attr($cat->name) . '" ' .
                'class="nasa-filter-item nasa-filter-by-tax nasa-filter-by-cat' . $nasa_active . '">' .
                    $icon . $cat->name;
            
            $output .= $args['show_count'] ? ' <span class="count">' . $cat->count . '</span>' : '';
            
            $output .= '</a>';
        }
    }
}
