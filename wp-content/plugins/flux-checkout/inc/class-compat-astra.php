<?php
/**
 * Iconic_Flux_Compat_Astra.
 *
 * Compatibility with Astra.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Astra' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Astra.
 *
 * @class    Iconic_Flux_Compat_Astra.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Astra {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'compat_astra' ) );
		add_action( 'init', array( __CLASS__, 'compat_astra_pro_woo_extension' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'compat_astra_dequeue_scripts' ), 100 );
		add_filter( 'astra_addon_enqueue_assets', array( __CLASS__, 'prevent_astra_pro_assets' ) );
	}

	/**
	 * Disable astra checkout customisations.
	 */
	public static function compat_astra() {
		if ( ! class_exists( 'Astra_Woocommerce' ) || ! Iconic_Flux_Core::is_checkout() ) {
			return;
		}

		$astra = Astra_Woocommerce::get_instance();

		remove_action( 'wp', array( $astra, 'woocommerce_checkout' ) );
		remove_action( 'wp_enqueue_scripts', array( $astra, 'add_styles' ) );
		remove_filter( 'woocommerce_enqueue_styles', array( $astra, 'woo_filter_style' ) );
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
		remove_filter( 'woocommerce_cart_item_remove_link', array( $astra, 'change_cart_close_icon' ), 10, 2 );
	}

	/**
	 * Compatibility with Astra Pro WooCommerce Extension.
	 *
	 * @return void
	 */
	public static function compat_astra_pro_woo_extension() {
		if ( ! class_exists( 'ASTRA_Ext_WooCommerce_Markup' ) ) {
			return;
		}

		$astra_woo_markup = ASTRA_Ext_WooCommerce_Markup::get_instance();
		remove_action( 'wp', array( $astra_woo_markup, 'modern_checkout' ) );
	}

	/**
	 * Dequeue scripts.
	 */
	public static function compat_astra_dequeue_scripts() {
		wp_dequeue_script( 'astra-checkout-persistence-form-data' );
	}

	/**
	 * Prevent Astra Pro assets from loading on the checkout page.
	 *
	 * @param bool $load_assets Whether to load assets.
	 *
	 * @return bool
	 */
	public static function prevent_astra_pro_assets( $load_assets ) {
		if ( Iconic_Flux_Core::is_flux_template() ) {
			return false;
		}

		return $load_assets;
	}

}
