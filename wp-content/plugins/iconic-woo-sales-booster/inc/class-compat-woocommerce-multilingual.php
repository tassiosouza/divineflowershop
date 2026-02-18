<?php
/**
 * Compatibility with WooCommerce Multilingual & Multicurrency.
 *
 * @see https://wpml.org/documentation/related-projects/woocommerce-multilingual/.
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WooCommerce_Multilingual class.
 *
 * @since 1.12.0
 */
class Iconic_WSB_Compat_WooCommerce_Multilingual {
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
		if ( ! class_exists( 'woocommerce_wpml' ) ) {
			return;
		}

		add_filter( 'wcml_multi_currency_ajax_actions', array( __CLASS__, 'add_wsb_ajax_actions' ) );
	}

	/**
	 * Add WSB AJAX actions.
	 *
	 * On AJAX request, the WooCommerce Multilingual
	 * & Multicurrency plugin only loads for allowed
	 * actions.
	 *
	 * @param array $actions The URL to redirect to.
	 * @return array
	 */
	public static function add_wsb_ajax_actions( $actions ) {
		$actions[] = 'iconic_wsb_checkout_get_variation';
		$actions[] = 'iconic_wsb_fbt_get_products_price';

		return $actions;
	}
}
