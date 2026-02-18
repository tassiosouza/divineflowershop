<?php
/**
 * Iconic_Flux_Compat_Visual_Composer.
 *
 * Compatibility with Visual Composer Theme builder.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Visual_Composer' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Visual_Composer.
 *
 * @class    Iconic_Flux_Compat_Visual_Composer.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Visual_Composer {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp', array( __CLASS__, 'compat_visual_composer' ) );
	}

	/**
	 * Compatibility with Visual Composer Theme builder.
	 */
	public static function compat_visual_composer() {
		if ( ! defined( 'VCV_VERSION' ) ) {
			return;
		}

		$header_footer_enabled = Iconic_Flux_Core_Settings::$settings['general_general_enable_header_footer'] ?? '0';
		if ( '1' !== $header_footer_enabled ) {
			return;
		}

		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return;
		}

		add_action( 'flux_before_layout', array( __CLASS__, 'add_header' ) );
		add_action( 'flux_after_layout', array( __CLASS__, 'add_footer' ) );
	}

	/**
	 * Add header.
	 *
	 * Code inspired from:
	 * wp-content/uploads/visualcomposer-assets/addons/themeEditor/themeEditor/views/layouts/vcv-custom-header.php
	 *
	 * @return void
	 */
	public static function add_header() {
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
		$header_enabled = apply_filters( 'vcv:themeEditor:header:enabled', true );

		if ( $header_enabled ) {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
			do_action( 'vcv:themeEditor:before:header' );
			echo '<header class="vcv-header" data-vcv-layout-zone="header">';
			$original_id              = get_the_ID();
			$previous_dynamic_content = \VcvEnv::get( 'DYNAMIC_CONTENT_SOURCE_ID' );

			if ( empty( $previous_dynamic_content ) ) {
				\VcvEnv::set( 'DYNAMIC_CONTENT_SOURCE_ID', $original_id );
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
			do_action( 'vcv:themeEditor:header' );
			\VcvEnv::set( 'DYNAMIC_CONTENT_SOURCE_ID', $previous_dynamic_content );
			echo '</header>';
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
			do_action( 'vcv:themeEditor:after:header' );
		}
		// phpcs:enable
	}

	/**
	 * Add footer.
	 *
	 * Code inspired from:
	 * wp-content/uploads/visualcomposer-assets/addons/themeEditor/themeEditor/views/layouts/vcv-custom-footer.php
	 *
	 * @return void
	 */
	public static function add_footer() {
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
		if ( apply_filters( 'vcv:themeEditor:footer:enabled', true ) ) :
			echo '<footer class="vcv-footer" data-vcv-layout-zone="footer">';
			?>
			<?php
			$original_id              = get_the_ID();
			$previous_dynamic_content = \VcvEnv::get( 'DYNAMIC_CONTENT_SOURCE_ID' );

			if ( empty( $previous_dynamic_content ) ) {
				\VcvEnv::set( 'DYNAMIC_CONTENT_SOURCE_ID', $original_id );
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase, WooCommerce.Commenting.CommentHooks.MissingHookComment, WordPress.NamingConventions.ValidHookName.UseUnderscores
			do_action( 'vcv:themeEditor:footer' );

			\VcvEnv::set( 'DYNAMIC_CONTENT_SOURCE_ID', $previous_dynamic_content );
			?>
			<?php
			echo '</footer>';
		endif;
	}
}
