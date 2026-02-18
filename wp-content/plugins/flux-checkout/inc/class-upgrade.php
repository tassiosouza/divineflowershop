<?php
/**
 * Iconic_Flux_Upgrade.
 *
 * Upgrade the settings if needed.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Upgrade.
 *
 * Upgrade the settings if needed.
 *
 * @class    Iconic_Flux_Upgrade
 * @version  1.0.0
 */
class Iconic_Flux_Upgrade {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'plugins_loaded', array( __CLASS__, 'upgrade' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'update_google_api_key_setting' ), 11 );
	}

	/**
	 * Run on upgrade.
	 */
	public static function upgrade() {
		// Check if we have upgraded the legacy settings.
		if ( get_option( 'iconic_flux_after_titan_flag' ) ) {
			return;
		}

		// Migrate the old settings to the new settings.
		$classic_settings = maybe_unserialize( get_option( 'flux-settings_options' ) );
		$settings         = get_option( 'iconic_flux_settings' );
		$updated          = false;
		$mappings         = array(
			'general_general' => array(
				'google_api_key',
				'show_company_field',
				'use_autocomplete',
				'store_checkout_data',
				'separate_street_number_field',
			),
			'styles_header'   => array(
				'branding',
				'logo_image',
				'header_text',
				'header_font_family',
				'header_font_colour',
				'header_font_size',
				'header_background',
				'background',
				'custom_header_color',
				'cart_icon_color',
			),
			'styles_checkout' => array(
				'use_custom_colors',
				'primary_color',
				'accent_color',
				'custom_primary_color',
				'custom_accent_color',
				'custom_css',
			),
		);

		foreach ( $mappings as $key => $maps ) {
			foreach ( $maps as $map ) {
				if ( ! isset( $classic_settings[ $map ] ) ) {
					continue;
				}

				if ( isset( $settings[ $key . '_' . $map ] ) ) {
					continue;
				}

				$settings[ $key . '_' . $map ] = $classic_settings[ $map ];

				// If its a logo, we need to get the path instead of the ID.
				if ( 'logo_image' === $map ) {
					$settings[ $key . '_' . $map ] = wp_get_attachment_url( $classic_settings[ $map ] );
				}

				$updated = true;
			}
		}

		// Handle `header_font` settings.
		if ( isset( $classic_settings['header_font'] ) ) {
			$header_font = maybe_unserialize( $classic_settings['header_font'] );

			if ( isset( $header_font['font-family'] ) && ! isset( $settings['styles_header_header_font_family'] ) ) {
				$settings['styles_header_header_font_family'] = $header_font['font-family'];
				$updated                                      = true;
			}

			if ( isset( $header_font['color'] ) && ! isset( $settings['styles_header_header_font_colour'] ) ) {
				$settings['styles_header_header_font_colour'] = $header_font['color'];
				$updated                                      = true;
			}

			if ( isset( $header_font['font-size'] ) && ! isset( $settings['styles_header_header_font_size'] ) ) {
				$settings['styles_header_header_font_size'] = $header_font['font-size'];
				$updated                                    = true;
			}
		}

		if ( $updated ) {
			update_option( 'iconic_flux_settings', $settings );
		}

		// Set the flag.
		update_option( 'iconic_flux_after_titan_flag', true );
	}

	/**
	 * Update Google API key setting.
	 *
	 * @return void
	 */
	public static function update_google_api_key_setting() {
		$settings = get_option( 'iconic_flux_settings' );

		if ( ! empty( $settings['general_general_google_api_key'] ) && empty( $settings['integrations_integrations_google_api_key'] ) ) {
			$settings['integrations_integrations_google_api_key'] = $settings['general_general_google_api_key'];
			update_option( 'iconic_flux_settings', $settings );
		}
	}
}
