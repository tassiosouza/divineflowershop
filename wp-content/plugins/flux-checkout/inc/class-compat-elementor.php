<?php
/**
 * Iconic_Flux_Compat_Elementor.
 *
 * Compatibility with Elementor.
 *
 * @package Iconic_Flux
 */

use ElementorPro\Modules\ThemeBuilder\Module;
use Elementor\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Elementor' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Elementor.
 *
 * @class    Iconic_Flux_Compat_Elementor.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Elementor {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'compat_elementor_theme_builder' ) );
		add_action( 'wp', array( __CLASS__, 'fix_autop_issue' ) );
	}

	/**
	 * Compatiblity with Elementor's theme builder.
	 */
	public static function compat_elementor_theme_builder() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return;
		}

		if ( '1' !== Iconic_Flux_Core_Settings::$settings['general_general_enable_header_footer'] ) {
			return;
		}

		add_action( 'flux_before_layout', array( __CLASS__, 'add_elementor_header' ) );
		add_action( 'flux_after_layout', array( __CLASS__, 'add_elementor_footer' ) );
	}

	/**
	 * Include Elementor theme builder's Header.
	 */
	public static function add_elementor_header() {
		$location_manager = Module::instance()->get_locations_manager();
		$location_manager->do_location( 'header' );
	}

	/**
	 * Include Elementor theme builder's Header.
	 */
	public static function add_elementor_footer() {
		$location_manager = Module::instance()->get_locations_manager();
		$location_manager->do_location( 'footer' );
	}

	/**
	 * Disable autop
	 *
	 * @return void
	 */
	public static function fix_autop_issue() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		if ( is_checkout() ) {
			remove_action( 'the_content', 'wpautop' );
		}
	}
}
