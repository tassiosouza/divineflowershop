<?php
/**
 * Compatibility with WooCommerce Shipping & Tax.
 *
 * @see https://wordpress.org/plugins/woocommerce-services/
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WooCommerce_Shipping_Tax class.
 *
 * @since 1.23.0
 */
class Iconic_WSB_Compat_WooCommerce_Shipping_Tax {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', [ __CLASS__, 'hooks' ], 15 );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		if ( ! Iconic_WSB_Core_Helpers::is_plugin_active( 'woocommerce-services/woocommerce-services.php' ) ) {
			return;
		}

		add_action( 'woocommerce_cart_reset', [ __CLASS__, 'remove_fbt_fees' ] );
	}

	/**
	 * Remove `_iconic_wsb_fbt_discount` fees from the cart.
	 *
	 * @param WC_Cart $cart The WooCommerce cart object.
	 * @return void
	 */
	public static function remove_fbt_fees( WC_Cart $cart ) {
		$current_fees = $cart->fees_api()->get_fees();

		unset( $current_fees['_iconic_wsb_fbt_discount'] );

		$cart->fees_api()->set_fees( $current_fees );
	}
}
