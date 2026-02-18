<?php
/**
 * Iconic_Flux_Compat_Beaver_Builder.
 *
 * Compatibility with Beaver Builder.
 *
 * @package Iconic_Flux
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Beaver_Builder' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Beaver_Builder.
 *
 * @class    Iconic_Flux_Compat_Beaver_Builder.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Beaver_Builder {
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
		if ( ! class_exists( 'FLThemeBuilderLoader' ) ) {
			return;
		}

		add_action( 'flux_before_layout', 'FLThemeBuilderLayoutRenderer::render_header' );
		add_action( 'flux_after_layout', 'FLThemeBuilderLayoutRenderer::render_footer' );
	}
}
