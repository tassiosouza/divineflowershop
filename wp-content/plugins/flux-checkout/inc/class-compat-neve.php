<?php
/**
 * Iconic_Flux_Compat_Neve.
 *
 * Compatibility with Neve.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Neve' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Neve.
 *
 * @class    Iconic_Flux_Compat_Neve.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Neve {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'neve_filter_main_modules', array( __CLASS__, 'modify_modules' ) );
	}

	/**
	 * Disable Woo module.
	 *
	 * @param array $modules Array of modules.
	 *
	 * @return mixed
	 */
	public static function modify_modules( $modules ) {
		if ( ! Iconic_Flux_Core::is_checkout( true ) ) {
			return $modules;
		}

		$key = array_search( 'Compatibility\WooCommerce', $modules );

		if ( false !== $key ) {
			unset( $modules[ $key ] );
		}

		return $modules;
	}
}
