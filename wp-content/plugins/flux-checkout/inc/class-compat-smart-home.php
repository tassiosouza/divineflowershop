<?php
/**
 * Iconic_Flux_Compat_Smart_Home.
 *
 * Compatibility with Smart Home theme.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Smart_Home' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Smart_Home.
 *
 * @class    Iconic_Flux_Compat_Smart_Home.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Smart_Home {
	/**
	 * Init.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		if ( ! function_exists( 'thb_wc_supported' ) ) {
			return;
		}

		if ( ! thb_wc_supported() || ! Iconic_Flux_Core::is_checkout( true ) ) {
			return;
		}

		remove_action(
			'woocommerce_checkout_before_customer_details',
			'thb_checkout_before_customer_details',
			5
		);

		remove_action(
			'woocommerce_checkout_after_customer_details',
			'thb_checkout_after_customer_details',
			30
		);

		remove_action(
			'woocommerce_checkout_after_order_review',
			'thb_checkout_after_order_review',
			30
		);

		remove_action( 'wp_footer', 'thb_mobile_menu' );
	}
}
