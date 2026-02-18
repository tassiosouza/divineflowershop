<?php
/**
 * WooCommerce Local Pickup.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

defined( 'ABSPATH' ) || exit;

/**
 * All functions related to WooCommerce Local Pickup functionality.
 *
 * @since 2.11.0
 */
class WooCommerceLocalPickup {
	public static function run() {
		add_filter( 'pre_update_option_pickup_location_pickup_locations', array( __CLASS__, 'clear_shipping_method_transient' ), 10 );
		add_filter( 'pre_update_option_woocommerce_pickup_location_settings', array( __CLASS__, 'clear_shipping_method_transient' ), 10 );
	}

	public static function clear_shipping_method_transient( $value ) {
		delete_transient( 'iconic-wds-shipping-methods' );
		return $value;
	}

	/**
	 * Modify shipping method options for local pickup locations.
	 *
	 * @param array $shipping_method_options Shipping method options.
	 *
	 * @return array
	 */
	public static function modify_shipping_method_options( $shipping_method_options ) {
		// Remove the automattic\woocommerce\blocks\shipping\pickuplocation key.
		if ( isset( $shipping_method_options['automattic\woocommerce\blocks\shipping\pickuplocation'] ) ) {
			unset( $shipping_method_options['automattic\woocommerce\blocks\shipping\pickuplocation'] );
		}

		// Fetch all local pickup locations.
		$pickup_locations = get_option( 'pickup_location_pickup_locations', array() );

		if ( empty( $pickup_locations ) || ! is_array( $pickup_locations ) ) {
			return $shipping_method_options;
		}

		// Populate the locations in the shipping method options.
		foreach ( $pickup_locations as $key => $location ) {
			if ( ! isset( $location['name'] ) || empty( $location['name'] ) ) {
				continue;
			}

			// Create a unique identifier for each location.
			$method_id = 'pickup_location:' . $key;

			// Add the location to shipping method options.
			$shipping_method_options[ $method_id ] = sprintf( '%s: %s', __( 'Local Pickup', 'jckwds' ), esc_html( $location['name'] ) );
		}

		return $shipping_method_options;
	}
}

