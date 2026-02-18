<?php
/**
 * Iconic_Flux_Compat_Bricks.
 *
 * Compatibility with Bricks Theme.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Bricks' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Bricks.
 *
 * @class    Iconic_Flux_Compat_Bricks.
 */
class Iconic_Flux_Compat_Bricks {
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
		if ( ! defined( 'BRICKS_VERSION' ) ) {
			return;
		}

		self::compat_header_footer_builder();
		self::remove_hooks();
	}

	/**
	 * Compatibility with Bricks Header Footer Builder.
	 */
	public static function compat_header_footer_builder() {
		if ( '1' !== Iconic_Flux_Core_Settings::$settings['general_general_enable_header_footer'] ) {
			return;
		}

		add_filter( 'flux_checkout_allowed_sources', array( __CLASS__, 'allowed_sources' ) );
		add_filter( 'wp_footer', array( __CLASS__, 'add_custom_css' ) );

		// Add actions to render the header and footer.
		add_action(
			'flux_before_layout',
			function() {
				/**
				 * Fires the header.
				 *
				 * @since 2.15.0
				 */
				do_action( 'render_header' );
			}
		);

		add_action(
			'flux_after_layout',
			function() {
				/**
				 * Fires the Footer.
				 *
				 * @since 2.15.0
				 */
				do_action( 'render_footer' );
			}
		);
	}


	/**
	 * Remove hooks.
	 */
	public static function remove_hooks() {
		if ( ! is_checkout() ) {
			return;
		}

		$theme = Bricks\Theme::instance();
		remove_action( 'woocommerce_after_quantity_input_field', array( $theme->woocommerce, 'quantity_input_field_add_minus_button' ) );
		remove_action( 'woocommerce_after_quantity_input_field', array( $theme->woocommerce, 'quantity_input_field_add_plus_button' ) );
	}

	/**
	 * Allow theme's assets to be loaded.
	 *
	 * @param array $allowed_sources Allowed sources.
	 *
	 * @return array
	 */
	public static function allowed_sources( $allowed_sources ) {
		$allowed_sources [] = site_url( 'wp-content/themes/bricks/assets/css/frontend.min.css' );
		$allowed_sources [] = site_url( 'wp-content/themes/bricks/assets/css/admin.min.css' );
		$allowed_sources [] = site_url( 'wp-content/themes/bricks/assets/css/frontend/content-default.min.css' );
		$allowed_sources [] = site_url( 'wp-content/themes/bricks/assets/js/bricks.min.js' );

		return $allowed_sources;
	}

	/**
	 * Add custom CSS to the footer.
	 */
	public static function add_custom_css() {
		if ( ! is_checkout() ) {
			return;
		}

		?>
		<style>
			:root {
				--bricks-text-light: #000;
			}
			.brxe-dropdown button:hover {
				background-color: transparent !important;
			}

			span.action.minus {
				display: none;
			}

			span.action.plus {
				display: none;
			}

			.quantity__button {
				text-align: center;
			}
		</style>
		<?php
	}
}
