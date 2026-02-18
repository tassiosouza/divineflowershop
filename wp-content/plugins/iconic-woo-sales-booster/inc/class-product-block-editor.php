<?php
/**
 * Integrate with WooCommerce Product Block editor.
 *
 * @see https://github.com/woocommerce/woocommerce/tree/trunk/docs/product-editor-development
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Automattic\WooCommerce\Admin\Features\ProductBlockEditor\ProductTemplates\GroupInterface;

/**
 * Iconic_WSB_Product_Block_Editor class.
 *
 * @since 1.20.0
 */
class Iconic_WSB_Product_Block_Editor {
	/**
	 * Run
	 */
	public static function run() {
		self::add_sales_booster_group();

		self::handle_frequently_bought_together();
		self::handle_after_add_to_cart_popup();

		add_action( 'pre_get_posts', [ __CLASS__, 'add_product_variations_to_the_product_search_query' ] );
	}

	/**
	 * Add Sales Booster group (tab).
	 *
	 * @return void
	 */
	protected static function add_sales_booster_group() {
		add_action(
			'woocommerce_block_template_area_product-form_after_add_block_linked-products',
			function( $general_group ) {
				$parent = $general_group->get_parent();

				$parent->add_group(
					[
						'id'         => 'iconic-wsb-group',
						'order'      => $general_group->get_order() + 5,
						'attributes' => [
							'title' => __( 'Sales Booster', 'iconic-wsb' ),
						],
					]
				);
			}
		);
	}

	/**
	 * Handle the Frequently Bought Together section.
	 *
	 * @return void
	 */
	protected static function handle_frequently_bought_together() {
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_iconic-wsb-group', [ __CLASS__, 'add_frequently_bought_together_fields' ] );
		add_action( 'woocommerce_rest_insert_product_object', [ __CLASS__, 'save_frequently_bought_together_fields' ], 10, 2 );

		add_filter( 'woocommerce_rest_prepare_product_object', [ __CLASS__, 'add_frequently_bought_together_data_to_rest_response' ], 10, 3 );
	}


	/**
	 * Add Frequently Bought Together fields.
	 *
	 * @param GroupInterface $sales_booster_group The Sales Booster group.
	 * @return void
	 */
	public static function add_frequently_bought_together_fields( GroupInterface $sales_booster_group ) {
		$section = $sales_booster_group->add_section(
			[
				'id'         => 'iconic-wsb-fbt-section',
				'order'      => 5,
				'attributes' => [
					'title'       => __( 'Frequently Bought Together', 'iconic-wsb' ),
					'description' => __( 'Display these products on the single product page and easily add them to the cart with a single button.', 'iconic-wsb' ),
				],
			]
		);

		$section->add_block(
			[
				'id'         => 'iconic-wsb-fbt-title-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-text-field',
				'attributes' => [
					'property'    => 'iconic-wsb-fbt-title',
					'label'       => __( 'Title', 'iconic-wsb' ),
					'placeholder' => __( 'Frequently Bought Together', 'iconic-wsb' ),
				],
			]
		);

		$section->add_block(
			[
				'id'         => 'iconic-wsb-fbt-description-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-text-field',
				'attributes' => [
					'property' => 'iconic-wsb-fbt-sales-pitch',
					'label'    => __( 'Description', 'iconic-wsb' ),
					'tooltip'  => __( 'Use this to "pitch" the bundle to your customers.', 'iconic-wsb' ),
				],
			]
		);

		$section->add_block(
			[
				'id'         => 'iconic-wsb-fbt-products-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-linked-list-field',
				'attributes' => [
					'property'   => 'iconic_wsb_product_page_order_bump_ids',
					'emptyState' => [
						'tip'   => __( 'Should be in stock and have price greater than zero.', 'iconic-wsb' ),
						'image' => 'ShoppingBags',
					],
				],
			]
		);

		$section->add_block(
			[
				'id'         => 'iconic-wsb-fbt-unchecked-by-default-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-checkbox-field',
				'attributes' => [
					'property'     => 'iconic-wsb-fbt-set-unchecked',
					'label'        => __( 'Unchecked by Default', 'iconic-wsb' ),
					'tooltip'      => __( 'When enabled, the products in the frequently bought together bundle will be unchecked by default.', 'iconic-wsb' ),
					'checkedValue' => 'yes',
				],
			]
		);

		$subsection = $section->add_subsection(
			[
				'id'    => 'subiconic-wsb-after-add-to-cart-popup-section',
				'order' => 5,
			]
		);

		$subsection->add_block(
			[
				'id'         => 'iconic-wsb-fbt-pricing-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-pricing-field',
				'attributes' => [
					'property' => 'iconic-wsb-fbt-discount-value',
					'label'    => __( 'Discount (Optional)', 'iconic-wsb' ),
				],
			]
		);

		$subsection->add_block(
			[
				'id'         => 'iconic-wsb-fbt-discount-type-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-radio-field',
				'attributes' => [
					'property' => 'iconic-wsb-fbt-discount-type',
					'title'    => __( 'Discount type', 'iconic-wsb' ),
					'options'  => [
						[
							'value' => 'percentage',
							'label' => 'Percent',
						],
						[
							'value' => 'simple',
							'label' => html_entity_decode( get_woocommerce_currency_symbol() ),
						],
					],
				],
			]
		);
	}

	/**
	 * Add Frequently Bought Together data to the Product Block editor REST response.
	 *
	 * @param WP_REST_Response $response The Product Block editor REST response.
	 * @param WC_Product       $product  The edited product.
	 * @param WP_REST_Request  $request  The request to retrieve the product object.
	 * @return WP_REST_Response
	 */
	public static function add_frequently_bought_together_data_to_rest_response( WP_REST_Response $response, WC_Product $product, WP_REST_Request $request ) {
		if ( ! self::is_product_block_editor_enabled()) {
			return $response;
		}

		if ( 'edit' !== $request->get_param( 'context' ) ) {
			return $response;
		}

		if ( $product->get_id() !== $request->get_param( 'id' ) ) {
			return $response;
		}

		$fbt      = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance();
		$fbt_data = $fbt->get_fields_data( $product->get_id() );

		$title = get_post_meta( $product->get_id(), '_iconic_wsb_fbt_title', true );

		$response->data['iconic-wsb-fbt-title']                   = is_string( $title ) ? trim( $title ) : '';
		$response->data['iconic-wsb-fbt-sales-pitch']             = $fbt_data['sales_pitch'] ?? '';
		$response->data['iconic_wsb_product_page_order_bump_ids'] = $fbt_data['fbt_products'] ?? [];
		$response->data['iconic-wsb-fbt-set-unchecked']           = $fbt_data['set_unchecked'] ?? '';
		$response->data['iconic-wsb-fbt-discount-value']          = $fbt_data['discount_value'] ?? '';
		$response->data['iconic-wsb-fbt-discount-type']           = empty( $fbt_data['discount_type'] ) ? 'simple' : $fbt_data['discount_type'];

		return $response;
	}

	/**
	 * Save Frequently Bought Together fields.
	 *
	 * @param WC_Product      $product The product object saved.
	 * @param WP_REST_Request $request The HTTP request.
	 * @return void
	 */
	public static function save_frequently_bought_together_fields( WC_Product $product, WP_REST_Request $request ) {
		if ( ! self::is_product_block_editor_enabled()) {
			return;
		}

		$discount_type = $request->get_param( 'iconic-wsb-fbt-discount-type' );

		if ( ! is_null( $discount_type ) ) {
			$discount_type = 'percentage' === $discount_type ? 'percentage' : 'simple';
		}

		$discount_value = $request->get_param( 'iconic-wsb-fbt-discount-value' );

		if ( is_numeric( $discount_value ) ) {
			$discount_value = wc_format_decimal( $discount_value );

			if ( 'percentage' === $discount_type && $discount_value < 0 ) {
				$discount_value = 0;
			}

			if ( 'percentage' === $discount_type && $discount_value > 100 ) {
				$discount_value = 100;
			}
		}

		$products = self::get_product_ids( $request->get_param( 'iconic_wsb_product_page_order_bump_ids' ) );

		self::save_meta_value( $product->get_id(), '_iconic_wsb_fbt_title', $request->get_param( 'iconic-wsb-fbt-title' ) );
		self::save_meta_value( $product->get_id(), '_iconic_wsb_fbt_sales_pitch', $request->get_param( 'iconic-wsb-fbt-sales-pitch' ) );
		self::save_meta_value( $product->get_id(), '_iconic_wsb_product_page_order_bump_ids', $products );
		self::save_meta_value( $product->get_id(), '_iconic_wsb_fbt_set_unchecked', $request->get_param( 'iconic-wsb-fbt-set-unchecked' ) );
		self::save_meta_value( $product->get_id(), '_iconic_wsb_fbt_discount_value', $discount_value );
		self::save_meta_value( $product->get_id(), '_iconic_wsb_fbt_discount_type', $discount_type );
	}

	/**
	 * Save meta value.
	 *
	 * Since a field value in the request can be set to `null`, meaning
	 * that there is no change to that field and it doesn't need to be
	 * updated.
	 *
	 * @param int    $product_id The product ID.
	 * @param string $meta_key   The meta key.
	 * @param mixed  $meta_value The meta value.
	 * @return void
	 */
	protected static function save_meta_value( $product_id, $meta_key, $meta_value ) {
		if ( is_null( $meta_value ) ) {
			return;
		}

		update_post_meta( $product_id, $meta_key, $meta_value );
	}

	/**
	 * Handle the After Add to Cart Popup section.
	 *
	 * @return void
	 */
	protected static function handle_after_add_to_cart_popup() {
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_iconic-wsb-group', [ __CLASS__, 'add_after_add_to_cart_popup_fields' ] );
		add_action( 'woocommerce_rest_insert_product_object', [ __CLASS__, 'save_after_add_to_cart_popup_fields' ], 10, 2 );

		add_filter( 'woocommerce_rest_prepare_product_object', [ __CLASS__, 'add_after_add_to_cart_popup_data_to_rest_response' ], 10, 3 );
	}

	/**
	 * Add After Add to Cart Popup fields.
	 *
	 * @param GroupInterface $sales_booster_group The Sales Booster group.
	 * @return void
	 */
	public static function add_after_add_to_cart_popup_fields( $sales_booster_group ) {
		$section = $sales_booster_group->add_section(
			[
				'id'         => 'iconic-wsb-after-add-to-cart-popup-section',
				'order'      => 5,
				'attributes' => [
					'title'       => __( 'After Add to Cart Popup', 'iconic-wsb' ),
					'description' => __( 'Display these suggested products in a modal popup after adding the main product to the cart.', 'iconic-wsb' ),
				],
			]
		);

		$section->add_block(
			[
				'id'         => 'iconic-wsb-after-add-to-cart-popup-products-field',
				'order'      => 5,
				'blockName'  => 'woocommerce/product-linked-list-field',
				'attributes' => [
					'property'   => 'iconic_wsb_product_page_bump_modal_ids',
					'emptyState' => [
						'tip'   => __( 'Should be in stock and have price greater than zero.', 'iconic-wsb' ),
						'image' => 'ShoppingBags',
					],
				],
			]
		);
	}

	/**
	 * Add After Add to Cart Popup data to the Product Block editor REST response.
	 *
	 * @param WP_REST_Response $response The Product Block editor REST response.
	 * @param WC_Product       $product  The edited product.
	 * @param WP_REST_Request  $request  The request to retrieve the product object.
	 * @return WP_REST_Response
	 */
	public static function add_after_add_to_cart_popup_data_to_rest_response( WP_REST_Response $response, WC_Product $product, WP_REST_Request $request ) {
		if ( ! self::is_product_block_editor_enabled()) {
			return $response;
		}

		if ( 'edit' !== $request->get_param( 'context' ) ) {
			return $response;
		}

		if ( $product->get_id() !== $request->get_param( 'id' ) ) {
			return $response;
		}

		$products = (array) get_post_meta( $product->get_id(), '_iconic_wsb_product_page_bump_modal_ids', true );

		$response->data['iconic_wsb_product_page_bump_modal_ids'] = $products;

		return $response;
	}

	/**
	 * Save After Add to Cart Popup fields.
	 *
	 * @param WC_Product      $product The product object saved.
	 * @param WP_REST_Request $request The HTTP request.
	 * @return void
	 */
	public static function save_after_add_to_cart_popup_fields( WC_Product $product, WP_REST_Request $request ) {
		if ( ! self::is_product_block_editor_enabled()) {
			return;
		}

		$products = self::get_product_ids( $request->get_param( 'iconic_wsb_product_page_bump_modal_ids' ) );

		self::save_meta_value( $product->get_id(), '_iconic_wsb_product_page_bump_modal_ids', $products );
	}

	/**
	 * Get the product IDs
	 *
	 * @param null|int[] $product_ids The product IDs from the WP_REST_Request object.
	 * @return null|int[]
	 */
	protected static function get_product_ids( $product_ids ) {
		if ( is_null( $product_ids ) ) {
			return $product_ids;
		}

		$product_ids = array_filter(
			(array) $product_ids,
			function( $product_id ) {
				return ! empty( $product_id ) && is_int( $product_id );
			}
		);

		return $product_ids;
	}

	/**
	 * Intercept the product search query and add the product variations post type.
	 *
	 * @param WP_Query $query The WP_Query instance (passed by reference).
	 * @return void
	 */
	public static function add_product_variations_to_the_product_search_query( WP_Query $query ) {
		if ( ! self::is_product_block_editor_enabled()) {
			return;
		}

		if ( ! wp_is_serving_rest_request() ) {
			return;
		}

		$request_uri = $_SERVER['REQUEST_URI'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( empty( $request_uri ) ) {
			return;
		}

		if ( ! str_contains( $request_uri, '/wc/v3/products' ) ) {
			return;
		}

		if ( empty( $query->query_vars['post_type'] ) ) {
			return;
		}

		if ( 'product' !== $query->query_vars['post_type'] ) {
			return;
		}

		if ( empty( $_GET['include'] ) && empty( $_GET['search'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$query->set( 'post_type', [ 'product', 'product_variation' ] );
	}

	/**
	 * Check if `product_block_editor` feature is enabled.
	 *
	 * @return boolean
	 */
	protected static function is_product_block_editor_enabled() {
		return class_exists('FeaturesUtil') && FeaturesUtil::feature_is_enabled( 'product_block_editor' );
	}
}
