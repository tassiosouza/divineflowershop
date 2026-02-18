<?php
/**
 * Iconic_Flux_Compat_Breakance.
 *
 * Compatibility with Breakdance builder.
 *
 * @package Iconic_Flux
 */

use Breakdance\GlobalDefaultStylesheets\GlobalDefaultStylesheetsController;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Breakdance' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Breakance.
 *
 * @class    Iconic_Flux_Compat_Breakance.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Breakdance {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp', array( __CLASS__, 'compat_breakdance' ) );
	}

	/**
	 * Disable Breakdance template functions.
	 */
	public static function compat_breakdance() {
		if ( ! function_exists( 'Breakdance\ActionsFilters\template_include' ) ) {
			return;
		}

		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return;
		}

		self::unhook_unonymous_callbacks( 'wc_get_template', 10 );
		self::remove_breakdance_woocommerce_css();

		if ( '1' !== Iconic_Flux_Core_Settings::$settings['general_general_enable_header_footer'] ) {
			return;
		}

		// Header and footer compatibility code.

		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_footer', array( __CLASS__, 'custom_style' ) );

		add_action(
			'flux_before_layout',
			function() {
				\Breakdance\Themeless\override_get_header();
			}
		);

		add_action(
			'flux_after_layout',
			function() {
				\Breakdance\Themeless\override_get_footer();
			}
		);
	}

	/**
	 * Add 'breakdance' to the body class.
	 *
	 * @param array $classes Classes.
	 *
	 * @return array
	 */
	public static function body_class( $classes ) {
		$classes[] = 'breakdance';
		return $classes;
	}

	/**
	 * Unhook unanymous/closure functions from the given action and prority.
	 *
	 * @param string $action   Action.
	 * @param int    $priority Priority.
	 *
	 * @return void
	 */
	public static function unhook_unonymous_callbacks( $action, $priority ) {
		global $wp_filter;

		if ( empty( $wp_filter[ $action ] ) || empty( $wp_filter[ $action ]->callbacks[ $priority ] ) ) {
			return;
		}

		foreach ( $wp_filter[ $action ]->callbacks[ $priority ] as $function ) {
			if ( is_object( $function['function'] ) ) {
				unset( $wp_filter[ $action ]->callbacks[ $priority ] );
			}
		}
	}

	/**
	 * Remove breakdance WooCommerce css.
	 */
	public static function remove_breakdance_woocommerce_css() {
		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return;
		}

		$stylesheet_controller = GlobalDefaultStylesheetsController::getInstance();
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$urls = $stylesheet_controller->stylesheetUrls;

		foreach ( $urls as $index => $url ) {
			if ( str_contains( $url, 'breakdance-woocommerce.css' ) ) {
				unset( $urls[ $index ] );
			}
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$stylesheet_controller->stylesheetUrls = $urls;
	}

	/**
	 * Add Custom styles.
	 */
	public static function custom_style() {
		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return;
		}
		?>
		<style>
			button.breakdance-menu-link-arrow {
				background: transparent;
			}

			h2#order_review_heading {
				font-size: 20px;
			}
		</style>
		<?php
	}
}
