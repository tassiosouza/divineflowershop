<?php
/**
 * Iconic_Flux_Compat_Advanced_Nocaptcha.
 *
 * Compatibility with Advanced noCaptcha & invisible Captcha (v2 & v3) plugin.
 * [https://wordpress.org/plugins/advanced-nocaptcha-recaptcha/]
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Advanced_Nocaptcha' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Advanced_Nocaptcha.
 *
 * @class    Iconic_Flux_Compat_Advanced_Nocaptcha.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Advanced_Nocaptcha {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'add_captcha' ) );
	}

	/**
	 * Add captcha field.
	 */
	public static function add_captcha() {
		if ( ! class_exists( 'anr_captcha_class' ) ) {
			return;
		}

		$anr_captcha = anr_captcha_class::init();
		add_action( 'woocommerce_review_order_before_payment', array( $anr_captcha, 'wc_form_field' ), 10 );
	}
}
