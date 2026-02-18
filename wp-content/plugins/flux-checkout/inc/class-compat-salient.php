<?php
/**
 * Iconic_Flux_Compat_Salient.
 *
 * Compatibility with Salient.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Salient' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Salient.
 *
 * @class    Iconic_Flux_Compat_Salient.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Salient {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp', array( __CLASS__, 'compat_salient' ) );
	}

	/**
	 * Disable Salient checkout customisations.
	 */
	public static function compat_salient() {
		if ( ! function_exists( 'nectar_get_theme_version' ) || ! Iconic_Flux_Core::is_checkout() ) {
			return;
		}

		remove_action( 'woocommerce_before_quantity_input_field', 'nectar_quantity_markup_mod_before', 10 );
		remove_action( 'woocommerce_after_quantity_input_field', 'nectar_quantity_markup_mod_after', 10 );
	}
}
