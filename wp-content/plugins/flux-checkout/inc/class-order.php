<?php
/**
 * Iconic_Flux_Order.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Order.
 *
 * @class    Iconic_Flux_Order.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Order {
	/**
	 * Run.
	 *
	 * @return void
	 */
	public static function run() {
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'maybe_assign_guest_order_to_existing_customer' ) );
		add_filter( 'woocommerce_order_received_verify_known_shoppers', array( __CLASS__, 'verify_known_shoppers' ), 10 );
	}

	/**
	 * For the guest orders, check if there exists a user with matching email.
	 * If it does then assign this order to the user.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public static function maybe_assign_guest_order_to_existing_customer( $order_id ) {
		if ( empty( Iconic_Flux_Core_Settings::$settings['general_user_auto_assign_guest_orders'] ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return;
		}

		if ( 0 !== $order->get_user_id() ) {
			return;
		}

		$email = $order->get_billing_email();

		// Check if there is an existing user with the given email address.
		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			return;
		}

		// Assign the order to the existing user.
		$order->set_customer_id( $user->ID );
		$order->save();
	}

	/**
	 * Disable verify known shoppers.
	 *
	 * @param bool $verify Verify known shoppers.
	 *
	 * @return bool
	 */
	public static function verify_known_shoppers( $verify ) {
		if ( empty( Iconic_Flux_Core_Settings::$settings['general_user_auto_assign_guest_orders'] ) ) {
			return $verify;
		}

		return false;
	}
}
