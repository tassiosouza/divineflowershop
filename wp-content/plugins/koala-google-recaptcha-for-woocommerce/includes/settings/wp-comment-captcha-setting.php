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
	'tab_9_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_9_section_callback', // Callback used to render the description of the section.
	'wp_recaptcha_coment_meta'                           // Page on which to add this section of options.
);

add_settings_field(
	'wp_comment_captcha_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on WP Comment', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_comment_captcha_check_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_coment_meta', // The page on which this option will be displayed.
	'tab_9_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_coment_meta',
	'wp_comment_captcha_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_comment_captcha_user_role', // ID used to identify the field throughout the theme.
	esc_html__( 'Select User role', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_comment_captcha_user_role_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_coment_meta', // The page on which this option will be displayed.
	'tab_9_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_coment_meta',
	'wp_comment_captcha_user_role',
	array(
		'type'              => 'array',
		'sanitize_callback' => 'ka_comment_sanitize_array',
	)
);

add_settings_field(
	'wp_comment_captcha_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_comment_captcha_title_set_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_coment_meta', // The page on which this option will be displayed.
	'tab_9_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_coment_meta',
	'wp_comment_captcha_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_comment_captcha_theme_fields', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_comment_captcha_theme_settings_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_coment_meta', // The page on which this option will be displayed.
	'tab_9_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_coment_meta',
	'wp_comment_captcha_theme_fields',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'wp_comment_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'wp_comment_captcha_size_setting_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_coment_meta', // The page on which this option will be displayed.
	'tab_9_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_coment_meta',
	'wp_comment_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
function ka_comment_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}
if ( ! function_exists( 'wp_comment_captcha_user_role_clbck' ) ) {
	/**
	 * Callback.
	 */
	function wp_comment_captcha_user_role_clbck() {
		global $wp_roles;
		$addf_cc_roles                = $wp_roles->get_names();
		$wp_comment_captcha_user_role = get_option( 'wp_comment_captcha_user_role' );
		?>

		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select user role which you want to show recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>

		<select class="wc-enhanced-select wp_comment_captcha_user_role" name="wp_comment_captcha_user_role[]" multiple style="width: 40%";>
			<?php
			foreach ( $addf_cc_roles as $key => $addf_cc_role ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"
					<?php
					if ( in_array( $key, (array) $wp_comment_captcha_user_role, true ) ) {
						echo 'selected="selected"'; }
					?>
					>
					<?php echo esc_html( $addf_cc_role ); ?></option>
			<?php } ?>
			<option value="guest"  
			<?php
			if ( in_array( 'guest', (array) $wp_comment_captcha_user_role, true ) ) {
				echo 'selected="selected"'; }
			?>
			><?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?></option>
		</select>
		<?php
	}
}

if ( ! function_exists( 'wp_comment_captcha_check_clbck' ) ) {
	/**
	 * Callback.
	 */
	function wp_comment_captcha_check_clbck() {
		?>
		<input type="checkbox" name="wp_comment_captcha_check" value="1"<?php checked( get_option( 'wp_comment_captcha_check' ), '1' ); ?>>
		<p><?php echo esc_html__( 'Enables captcha for WordPress Comment page', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'wp_comment_captcha_title_set_clbck' ) ) {
	/**
	 * Callback.
	 */
	function wp_comment_captcha_title_set_clbck() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for WordPress registration captcha.', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="text" name="wp_comment_captcha_title" value="<?php echo esc_attr( get_option( 'wp_comment_captcha_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'wp_comment_captcha_theme_settings_clbck' ) ) {
	/**
	 * Callback.
	 */
	function wp_comment_captcha_theme_settings_clbck() {
		?>
		<input type="radio" name="wp_comment_captcha_theme_fields"value="light"<?php checked( get_option( 'wp_comment_captcha_theme_fields' ), 'light' ); ?>  checked >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_comment_captcha_theme_fields" value="dark"<?php checked( get_option( 'wp_comment_captcha_theme_fields' ), 'dark' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'wp_comment_captcha_size_setting_clbck' ) ) {
	/**
	 * Callback.
	 */
	function wp_comment_captcha_size_setting_clbck() {
		?>
		<input type="radio" name="wp_comment_add_captcha_size_radio" value="normal"<?php checked( get_option( 'wp_comment_add_captcha_size_radio' ), 'normal' ); ?>  checked  >
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="wp_comment_add_captcha_size_radio"value="compact"<?php checked( get_option( 'wp_comment_add_captcha_size_radio' ), 'compact' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
/**
 * Section.
 */
function tab_9_section_callback() {
}
