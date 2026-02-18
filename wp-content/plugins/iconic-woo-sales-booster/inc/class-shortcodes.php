<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Shortcodes.
 *
 * Register shortcodes.
 *
 * @class    Iconic_WSB_Shortcodes
 * @version  1.0.0
 */
class Iconic_WSB_Shortcodes {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ), 10 );
	}

	/**
	 * Register Shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'iconic_wsb_fbt', array( __CLASS__, 'iconic_wsb_fbt_shortcode' ) );
		add_shortcode( 'iconic_wsb_order_bump', array( __CLASS__, 'iconic_wsb_order_bump_shortcode' ) );
	}

	/**
	 * Register Frequently Bought Together Shortcodes.
	 *
	 * @param array $attributes Attributes.
	 */
	public static function iconic_wsb_fbt_shortcode( $attributes ) {
		global $product;

		$bump_product_page_manager = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance();

		$product_id = isset( $attributes['product_id'] ) ? intval( $attributes['product_id'] ) : null;

		ob_start();

		// If this is not a product page, add an area for the notices to go.
		if ( ! is_product() ) {
			echo '<div class="woocommerce-notices-wrapper"></div>';
		}

		// Render the FBT panel.
		$bump_product_page_manager->frontend_product_page_order_bump( $product_id );

		return ob_get_clean();
	}

	/**
	 * The [iconic_wsb_order_bump] shortcode callback.
	 *
	 * @return string
	 */
	public static function iconic_wsb_order_bump_shortcode() {

		/**
		 * Filter whether the Checkout Bump shortcode should be rendered or not.
		 *
		 * @since 1.10.0
		 * @hook iconic_wsb_should_render_order_bump_shortcode
		 * @param  bool $render_order_bump_shortcode Default: `is_checkout()`.
		 * @return bool New value
		 */
		$render_order_bump_shortcode = apply_filters( 'iconic_wsb_should_render_order_bump_shortcode', is_checkout() );

		if ( ! $render_order_bump_shortcode ) {
			return;
		}

		$bump = Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->get_suitable_bump();

		if ( empty( $bump ) ) {
			return;
		}

		ob_start();

		Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->include_order_bump_template( $bump );

		/**
		 * Filter the output of the Checkout Bump shortcode.
		 *
		 * @since 1.10.0
		 * @hook iconic_wsb_order_bump_shortcode
		 * @param  string                            $order_bump_html The output of the shortcode.
		 * @param  Iconic_WSB_Order_Bump_At_Checkout $order_bump      The Order bump.
		 * @return string New value
		 */
		$order_bump_html = apply_filters( 'iconic_wsb_order_bump_shortcode', ob_get_clean(), $bump );

		return $order_bump_html;
	}
}
