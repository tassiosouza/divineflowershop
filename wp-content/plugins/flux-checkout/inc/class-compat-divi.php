<?php
/**
 * Iconic_Flux_Compat_Divi.
 *
 * Compatibility with Divi.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Divi' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Divi.
 *
 * @class    Iconic_Flux_Compat_Divi.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Divi {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'template_redirect', array( __CLASS__, 'compat_divi' ) );
		add_action( 'wp', array( __CLASS__, 'compat_checkout_elements' ), 20 );
	}

	/**
	 * Disable Divi checkout customisations.
	 */
	public static function compat_divi() {
		if ( ! function_exists( 'et_setup_theme' ) || ! Iconic_Flux_Core::is_checkout() ) {
			return;
		}

		if ( '1' !== Iconic_Flux_Core_Settings::$settings['general_general_enable_header_footer'] ) {
			return;
		}

		add_action( 'flux_before_layout', array( __CLASS__, 'add_divi_header' ) );
		add_action( 'flux_after_layout', array( __CLASS__, 'add_divi_footer' ) );
	}

	/**
	 * Add divi header.
	 *
	 * @return void
	 */
	public static function add_divi_header() {
		if ( ! function_exists( 'et_theme_builder_get_template_layouts' ) || ! function_exists( 'et_theme_builder_frontend_render_header' ) ) {
			return;
		}

		$layouts = et_theme_builder_get_template_layouts();

		if ( empty( $layouts ) || empty( $layouts[ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE ] ) ) {
			return;
		}

		et_theme_builder_frontend_render_header(
			$layouts[ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE ]['id'],
			$layouts[ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE ]['enabled'],
			$layouts[ ET_THEME_BUILDER_TEMPLATE_POST_TYPE ]
		);
	}

	/**
	 * Add Divi footer.
	 *
	 * @return void
	 */
	public static function add_divi_footer() {
		if ( ! function_exists( 'et_theme_builder_get_template_layouts' ) || ! function_exists( 'et_theme_builder_frontend_render_footer' ) ) {
			return;
		}

		$layouts = et_theme_builder_get_template_layouts();

		if ( empty( $layouts ) || empty( $layouts[ ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ] ) ) {
			return;
		}

		et_theme_builder_frontend_render_footer(
			$layouts[ ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ]['id'],
			$layouts[ ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ]['enabled'],
			$layouts[ ET_THEME_BUILDER_TEMPLATE_POST_TYPE ]
		);
	}

	/**
	 * Fix issue where checkout elements built with Divi builder vanish
	 * after the order review Ajax is completed.
	 *
	 * @return void
	 */
	public static function compat_checkout_elements() {
		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'update_order_review' !== $wc_ajax ) {
			return;
		}

		remove_filter( 'the_content', 'et_fb_app_boot', 1 );
	}
}
