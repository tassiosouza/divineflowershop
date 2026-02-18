<?php
/**
 * Define fille.
 *
 * @package Google_reCaptcha_for_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_settings_section(
	'tab_4_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_4_section_callback', // Callback used to render the description of the section.
	'recaptcha-woo-lpass'                           // Page on which to add this section of options.
);

add_settings_field(
	'woo_lpass_add_captcha_enable_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on Woo-commerce Lost Password', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'lpass_captcha_enable_check',   // The name of the function responsible for rendering.
	'recaptcha-woo-lpass', // The page on which this option will be displayed.
	'tab_4_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-lpass',
	'woo_lpass_add_captcha_enable_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'lpass_add_captcha_field_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'lpass_captcha_field_title_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-lpass', // The page on which this option will be displayed.
	'tab_4_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-lpass',
	'lpass_add_captcha_field_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'woo_lpass_add_captcha_authentication_key_fields', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes ', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'woo_lpass_captcha_reg_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-lpass', // The page on which this option will be displayed.
	'tab_4_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-lpass',
	'woo_lpass_add_captcha_authentication_key_fields',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'woo_lpass_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'woo_lpass_captcha_reg_size_radio_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-lpass', // The page on which this option will be displayed.
	'tab_4_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-lpass',
	'woo_lpass_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

if ( ! function_exists( 'lpass_captcha_enable_check' ) ) {
	/**
	 * Callback.
	 */
	function lpass_captcha_enable_check() {
		?>
		<input type="checkbox" name="woo_lpass_add_captcha_enable_check"value="1"<?php checked( get_option( 'woo_lpass_add_captcha_enable_check' ), '1' ); ?>><p><?php echo esc_html__( 'Enables captcha for Woo Commerce lost password page', 'recaptcha_verification' ); ?></p>
		<br>
		<?php
	}
}
if ( ! function_exists( 'lpass_captcha_field_title_callback' ) ) {
	/**
	 * Callback.
	 */
	function lpass_captcha_field_title_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field Text for Woo Commerce lost password page.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="lpass_add_captcha_field_title" value="<?php echo esc_attr( get_option( 'lpass_add_captcha_field_title' ) ); ?>">
		<?php
	}
}

if ( ! function_exists( 'woo_lpass_captcha_reg_authentication_callback' ) ) {
	/**
	 * Callback.
	 */
	function woo_lpass_captcha_reg_authentication_callback() {
		?>
		<input type="radio" name="woo_lpass_add_captcha_authentication_key_fields" value="light"<?php checked( get_option( 'woo_lpass_add_captcha_authentication_key_fields' ), 'light' ); ?> checked  >
		<label for="normal"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="woo_lpass_add_captcha_authentication_key_fields" value="dark"<?php checked( get_option( 'woo_lpass_add_captcha_authentication_key_fields' ), 'dark' ); ?> >
		<label for="compact"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'woo_lpass_captcha_reg_size_radio_callback' ) ) {
	/**
	 * Callback.
	 */
	function woo_lpass_captcha_reg_size_radio_callback() {
		?>
		<input type="radio" name="woo_lpass_add_captcha_size_radio" value="normal"<?php checked( get_option( 'woo_lpass_add_captcha_size_radio' ), 'normal' ); ?> checked>
		<label for="normal"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="woo_lpass_add_captcha_size_radio" value="compact"<?php checked( get_option( 'woo_lpass_add_captcha_size_radio' ), 'compact' ); ?>>
		<label for="compact"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'tab_4_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_4_section_callback() {
	}
}
