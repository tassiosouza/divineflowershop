<?php
/**
 * Iconic_Flux_Coupon.
 *
 * Coupon related functions.
 *
 * @package Iconic_Flux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Iconic_Flux_Coupon.
 *
 * @class    Iconic_Flux_Coupon.
 */
class Iconic_Flux_Coupon {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp', array( __CLASS__, 'auto_apply_coupon' ) );
		add_action( 'woocommerce_removed_coupon', array( __CLASS__, 'register_removed_coupon' ) );
		add_filter( 'option_woocommerce_cart_redirect_after_add', array( __CLASS__, 'disable_add_to_cart_redirect_for_checkout' ) );
	}

	/**
	 * Auto apply coupon if enabled in the settings.
	 */
	public static function auto_apply_coupon() {
		if ( empty( Iconic_Flux_Core_Settings::$settings['general_general_auto_apply_coupon'] ) ) {
			return;
		}

		$coupon = Iconic_Flux_Core_Settings::$settings['general_general_auto_apply_coupon'];

		if ( empty( $coupon ) || ! is_checkout() ) {
			return;
		}

		if ( '1' === WC()->session->get( 'flux_dont_auto_apply_coupon_flag' ) ) {
			return;
		}

		if ( ! WC()->cart->has_discount( $coupon ) ) {
			WC()->cart->apply_coupon( $coupon );
		}
	}

	/**
	 * Register removed coupon in session so we do not apply it automatically.
	 *
	 * @param string $removed_coupon Removed coupon code.
	 *
	 * @return void
	 */
	public static function register_removed_coupon( $removed_coupon ) {
		$auto_coupon = self::get_auto_apply_coupon();

		if ( empty( $auto_coupon ) ) {
			return;
		}

		if ( $removed_coupon === $auto_coupon ) {
			WC()->session->set( 'flux_dont_auto_apply_coupon_flag', '1' );
		}
	}

	/**
	 * Get Coupon to be auto applied.
	 *
	 * @return string|false
	 */
	public static function get_auto_apply_coupon() {
		if ( empty( Iconic_Flux_Core_Settings::$settings['general_general_auto_apply_coupon'] ) ) {
			/**
			 * Coupon to be auto-applied.
			 *
			 * @since 2.3.0
			 */
			return apply_filters( 'flux_auto_apply_coupon', false );
		}

		/**
		 * Coupon to be auto-applied.
		 *
		 * @since 2.3.0
		 */
		return apply_filters( 'flux_auto_apply_coupon', Iconic_Flux_Core_Settings::$settings['general_general_auto_apply_coupon'] );
	}

	/**
	 * Disable Add to cart redirection for checkout.
	 *
	 * @param array $value Value.
	 *
	 * @return mixed.
	 */
	public static function disable_add_to_cart_redirect_for_checkout( $value ) {
		$add_to_cart = filter_input( INPUT_GET, 'add-to-cart', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $add_to_cart ) || ! did_filter( 'woocommerce_add_to_cart_product_id' ) ) {
			return $value;
		}

		if ( ! Iconic_Flux_Core::is_checkout( true ) ) {
			return $value;
		}

		return false;
	}
}
