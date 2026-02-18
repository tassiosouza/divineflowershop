<?php
/**
 * Compatibility with Booking & Appointment Plugin for WooCommerce.
 *
 * @see https://www.tychesoftwares.com/products/woocommerce-booking-and-appointment-plugin/
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WooCommerce_Booking class.
 *
 * @since 1.20.0
 */
class Iconic_WSB_Compat_WooCommerce_Booking {
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
		if ( ! class_exists( 'Woocommerce_Booking' ) ) {
			return;
		}

		add_action( 'iconic_wsb_after_view_cart_link_in_after_add_to_cart_modal', array( __CLASS__, 'update_add_to_cart_button_on_after_add_to_cart_modal' ) );

		add_filter( 'bkap_skip_add_to_cart_validation', array( __CLASS__, 'skip_booking_validation' ), 5 );
		add_filter( 'bkap_cart_allow_add_bookings', array( __CLASS__, 'allow_booking_for_frequently_bought_together_products' ), 20, 2 );
	}

	/**
	 * Update the Add to Cart button on After Add to Cart modal.
	 *
	 * @return void
	 */
	public static function update_add_to_cart_button_on_after_add_to_cart_modal() {
		self::remove_filter(
			'woocommerce_product_add_to_cart_url',
			'Class_Bkap_Product_Resource',
			'woocommerce_product_add_to_cart_url_callback'
		);

		add_filter(
			'woocommerce_loop_add_to_cart_link',
			function( $add_to_cart_link ) {
				$new_add_to_cart_link = str_replace( 'add_to_cart_button', 'add_to_cart_button ajax_add_to_cart', $add_to_cart_link );

				return $new_add_to_cart_link;
			},
			10
		);
	}

	/**
	 * Skip booking validation when a product is added via After Add To Cart modal.
	 *
	 * @param bool $skip Whether skip the validation or not.
	 * @return bool
	 */
	public static function skip_booking_validation( $skip ) {
		// The `skipAfterAddToCartModal` data is sent when a product is added via After Add To Cart modal.
		if ( ! isset( $_POST['skipAfterAddToCartModal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $skip;
		}

		return true;
	}

	/**
	 * Remove WordPress filter hook.
	 *
	 * @param string $filter The filter to be removed.
	 * @param string $class The class name used in the callback parameter.
	 * @param string $method The method name used in the callback parameter.
	 * @return void
	 */
	protected static function remove_filter( $filter, $class, $method ) {
		$filters = $GLOBALS['wp_filter'][ $filter ] ?? false;

		if ( empty( $filters->callbacks ) ) {
			return;
		}

		foreach ( $filters->callbacks as $filter_priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( empty( $callback['function'][0] ) || empty( $callback['function'][1] ) ) {
					continue;
				}

				$object          = $callback['function'][0];
				$callback_method = $callback['function'][1];

				if ( ! is_a( $object, $class ) ) {
					return;
				}

				if ( $method !== $callback_method ) {
					return;
				}

				remove_filter( $filter, array( $object, $callback_method ), $filter_priority );

				break;
			}
		}
	}

	/**
	 * Allowing booking when a product is added via Frequently Bought Together section.
	 *
	 * Only the main product will have the booking dates.
	 *
	 * @param bool  $allow_bookings Whether or not allow bookings.
	 * @param array $cart_item_meta The cart item meta array.
	 * @return bool
	 */
	public static function allow_booking_for_frequently_bought_together_products( $allow_bookings, $cart_item_meta ) {
		if ( empty( $cart_item_meta['iconic_wsb_fbt'] ) ) {
			return $allow_bookings;
		}

		if ( empty( $_POST['product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $allow_bookings;
		}

		if ( (int) $_POST['product_id'] === (int) $cart_item_meta['iconic_wsb_fbt'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $allow_bookings;
		}

		return false;
	}
}
