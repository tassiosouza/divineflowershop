<?php
/**
 * WDS Compat class for WooCommerce PayPal Payments.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Compatibility;

defined( 'ABSPATH' ) || exit;

use Iconic_WDS_Core_Helpers;

/**
 * Compatibility with WooCommerce PayPal Payments.
 * https://wordpress.org/plugins/woocommerce-paypal-payments/
 *
 * @class    WooPaypalPayments
 */
class WooPaypalPayments {
	/**
	 * Run.
	 */
	public static function run() {
		if ( ! Iconic_WDS_Core_Helpers::is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) {
			return;
		}

		remove_action( 'woocommerce_after_checkout_validation', array( 'Iconic_WDS_Checkout', 'catch_shipping' ), 10 );
	}
}
