<?php
/**
 * Iconic_Flux_Compat_Fastcart.
 *
 * Compatibility with Fast Cart by Barn2.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Fastcart' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Fastcart.
 *
 * @class    Iconic_Flux_Compat_Fastcart.
 * @version  2.3.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Fastcart {
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
		if ( ! class_exists( 'Barn2\Plugin\WC_Fast_Cart\Plugin' ) ) {
			return;
		}

		remove_action( 'template_redirect', array( 'Iconic_Flux_Sidebar', 'redirect_template_to_checkout' ), 10 );
	}
}
