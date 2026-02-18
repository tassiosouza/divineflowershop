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
	'tab_6_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_6_section_callback', // Callback used to render the description of the section.
	'wp_recaptcha_login'                           // Page on which to add this section of options.
);

add_settings_field(
	'wp_login_captcha_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on WP login', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_login_captcha_check_setting',   // The name of the function responsible for rendering.
	'wp_recaptcha_login', // The page on which this option will be displayed.
	'tab_6_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_login',
	'wp_login_captcha_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_login_captcha_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_login_captcha_title_set',   // The name of the function responsible for rendering.
	'wp_recaptcha_login', // The page on which this option will be displayed.
	'tab_6_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_login',
	'wp_login_captcha_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_login_captcha_theme_fields', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_captcha_themes_settings',   // The name of the function responsible for rendering.
	'wp_recaptcha_login', // The page on which this option will be displayed.
	'tab_6_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_login',
	'wp_login_captcha_theme_fields',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_login_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_login_captcha_size_setting',   // The name of the function responsible for rendering.
	'wp_recaptcha_login', // The page on which this option will be displayed.
	'tab_6_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_login',
	'wp_login_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

if ( ! function_exists( 'wp_login_captcha_check_setting' ) ) {
	/**
	 * Callback.
	 */
	function wp_login_captcha_check_setting() {
		?>
		<input type="checkbox" name="wp_login_captcha_check" value="1"<?php checked( get_option( 'wp_login_captcha_check' ), '1' ); ?>>
		<p><?php echo esc_html__( 'Enables captcha for WordPress login page.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'wp_login_captcha_title_set' ) ) {
	/**
	 * Callback.
	 */
	function wp_login_captcha_title_set() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for captcha in WordPress login page.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="wp_login_captcha_title" value="<?php echo esc_attr( get_option( 'wp_login_captcha_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'wp_captcha_themes_settings' ) ) {
	/**
	 * Callback.
	 */
	function wp_captcha_themes_settings() {
		?>
		<input type="radio" name="wp_login_captcha_theme_fields" value="light"<?php checked( get_option( 'wp_login_captcha_theme_fields' ), 'light' ); ?> checked  >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_login_captcha_theme_fields" value="dark"<?php checked( get_option( 'wp_login_captcha_theme_fields' ), 'dark' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'wp_login_captcha_size_setting' ) ) {
	/**
	 * Callback.
	 */
	function wp_login_captcha_size_setting() {
		?>
		<input type="radio" name="wp_login_captcha_size_radio" value="normal"<?php checked( get_option( 'wp_login_captcha_size_radio' ), 'normal' ); ?>  checked  >
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_login_captcha_size_radio" value="compact"<?php checked( get_option( 'wp_login_captcha_size_radio' ), 'compact' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}


if ( ! function_exists( 'tab_6_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_6_section_callback() {
	}
}
