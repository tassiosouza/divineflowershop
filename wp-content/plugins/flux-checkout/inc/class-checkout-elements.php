<?php
/**
 * Iconic_Flux_Checkout_Elements.
 *
 * Checkout Elements.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Checkout_Elements' ) ) {
	return;
}

/**
 * Iconic_Flux_Checkout_Elements.
 *
 * @class    Iconic_Flux_Checkout_Elements.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Checkout_Elements {

	/**
	 * The post type key.
	 *
	 * @var string $post_type_key The post type key.
	 */
	public static $post_type_key = 'checkout_elements';

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'on_init' ), 10 );

		if ( is_admin() ) {
			add_action( 'save_post', array( __CLASS__, 'save_meta_box' ) );
			add_action( 'admin_menu', array( __CLASS__, 'insert_checkout_elements_menu_after_flux' ), 200 );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'disable_wp_welcome_guide_for_elements' ), 20 );
			add_filter( 'post_row_actions', array( __CLASS__, 'remove_checkout_elements_view_action' ), 10, 2 );

			// Custom column.
			add_filter( 'manage_checkout_elements_posts_columns', array( __CLASS__, 'add_custom_post_column' ) );
			add_action( 'manage_checkout_elements_posts_custom_column', array( __CLASS__, 'display_column_data' ), 10, 2 );

			// duplicate checkout element.
			add_filter( 'post_row_actions', array( __CLASS__, 'add_duplicate_link' ), 10, 2 );
			add_action( 'admin_action_duplicate_checkout_element', array( __CLASS__, 'duplicate_checkout_element' ) );

		} else {
			add_action( 'wp', array( __CLASS__, 'on_wp_hook' ), 10 );
		}
	}

	/**
	 * On init.
	 */
	public static function on_init() {
		self::register_post_type();
	}

	/**
	 * Runs on `wp` hook so we can use Woo's template tags.
	 */
	public static function on_wp_hook() {
		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( is_checkout( true ) || 'update_order_review' === $wc_ajax ) {
			self::add_placeholder_positions();
			add_filter( 'wp_kses_allowed_html', array( __CLASS__, 'allow_noscript_on_checkout' ), 10, 2 );
		}

		add_filter( 'woocommerce_update_order_review_fragments', array( __CLASS__, 'add_elements_to_order_review_fragments' ) );
	}


	/**
	 * Add cusotm column.
	 *
	 * @param array $columns Array of columns.
	 * @return array Modified array of columns.
	 */
	public static function add_custom_post_column( $columns ) {
		$columns['fce_position'] = __( 'Position', 'text-domain' );
		return $columns;
	}

	/**
	 * Display custom column data.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 */
	public static function display_column_data( $column_name, $post_id ) {
		if ( 'fce_position' !== $column_name ) {
			return;
		}

		$element  = self::get_checkout_element( $post_id );
		$data     = $element->get_element_data( $post_id );
		$position = $data['position']['value'];

		if ( empty( $position ) ) {
			return;
		}

		echo esc_html( self::get_position_label( $position ) );
	}

	/**
	 * Add duplicate link.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post    Post object.
	 *
	 * @return array Modified array of actions.
	 */
	public static function add_duplicate_link( $actions, $post ) {
		if ( $post->post_type === self::$post_type_key ) {
			$actions['duplicate'] = '<a href="' . wp_nonce_url( 'admin.php?action=duplicate_checkout_element&amp;post=' . $post->ID, 'flux_duplicate_nonce' ) . '" title="' . esc_attr( __( 'Duplicate this item', 'flux-checkout' ) ) . '" rel="permalink">' . esc_attr( __( 'Duplicate', 'flux-checkout' ) ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Duplicate checkout element action.
	 */
	public static function duplicate_checkout_element() {
		$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'flux_duplicate_nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce!', 'flux-checkout' ) );
		}

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$action  = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $post_id && filter_input( INPUT_POST, 'post', FILTER_SANITIZE_NUMBER_INT ) ) {
			$post_id = filter_input( INPUT_POST, 'post', FILTER_SANITIZE_NUMBER_INT );
		}

		if ( ! $post_id || ( 'duplicate_checkout_element' !== $action ) ) {
			wp_die( esc_html__( 'No post to duplicate has been supplied!', 'flux-checkout' ) );
		}

		$post        = get_post( $post_id );
		$new_post_id = wp_insert_post(
			array(
				'post_title'   => $post->post_title . ' (Copy)',
				'post_content' => $post->post_content,
				'post_status'  => 'draft',
				'post_type'    => $post->post_type,
			)
		);

		if ( ! $new_post_id ) {
			wp_die( esc_html__( 'Something went wrong.', 'flux-checkout' ) );
		}

		$meta_info = get_post_meta( $post_id );
		if ( ! empty( $meta_info ) ) {
			foreach ( $meta_info as $meta_key => $meta_value ) {
				$dont_copy_meta = array( '_edit_lock', '_edit_last' );

				if ( in_array( $meta_key, $dont_copy_meta, true ) ) {
					continue;
				}

				if ( is_array( $meta_value ) && array_key_exists( '0', $meta_value ) ) {
					$meta_value = $meta_value[0];
				}

				update_post_meta( $new_post_id, $meta_key, $meta_value );
			}
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	}

	/**
	 * Add placeholders for all positions even if they are empty.
	 *
	 * @return void
	 */
	public static function add_placeholder_positions() {
		$position_groups = self::get_positions();

		foreach ( $position_groups as $positions ) {
			if ( empty( $positions ) || ! is_array( $positions ) ) {
				continue;
			}

			foreach ( $positions as $position_value => $position ) {
				$position_hook = self::get_position_hook( $position_value );
				$priority      = self::get_position_priority( $position_value );

				if ( empty( $position_hook ) ) {
					continue;
				}

				add_action(
					$position_hook,
					function() use ( $position_value ) {
						self::print_position( $position_value );
					},
					$priority
				);
			}
		}
	}

	/**
	 * Get position hook from the option value.
	 *
	 * @param string $hook_value Hook option value.
	 *
	 * @return string|false
	 */
	public static function get_position_hook( $hook_value ) {
		if ( false === strpos( $hook_value, ':' ) ) {
			return false;
		}

		$arr = explode( ':', $hook_value );

		return isset( $arr[0] ) ? $arr[0] : false;
	}

	/**
	 * Get position priority.
	 *
	 * @param string $postion_value Position value.
	 *
	 * @return string|false
	 */
	public static function get_position_priority( $postion_value ) {
		if ( false === strpos( $postion_value, ':' ) ) {
			return 10;
		}

		$arr = explode( ':', $postion_value );

		return isset( $arr[1] ) ? $arr[1] : 10;
	}

	/**
	 * Get position wrap.
	 *
	 * @param string $postion_value Position value.
	 *
	 * @return string|false
	 */
	public static function get_position_wrap( $postion_value ) {
		if ( false === strpos( $postion_value, ':' ) ) {
			return false;
		}

		$arr = explode( ':', $postion_value );

		return isset( $arr[2] ) ? $arr[2] : false;
	}

	/**
	 * Add elements to order review fragments.
	 *
	 * @param array $fragments Fragments.
	 *
	 * @return array
	 */
	public static function add_elements_to_order_review_fragments( $fragments ) {
		$new_hash = WC()->cart->get_cart_hash();
		$old_hash = WC()->session->get( 'flux_cart_hash_cache' );

		$position_groups = self::get_positions();

		foreach ( $position_groups as $positions ) {
			if ( empty( $positions ) || ! is_array( $positions ) ) {
				continue;
			}

			foreach ( $positions as $positon_hook => $position ) {
				ob_start();
				self::print_position( $positon_hook );
				$fragment = ob_get_clean();

				$class                     = self::get_hook_class( $positon_hook );
				$fragments[ '.' . $class ] = $fragment;
			}
		}

		WC()->session->set( 'flux_cart_hash_cache', $new_hash );

		return $fragments;
	}

	/**
	 * Sanitize hook class.
	 *
	 * @param string $class Class.
	 *
	 * @return string
	 */
	public static function get_hook_class( $class ) {
		return sprintf( 'flux-element--%s', str_replace( ':', '-', $class ) );
	}

	/**
	 * Print all elements for the given position.
	 *
	 * @param string $position_value Hook value.
	 *
	 * @return void
	 */
	public static function print_position( $position_value ) {
		$wrap = self::get_position_wrap( $position_value );

		if ( 'tr_td' === $wrap ) {
			echo sprintf( '<tr class="flux-element flux-element--table %s"><td colspan="2">', esc_attr( self::get_hook_class( $position_value ) ) );
		} else {
			echo sprintf( '<div class="flux-element %s">', esc_attr( self::get_hook_class( $position_value ) ) );
		}

		$elements = self::get_elements_for_position( $position_value );
		foreach ( $elements as $element ) {
			self::print_content( $element );
		}

		if ( 'tr_td' === $wrap ) {
			echo '</td></tr>';
		} else {
			echo '</div>';
		}
	}

	/**
	 * Get checkout elements for the given given position.
	 *
	 * @param string $position Position.
	 *
	 * @return array
	 */
	public static function get_elements_for_position( $position ) {
		$elements_cache = WC()->session->get( 'flux_checkout_elements' );
		$hash           = WC()->cart->get_cart_hash();
		$wc_ajax        = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// Use cache only for `update_order_review` ajax call.
		if (
			'update_order_review' === $wc_ajax
			&& ! empty( $elements_cache[ $hash ] )
			&& array_key_exists( $position, $elements_cache[ $hash ] )
		) {
			return $elements_cache[ $hash ][ $position ];
		}

		$posts  = self::get_checkout_element_posts();
		$result = array();

		foreach ( $posts as $post ) {
			$element            = self::get_checkout_element( $post->ID );
			$data               = $element->get_element_data();

			$conditions_enabled = $data['settings']['enable_rules'];
			$position_meta      = $data['position']['value'];

			if ( $position !== $position_meta ) {
				continue;
			}

			if ( empty( $conditions_enabled ) ) {
				$result [] = $element;
				continue;
			}

			$match          = $element->do_rules_match();
			$rule_condition = $data['settings']['rule_condition'];

			if ( ( 'hide' === $rule_condition && true === $match ) || ( 'show' === $rule_condition && false === $match ) ) {
				continue;
			}

			$result [] = $element;
		}

		// Format cache.
		if ( ! isset( $elements_cache[ $hash ] ) ) {
			$elements_cache = array(
				$hash => array(
					$position => $result,
				),
			);
		} else {
			$elements_cache[ $hash ][ $position ] = $result;
		}

		// Save cache.
		WC()->session->set( 'flux_checkout_elements', $elements_cache );

		return $result;
	}

	/**
	 * Get all checkout elements posts.
	 *
	 * @return array
	 */
	public static function get_checkout_element_posts() {
		static $checkout_elements_posts = false;

		if ( false !== $checkout_elements_posts ) {
			return $checkout_elements_posts;
		}

		$args = array(
			'post_type'              => self::$post_type_key,
			'posts_per_page'         => 100,
			'post_status'            => 'publish',
			'suppress_filters'       => false,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		);

		$checkout_elements_posts = get_posts( $args );

		return $checkout_elements_posts;
	}

	/**
	 * Print Content.
	 *
	 * @param Iconic_Flux_Checkout_Element $element Checkout Element Object.
	 */
	public static function print_content( $element ) {
		if ( empty( $element->post ) ) {
			return;
		}

		/**
		 * Before content for a single Checkout Element is printed.
		 *
		 * @param Iconic_Flux_Checkout_Element $element Checkout Element Object.
		 *
		 * @since 2.10.0
		 */
		do_action( 'flux_elements_before_print_content', $element );

		global $post;

		/**
		 * 1. the_content is the most flexible and safest way of printing the content. We tried wp_kses but it's
		 * not the most flexible.
		 *
		 * 2. We cannot directly update the global variable because of the PHPCS errors. So we utilize reference
		 * to bypass the PHPCS error.
		 *
		 * 3. We store backup of the current post in $post_backup and restore it after content has been printed.
		 */
		$post_backup = $post;
		$post_ref    = &$post;
		$post_ref    = $element->post;

		setup_postdata( $post_ref );

		the_content();

		$post_ref = $post_backup;
		wp_reset_postdata();

		/**
		 * After content for a single Checkout Element is printed.
		 *
		 * @param Iconic_Flux_Checkout_Element $element Checkout Element Object.
		 *
		 * @since 2.10.0
		 */
		do_action( 'flux_elements_after_print_content', $element );
	}

	/**
	 * Add meta box.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'flux_checkout_elements',
			__( 'Flux Checkout Elements', 'flux-checkout' ),
			array( __CLASS__, 'render_meta_box' ),
			self::$post_type_key,
			'advanced',
			'default'
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_meta_box( $post ) {
		$element            = self::get_checkout_element( $post->ID );
		$data               = $element->get_element_data( $post->ID );
		$selected_position  = $data['position']['value'];
		$settings           = $data['settings'];
		$product_categories = self::get_product_category_options();

		include ICONIC_FLUX_PATH . '/templates/admin/checkout-elements/checkout-elements-metabox.php';
	}

	/**
	 * Save meta box.
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save_meta_box( $post_id ) {
		$position = filter_input( INPUT_POST, 'fce_position', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$settings = filter_input( INPUT_POST, 'fce_settings', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$nonce    = filter_input( INPUT_POST, 'fce_metabox_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $settings ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'fce_metabox_nonce' ) ) {
			return;
		}

		$position = sanitize_text_field( $position );
		$settings = sanitize_text_field( $settings );
		$settings = html_entity_decode( $settings );

		$element = self::get_checkout_element( $post_id );
		$element->save( $position, $settings );
	}

	/**
	 * Insert checkout elements CTP menu after Flux checkout.
	 */
	public static function insert_checkout_elements_menu_after_flux() {
		global $menu, $submenu;

		$flux_index              = self::find_menu_index( 'iconic-flux-settings' );
		$checkout_elements_index = self::find_menu_index( 'edit.php?post_type=checkout_elements' );

		if ( false === $flux_index || false === $checkout_elements_index ) {
			return;
		}

		$checkout_elements_menu    = $submenu['woocommerce'][ $checkout_elements_index ];
		$checkout_elements_menu[4] = 'flux-checkout-elements-menu';

		unset( $submenu['woocommerce'][ $checkout_elements_index ] );
		array_splice( $submenu['woocommerce'], $flux_index, 0, array( $checkout_elements_menu ) );
	}

	/**
	 * Searches the WooCommerce submenu by the given menu slug.
	 *
	 * @param string $menu_slug Menu slug.
	 */
	public static function find_menu_index( $menu_slug ) {
		global $submenu;

		$result_index = false;

		if ( empty( $submenu['woocommerce'] ) ) {
			return false;
		}

		foreach ( $submenu['woocommerce'] as $index => $item ) {
			if ( $menu_slug === $item[2] ) {
				$result_index = $index;
				break;
			}
		}

		return $result_index;
	}

	/**
	 * Register CPT.
	 */
	public static function register_post_type() {
		$args = array(
			'label'               => __( 'Elements', 'flux-checkout' ),
			'labels'              => array(
				'add_new'      => __( 'Add new Element', 'flux-checkout' ),
				'add_new_item' => __( 'Add new Element', 'flux-checkout' ),
			),
			'supports'            => array( 'title', 'editor', 'page-attributes', 'elementor' ),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => false,
			'publicly_queryable'  => self::is_publicly_queryable(),
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'woocommerce',
			'menu_position'       => 10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'feeds'               => false,
			'rewrite'             => array( 'slug' => 'checkout-elements' ),
		);

		register_post_type( self::$post_type_key, $args );
	}

	/**
	 * Returns an array of positions for different elements in a checkout process.
	 */
	public static function get_positions() {
		$positions = array(
			'Common'                => array(
				'flux_before_layout:10:no_wrap'       => array(
					'text' => __( 'Checkout Header', 'flux-checkout' ),
					'icon' => 'common_header.svg',
				),
				'flux_after_layout:10:no_wrap'        => array(
					'text' => __( 'Checkout Footer', 'flux-checkout' ),
					'icon' => 'common_footer.svg',
				),
				'flux_before_header:10:no_wrap'       => array(
					'text' => __( 'Before Flux Header', 'flux-checkout' ),
					'icon' => 'common_before_header.svg',
				),
				'flux_after_header:10:no_wrap'        => array(
					'text' => __( 'After Flux Header', 'flux-checkout' ),
					'icon' => 'common_after_header.svg',
				),
				'flux_before_step_content:10:no_wrap' => array(
					'text' => __( 'Before All Fields', 'flux-checkout' ),
					'icon' => 'before_all_fields.svg',
				),
				'flux_after_step_content:10:no_wrap'  => array(
					'text' => __( 'After All Fields', 'flux-checkout' ),
					'icon' => 'after_all_fields.svg',
				),
			),
			'Customer Details Step' => array(
				'flux_before_step_content_details:10:no_wrap' => array(
					'text' => __( 'Before Fields', 'flux-checkout' ),
					'icon' => 'before_all_fields.svg',
				),
				'flux_after_step_content_details:10:no_wrap'  => array(
					'text' => __( 'After Fields', 'flux-checkout' ),
					'icon' => 'after_all_fields.svg',
				),
			),
			'Address Step'          => array(
				'flux_before_step_content_address:10:no_wrap' => array(
					'text' => __( 'Before Fields', 'flux-checkout' ),
					'icon' => 'before_all_fields.svg',
				),
				'woocommerce_before_order_notes:10:no_wrap' => array(
					'text' => __( 'Before Order Notes', 'flux-checkout' ),
					'icon' => 'before_order_notes.svg',
				),
				'woocommerce_after_order_notes:10:no_wrap' => array(
					'text' => __( 'After Order Notes', 'flux-checkout' ),
					'icon' => 'after_order_notes.svg',
				),
				'flux_after_step_content_address:10:no_wrap' => array(
					'text' => __( 'After Fields', 'flux-checkout' ),
					'icon' => 'after_all_fields.svg',
				),
			),
			'Payment Step'          => array(
				'flux_before_step_content_payment:10:no_wrap' => array(
					'text' => __( 'Before Fields', 'flux-checkout' ),
					'icon' => 'before_all_fields.svg',
				),
				'woocommerce_review_order_before_payment:10:no_wrap' => array(
					'text' => __( 'Before Payment', 'flux-checkout' ),
					'icon' => 'before_payment.svg',
				),
				'woocommerce_review_order_before_submit:10:no_wrap'  => array(
					'text' => __( 'Before Submit button', 'flux-checkout' ),
					'icon' => 'before_submit.svg',
				),
				'woocommerce_review_order_after_submit:10:no_wrap'   => array(
					'text' => __( 'After Submit button', 'flux-checkout' ),
					'icon' => 'after_submit.svg',
				),
			),
			'Order review/Sidebar'  => array(
				'woocommerce_review_order_before_cart_contents:10:tr_td' => array(
					'text' => __( 'Before Cart Contents', 'flux-checkout' ),
					'icon' => 'before_cart_contents.svg',
				),
				'woocommerce_review_order_after_cart_contents:1:tr_td' => array(
					'text' => __( 'Before Coupon', 'flux-checkout' ),
					'icon' => 'before_coupon.svg',
				),
				'flux_after_coupon_form:10:tr_td' => array(
					'text' => __( 'After Coupon', 'flux-checkout' ),
					'icon' => 'after_coupon.svg',
				),
				'woocommerce_review_order_before_order_total:10:tr_td' => array(
					'text' => __( 'Before total', 'flux-checkout' ),
					'icon' => 'before_total.svg',
				),
				'woocommerce_review_order_after_order_total:10:tr_td' => array(
					'text' => __( 'After total', 'flux-checkout' ),
					'icon' => 'after_total.svg',
				),
			),
			'Thank you Page'        => self::get_thankyou_page_positions(),
		);

		/**
		 * Checkout elements positions.
		 *
		 * @param array $positions Positions.
		 *
		 * @since 2.7.0.
		 */
		return apply_filters( 'flux_checkout_elements_positions', $positions );
	}

	/**
	 * Get position label by key.
	 *
	 * @param string $position_key Position Key.
	 *
	 * @return string
	 */
	public static function get_position_label( $position_key ) {
		$positions = self::get_positions();

		foreach ( $positions as $category_label => $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}

			foreach ( $category as $loop_position_key => $position ) {
				if ( $loop_position_key === $position_key ) {
					return sprintf( '%s &rsaquo; %s', $category_label, $position['text'] );
				}
			}
		}

		return false;
	}

	/**
	 * Get thank you page positions.
	 *
	 * @return array
	 */
	public static function get_thankyou_page_positions() {
		// Return differen settings based on if customer has enabled the thank you page.
		if ( '1' === Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] ) {
			return array(
				'flux_thankyou_before_order_status:10:no_wrap' => array(
					'text' => __( 'Before Thank you section', 'flux-checkout' ),
					'icon' => 'ty_e_before_ty_section.svg',
				),
				'flux_thankyou_before_map:10:no_wrap' => array(
					'text' => __( 'Before Map', 'flux-checkout' ),
					'icon' => 'ty_e_before_before_map.svg',
				),
				'flux_thankyou_before_customer_details:10:no_wrap' => array(
					'text' => __( 'Before Customer details', 'flux-checkout' ),
					'icon' => 'ty_e_before_customer_details.svg',
				),
				'flux_thankyou_before_downloads:10:no_wrap' => array(
					'text' => __( 'Before Downloads', 'flux-checkout' ),
					'icon' => 'ty_e_before_downloads.svg',
				),
				'flux_thankyou_before_product_details:10:no_wrap' => array(
					'text' => __( 'Before product details', 'flux-checkout' ),
					'icon' => 'ty_e_before_product_details.svg',
				),
				'flux_thankyou_after_product_details:10:no_wrap' => array(
					'text' => __( 'After product details', 'flux-checkout' ),
					'icon' => 'ty_e_after_product_details.svg',
				),
			);
		} else {
			return array(
				'woocommerce_before_thankyou:10:no_wrap' => array(
					'text' => __( 'Before Thank you section', 'flux-checkout' ),
					'icon' => 'ty_d_before_ty.svg',
				),
				'woocommerce_thankyou:5:no_wrap' => array(
					'text' => __( 'Before Order details', 'flux-checkout' ),
					'icon' => 'ty_d_before_order_section.svg',
				),
				'woocommerce_order_details_after_order_table:10:no_wrap' => array(
					'text' => __( 'After Order details', 'flux-checkout' ),
					'icon' => 'ty_d_after_order_details.svg',
				),
				'woocommerce_thankyou:15:no_wrap' => array(
					'text' => __( 'After Customer address', 'flux-checkout' ),
					'icon' => 'ty_d_after_address.svg',
				),
			);
		}
	}

	/**
	 * Get all product categories in the [{label:'', code: ''}] format.
	 *
	 * @return array
	 */
	public static function get_product_category_options() {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			return array();
		}

		$formatted = array();
		foreach ( $categories as $category ) {
			$formatted [] = array(
				'label' => $category->name,
				'code'  => $category->term_id,
			);
		}

		return $formatted;
	}

	/**
	 * Get checkout element object.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return Iconic_Flux_Checkout_Element
	 */
	public static function get_checkout_element( $post_id ) {
		static $elements = array();

		if ( isset( $elements[ $post_id ] ) ) {
			return $elements[ $post_id ];
		}

		$elements[ $post_id ] = new Iconic_Flux_Checkout_Element( $post_id );

		return $elements[ $post_id ];
	}

	/**
	 * Disable the WP welcome guide on the block editor if we are on the checkout elements page.
	 *
	 * @return void
	 */
	public static function disable_wp_welcome_guide_for_elements() {
		$screen = get_current_screen();

		if ( 'checkout_elements' !== $screen->post_type ) {
			return;
		}

		wp_add_inline_script(
			'wp-data',
			"window.onload = function() {
				const selectPost = wp.data.select( 'core/edit-post' );
				const isWelcomeGuidePost = selectPost.isFeatureActive( 'welcomeGuide' );
				
				if ( isWelcomeGuidePost ) {
					wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' );
				}
			}"
		);

		if ( ! self::has_seen_welcome_screen() ) {
			wp_add_inline_script(
				'wp-data',
				'window.fce_show_welcome_screen = 1'
			);
		}
	}

	/**
	 * Check if the user has seen the welcome screen.
	 *
	 * @return bool
	 */
	public static function has_seen_welcome_screen() {
		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return true;
		}

		$seen = get_user_meta( $user_id, 'fce_welcome_guide_seen', true );

		return empty( $seen ) ? false : true;
	}

	/**
	 * Allow noscript tag on checkout page.
	 *
	 * This is needed for the noscript tags added by the lazy-loading plugin
	 * to fallback to the img tags when JS is disabled.
	 * Without this, double images appear on the checkout page.
	 *
	 * @param array  $tags    Allowed tags.
	 * @param string $context Context.
	 *
	 * @return array
	 */
	public static function allow_noscript_on_checkout( $tags, $context ) {
		$tags['noscript'] = array();
		return $tags;
	}

	/**
	 * Conditionally modify public_queryable for checkout elements
	 * So it doesn't show for SEO.
	 *
	 * @return bool
	 */
	public static function is_publicly_queryable() {
		$et_fb             = filter_input( INPUT_GET, 'et_fb' );
		$post_type         = filter_input( INPUT_GET, 'post_type' );
		$page              = filter_input( INPUT_GET, 'page' );
		$post              = filter_input( INPUT_GET, 'post' );
		$action            = filter_input( INPUT_GET, 'action' );
		$elementor_preview = filter_input( INPUT_GET, 'elementor-preview' );
		$request_uri       = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL ) ?? '';
		$request_uri       = basename( $request_uri ?? '' );

		if (
			! empty( $et_fb )
			||
			self::$post_type_key === $post_type
			||
			'options-permalink.php' === $request_uri
			||
			in_array( $page, array( 'et_divi_options', 'elementor' ), true )
			||
			'elementor' === $action
			||
			! empty( $elementor_preview )
		) {
			return true;
		}

		if ( ! empty( $post ) ) {
			$post = get_post( $post );

			if ( empty( $post ) ) {
				return false;
			}

			if ( 'checkout_elements' === $post->post_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Remove View action.
	 *
	 * @param array   $actions Actions.
	 * @param WP_Post $post    Post.
	 *
	 * @return array
	 */
	public static function remove_checkout_elements_view_action( $actions, $post ) {
		if ( 'checkout_elements' !== $post->post_type ) {
			return $actions;
		}

		unset( $actions['view'] );

		return $actions;
	}
}
