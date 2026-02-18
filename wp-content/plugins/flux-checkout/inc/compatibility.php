<?php
/**
 * Compatibility.
 *
 * Provides backwards compatibility for legacy functions.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Backwards compatibility for assets url.
 *
 * @param string $url URL.
 *
 * @return string
 */
function wc_asset_url( $url ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return ICONIC_FLUX_PATH . $url;
}

/**
 * Backwards compatibility for assets url.
 */
function flux_remove_checkout_shipping() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::remove_checkout_shipping()' );
	Iconic_Flux_Core::remove_checkout_shipping();
}

/**
 * Backwards compatibility for loading textdomain.
 */
function flux_checkout_load_plugin_textdomain() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::checkout_load_plugin_textdomain()' );
	Iconic_Flux_Core::checkout_load_plugin_textdomain();
}

/**
 * Backwards compatibility for overriding checkout fields.
 *
 * @param array $fields Fields.
 *
 * @return array
 */
function flux_custom_override_checkout_fields( $fields ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::custom_override_checkout_fields()' );
	return Iconic_Flux_Core::custom_override_checkout_fields( $fields );
}

/**
 * Backwards compatibility for setting field priority.
 *
 * @param array  $fields_group Field group.
 * @param string $field_id     Field ID.
 * @param int    $priority     Priority.
 */
function flux_set_field_priority( &$fields_group, $field_id, $priority ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::set_field_priority()' );
	Iconic_Flux_Core::set_field_priority( $fields_group, $field_id, $priority );
}

/**
 * Backwards compatibility for overriding default fields.
 *
 * @param array $fields Fields.
 *
 * @return array
 */
function flux_custom_override_default_fields( $fields ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::custom_override_default_fields()' );
	return Iconic_Flux_Core::custom_override_default_fields( $fields );
}

/**
 * Backwards compatibility for overriding default fields.
 *
 * @return array
 */
function flux_get_allowed_details_fields() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_allowed_details_fields()' );
	return Iconic_Flux_Helpers::get_allowed_details_fields();
}

/**
 * Backwards compatibility for getting details fields.
 *
 * @param object $checkout Checkout.
 *
 * @return array
 */
function flux_get_details_fields( $checkout ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_details_fields()' );
	return Iconic_Flux_Helpers::get_details_fields( $checkout );
}

/**
 * Backwards compatibility for checking if flux uses autocomplete.
 *
 * @return bool
 */
function flux_use_autocomplete() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::use_autocomplete()' );
	return Iconic_Flux_Helpers::use_autocomplete();
}


/**
 * Backwards compatibility for getting billing fields.
 *
 * @param object $checkout Checkout.
 *
 * @return array
 */
function flux_get_billing_fields( $checkout ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_billing_fields()' );
	return Iconic_Flux_Helpers::get_billing_fields( $checkout );
}

/**
 * Backwards compatibility for checking if flux has pre-populated fields.
 *
 * @param string $type Type.
 *
 * @return bool
 */
function flux_has_prepopulated_fields( $type ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::has_prepopulated_fields()' );
	return Iconic_Flux_Helpers::has_prepopulated_fields( $type );
}

/**
 * Backwards compatibility for getting shipping fields.
 *
 * @param object $checkout Checkout.
 *
 * @return array
 */
function flux_get_shipping_fields( $checkout ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_shipping_fields()' );
	return Iconic_Flux_Helpers::get_shipping_fields( $checkout );
}

/**
 * Backwards compatibility for getting the mobile plugin path.
 *
 * @return string
 */
function flux_mobile_plugin_path() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::mobile_plugin_path()' );
	return Iconic_Flux_Core::plugin_path();
}

/**
 * Backwards compatibility for the print filters for debug function.
 *
 * @param string $hook Hook.
 */
function print_filters_for( $hook = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	global $wp_filter;
	if ( empty( $hook ) || ! isset( $wp_filter[ $hook ] ) ) {
		return;
	}

	print '<pre>';
	print_r( $wp_filter[ $hook ] ); // @codingStandardsIgnoreLine.
	print '</pre>';
}

/**
 * Backwards compatibility for the field arguments.
 *
 * @param array  $data Data.
 * @param string $key Key.
 * @param string $value Value.
 *
 * @return array
 */
function flux_field_args( $data, $key, $value ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::field_args()' );
	return Iconic_Flux_Core::field_args( $data, $key, $value );
}

/**
 * Backwards compatibility for removing empty placeholders.
 *
 * @param array $locale_base Locale Base.
 *
 * @return array
 */
function flux_remove_empty_placeholders( $locale_base ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::remove_empty_placeholders()' );
	return Iconic_Flux_Core::remove_empty_placeholders( $locale_base );
}

/**
 * Backwards compatibility for removing empty placeholders from the HTML.
 *
 * @param string $field Field.
 * @param string $key Key.
 * @param array  $args Args.
 * @param string $value Value.
 *
 * @return string
 */
function flux_remove_empty_placeholders_html( $field, $key, $args, $value ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::remove_empty_placeholders_html()' );
	return Iconic_Flux_Core::remove_empty_placeholders_html( $field, $key, $args, $value );
}

/**
 * Backwards compatibility for modifying the form field HTML.
 *
 * @param string $field Field.
 * @param string $key Key.
 * @param array  $args Args.
 * @param string $value Value.
 */
function flux_modify_form_field_html( $field, $key, $args, $value ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility for modifying the form field placeholder.
 *
 * @param string $field Field.
 *
 * @return string
 */
function flux_modify_form_field_replace_placeholder( $field ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::modify_form_field_replace_placeholder()' );
	return Iconic_Flux_Core::modify_form_field_replace_placeholder( $field );
}

/**
 * Backwards compatibility for hiding the admin bar when flux is active.
 *
 * @param bool $show_admin_bar Show admin bar.
 *
 * @return bool
 */
function flux_show_admin_bar( $show_admin_bar ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::show_admin_bar()' );
	return Iconic_Flux_Core::show_admin_bar( $show_admin_bar );
}

/**
 * Backwards compatibility for checking if flux is in desktop mode.
 *
 * @return boolean
 */
function flux_is_desktop() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::is_desktop()' );
	return Iconic_Flux_Core::is_desktop();
}

/**
 * Backwards compatibility for enqueueing scripts.
 */
function flux_mobile_enqueue_scripts() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Assets::frontend_assets()' );
	Iconic_Flux_Assets::frontend_assets();
}

/**
 * Backwards compatibility for enqueueing footer scripts.
 */
function flux_include_footer() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	add_action(
		'wp_footer',
		function() {
			$settings = Iconic_Flux_Core_Settings::$settings;
			// @codingStandardsIgnoreStart.
			echo '<style>';
			echo wp_kses_post( Iconic_Flux_Assets::get_dynamic_styles( $settings ) );
			echo '</style>';
			// @codingStandardsIgnoreEnd.
		}
	);
}

/**
 * Backwards compatibility for getting the CSS File.
 *
 * @return string
 */
function flux_get_css_file() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Assets::get_css_file()' );
	$settings = Iconic_Flux_Core_Settings::$settings;
	return Iconic_Flux_Assets::get_css_file( $settings );
}

/**
 * Backwards compatibility for checking if we are on the flux checkout.
 *
 * @return boolean
 */
function flux_is_checkout() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::is_checkout()' );
	return Iconic_Flux_Core::is_checkout();
}

/**
 * Backwards compatibility to modify the payment button html.
 *
 * @param string $button Button.
 */
function flux_modify_payment_button( $button ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility to open the checkout step wrapper.
 */
function flux_open_checkout_wrap() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility to return the flux icon.
 *
 * @return string
 */
function flux_plugin_icon() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return ICONIC_FLUX_PATH . '/assets/img/plugin-icon.png';
}

/**
 * Backwards compatibility to close the div.
 */
function flux_close_div() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility to dequeue select2.
 */
function flux_dequeue_select2() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility to dequeue woocommerce.
 */
function flux_dequeue_woocommerce() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility pre-pending street number to billing and shipping address_1 field when order is created.
 *
 * @param WC_Order $order Order.
 * @param array    $data  Posted Data.
 */
function flux_prepend_street_number_to_address_1( $order, $data ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Core::prepend_street_number_to_address_1()' );
	Iconic_Flux_Core::prepend_street_number_to_address_1( $order, $data );
}

/**
 * Backwards compatibility for the AJAX callback function to check if a user with given email already exists.
 */
function flux_check_email_exists() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Backwards compatibility to fix titan wpColorPickerL10n error.
 */
function flux_titan_color_picker_translation_fix() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	if ( ! is_admin() ) {
		return;
	}

	$wp_scripts = wp_scripts();

	$wp_scripts->localize(
		'wp-color-picker',
		'wpColorPickerL10n',
		array(
			'clear'            => __( 'Clear' ),
			'clearAriaLabel'   => __( 'Clear color' ),
			'defaultString'    => __( 'Default' ),
			'defaultAriaLabel' => __( 'Select default color' ),
			'pick'             => __( 'Select Color' ),
			'defaultLabel'     => __( 'Color value' ),
		)
	);
}

/**
 * Backwards compatibility to get Logo Image.
 *
 * @return string
 */
function flux_get_logo_image() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_logo_image()' );
	return Iconic_Flux_Helpers::get_logo_image();
}

/**
 * Backwards compatibility to get Header Text.
 *
 * @return string
 */
function flux_get_header_text() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_header_text()' );
	return Iconic_Flux_Helpers::get_header_text();
}

/**
 * Backwards compatibility for order review.
 */
function flux_order_review() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	global $flux_shipping_prefix;
	$flux_shipping_prefix = 'review';
	woocommerce_order_review();
	$flux_shipping_prefix = '';
}

/**
 * Backwards compatibility for converting material palette to radio.
 *
 * @param array $data Data.
 *
 * @return array
 */
function flux_convert_material_palette_to_radio( $data ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return array( $data );
}

/**
 * Backwards compatibility to load the Material Design Pallet.
 *
 * @param boolean $is_accent Colour is Accent.
 *
 * @return array.
 */
function flux_get_material_palette( $is_accent = false ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::get_classic_pallet()' );
	return Iconic_Flux_Helpers::get_classic_pallet( $is_accent );
}

/**
 * Backwards compatibility to convert hex to rgba.
 *
 * @param string $color Colour.
 * @param bool   $opacity Opacity.
 *
 * @return string
 */
function flux_hex2rgba( $color, $opacity = false ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::hex2rgba()' );
	return Iconic_Flux_Helpers::hex2rgba( $color, $opacity );
}

/**
 * Backwards compatibility to return the flux address panel.
 */
function flux_address_panel() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::render_address_panel()' );
	Iconic_Flux_Helpers::render_address_panel();
}

/**
 * Backwards compatibility to return the flux details panel.
 */
function flux_details_panel() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'Iconic_Flux_Helpers::render_details_panel()' );
	Iconic_Flux_Helpers::render_details_panel();
}
