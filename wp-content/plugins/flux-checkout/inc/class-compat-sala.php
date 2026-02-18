<?php
/**
 * Iconic_Flux_Compat_Sala.
 *
 * Compatibility with Sala theme.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Sala' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Sala.
 *
 * @class    Iconic_Flux_Compat_Sala.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Sala {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'after_setup_theme', array( __CLASS__, 'compat_sala' ), 20 );
	}

	/**
	 * Disable Sala checkout customisations.
	 */
	public static function compat_sala() {
		if ( ! class_exists( 'Sala_Woo' ) || ! Iconic_Flux_Core::is_checkout( true ) ) {
			return;
		}

		add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		remove_action( 'woocommerce_after_order_notes', 'woocommerce_checkout_payment', 20 );
	}
}
