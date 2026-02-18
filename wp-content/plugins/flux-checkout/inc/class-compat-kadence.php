<?php
/**
 * Iconic_Flux_Compat_Kadence.
 *
 * Compatibility with Kadence.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Kadence' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Kadence.
 *
 * @class    Iconic_Flux_Compat_Kadence.
 */
class Iconic_Flux_Compat_Kadence {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hooks
	 */
	public static function hooks() {
		if ( ! defined( 'KADENCE_VERSION' ) ) {
			return;
		}

		add_filter( 'flux_checkout_allowed_sources', array( __CLASS__, 'allow_kadnece_sources' ) );
	}

	/**
	 * Allow essential Kadence CSS and JS.
	 *
	 * @param array $allowed_sources Allowed sources.
	 *
	 * @return array
	 */
	public static function allow_kadnece_sources( $allowed_sources ) {
		$allowed_sources[] = site_url() . '/wp-content/themes/kadence/assets/css/global.min.css';
		$allowed_sources[] = site_url() . '/wp-content/themes/kadence/assets/css/global.css';
		$allowed_sources[] = site_url() . '/wp-content/themes/kadence/assets/js/navigation.min.js';
		return $allowed_sources;
	}
}
