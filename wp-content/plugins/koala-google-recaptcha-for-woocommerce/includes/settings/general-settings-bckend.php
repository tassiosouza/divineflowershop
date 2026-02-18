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
	'tab_1_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_1_section_callback', // Callback used to render the description of the section.
	'wp_recaptcha_general'                           // Page on which to add this section of options.
);

add_settings_field(
	'captcha_type_option', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Version', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'set_recaptcha_version',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'captcha_type_option',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
// Update: version(1.3.0) Added recaptcha.net support.
add_settings_field(
	'ka_grc_captcha_source_option', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Type', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_set_recaptcha_source',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'ka_grc_captcha_source_option',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

// Update: version(1.3.0) Added No-Conflict mode.
add_settings_field(
	'ka_grc_captcha_no_conflict', // ID used to identify the field throughout the theme.
	esc_html__( 'No-Conflict Mode', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_no_conflict_mode',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'ka_grc_captcha_no_conflict',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'captcha_general_title_option', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_general_title_clbck',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'captcha_general_title_option',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'add_captcha_site_key_field', // ID used to identify the field throughout the theme.
	esc_html__( 'Site Key', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_fields_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'add_captcha_site_key_field',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
add_settings_field(
	'add_captcha_secret_key_field', // ID used to identify the field throughout the theme.
	esc_html__( 'Secret Key', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_fields_callback_1',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'add_captcha_secret_key_field',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
/**
 * WooCommerce.
 *
 * @since 1.0.0
 */
if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	add_settings_field(
		'captcha_country_select_opt', // ID used to identify the field throughout the theme.
		esc_html__( 'Exclude Countries', 'recaptcha_verification' ), // The label to the left of the option interface element.
		'recaptcha_country_select_clbck',   // The name of the function responsible for rendering.
		'wp_recaptcha_general', // The page on which this option will be displayed.
		'tab_1_section'// The name of the section to which this field belongs.
	);
	register_setting(
		'wp_captcha_general',
		'captcha_country_select_opt',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ka_general_sanitize_array',
		)
	);
}

add_settings_field(
	'captcha_ip_range_opt', // ID used to identify the field throughout the theme.
	esc_html__( 'Exclude Ip Address', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_ip_range_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'captcha_ip_range_opt',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'captcha_custom_score_opt', // ID used to identify the field throughout the theme.
	esc_html__( 'ReCaptcha Score', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_custom_score_callback',   // The name of the function responsible for rendering.
	'wp_recaptcha_general', // The page on which this option will be displayed.
	'tab_1_section'// The name of the section to which this field belongs.
);
register_setting(
	'wp_captcha_general',
	'captcha_custom_score_opt',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

function ka_general_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}

if ( ! function_exists( 'recaptcha_country_select_clbck' ) ) {
	/**
	 * Country.
	 */
	function recaptcha_country_select_clbck() {
		$country_select = get_option( 'captcha_country_select_opt' );
		$countries      = WC()->countries->get_shipping_countries();
		asort( $countries );
		?>

		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Disable reCAPTCHA for specific countries.', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<select class="wc-enhanced-select captcha_country_select_opt"  id="captcha_country_select_opt" multiple="multiple" name="captcha_country_select_opt[]">
			<?php
			if ( ! empty( $countries ) ) {
				foreach ( $countries as $key => $val ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>" 
						<?php
						if ( in_array( $key, (array) $country_select, true ) ) {
							echo 'selected="selected"';
						}
						?>
						>
						<?php echo esc_html( $val ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
		<?php
	}
}

if ( ! function_exists( 'captcha_general_title_clbck' ) ) {
	/**
	 * Title.
	 */
	function captcha_general_title_clbck() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for Recaptcha.', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="text" name="captcha_general_title_option"  value="<?php echo esc_attr( get_option( 'captcha_general_title_option' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'captcha_ip_range_callback' ) ) {
	/**
	 * IP Range.
	 */
	function captcha_ip_range_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Disable reCaptcha for IP address', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="text" name="captcha_ip_range_opt"  value="<?php echo esc_attr( get_option( 'captcha_ip_range_opt' ) ); ?>">
		<?php
	}
}

if ( ! function_exists( 'captcha_custom_score_callback' ) ) {
	/**
	 * Custom Score.
	 */
	function captcha_custom_score_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Customize ReCaptcha Score.score range is 0.1 to 0.9', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="number" min="0.1" max="0.9" step=".1" name="captcha_custom_score_opt"  value="<?php echo esc_attr( get_option( 'captcha_custom_score_opt' ) ); ?>">
		<p><?php echo esc_html__('You can set reCaptcha score between 0.1 to 0.9.', 'recaptcha_verification'); ?></p>
		<p><?php echo esc_html__('0.9 = ReCaptcha will show unless the system is 90% sure it’s human. Humans will most likely marked as bots and will be asked to solve reCaptcha.', 'recaptcha_verification'); ?></p>
		<p><?php echo esc_html__('0.1 = ReCaptcha will show unless the system is 10% sure it’s human. Bots are likely to pass.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'set_recaptcha_version' ) ) {
	/**
	 * Set Recaptcha Version.
	 */
	function set_recaptcha_version() {
		?>
		<input type="radio" name="captcha_type_option" value="v2" <?php checked( get_option( 'captcha_type_option' ), 'v2' ); ?> checked >
		<label for="v2"><?php echo esc_html__( 'V2', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="captcha_type_option" value="v3" <?php checked( get_option( 'captcha_type_option' ), 'v3' ); ?>   >
		<label for="V3"><?php echo esc_html__( 'V3', 'recaptcha_verification' ); ?></label>
		<?php
	}
}
if ( ! function_exists( 'ka_grc_set_recaptcha_source' ) ) {
	/**
	 * Set Recaptcha source.
	 *
	 * @since 1.3.0
	 */
	function ka_grc_set_recaptcha_source() {
		?>
		<input type="radio" name="ka_grc_captcha_source_option" value="google.com" <?php checked( get_option( 'ka_grc_captcha_source_option' ), 'google.com' ); ?> checked >
		<label for="google.com"><?php echo esc_html__( 'Google.com', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="ka_grc_captcha_source_option" value="recaptcha.net" <?php checked( get_option( 'ka_grc_captcha_source_option' ), 'recaptcha.net' ); ?>   >
		<label for="recaptcha.net"><?php echo esc_html__( 'reCaptcha.net', 'recaptcha_verification' ); ?></label>
		<?php
	}
}
if ( ! function_exists( 'ka_grc_no_conflict_mode' ) ) {
	/**
	 * No-Conflict Mode.
	 *
	 * @since 1.3.0
	 */
	function ka_grc_no_conflict_mode() {
		?>
		<input type="checkbox" name="ka_grc_captcha_no_conflict" value="1" <?php checked( get_option( 'ka_grc_captcha_no_conflict' ), '1' ); ?> >
		<br>
		<label for="1"><?php echo esc_html__( 'Remove other CAPTCHA occurrences in order to prevent conflicts.', 'recaptcha_verification' ); ?></label>
		<?php
	}
}
if ( ! function_exists( 'captcha_fields_callback' ) ) {
	/**
	 * Fields.
	 */
	function captcha_fields_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Add site key for Recaptcha API', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="text" name="add_captcha_site_key_field"  value="<?php echo esc_attr( get_option( 'add_captcha_site_key_field' ) ); ?>">
		<p style="margin-left: 20px;"><?php echo esc_html__( 'Get site key for reCaptcha from ', 'recaptcha_verification' ); ?>
			<a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php echo esc_html__( 'here', 'recaptcha_verification' ); ?></a>
		</p>
		<?php
	}
}

if ( ! function_exists( 'captcha_fields_callback_1' ) ) {
	/**
	 * Fields.
	 */
	function captcha_fields_callback_1() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Add secret key for Recaptcha API', 'recaptcha_verification' ); ?>">
			<span class="ka-grc-tooltip-trigger">?</span>
		</span>
		<input type="text" value="<?php echo esc_attr( get_option( 'add_captcha_secret_key_field' ) ); ?>" name="add_captcha_secret_key_field"  value="<?php echo esc_attr( get_option( 'add_captcha_secret_key_field' ) ); ?>">
		<p style="margin-left: 20px;"><?php echo esc_html__( 'Get secret key for reCaptcha from ', 'recaptcha_verification' ); ?>
			<a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php echo esc_html__( 'here', 'recaptcha_verification' ); ?></a>
		</p>
		<?php
	}
}

if ( ! function_exists( 'tab_1_section_callback' ) ) {
	/**
	 * Fields.
	 */
	function tab_1_section_callback() {
	}
}
