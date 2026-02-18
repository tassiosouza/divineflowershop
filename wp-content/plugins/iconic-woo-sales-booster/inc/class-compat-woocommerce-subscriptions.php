<?php
/**
 * Compatibility with WooCommerce Subscriptions
 *
 * @package woo-sales-booster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WooCommerce_Subscriptions class.
 *
 * @class    Iconic_WSB_Compat_WooCommerce_Subscriptions
 * @since    1.11.0
 */
class Iconic_WSB_Compat_WooCommerce_Subscriptions {
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
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return;
		}

		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'remove_check_for_removing' ), 5 );
		add_action( 'woocommerce_after_calculate_totals', array( __CLASS__, 're_add_check_for_removing' ), 5 );
	}

	/**
	 * Remove `check_for_removing` from the hook `woocommerce_before_calculate_totals`.
	 *
	 * The WooCommerce Subscriptions calculates the initial and recurring totals for
	 * all subscription products in the cart by clonning the cart. In this process,
	 * it triggers the `woocommerce_before_calculate_totals` and
	 * `woocommerce_after_calculate_totals`.
	 *
	 * Since we check for removing if the bumps are suitable on the hook
	 * `woocommerce_before_calculate_totals`, we need to skip this
	 * check in this case.
	 *
	 * @param WC_Cart $cart The cart.
	 * @return void
	 */
	public static function remove_check_for_removing( $cart ) {
		// WooCommerce Subscriptions adds the key `recurring_carts`.
		if ( isset( $cart->recurring_carts ) ) {
			remove_action(
				'woocommerce_before_calculate_totals',
				array(
					Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance(),
					'check_for_removing',
				),
				100
			);

			remove_action(
				'woocommerce_before_calculate_totals',
				array(
					Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance(),
					'check_for_removing',
				),
				100
			);
		}
	}

	/**
	 * Re-add `check_for_removing` to the hook `woocommerce_before_calculate_totals`.
	 *
	 * @see remove_check_for_removing documentation.
	 *
	 * @param WC_Cart $cart The cart.
	 * @return void
	 */
	public static function re_add_check_for_removing( $cart ) {
		if ( isset( $cart->recurring_carts ) ) {
			add_action(
				'woocommerce_before_calculate_totals',
				array(
					Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance(),
					'check_for_removing',
				),
				100
			);

			add_action(
				'woocommerce_before_calculate_totals',
				array(
					Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance(),
					'check_for_removing',
				),
				100
			);
		}
	}

}
