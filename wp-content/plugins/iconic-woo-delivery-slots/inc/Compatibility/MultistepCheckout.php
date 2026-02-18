<?php
/**
 * Compatibility with Multi-Step Checkout for WooCommerce
 * by https://www.themehigh.com/
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Compatibility;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility with Multi-Step Checkout for WooCommerce.
 */
class MultistepCheckout {
	/**
	 * Run.
	 */
	public static function run() {
		if ( ! class_exists( 'THWMSC' ) ) {
			return;
		}

		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'update_checkout_fields' ), 11, 1 );
	}

	/**
	 * Set 'required' parameter false if iconic-wds-fields-hidden is true.
	 * Doing this will ensure delivery date field is not validated when it is not supposed to.
	 *
	 * @param array $fields Checkout fields.
	 *
	 * @return array
	 */
	public static function update_checkout_fields( $fields ) {
		if ( ! isset( $fields['jckwds'] ) ) {
			return $fields;
		}

		$posted = filter_input( INPUT_POST, 'posted', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( ! isset( $posted['iconic-wds-fields-hidden'] ) || '1' !== $posted['iconic-wds-fields-hidden'] ) {
			return $fields;
		}

		foreach ( $fields['jckwds'] as $key => &$field ) {
			$field['required'] = 0;
		}

		return $field;
	}
}
