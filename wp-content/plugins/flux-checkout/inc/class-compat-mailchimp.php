<?php
/**
 * Iconic_Flux_Compat_Mailchimp.
 *
 * Compatibility with Mailchimp for WooCommerce.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Mailchimp' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Mailchimp.
 *
 * @class    Iconic_Flux_Compat_Mailchimp.
 */
class Iconic_Flux_Compat_Mailchimp {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Dequeue scripts.
	 *
	 * Hook just after scripts are enqueued, but before they're output in the footer.
	 */
	public static function hooks() {
		if ( ! class_exists( 'MailChimp_Newsletter' ) ) {
			return;
		}

		add_action( 'flux_after_step_content', array( __CLASS__, 'add_field' ) );
	}

	/**
	 * Place mailchimp newletter checkbox field after customer details.
	 *
	 * @param array $step Steps data.
	 *
	 * @return void
	 */
	public static function add_field( $step ) {
		if ( 'details' === $step['slug'] ) {
			$service = MailChimp_Newsletter::instance();

			if ( empty( $service ) ) {
				return;
			}

			$action = $service->getOption( 'mailchimp_checkbox_action', 'woocommerce_after_checkout_billing_form' );

			if ( 'woocommerce_after_checkout_billing_form' !== $action ) {
				return;
			}

			remove_action( 'woocommerce_after_checkout_billing_form', array( $service, 'applyNewsletterField' ), 10 );

			$service->applyNewsletterField( WC()->checkout );
		}
	}
}
