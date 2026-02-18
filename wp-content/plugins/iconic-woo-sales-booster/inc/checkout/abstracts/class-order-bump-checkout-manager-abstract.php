<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Order_Bump_Checkout_Manager_Abstract.
 *
 * @class    Iconic_WSB_Order_Bump_Checkout_Manager_Abstract
 * @version  1.0.0
 * @category Abstract Class
 * @author   Iconic
 */
abstract class Iconic_WSB_Order_Bump_Checkout_Manager_Abstract {
	/**
	 * Validation Errors.
	 *
	 * @var array
	 */
	protected $validation_errors = array();

	/**
	 * Singleton
	 *
	 * @return self
	 */
	final public static function get_instance() {
		static $instances = array();

		$called_class = get_called_class();

		if ( ! isset( $instances [ $called_class ] ) ) {
			$instances[ $called_class ] = new $called_class();
		}

		return $instances[ $called_class ];
	}

	/**
	 * Iconic_WSB_Order_Bump_Checkout_Manager_Abstract constructor.
	 */
	protected function __construct() {
		$this->common_hooks();
	}

	/**
	 * Register common hooks
	 */
	protected function common_hooks() {
		add_action( 'init', array( $this, 'registerCPT' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_bump_edit_section' ) );
		add_filter( 'post_row_actions', array( $this, 'remove_inline_actions' ), 10, 2 );
		add_filter( 'request', array( $this, 'order_by_priority' ) );
		add_filter( 'manage_' . $this->get_post_type() . '_posts_columns', array( $this, 'remove_date_column' ) );
		// @phpstan-ignore-next-line (Changing the save_bump return is a breaking change)
		add_action( 'save_post_' . $this->get_post_type(), array( $this, 'save_bump' ), 1, 3 );
		add_filter( 'post_updated_messages', array( $this, 'change_bump_messages' ) );
		add_action( 'wp_ajax_iconic_wsb_handle_sorting_bump_checkout_product', array( $this, 'handle_sorting' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_bump_price' ), 100 );

		// Statistics columns.
		add_filter( 'manage_' . $this->get_post_type() . '_posts_columns', array( $this, 'add_statistics_columns' ), 99, 1 );
		add_action(
			'manage_' . $this->get_post_type() . '_posts_custom_column',
			array( $this, 'render_statistics_columns' ),
			10,
			2
		);

		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'bump_purchased' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'check_for_removing' ), 100 );
		add_filter( 'months_dropdown_results', array( $this, 'remove_date_filter' ), 1, 2 );

		add_action( 'woocommerce_thankyou', array( $this, 'remove_viewed_bumps' ) );

		add_filter( 'post_row_actions', array( $this, 'add_duplicate_row_action_link' ), 10, 2 );
		add_action( 'admin_action_duplicate_' . $this->get_post_type(), array( $this, 'handle_duplicate_action' ), 10 );
	}

	/**
	 * Remove all view bumps. Let increase statistics again
	 */
	public function remove_viewed_bumps() {
		if ( property_exists( WC()->session, 'set' ) ) {
			WC()->session->set( 'iconic_wsb_viewed_bumps', array() );
		}
	}

	/**
	 * Remove filter by date on bumps list page
	 *
	 * @param array  $dates Dates.
	 * @param string $post_type Post Type.
	 *
	 * @return array
	 */
	public function remove_date_filter( $dates, $post_type ) {
		if ( $post_type === $this->get_post_type() ) {
			return array();
		}

		return $dates;
	}

	/**
	 * Remove bumps if they are not suitable
	 *
	 * @param WC_cart $cart Cart Object.
	 */
	public function check_for_removing( $cart ) {
		$all_cart_products = array();
		$offered_products  = array();

		// Remove the products when Order bump is enabled for 'specific' products.
		foreach ( $cart->cart_contents as $cart_item ) {
			$all_cart_products[] = $cart_item['product_id'];

			if ( isset( $cart_item['bump_id'] ) ) {
				$offered_products[] = $cart_item['product_id'];
				$bump               = $this->get_order_bump( $cart_item['bump_id'] );

				if ( empty( $bump ) ) {
					continue;
				}

				$bump_product_offer                        = $bump->get_product_offer();
				$is_cart_item_equals_to_bump_product_offer = empty( $bump_product_offer ) ? false : $bump_product_offer->get_id() === $cart_item['product_id'];

				if ( ! $is_cart_item_equals_to_bump_product_offer && ! $bump->is_suitable( false ) ) {
					$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
					Iconic_WSB_Cart::remove_from_cart( $product_id );
					wc_add_notice(
						sprintf(
							// Translators: Product name.
							__( '%s was removed from cart, because the offer is no longer valid.' ),
							$cart_item['data']->get_title()
						)
					);
				}
			}
		}

		// Remove the discounted product, if that's the only product in cart.
		$non_offer_products = array_diff( $all_cart_products, $offered_products );

		if ( ! empty( $non_offer_products ) || empty( $offered_products ) ) {
			return;
		}

		foreach ( $offered_products as $product_id ) {
			// If the offer has been applied to itself, do not remove.
			if ( array_count_values( $all_cart_products )[ $product_id ] > 1 ) {
				continue;
			}

			Iconic_WSB_Cart::remove_from_cart( $product_id );
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			wc_add_notice(
				sprintf(
					// Translators: Product name.
					__( '%s was removed from cart, because the offer is no longer valid.' ),
					$product->get_name()
				)
			);
		}
	}

	/**
	 * Increase statistic purchasing
	 *
	 * @param WC_Order_Item $item Item.
	 * @param string        $cart_item_key Cart Item Key.
	 */
	public function bump_purchased( $item, $cart_item_key ) {
		if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

			if ( empty( $cart_item['bump_id'] ) ) {
				return;
			}

			$bump = $this->get_order_bump( $cart_item['bump_id'] );

			if ( empty( $bump ) ) {
				return;
			}

			$bump->increase_purchases_count();

			if ( empty( $cart_item['bump_price'] ) ) {
				return;
			}

			$bump->increase_added_revenue( $cart_item['bump_price'] );
		}
	}

	/**
	 * Increase viewing bump if user is seeing it in first time
	 *
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump Bump.
	 */
	public function view( $bump ) {
		$viewed_bumps = WC()->session->get( 'iconic_wsb_viewed_bumps', array() );

		if ( ! in_array( $bump->get_id(), $viewed_bumps ) ) {
			$bump->increase_impression_count();
			$viewed_bumps[] = $bump->get_id();

			WC()->session->set( 'iconic_wsb_viewed_bumps', $viewed_bumps );
		}
	}

	/**
	 * Add statistics column to bump admin table
	 *
	 * @param array $columns Columns.
	 *
	 * @return mixed
	 */
	public function add_statistics_columns( $columns ) {
		$columns['impression']    = __( 'Impressions', 'iconic-wsb' );
		$columns['purchases']     = __( 'Purchases', 'iconic-wsb' );
		$columns['added-revenue'] = __( 'Added Revenue', 'iconic-wsb' );
		$columns['conversion']    = __( 'Conversions', 'iconic-wsb' );
		$columns['draggable']     = '';

		return $columns;
	}

	/**
	 * Render Statistics Columns.
	 *
	 * @param string $column Column.
	 * @param int    $post_id Post ID.
	 */
	public function render_statistics_columns( $column, $post_id ) {
		$bump = $this->get_order_bump( $post_id );

		switch ( $column ) {
			case 'impression':
				echo $bump->get_impression_count();
				break;
			case 'purchases':
				echo $bump->get_purchases_count();
				break;
			case 'conversion':
				$this->conversation_column( $bump );
				break;
			case 'added-revenue':
				echo wp_kses_post( wc_price( $bump->get_added_revenue() ) );
				break;
			case 'draggable':
				echo '<span class="dashicons dashicons-menu iconic-wsb-sortable"></span>';
				break;
		}
	}

	/**
	 * Render conversation column
	 *
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump Bump.
	 */
	public function conversation_column( $bump ) {
		$rate = $bump->get_conversion_rate() * 100;

		/**
		 * Filter description.
		 *
		 * @since 1.17.0
		 * @hook iconic_wsb_checkout_bump_target_rate
		 * @param float                                   $target_rate The target rate.
		 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump        The bump object.
		 * @return float New value
		 */
		$target_rate = apply_filters( 'iconic_wsb_checkout_bump_target_rate', (float) $bump->get_target_rate(), $bump );

		$class           = 'conversation-mark--normal';
		$title_attribute = '';

		if ( 0 < $target_rate ) {
			// Translators: %1$s - the target rate.
			$title_attribute = sprintf( __( 'Target: %1$s%%' ), $target_rate );
		}

		if ( 0 < $target_rate && $rate >= $target_rate ) {
			$class = 'conversation-mark--good';
		}

		$conversation = number_format( $rate, 2, '.', '2' ) . '%';

		echo sprintf(
			'<span class="conversation-mark %1$s" title="%2$s"><span>%3$s</span></span>',
			esc_attr( $class ),
			esc_attr( $title_attribute ),
			esc_html( $conversation )
		);
	}

	/**
	 * Get Active Bumps.
	 *
	 * @return Iconic_WSB_Order_Bump_Checkout_Abstract[]
	 */
	public function get_active_bumps() {
		$bumps = get_posts(
			array(
				'numberposts' => - 1,
				'post_type'   => $this->get_post_type(),
				'post_status' => 'publish',
				'orderby'     => 'meta_value',
				'order'       => 'ASC',
				'meta_key'    => '_priority',
			)
		);

		return array_map(
			function ( $post ) {
				return $this->get_order_bump( $post->ID );
			},
			$bumps
		);
	}

	/**
	 * Return first suitable bump for user cart
	 *
	 * @return bool|Iconic_WSB_Order_Bump_Checkout_Abstract|mixed
	 */
	public function get_suitable_bump() {
		$checkout_bumps = $this->get_active_bumps();

		foreach ( $checkout_bumps as $checkout_bump ) {
			// Dont check the product in cart if same product is enabled as offer product.
			$check_for_cart = ! $checkout_bump->get_enable_bump_for_same_product();
			if ( $checkout_bump->is_suitable( $check_for_cart ) && $checkout_bump->is_valid() ) {
				return $checkout_bump;
			}
		}

		return false;
	}

	/**
	 * Calculate Bump PRice.
	 *
	 * @param WC_Cart $cart_object Cart Object.
	 */
	public function calculate_bump_price( $cart_object ) {
		foreach ( $cart_object->cart_contents as $key => $value ) {
			if ( isset( $value['bump_price'] ) ) {
				if ( $value['data'] instanceof WC_Product ) {
					$value['data']->set_price( $value['bump_price'] );
				}
			}
		}
	}

	/**
	 * Save data by calling setter with validation for requiring
	 *
	 * @param string   $field Field.
	 * @param mixed    $value Value.
	 * @param callable $method Method.
	 * @param bool     $required Required.
	 */
	protected function save_field( $field, $value, $method, $required = true ) {
		if ( empty( $value ) && $required ) {
			$this->validation_errors[] = sprintf( __( '%s is required', 'iconic-wsb' ), $field );

			return;
		}

		call_user_func( $method, $value );
	}

	/**
	 * Save priority when user change order for checkout order bump
	 */
	public function handle_sorting() {
		$output   = array();
		$replaces = array();

		parse_str( $_REQUEST['data'], $output );

		$post_type = $_REQUEST['post_type'];
		if ( $post_type !== $this->get_post_type() ) {
			return;
		}

		$before_posts = $_REQUEST['posts'];
		$after_posts  = $output['post'];

		foreach ( $after_posts as $key => $post_id ) {
			// Before post on this position
			$before_post_id = $before_posts[ array_search( $post_id, $after_posts ) ];
			// If post dont change position
			if ( $before_post_id == $post_id ) {
				continue;
			}

			$before_order_bump = $this->get_order_bump( $before_post_id );
			$order_bump        = $this->get_order_bump( $post_id );

			$replaces[] = array(
				'order_bump' => $order_bump,
				'priority'   => $before_order_bump->get_priority(),
			);
		}

		foreach ( $replaces as $replace ) {
			if ( $replace['order_bump'] instanceof Iconic_WSB_Order_Bump_Checkout_Abstract ) {
				$replace['order_bump']->set_priority( $replace['priority'] );
			}
		}

		wp_send_json( array( 'posts' => $after_posts ) );
	}

	/**
	 * Save Product Step.
	 *
	 * @param array                                   $data Data.
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump Bump.
	 */
	protected function save_product_step( $data, $bump ) {
		$this->save_field(
			__( 'Display type', 'iconic-wsb' ),
			$data['iconic_wsb_display_type'],
			array( $bump, 'set_display_type' )
		);
		$this->save_field(
			__( 'Apply when', 'iconic-wsb' ),
			$data['iconic_wsb_apply_when_specific'],
			array( $bump, 'set_apply_when_specific' )
		);

		$enable_for_same_product = isset( $data['iconic_wsb_enable_bump_for_same_product'] ) ? true : false;
		$this->save_field(
			__( 'Show Order Bump even if the offer product is already in the cart.', 'iconic-wsb' ),
			$enable_for_same_product,
			array( $bump, 'set_enable_bump_for_same_product' ),
			false
		);

		if ( isset( $data['iconic_wsb_specific_product'] ) ) {
			$this->save_field(
				__( 'Specific products', 'iconic-wsb' ),
				$data['iconic_wsb_specific_product'],
				array( $bump, 'set_specific_products' ),
				false
			);
		}

		if ( isset( $data['iconic_wsb_specific_categories'] ) ) {
			$this->save_field(
				__( 'Specific categories', 'iconic-wsb' ),
				$data['iconic_wsb_specific_categories'],
				array( $bump, 'set_specific_categories' ),
				false
			);
		}
	}

	/**
	 * Save Offer Step.
	 *
	 * @param array                                   $data Data.
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump Bump.
	 */
	protected function save_offer_step( $data, $bump ) {
		if ( 'percentage' === $data['iconic_wsb_discount_type'] && $data['iconic_wsb_discount'] > 100 ) {
			$this->validation_errors[] = __( 'Discount cannot be more than 100%', 'iconic-wsb' );

			return;
		} elseif ( $data['iconic_wsb_discount_type'] === 'simple' ) {
			$product = wc_get_product( $data['iconic_wsb_product_offer'] );

			if ( $product && $product->get_price() < $data['iconic_wsb_discount'] ) {
				$this->validation_errors[] = __( 'Discount cannot be more than product price', 'iconic-wsb' );

				return;
			}
		}

		if ( ( ! isset( $data['iconic_wsb_enable_bump_for_same_product'] ) || ! $data['iconic_wsb_enable_bump_for_same_product'] ) && 'specific' === $data['iconic_wsb_display_type'] && in_array(
			$data['iconic_wsb_product_offer'],
			$data['iconic_wsb_specific_product']
		) ) {
			$this->validation_errors[] = __(
				'Offered product cannot be in list of products used for condition',
				'iconic-wsb'
			);

			return;
		}

		$this->save_field(
			__( 'Product offer', 'iconic-wsb' ),
			$data['iconic_wsb_product_offer'],
			array( $bump, 'set_product_offer' )
		);
		$this->save_field(
			__( 'Discount', 'iconic-wsb' ),
			$data['iconic_wsb_discount'],
			array( $bump, 'set_discount' ),
			false
		);
		$this->save_field(
			__( 'Discount type', 'iconic-wsb' ),
			$data['iconic_wsb_discount_type'],
			array( $bump, 'set_discount_type' )
		);
		$this->save_field(
			__( 'Target rate', 'iconic-wsb' ),
			$data['iconic_wsb_target_rate'],
			array( $bump, 'set_target_rate' ),
			false
		);
		$this->save_field(
			__( 'Allow changing the quantity of the product offered on the cart page', 'iconic-wsb' ),
			$data['iconic_allow_product_offer_quantity_change'],
			array( $bump, 'set_allow_product_offer_quantity_change' ),
			false
		);
	}

	/**
	 * Change default updating messages
	 *
	 * @param array $messages Message.
	 *
	 * @return mixed
	 */
	public function change_bump_messages( $messages ) {
		global $post;

		if ( $post && $post->post_type == $this->get_post_type() ) {
			$messages['post'][1] = __( 'Updated.', 'iconic-wsb' );
			$messages['post'][6] = __( 'Created.', 'iconic-wsb' );
		}

		return $messages;
	}

	/**
	 * Check saving
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return bool
	 */
	protected function is_user_save_post( $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( ! empty( $_REQUEST['action'] ) && in_array(
			$_REQUEST['action'],
			array( 'delete', 'trash', 'untrash' )
		) ) {
			return false;
		}
		if ( $post->post_status == 'auto-draft' ) {
			return false;
		}

		return true;
	}

	/**
	 * Save Bump.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post Object.
	 * @param bool    $update  Should Update.
	 *
	 * @return bool
	 */
	public function save_bump( $post_id, $post, $update ) {
		if ( ! $this->is_user_save_post( $post ) ) {
			return false;
		}

		static $saved = false;

		$bump = $this->get_order_bump( $post_id );
		$data = $_REQUEST;

		if ( $bump && ! $saved ) {
			do_action_deprecated( 'iconic-wsb-before-save-checkout-bump', [ $bump, $update ], '1.24.0', 'iconic_wsb_before_save_checkout_bump' );

			/**
			 * Fires before save checkout bump.
			 *
			 * @since 1.24.0
			 * @hook iconic_wsb_before_save_checkout_bump
			 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump   The bump.
			 * @param bool                                    $update Should update.
			 */
			do_action( 'iconic_wsb_before_save_checkout_bump', $bump, $update );

			if ( ! $bump->get_priority() ) {
				$bump->generate_priority();
			}

			$this->save_product_step( $data, $bump );
			$this->save_offer_step( $data, $bump );
			$this->save_customization_step( $data, $bump );

			$saved = true;

			if ( ! empty( $this->validation_errors ) ) {
				$this->show_validation_errors();

				if ( ! $update ) {
					$bump->set_draft();
				}
			}

			do_action_deprecated( 'iconic-wsb-after-save-checkout-bump', [ $bump, $update ], '1.24.0', 'iconic_wsb_after_save_checkout_bump' );

			/**
			 * Fires after save checkout bump.
			 *
			 * @since 1.24.0
			 * @hook iconic_wsb_after_save_checkout_bump
			 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump   The bump.
			 * @param bool                                    $update Should update.
			 */
			do_action( 'iconic_wsb_after_save_checkout_bump', $bump, $update );
		}

		return true;
	}

	/**
	 * Render validation error after save bump.
	 */
	public function show_validation_errors() {
		$validation_errors = apply_filters_deprecated(
			'iconic-wsb-checkout-order-bump-validation-errors',
			[ $this->validation_errors ],
			'1.24.0',
			'iconic_wsb_checkout_order_bump_validation_errors'
		);

		/**
		 * Filter the checkout order bump validation errors.
		 *
		 * @since 1.24.0
		 * @hook iconic_wsb_checkout_order_bump_validation_errors
		 * @param array $validation_errors The validation errors.
		 */
		$validation_errors = apply_filters(
			'iconic_wsb_checkout_order_bump_validation_errors',
			$validation_errors
		);

		foreach ( $validation_errors as $error ) {
			Iconic_WSB_Notifier::flash( $error, Iconic_WSB_Notifier::ERROR );
		}
	}

	/**
	 * Remove Date Column.
	 *
	 * @param array $defaults Default Parameters.
	 *
	 * @return array
	 */
	public static function remove_date_column( $defaults ) {
		unset( $defaults['date'] );

		return $defaults;
	}

	/**
	 * Order bumps by priority meta key
	 *
	 * @param array $vars Variables.
	 *
	 * @return array
	 */
	public function order_by_priority( $vars ) {
		if ( isset( $vars['post_type'] ) && $vars['post_type'] === $this->get_post_type() && $vars['post_status'] !== 'draft' ) {
			$vars['orderby']  = array(
				'_priority' => 'ASC',
				'title'     => 'ASC',
			);
			$vars['meta_key'] = apply_filters_deprecated( // phpcs:ignore WordPress.DB.SlowDBQuery
				'iconic-wsb-checkout-order-bump-priority-key',
				[ '_priority' ],
				'1.24.0',
				'iconic_wsb_checkout_order_bump_priority_key'
			);

			/**
			 * Filter the order bump priority key.
			 *
			 * @since 1.24.0
			 * @hook iconic_wsb_checkout_order_bump_priority_key
			 * @param string $meta_key The meta key.
			 */
			$vars['meta_key'] = apply_filters( 'iconic_wsb_checkout_order_bump_priority_key', $vars['meta_key'] ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		return $vars;
	}

	/**
	 * Remove quick view and frontend view inline actions
	 *
	 * @param array   $actions Actions.
	 * @param WP_Post $post    Post Object.
	 *
	 * @return array
	 */
	public function remove_inline_actions( $actions, $post ) {
		if ( $post->post_type === $this->get_post_type() ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}

		return $actions;
	}

	/**
	 * Get Order Bump.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return Iconic_WSB_Order_Bump_Checkout_Abstract
	 */
	abstract public function get_order_bump( $post_id );

	/**
	 * Return managed post type
	 *
	 * @return string
	 */
	abstract public function get_post_type();

	/**
	 * Save Customization Step.
	 *
	 * @param array                                   $data Data.
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump Bump.
	 *
	 * @return mixed
	 */
	abstract protected function save_customization_step( $data, $bump );

	/**
	 * Register CPT for bump
	 */
	abstract public function registerCPT();

	/**
	 * Render section create\edit bump
	 *
	 * @param WP_Post $post
	 */
	abstract public function render_bump_edit_section( $post );

	/**
	 * Clone.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Check if the order bump can be duplicated.
	 *
	 * @return bool
	 */
	protected function action_to_duplicate_is_allowed() {
		/**
		 * Filter whether the action to duplicate an order bump should be allowed.
		 *
		 * @since 1.12.0
		 * @hook iconic_wsb_duplicate_is_allowed_to_order_bump
		 * @param  bool   $is_alowed  Default: true.
		 * @param  string $class_name The class name of the order bump.
		 * @return string New value
		 */
		$duplicate_is_allowed = apply_filters( 'iconic_wsb_duplicate_is_allowed_to_order_bump', true, get_called_class() );

		return $duplicate_is_allowed;
	}

	/**
	 * Add the duplicate row action link.
	 *
	 * @param  string[] $actions An array of row action links.
	 * @param  WP_Post  $post    The post object.
	 * @return string[]
	 */
	public function add_duplicate_row_action_link( $actions, $post ) {
		if ( ! $this->action_to_duplicate_is_allowed() ) {
			return $actions;
		}

		if ( $this->get_post_type() !== $post->post_type ) {
			return $actions;
		}

		$post_type_object = get_post_type_object( $this->get_post_type() );

		if ( empty( $post_type_object ) ) {
			return $actions;
		}

		$url_to_duplicate = wp_nonce_url(
			admin_url(
				sprintf(
					'edit.php?post_type=%1$s&action=duplicate_%1$s&amp;post=%2$d',
					$this->get_post_type(),
					$post->ID
				)
			),
			'wsb_duplicate_' . $this->get_post_type() . '_' . $post->ID
		);

		$actions['duplicate'] = sprintf(
			'<a href="%s" aria-label="%s" rel="permalink">%s</a>',
			$url_to_duplicate,
			// translators: %s - singular name of the Order Bump type.
			sprintf( __( 'Make a duplicate from this %s' ), $post_type_object->labels->singular_name ),
			__( 'Duplicate', 'iconic-wsb' )
		);

		return $actions;
	}

	/**
	 * Handle the action to duplicate the order bump.
	 *
	 * @return void
	 */
	public function handle_duplicate_action() {
		if ( ! $this->action_to_duplicate_is_allowed() ) {
			wp_die( esc_html__( "It's not possible to duplicate this post", 'iconic-wsb' ) );
		}

		$post_type_object = get_post_type_object( $this->get_post_type() );

		if ( empty( $post_type_object ) ) {
			wp_die( esc_html__( "It's not possible to duplicate this post", 'iconic-wsb' ) );
		}

		if ( empty( $_REQUEST['post'] ) ) {
			wp_die(
				sprintf(
					// translators: %s - Order Bump type.
					esc_html__( 'No %s to duplicate has been supplied!', 'iconic-wsb' ),
					esc_html( $post_type_object->labels->singular_name )
				)
			);
		}

		$order_bump_id = absint( $_REQUEST['post'] );

		check_admin_referer( 'wsb_duplicate_' . $this->get_post_type() . '_' . $order_bump_id );

		$args = array(
			'p'              => $order_bump_id,
			'post_type'      => $this->get_post_type(),
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		);

		$order_bump_query = new WP_Query( $args );

		if ( empty( $order_bump_query->get_posts()[0] ) ) {
			wp_die(
				sprintf(
					/* translators: %1$s: Order Bump type; %2$d: Order Bump ID*/
					esc_html__( '%1$s creation failed, could not find original post: %2$d', 'iconic-wsb' ),
					esc_html( $post_type_object->labels->singular_name ),
					esc_html( $order_bump_id )
				)
			);
		}

		$order_bump = get_called_class()::get_instance()->get_order_bump( $order_bump_id );

		if ( empty( $order_bump ) ) {
			wp_die(
				sprintf(
					/* translators: %1$s: Order Bump type; %2$d: Order Bump ID*/
					esc_html__( '%1$s creation failed, could not find original post: %2$d', 'iconic-wsb' ),
					esc_html( $post_type_object->labels->singular_name ),
					esc_html( $order_bump_id )
				)
			);
		}

		$metadata = $order_bump->get_all_metadata();

		$duplicated_post_args = array(
			/* translators: %s contains the name of the original post. */
			'post_title' => sprintf( esc_html__( '%s (Copy)', 'iconic-wsb' ), get_the_title( $order_bump_id ) ),
			'post_type'  => get_post_type( $order_bump_id ),
			'meta_input' => $metadata,
		);

		remove_action( 'save_post_' . $this->get_post_type(), array( get_called_class()::get_instance(), 'save_bump' ), 1 );

		$duplicated_post_id = wp_insert_post( $duplicated_post_args );

		// @phpstan-ignore-next-line (Changing the save_bump return is a breaking change)
		add_action( 'save_post_' . $this->get_post_type(), array( get_called_class()::get_instance(), 'save_bump' ), 1, 3 );

		// @phpstan-ignore-next-line (It seems a false positive: `is_wp_error(true) will always evaluate to false`)
		if ( is_wp_error( $duplicated_post_id ) ) {
			wp_die(
				sprintf(
					// translators: %s - Order Bump type.
					esc_html__( '%s creation failed', 'iconic-wsb' ),
					esc_html( $post_type_object->labels->singular_name )
				)
			);
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $duplicated_post_id ) );
		exit;
	}
}
