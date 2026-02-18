<?php
/**
 * Iconic_WSB_Compat_Divi class
 *
 * @package woo-sales-booster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_Divi class.
 *
 * @class    Iconic_WSB_Compat_Divi
 * @since    1.10.0
 */
class Iconic_WSB_Compat_Divi {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		$theme = wp_get_theme( 'Divi' );

		if ( ! $theme->exists() ) {
			return;
		}

		add_filter( 'iconic_wsb_should_render_order_bump_shortcode', array( __CLASS__, 'allow_order_bump_shortcode_on_visual_builder' ), 10, 1 );
	}

	/**
	 * Allow Checkout Bump shortcode on Divi Visual Builder.
	 *
	 * @param bool $is_checkout The result of is_checkout().
	 *
	 * @return bool
	 */
	public static function allow_order_bump_shortcode_on_visual_builder( $is_checkout ) {
		if ( empty( $_REQUEST['et_pb_preview_nonce'] ) ) {
			return $is_checkout;
		}

		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['et_pb_preview_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'et_pb_preview_nonce' ) ) {
			return $is_checkout;
		}

		if ( ! function_exists( 'is_et_pb_preview' ) || ! is_et_pb_preview() ) {
			return $is_checkout;
		}

		if ( empty( $_POST['shortcode'] ) || '[iconic_wsb_order_bump]' !== $_POST['shortcode'] ) {
			return $is_checkout;
		}

		return true;
	}
}
