<?php
/**
 * Iconic_Flux_Compat_AuthorizeNet.
 *
 * Compatibility with Authorize.Net plugin by SkyVerge.
 * URL: https://woocommerce.com/products/authorize-net/
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_AuthorizeNet' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_AuthorizeNet.
 */
class Iconic_Flux_Compat_AuthorizeNet {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'replace_authorize_net_scripts' ), 20 );
	}

	/**
	 * Replace Authorize.Net block checkout scripts with classic checkout scripts.
	 * 
	 * Since, Flux checkout works with Block checkout only, we want to ensure that the Authorize.net plugin's
	 * classic checkout script is enqueued instead of the block checkout script
	 * when the checkout page is using the block checkout.
	 */
	public static function replace_authorize_net_scripts() {
		// Check if Authorize.Net CIM plugin is active
		if ( ! function_exists( 'wc_authorize_net_cim' ) ) {
			return;
		}

		// Only run on checkout page
		if ( ! Iconic_Flux_Core::is_checkout() ) {
			return;
		}

		$plugin = wc_authorize_net_cim();

		if ( ! $plugin || !is_a( $plugin, 'WC_Authorize_Net_CIM' ) ) {
			return;
		}

		$url = plugins_url( '/assets/js/frontend/wc-authorize-net-cim.min.js', $plugin->get_plugin_file() );
		

		wp_dequeue_script( 'wc-authorize-net-cim-credit-card-checkout-block-js' );

		// Enqueue the classic checkout script instead
		wp_enqueue_script(
			'wc-authorize-net-cim',
			$url,
			array( 'jquery', 'wc-checkout' ),
			$plugin::VERSION,
			true
		);
	}
}
