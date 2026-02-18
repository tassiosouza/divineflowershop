<?php
/**
 * Iconic_Flux_Compat_Stripe_Express_Checkout.
 *
 * Compatibility with Google Pay/Apple Pay express by Stripe.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Woo_Payments' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Woo_Payments.
 *
 * @class    Iconic_Flux_Compat_Woo_Payments.
 * @version  2.14.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Woo_Payments {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		if ( ! class_exists( 'WC_Payments' ) ) {
			return;
		}

		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'fix_email_missing' ), 20 );
	}

	/**
	 * Fix email missing problem.
	 */
	public static function fix_email_missing() {
		remove_filter( 'woocommerce_form_field_email', array( 'WC_Payments', 'filter_woocommerce_form_field_woopay_email' ), 20 );
	}
}
