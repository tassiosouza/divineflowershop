<?php
/**
 * Compatibility with Variation Swatches for WooCommerce.
 *
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_Variation_Swatches_For_WooCommerce class.
 *
 * @since 1.9.0
 */
class Iconic_WSB_Compat_Variation_Swatches_For_WooCommerce {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		if ( ! class_exists( 'WooProductVariationSwatchesPro' ) ) {
			return;
		}

		/**
		 * Use the default variation attribute options to render the checkout Order Bump.
		 */
		add_action(
			'iconic_wsb_before_wc_dropdown_variation_attribute_options',
			function() {
				add_filter( 'default_rtwpvs_variation_attribute_options_html', '__return_true' );
			}
		);

		/**
		 * Use the Variation Swatches for WooCommerce variation attribute options
		 * after rendering the checkout Order Bump.
		 */
		add_action(
			'iconic_wsb_after_wc_dropdown_variation_attribute_options',
			function() {
				add_filter( 'default_rtwpvs_variation_attribute_options_html', '__return_false' );
			}
		);
	}
}
