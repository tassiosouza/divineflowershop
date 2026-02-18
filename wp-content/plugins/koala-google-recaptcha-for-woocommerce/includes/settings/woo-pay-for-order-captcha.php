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
	'tab_10_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_10_section_callback', // Callback used to render the description of the section.
	'recaptcha-p-order-settings'                           // Page on which to add this section of options.
);

add_settings_field(
	'pay_order_add_captcha_enable_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Woo pay_order', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'woo_pay_order_enable_captcha',   // The name of the function responsible for rendering.
	'recaptcha-p-order-settings', // The page on which this option will be displayed.
	'tab_10_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-p-order-settings',
	'pay_order_add_captcha_enable_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'pay_order_add_captcha_user_role', // ID used to identify the field throughout the theme.
	esc_html__( 'Select User role', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'woo_pay_order_captcha_user_role_clbk',   // The name of the function responsible for rendering.
	'recaptcha-p-order-settings', // The page on which this option will be displayed.
	'tab_10_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-p-order-settings',
	'pay_order_add_captcha_user_role',
	array(
		'type'              => 'array',
		'sanitize_callback' => 'ka_payorder_sanitize_array',
	)

);

add_settings_field(
	'add_p_order_captcha_field_title', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'p_order_captcha_field_title_callback',   // The name of the function responsible for rendering.
	'recaptcha-p-order-settings', // The page on which this option will be displayed.
	'tab_10_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-p-order-settings',
	'add_p_order_captcha_field_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'p_order_authentication_key_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'p_order_captcha_reg_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-p-order-settings', // The page on which this option will be displayed.
	'tab_10_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-p-order-settings',
	'p_order_authentication_key_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'p_order_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'p_order_captcha_reg_size_radio_callback',   // The name of the function responsible for rendering.
	'recaptcha-p-order-settings', // The page on which this option will be displayed.
	'tab_10_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-p-order-settings',
	'p_order_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
function ka_payorder_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}
if ( ! function_exists( 'woo_pay_order_captcha_user_role_clbk' ) ) {
	/**
	 * Callback.
	 */
	function woo_pay_order_captcha_user_role_clbk() {
		global $wp_roles;
		$addf_wpfo_roles                 = $wp_roles->get_names();
		$pay_order_add_captcha_user_role = get_option( 'pay_order_add_captcha_user_role' );

		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select user role which you want to show recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<select class="wc-enhanced-select pay_order_add_captcha_user_role" name="pay_order_add_captcha_user_role[]" multiple style="width: 40%";>
			<?php
			foreach ( $addf_wpfo_roles as $key => $addf_wpfo_role ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"
					<?php
					if ( in_array( $key, (array) $pay_order_add_captcha_user_role, true ) ) {
						echo 'selected="selected"'; }
					?>
					>
					<?php echo esc_html( $addf_wpfo_role ); ?></option>
					<?php } ?>
					<option value="guest"  
					<?php
					if ( in_array( 'guest', (array) $pay_order_add_captcha_user_role, true ) ) {
						echo 'selected="selected"'; }
					?>
					> <?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?>
				</option>
		</select>
		<p><?php echo esc_html__( 'If empty, captcha will work for all user roles.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}

if ( ! function_exists( 'woo_pay_order_enable_captcha' ) ) {
	/**
	 * Callback.
	 */
	function woo_pay_order_enable_captcha() {
		?>
		<input type="checkbox" name="pay_order_add_captcha_enable_check" value="1"<?php checked( get_option( 'pay_order_add_captcha_enable_check' ), '1' ); ?>><p><?php echo esc_html__( 'Enables captcha for pay for order', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'p_order_captcha_field_title_callback' ) ) {
	/**
	 * Callback.
	 */
	function p_order_captcha_field_title_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Field text for pay for order captcha. ', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="add_p_order_captcha_field_title" value="<?php echo esc_attr( get_option( 'add_p_order_captcha_field_title' ) ); ?>" >
		<?php
	}
}
if ( ! function_exists( 'p_order_captcha_reg_authentication_callback' ) ) {
	/**
	 * Callback.
	 */
	function p_order_captcha_reg_authentication_callback() {
		?>
		<input type="radio" name="p_order_authentication_key_radio" value="light"<?php checked( get_option( 'p_order_authentication_key_radio' ), 'light' ); ?> checked>
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="p_order_authentication_key_radio" value="dark"<?php checked( get_option( 'p_order_authentication_key_radio' ), 'dark' ); ?>>
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'p_order_captcha_reg_size_radio_callback' ) ) {
	/**
	 * Callback.
	 */
	function p_order_captcha_reg_size_radio_callback() {
		?>
		<input type="radio" name="p_order_add_captcha_size_radio" value="normal"<?php checked( get_option( 'p_order_add_captcha_size_radio' ), 'normal' ); ?> checked>
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="p_order_add_captcha_size_radio" value="compact"<?php checked( get_option( 'p_order_add_captcha_size_radio' ), 'compact' ); ?> >
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}

if ( ! function_exists( 'tab_10_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_10_section_callback() {
	}
}
