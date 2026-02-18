<?php
/**
 * Shortcode captcha.
 *
 * @package Google_reCaptcha_for_WooCommerce
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_settings_section(
	'tab_13_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_13_section_callback', // Callback used to render the description of the section.
	'ka_grc_recaptcha_shortcode'                           // Page on which to add this section of options.
);

add_settings_field(
	'ka_grc_shortcode_captcha_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Shortcode reCaptcha', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_shortcode_captcha_check_setting',   // The name of the function responsible for rendering.
	'ka_grc_recaptcha_shortcode', // The page on which this option will be displayed.
	'tab_13_section'// The name of the section to which this field belongs.
);
register_setting(
	'ka_grc_captcha_shortcode',
	'ka_grc_shortcode_captcha_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'ka_grc_sc_captcha_user_role_enable', // ID used to identify the field throughout the theme.
	esc_html__( 'Select User role', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_captcha_user_role_call_back',   // The name of the function responsible for rendering.
	'ka_grc_recaptcha_shortcode', // The page on which this option will be displayed.
	'tab_13_section'// The name of the section to which this field belongs.
);
register_setting(
	'ka_grc_captcha_shortcode',
	'ka_grc_sc_captcha_user_role_enable',
	array(
		'type'              => 'array',
		'sanitize_callback' => 'ka_shortcode_sanitize_array',
	)
);

add_settings_field(
	'ka_grc_shortcode_captcha_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_shortcode_captcha_title_set',   // The name of the function responsible for rendering.
	'ka_grc_recaptcha_shortcode', // The page on which this option will be displayed.
	'tab_13_section'// The name of the section to which this field belongs.
);
register_setting(
	'ka_grc_captcha_shortcode',
	'ka_grc_shortcode_captcha_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'ka_grc_shortcode_captcha_theme_fields', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_shortcode_captcha_themes_settings',   // The name of the function responsible for rendering.
	'ka_grc_recaptcha_shortcode', // The page on which this option will be displayed.
	'tab_13_section'// The name of the section to which this field belongs.
);
register_setting(
	'ka_grc_captcha_shortcode',
	'ka_grc_shortcode_captcha_theme_fields',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'ka_grc_shortcode_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_shortcode_captcha_size_setting',   // The name of the function responsible for rendering.
	'ka_grc_recaptcha_shortcode', // The page on which this option will be displayed.
	'tab_13_section'// The name of the section to which this field belongs.
);
register_setting(
	'ka_grc_captcha_shortcode',
	'ka_grc_shortcode_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

function ka_shortcode_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}
if ( ! function_exists( 'ka_grc_captcha_user_role_call_back' ) ) {
	/**
	 * User role.
	 */
	function ka_grc_captcha_user_role_call_back() {
		global $wp_roles;
		$addf_gr_roles                      = $wp_roles->get_names();
		$ka_grc_sc_captcha_user_role_enable = get_option( 'ka_grc_sc_captcha_user_role_enable' );
		?>

		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select user role which you want to show recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>

		<select class="wc-enhanced-select ka_grc_sc_captcha_user_role_enable" name="ka_grc_sc_captcha_user_role_enable[]" multiple style="width: 40%";>
			<?php
			foreach ( $addf_gr_roles as $key => $addf_gr_role ) {
				?>
					<option value="<?php echo esc_attr( $key ); ?>"
					<?php
					if ( in_array( $key, (array) $ka_grc_sc_captcha_user_role_enable, true ) ) {
						echo 'selected="selected"'; }
					?>
					>
					<?php echo esc_html( $addf_gr_role ); ?></option>
					<?php } ?>
				<option value="guest"  
				<?php
				if ( in_array( 'guest', (array) $ka_grc_sc_captcha_user_role_enable, true ) ) {
					echo 'selected="selected"';
				}
				?>
				> <?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?></option>
		</select>
		<p><?php echo esc_html__( 'If empty, captcha will work for all user roles.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'ka_grc_shortcode_captcha_check_setting' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_shortcode_captcha_check_setting() {
		?>
		<input class="ka-grc-shortcode-checkbox" type="checkbox" name="ka_grc_shortcode_captcha_check" value="1"<?php checked( get_option( 'ka_grc_shortcode_captcha_check' ), '1' ); ?>>
		<p><?php echo esc_html__( 'Enables shortcode for captcha.', 'recaptcha_verification' ); ?></p>
		<p class="ka-grc-shortcode-show">
			<strong>
				<input type="text" class="ka-grc-shortcode-input" readonly value="[captcha_shortcode]">
			</strong>
			<?php echo esc_html__( 'Use shortcode to display captcha on any page.', 'recaptcha_verification' ); ?>
		</p>
		<?php
	}
}
if ( ! function_exists( 'ka_grc_shortcode_captcha_title_set' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_shortcode_captcha_title_set() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for captcha in Shortcode.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="ka_grc_shortcode_captcha_title" value="<?php echo esc_attr( get_option( 'ka_grc_shortcode_captcha_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'ka_grc_shortcode_captcha_themes_settings' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_shortcode_captcha_themes_settings() {
		?>
		<input type="radio" name="ka_grc_shortcode_captcha_theme_fields" value="light"<?php checked( get_option( 'ka_grc_shortcode_captcha_theme_fields' ), 'light' ); ?> checked  >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="ka_grc_shortcode_captcha_theme_fields" value="dark"<?php checked( get_option( 'ka_grc_shortcode_captcha_theme_fields' ), 'dark' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<?php
	}
}
if ( ! function_exists( 'ka_grc_shortcode_captcha_size_setting' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_shortcode_captcha_size_setting() {
		?>
		<input type="radio" name="ka_grc_shortcode_captcha_size_radio" value="normal"<?php checked( get_option( 'ka_grc_shortcode_captcha_size_radio' ), 'normal' ); ?>  checked  >
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="ka_grc_shortcode_captcha_size_radio" value="compact"<?php checked( get_option( 'ka_grc_shortcode_captcha_size_radio' ), 'compact' ); ?>  >
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<?php
	}
}


if ( ! function_exists( 'tab_13_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_13_section_callback() {
	}
}
