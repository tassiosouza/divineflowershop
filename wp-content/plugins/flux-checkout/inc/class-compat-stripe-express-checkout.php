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

if ( class_exists( 'Iconic_Flux_Compat_Stripe_Express_Checkout' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Stripe_Express_Checkout.
 *
 * @class    Iconic_Flux_Compat_Stripe_Express_Checkout.
 * @version  2.4.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Stripe_Express_Checkout {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp', array( __CLASS__, 'compat_express_checkout' ) );
		add_action( 'init', array( __CLASS__, 'fix_email_label_unwanted_html_issue' ) );
	}

	/**
	 * Disable street number fields validation when order is placed from Google Pay/Apple Pay express
	 * checkout button.
	 */
	public static function compat_express_checkout() {
		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'wc_stripe_create_order' !== $wc_ajax ) {
			return;
		}

		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'make_street_number_fields_options' ) );
	}

	/**
	 * Make street number fields options.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function make_street_number_fields_options( $fields ) {
		if ( isset( $fields['billing']['billing_street_number'] ) ) {
			$fields['billing']['billing_street_number']['required'] = false;
		}

		if ( isset( $fields['shipping']['shipping_street_number'] ) ) {
			$fields['shipping']['shipping_street_number']['required'] = false;
		}

		return $fields;
	}

	/**
	 * Fix issue where unwanted HTML is added to the email label.
	 *
	 * @return void
	 */
	public static function fix_email_label_unwanted_html_issue() {
		if ( ! class_exists( 'WC_Stripe' ) || ! method_exists( 'WC_Stripe', 'get_instance' ) ) {
			return;
		}

		$stripe = WC_Stripe::get_instance();
		remove_filter( 'woocommerce_billing_fields', array( $stripe, 'checkout_update_email_field_priority' ), 50 );
	}
}
