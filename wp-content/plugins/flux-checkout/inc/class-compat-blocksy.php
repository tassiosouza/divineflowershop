<?php
/**
 * Iconic_Flux_Compat_Blocksy.
 *
 * Compatibility with Blocksy Theme.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Blocksy' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Blocksy.
 *
 * @class    Iconic_Flux_Compat_Blocksy.
 */
class Iconic_Flux_Compat_Blocksy {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'set_blocksy_global_variable' ) );
	}

	/**
	 * Set this global variable to prevent Blocksy from overriding the checkout template.
	 *
	 * @return void
	 */
	public static function set_blocksy_global_variable() {
		$GLOBALS['ct_skip_checkout'] = 1;
	}
}
