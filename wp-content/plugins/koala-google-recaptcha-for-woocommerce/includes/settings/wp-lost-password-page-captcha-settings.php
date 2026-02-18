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
	'tab_8_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_8_section_callback', // Callback used to render the description of the section.
	'wp_recaptcha_lostpassword'                           // Page on which to add this section of options.
);

add_settings_field(
	'wp_lpass_page_enable_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on WP Lost Password', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_lpass_captcha_enable_check',   // The name of the function responsible for rendering.
	'wp_recaptcha_lostpassword', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_lostpassword',
	'wp_lpass_page_enable_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_lpass_add_captcha_field_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_lpass_captcha_field_title_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_lostpassword', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_lostpassword',
	'wp_lpass_add_captcha_field_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_lpass_add_captcha_authentication_key_fields', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_lpass_captcha_reg_authentication_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_lostpassword', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_lostpassword',
	'wp_lpass_add_captcha_authentication_key_fields',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_lpass_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_lpass_captcha_reg_size_radio_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_lostpassword', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_lostpassword',
	'wp_lpass_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

if ( ! function_exists( 'wp_lpass_captcha_enable_check' ) ) {
	/**
	 * Callback.
	 */
	function wp_lpass_captcha_enable_check() {
		?>
		<input type="checkbox" name="wp_lpass_page_enable_check" value="1"<?php checked( get_option( 'wp_lpass_page_enable_check' ), '1' ); ?>>
		<p><?php echo esc_html__( 'Enables captcha for WordPress lost password.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}

if ( ! function_exists( 'wp_lpass_captcha_field_title_callback' ) ) {
	/**
	 * Callback.
	 */
	function wp_lpass_captcha_field_title_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for WordPress lost password captcha', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="wp_lpass_add_captcha_field_title" value="<?php echo esc_attr( get_option( 'wp_lpass_add_captcha_field_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'wp_lpass_captcha_reg_authentication_callback' ) ) {
	/**
	 * Callback.
	 */
	function wp_lpass_captcha_reg_authentication_callback() {
		?>
		<input type="radio" name="wp_lpass_add_captcha_authentication_key_fields" value="light"<?php checked( get_option( 'wp_lpass_add_captcha_authentication_key_fields' ), 'light' ); ?>  checked >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_lpass_add_captcha_authentication_key_fields" value="dark"<?php checked( get_option( 'wp_lpass_add_captcha_authentication_key_fields' ), 'dark' ); ?>     >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'wp_lpass_captcha_reg_size_radio_callback' ) ) {
	/**
	 * Callback.
	 */
	function wp_lpass_captcha_reg_size_radio_callback() {
		?>
		<input type="radio" name="wp_lpass_add_captcha_size_radio"value="normal"<?php checked( get_option( 'wp_lpass_add_captcha_size_radio' ), 'normal' ); ?> checked>
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_lpass_add_captcha_size_radio" value="compact"<?php checked( get_option( 'wp_lpass_add_captcha_size_radio' ), 'compact' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}

if ( ! function_exists( 'tab_8_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_8_section_callback() {
	}
}
