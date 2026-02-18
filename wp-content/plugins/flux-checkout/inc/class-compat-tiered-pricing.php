<?php
/**
 * Iconic_Flux_Compat_Tiered_Pricing.
 *
 * Compatibility with WooCommerce Tiered Price Table (Premium).
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Tiered_Pricing' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Tiered_Pricing.
 *
 * @class    Iconic_Flux_Compat_Tiered_Pricing.
 */
class Iconic_Flux_Compat_Tiered_Pricing {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		if ( ! class_exists( 'TierPricingTable\TierPricingTablePlugin' ) ) {
			return;
		}

		self::fix_issue_with_remove_button();
	}

	/**
	 * Fix issue where the Remove button doesn't appear in the order review section
	 * when using WooCommerce Tiered Price Table (Premium).
	 */
	public static function fix_issue_with_remove_button() {
		// Increase priority of cart item subtotal.
		remove_filter( 'woocommerce_cart_item_subtotal', array( 'Iconic_Flux_Sidebar', 'cart_remove_link' ), 100 );
		add_filter( 'woocommerce_cart_item_subtotal', array( 'Iconic_Flux_Sidebar', 'cart_remove_link' ), 1000, 3 );
	}
}
