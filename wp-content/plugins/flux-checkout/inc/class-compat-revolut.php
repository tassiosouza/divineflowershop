<?php
/**
 * Iconic_Flux_Compat_Revolut.
 *
 * Compatibility with Revolut payment gateway.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Revolut' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Revolut.
 *
 * @class    Iconic_Flux_Compat_Revolut.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Revolut {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'compat_revolut' ) );
	}

	/**
	 * Compatibility with Revolut payment gateway.
	 *
	 * @return void
	 */
	public static function compat_revolut() {
		if ( ! class_exists( 'WC_Payment_Gateway_Revolut' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_revolut_scripts' ), 10 );
		add_action( 'wp_footer', array( __CLASS__, 'css_fix' ), 10 );
	}

	/**
	 * Enqueue Revolut scripts.
	 *
	 * @return void
	 */
	public static function enqueue_revolut_scripts() {
		$payment_gateways = WC()->payment_gateways->payment_gateways();
		if ( empty( $payment_gateways['revolut_cc'] ) ) {
			return;
		}

		if ( ! method_exists( $payment_gateways['revolut_cc'], 'enqueue_common_standard_scripts' ) ) {
			return;
		}

		$payment_gateways['revolut_cc']->enqueue_common_standard_scripts();
	}

	/**
	 * CSS fix.
	 *
	 * @return void
	 */
	public static function css_fix() {
		if ( ! is_checkout() ) {
			return;
		}

		?>
		<style>
			#payment .payment_methods li label[for=wc-revolut_cc-new-payment-method]:not(.checkbox,.woocommerce-form__label-for-checkbox) {
				position: static !important;
				padding-left:0;
				background: transparent;
			}

			input#wc-revolut_cc-new-payment-method {margin-top: 0 !important;}

			p.form-row.woocommerce-SavedPaymentMethods-saveNew.revolut-payment-method-save.woocommerce-validated.is-active {
				align-items: center;
				justify-content: center;
			}
		</style>
		<?php
	}
}
