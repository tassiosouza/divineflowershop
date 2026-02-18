<?php
/**
 * Compatibility with WooCommerce eCurring gateway.
 *
 * @see https://www.ecurring.com.
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WooCommerce_Ecurring_Gateway class.
 *
 * @since 1.11.0
 */
class Iconic_WSB_Compat_WooCommerce_Ecurring_Gateway {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ), 15 );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		if ( ! class_exists( 'eCurring_WC_Plugin' ) ) {
			return;
		}

		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'prevent_redirect_to_same_url' ), 15 );
	}

	/**
	 * Prevent redirect to the same URL.
	 *
	 * @param false|string $url The URL to redirect to.
	 * @return false|string
	 */
	public static function prevent_redirect_to_same_url( $url ) {
		if ( ! is_string( $url ) ) {
			return $url;
		}

		if ( empty( $_SERVER['HTTP_HOST'] ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return $url;
		}

		// We create a URL in the same way that the plugin does on the function eCurringRedirectToCheckout.
		$current_url = '//' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '/';

		if ( $url === $current_url ) {
			return false;
		}

		return $url;
	}
}
