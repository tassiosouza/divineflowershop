<?php
/**
 * Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce.
 *
 * Compatibility for: https://wordpress.org/plugins/pymntpl-paypal-woocommerce/
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce.
 *
 * @class    Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce.
 * @version  2.4.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'woocommerce_init', array( __CLASS__, 'move_paypal_button' ), 20 );
	}

	/**
	 * Move PayPal button at checkout.
	 */
	public static function move_paypal_button() {
		if ( ! class_exists( '\PaymentPlugins\WooCommerce\PPCP\PaymentButtonController' ) ) {
			return;
		}

		$instance = \PaymentPlugins\WooCommerce\PPCP\Main::container()->get( \PaymentPlugins\WooCommerce\PPCP\PaymentButtonController::class );

		remove_action( 'woocommerce_review_order_after_submit', array( $instance, 'render_checkout_button' ) );
		add_action( 'flux_footer_end_final_step', array( $instance, 'render_checkout_button' ) );
	}
}
