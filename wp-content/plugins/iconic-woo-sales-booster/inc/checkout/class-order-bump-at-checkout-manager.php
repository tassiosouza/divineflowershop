<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-checkout-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_At_Checkout_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_At_Checkout_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_At_Checkout_Manager extends Iconic_WSB_Order_Bump_Checkout_Manager_Abstract {

	protected $cart_meta_key = 'iconic_wsb_at_checkout';

	/**
	 * Run manager
	 */
	protected function __construct() {
		parent::__construct();

		// Clicks column.
		add_action( 'manage_' . $this->get_post_type() . '_posts_custom_column', array( $this, 'render_clicks_columns' ), 10, 2 );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'add_data_to_cart_item_store_api' ) );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'prepend_offer_text_to_cart_item' ), 50, 3 );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'add_data_value_attribute_to_option_elements' ), 50, 2 );
		add_filter( 'woocommerce_quantity_input_args', [ __CLASS__, 'update_product_offer_input_args' ], 10, 1 );
		add_filter( 'woocommerce_update_cart_validation', [ __CLASS__, 'prevent_updating_product_offer_quantity' ], 101, 4 );
		add_filter( 'woocommerce_store_api_product_quantity_editable', [ __CLASS__, 'update_product_offer_quantity_editable' ], 101, 3 );

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 * Get Supported Hooks
	 *
	 * @return array Array of hooks.
	 */
	public static function get_supported_hooks() {

		$hooks = array(
			'woocommerce_checkout_before_order_review'     => array(
				'label'             => esc_html__( 'Before Order Review', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => false,
			),
			'woocommerce_review_order_before_cart_contents' => array(
				'label'             => esc_html__( 'Before Cart Contents', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_after_cart_contents' => array(
				'label'             => esc_html__( 'After Cart Contents', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_before_shipping'     => array(
				'label'             => esc_html__( 'Before Shipping Information', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_after_shipping'      => array(
				'label'             => esc_html__( 'After Shipping Information', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_before_order_total'  => array(
				'label'             => esc_html__( 'Before Order Total', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_after_order_total'   => array(
				'label'             => esc_html__( 'After Order Total', 'iconic-wsb' ),
				'require_table_row' => true,
				'flux_support'      => true,
			),
			'woocommerce_review_order_before_payment'      => array(
				'label'             => esc_html__( 'Before Payment Information', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => true,
			),
			'woocommerce_review_order_before_submit'       => array(
				'label'             => esc_html__( 'Before "Place Order" button', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => true,
			),
			'woocommerce_review_order_after_submit'        => array(
				'label'             => esc_html__( 'After "Place Order" button', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => true,
			),
			'woocommerce_review_order_after_payment'       => array(
				'label'             => esc_html__( 'After Payment Information', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => true,
			),
			'woocommerce_checkout_after_order_review'      => array(
				'label'             => esc_html__( 'After Order Review', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => false,
			),
			'woocommerce_after_checkout_form'              => array(
				'label'             => esc_html__( 'After Checkout Form', 'iconic-wsb' ),
				'require_table_row' => false,
				'flux_support'      => false,
			),
		);

		return apply_filters( 'iconic_wsb_supported_hooks', $hooks );
	}

	/**
	 * Add clicks  column to bump admin table
	 *
	 * @param array $columns
	 *
	 * @return mixed
	 */
	public function add_statistics_columns( $columns ) {
		$columns        = parent::add_statistics_columns( $columns );
		$result_columns = array();

		foreach ( $columns as $column => $name ) {
			$result_columns[ $column ] = $name;

			if ( $column === 'impression' ) {
				$result_columns['clicks'] = __( 'Clicks', 'iconic-wsb' );
			}
		}

		return $result_columns;
	}

	/**
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_clicks_columns( $column, $post_id ) {
		if ( $column === 'clicks' ) {
			echo $this->get_order_bump( $post_id )->get_clicks_count();
		}
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		// Render order bump HTML on Page Load.
		add_action( 'template_redirect', array( $this, 'render_checkout' ) );
		// Render order bump HTML on AJAX.
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'render_checkout' ), 99 );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'handle_checkout_update' ) );
	}

	/**
	 * Render bump on checkout
	 */
	public function render_checkout() {
		$checkout_page_content = get_the_content( null, false, wc_get_page_id( 'checkout' ) );

		// Abort if Checkout Bump was added using the shortcode [iconic_wsb_order_bump].
		if ( has_shortcode( $checkout_page_content, 'iconic_wsb_order_bump' ) ) {
			return;
		}

		$bump = $this->get_suitable_bump();

		if ( $bump ) {
			$render_hook            = $bump->get_render_settings()['position'];
			$render_hook            = apply_filters( 'iconic_wsb_order_bump_position', $render_hook, $bump );
			$should_apply_table_row = $this->maybe_apply_table_row( $render_hook );

			add_filter(
				'render_block_' . self::map_woocommerce_hook_position_to_block_name( $render_hook ),
				array( $this, 'render_at_checkout_bump_in_checkout_block' )
			);

			add_action(
				$render_hook,
				function() use ( $bump ) {
					$this->include_order_bump_template( $bump );
				}
			);

			if ( $should_apply_table_row ) {
				add_action(
					'iconic_wsb_before_checkout_bump',
					function() {
						echo '<tr><td colspan="2">';
					}
				);

				add_action(
					'iconic_wsb_after_checkout_bump',
					function() {
						echo '</td></tr>';
					}
				);
			}
		}
	}

	/**
	 * Maybe Apply Table Row.
	 *
	 * Some hooks are rendered within the order table.
	 * If the correct table row wrap is not used for these,
	 * they are displayed before the table, and duplicated with AJAX.
	 *
	 * @param string $hook The hook we are adding the bump to.
	 *
	 * @return bool
	 */
	public function maybe_apply_table_row( $hook ) {

		$table_hooks = array_filter(
			$this->get_supported_hooks(),
			function( $item ) {
				return ( $item['require_table_row'] );
			}
		);

		$table_hooks = array_keys( $table_hooks );

		return apply_filters( 'iconic_wsb_order_bump_apply_table_row', in_array( $hook, $table_hooks, true ), $hook );
	}

	/**
	 * Handle ajax when on checkout trigger update_checkout
	 *
	 * @param string $post_data
	 */
	public function handle_checkout_update( $post_data ) {
		$data = array();

		parse_str( $post_data, $data );

		if ( ! empty( $data['iconic-wsb-checkout-bump-action'] ) && ! empty( $data['iconic-wsb-bump-id'] ) ) {

			$bump = self::get_order_bump( $data['iconic-wsb-bump-id'] );

			if ( $bump && $bump->is_suitable( false ) ) {

				$offer_product  = $bump->get_product_offer();
				$product_id     = ( isset( $data['iconic-wsb-checkout-variation-id'] ) && $data['iconic-wsb-checkout-variation-id'] ) ? $data['iconic-wsb-checkout-variation-id'] : $offer_product->get_id();
				$variation_data = null;

				if ( isset( $data['iconic-wsb-checkout-variation-data'] ) && $data['iconic-wsb-checkout-variation-data'] ) {
					$variation_data = json_decode( $data['iconic-wsb-checkout-variation-data'], true );
				}

				if ( $offer_product ) {

					$action = $data['iconic-wsb-checkout-bump-action'];

					if ( $action == 'add' ) {
						try {
							Iconic_WSB_Cart::remove_previously_added_item( $this->cart_meta_key );
							Iconic_WSB_Cart::add_to_cart(
								$product_id,
								1,
								array(
									'bump_price'           => $bump->get_discount_price( $product_id ),
									'bump_id'              => $bump->get_id(),
									"$this->cart_meta_key" => 1, // so we know this product was added in cart by checkout bump
								),
								$variation_data
							);

							$bump->increase_click_count();
						} catch ( Exception $e ) {
							wc_get_logger()->add( 'iconic_wsb_errors', $e->getMessage() );
						}
					} elseif ( $action == 'remove' ) {
						Iconic_WSB_Cart::remove_previously_added_item( $this->cart_meta_key );
					}
				}
			}
		}
	}


	/**
	 * @param array                             $data
	 * @param Iconic_WSB_Order_Bump_At_Checkout $bump
	 */
	public function save_customization_step( $data, $bump ) {
		$this->save_field(
			__( 'Checkbox text', 'iconic-wsb' ),
			$data['iconic_wsb_checkbox_text'],
			array( $bump, 'set_checkbox_text' )
		);
		$this->save_field(
			__( 'Bump description', 'iconic-wsb' ),
			$data['iconic_wsb_bump_description'],
			array( $bump, 'set_bump_description' )
		);
		$this->save_field(
			__( 'Attachment', 'iconic-wsb' ),
			$data['iconic_wsb_image_attachment_id'],
			array( $bump, 'set_custom_image_id' ),
			false
		);
		$this->save_field(
			__( 'Render settings', 'iconic-wsb' ),
			$data['iconic_wsb_render_settings'],
			array( $bump, 'set_render_settings' )
		);
	}

	/**
	 * Register checkout bump CTP
	 */
	public function registerCPT() {
		register_post_type(
			$this->get_post_type(),
			array(
				'labels'             => array(
					'name'               => __( 'Checkout Order Bumps', 'iconic-wsb' ),
					'singular_name'      => __( 'Checkout Order Bump', 'iconic-wsb' ),
					'add_new'            => __( 'Add New', 'iconic-wsb' ),
					'add_new_item'       => __( 'Add New Order Bump', 'iconic-wsb' ),
					'edit_item'          => __( 'Edit Order Bump', 'iconic-wsb' ),
					'new_item'           => __( 'New Order Bump', 'iconic-wsb' ),
					'view_item'          => __( 'View Order Bump', 'iconic-wsb' ),
					'search_items'       => __( 'Find Order Bump', 'iconic-wsb' ),
					'not_found'          => __( 'No order bumps were found.', 'iconic-wsb' ),
					'not_found_in_trash' => __( 'Not found in trash', 'iconic-wsb' ),
					'menu_name'          => __( 'Order Bumps', 'iconic-wsb' ),
				),
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => Iconic_WSB_Order_Bump::MENU_SLUG,
				'query_var'          => false,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => array( 'title' ),
			)
		);
	}

	/**
	 * @param WP_Post $post
	 */
	public function render_bump_edit_section( $post ) {

		if ( $post->post_type === $this->get_post_type() ) {

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_media();

			$bump = $this->get_order_bump( $post->ID );

			if ( $bump ) {
				global $iconic_wsb_class;

				$iconic_wsb_class->template->include_template(
					'admin/order-bump/checkout/edit.php',
					array(
						'bump'  => $bump,
						'steps' => array(
							'products'  => array(
								'title'    => __( 'Product(s)', 'iconic-wsb' ),
								'template' => 'admin/order-bump/checkout/steps/products.php',
							),
							'offer'     => array(
								'title'    => __( 'Offer', 'iconic-wsb' ),
								'template' => 'admin/order-bump/checkout/steps/offer.php',
							),
							'customize' => array(
								'title'    => __( 'Customize', 'iconic-wsb' ),
								'template' => 'admin/order-bump/checkout/at-checkout/steps/customization.php',
							),
						),
					)
				);
			}
		}
	}

	/**
	 * Return instance of checkout order bump
	 *
	 * @param int $id
	 *
	 * @return bool|Iconic_WSB_Order_Bump_At_Checkout
	 */
	public function get_order_bump( $id ) {
		try {
			require_once 'class-order-bump-at-checkout.php';

			$bump = new Iconic_WSB_Order_Bump_At_Checkout( $id );

		} catch ( Exception $e ) {
			return false;
		}

		return $bump;
	}

	/**
	 * Return managed post type
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'at_checkout_ob';
	}

	/**
	 * Change default updating messages
	 *
	 * @param array $messages
	 *
	 * @return mixed
	 */
	public function change_bump_messages( $messages ) {
		global $post;

		if ( $post && $post->post_type == $this->get_post_type() ) {
			$messages['post'][1]  = __( 'Order Bump Updated.', 'iconic-wsb' );
			$messages['post'][6]  = __( 'Order Bump Created.', 'iconic-wsb' );
			$messages['post'][10] = __( 'Order Bump draft updated.', 'iconic-wsb' );
		}

		return $messages;
	}

	/**
	 * Prepend offer text to cart item.
	 *
	 * Sometimes you may end up with two items the same if you add an offer to the basket.
	 * This function prepends the text '(offer)' so you can see which item is the offer.
	 *
	 * @param string $product_name  Product Name.
	 * @param array  $cart_item     Cart Item.
	 * @param string $cart_item_key Cart Item Key.
	 * @return string
	 */
	public function prepend_offer_text_to_cart_item( $product_name, $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['iconic_wsb_at_checkout'] ) || ! $cart_item['iconic_wsb_at_checkout'] ) {
			return $product_name;
		}

		$product_name_text = strip_tags( $product_name );

		// Translators: Product Name.
		$product_name_new = sprintf( esc_html__( '(Offer) %s', 'iconic-wsb' ), $product_name_text );

		// If html.
		if ( $product_name !== $product_name_text ) {
			$product_name = str_replace( $product_name_text, $product_name_new, $product_name );
		} else {
			$product_name = $product_name_new;
		}

		return $product_name;
	}

	/**
	 * Include Checkout Bump template.
	 *
	 * @param Iconic_WSB_Order_Bump_At_Checkout $bump The Checkout bump.
	 * @return void
	 */
	public function include_order_bump_template( $bump ) {
		global $iconic_wsb_class;

		$cart_item    = Iconic_WSB_Cart::get_cart_item( $this->cart_meta_key );
		$cart_item_id = false;
		$product      = $bump->get_product_offer();

		if ( $cart_item ) {
			$cart_item_id   = ( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$variation_data = Iconic_WSB_Cart::get_cart_item_variation_data( $this->cart_meta_key );
		} else {
			$variation_data = Iconic_WSB_Cart::remove_variation_key_prefix( $product->get_default_attributes() );
		}

		$cart_item_price = $bump->get_price_html( $cart_item_id );

		$iconic_wsb_class->template->include_template(
			'frontend/order-bump/checkout/checkout-bump.php',
			array(
				'bump'           => $bump,
				'variation_data' => $variation_data,
				'cart_item_id'   => $cart_item_id,
				'price'          => $cart_item_price,
			)
		);

		$this->view( $bump );
	}

	/**
	 * Add Sales Booster data to the wc/store/cart/items Store API endpoint.
	 *
	 * @return void
	 */
	public function add_data_to_cart_item_store_api() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema::IDENTIFIER,
				'namespace'       => 'iconic_sales_booster',
				'data_callback'   => function( $cart_item ) {
					return array(
						'added_via_at_checkout_bump' => ! empty( $cart_item['iconic_wsb_at_checkout'] ),
					);
				},
				'schema_callback' => function() {
					return array(
						'added_via_at_checkout_bump' => array(
							'description' => __( 'Whether a product was added via at checkout bump.', 'iconic-wsb' ),
							'type'        => 'boolean',
							'readonly'    => true,
						),
					);
				},
				'schema_type'     => ARRAY_A,
			)
		);
	}

	/**
	 * Add the At Checkout Bump the in checkout block.
	 *
	 * @param string $block_content The block content.
	 * @return string
	 */
	public function render_at_checkout_bump_in_checkout_block( $block_content ) {
		global $post;

		if ( ! has_block( 'woocommerce/checkout', $post ) ) {
			return $block_content;
		}

		$bump        = $this->get_suitable_bump();
		$render_hook = $bump->get_render_settings()['position'];
		$render_hook = apply_filters( 'iconic_wsb_order_bump_position', $render_hook, $bump ); // phpcs:ignore WooCommerce.Commenting.CommentHooks

		ob_start();
		$this->include_order_bump_template( $bump );

		$checkout_bump_html = ob_get_clean();

		switch ( $render_hook ) {
			case 'woocommerce_checkout_before_order_review':
			case 'woocommerce_review_order_before_cart_contents':
			case 'woocommerce_review_order_before_shipping':
			case 'woocommerce_review_order_before_payment':
			case 'woocommerce_review_order_before_submit':
				return $checkout_bump_html . $block_content;

			default:
				return $block_content . $checkout_bump_html;
		}
	}

	/**
	 * Map the checkout WooCommerce hooks to the block names.
	 *
	 * Example: `woocommerce_checkout_after_order_review` -> `woocommerce/checkout-order-summary-block`.
	 *
	 * @param string $position The WooCommerce hook name.
	 * @return string
	 */
	protected function map_woocommerce_hook_position_to_block_name( $position ) {
		switch ( $position ) {
			case 'woocommerce_checkout_before_order_review':
			case 'woocommerce_review_order_after_order_total':
			case 'woocommerce_checkout_after_order_review':
				return 'woocommerce/checkout-order-summary-block';

			case 'woocommerce_review_order_before_cart_contents':
			case 'woocommerce_review_order_after_cart_contents':
				return 'woocommerce/checkout-order-summary-cart-items-block';

			case 'woocommerce_review_order_before_shipping':
			case 'woocommerce_review_order_after_shipping':
			case 'woocommerce_review_order_before_order_total':
				return 'woocommerce/checkout-order-summary-shipping-block';

			case 'woocommerce_review_order_before_payment':
			case 'woocommerce_review_order_after_payment':
				return 'woocommerce/checkout-payment-block';

			case 'woocommerce_review_order_before_submit':
			case 'woocommerce_review_order_after_submit':
			case 'woocommerce_after_checkout_form':
				return 'woocommerce/checkout-actions-block';

			default:
				return '';
		}
	}

	/**
	 * Add `data-value` attribute to the variation select option elements.
	 *
	 * Although we use the `wc_dropdown_variation_attribute_options` function
	 * to output the select element, we need to add the `data-value` attribute
	 * because when we output the select element in the Checkout block,
	 * the `value` attribute is removed. It happens on the frontend. It
	 * seems happen as a part of a sanitization process.
	 *
	 * So, we use the `data-value` attribute to re-add the `value` attribute
	 * in the frontend.
	 *
	 * @param string $html The select element HTML markup.
	 * @param array  $args The variation attribute options args.
	 * @return mixed The html markup.
	 */
	public function add_data_value_attribute_to_option_elements( $html, $args ) {
		if ( ! is_string( $html ) ) {
			return $html;
		}

		if ( empty( $args['class'] ) ) {
			return $html;
		}

		if ( 'iconic-wsb-variation__select iconic-wsb-checkout-bump__select' !== $args['class'] ) {
			return $html;
		}

		if ( empty( $args['options'] ) || ! is_array( $args['options'] ) ) {
			return $html;
		}

		$search  = array( 'value=""' );
		$replace = array( 'value="" data-value=""' );
		foreach ( $args['options'] as $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			$search[]  = 'value="' . $value . '"';
			$replace[] = 'value="' . $value . '" data-value="' . $value . '"';
		}

		$html_with_replaced_values = str_replace(
			$search,
			$replace,
			$html
		);

		if ( empty( $html_with_replaced_values ) || ! is_string( $html_with_replaced_values ) ) {
			return $html;
		}

		return $html_with_replaced_values;
	}

	/**
	 * Update the input field args.
	 *
	 * @param array $args The input field args.
	 * @return array
	 */
	public static function update_product_offer_input_args( $args ) {
		if ( empty( $args['input_name'] ) ) {
			return $args;
		}

		preg_match(
			'/cart\[([a-z0-9]+)\]\[qty\]/i',
			$args['input_name'],
			$matches
		);

		if ( empty( $matches[1] ) ) {
			return $args;
		}

		$cart_item_key = $matches[1];

		$cart = WC()->cart->get_cart_contents();

		if ( empty( $cart[ $cart_item_key ] ) || self::is_cart_item_quantity_editable( $cart[ $cart_item_key ] ) ) {
			return $args;
		}

		$args['readonly'] = true;

		return $args;
	}

	/**
	 * Prevent updating the product offer quantity.
	 *
	 * @param bool   $passed_validation The cart validation.
	 * @param string $cart_item_key     The cart key.
	 * @param array  $cart_item         The cart item.
	 * @param int    $quantity          The new quantity value.
	 * @return bool
	 */
	public static function prevent_updating_product_offer_quantity( $passed_validation, $cart_item_key, $cart_item, $quantity ) {
		if ( self::is_cart_item_quantity_editable( $cart_item ) ) {
			return $passed_validation;
		}

		if ( $quantity > 1 ) {
			return false;
		}

		return $passed_validation;
	}

	/**
	 * Update the cart item quantity editable.
	 *
	 * `editable` is part of quantity limits.
	 *
	 * @param bool       $is_editable Whether is editable.
	 * @param WC_Product $product     The product.
	 * @param array      $cart_item   The cart item.
	 * @return bool
	 */
	public static function update_product_offer_quantity_editable( $is_editable, $product, $cart_item ) {
		if ( self::is_cart_item_quantity_editable( $cart_item ) ) {
			return $is_editable;
		}

		return false;
	}

	/**
	 * Check if it's allowed to update the cart item quantity.
	 *
	 * Since products can have a discount when added via order bump,
	 * it's important to not allow changing the quantity otherwise it
	 * would be possible to buy two or more products with the discounted price.
	 *
	 * @param array $cart_item The cart item.
	 * @return boolean
	 */
	protected static function is_cart_item_quantity_editable( $cart_item ) {
		if ( empty( $cart_item['bump_id'] ) || empty( $cart_item['iconic_wsb_at_checkout'] ) ) {
			return true;
		}

		$bump = self::get_instance()->get_order_bump( $cart_item['bump_id'] );

		if ( empty( $bump ) ) {
			return true;
		}

		if ( $bump->get_allow_product_offer_quantity_change() ) {
			return true;
		}

		return false;
	}
}
