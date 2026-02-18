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
	'tab_5_section',         // ID used to identify this section and with which to register options.
	'',                  // Title to be displayed on the administration page.
	'tab_5_section_callback', // Callback used to render the description of the section.
	'recaptcha-woo-guest-checkout'                           // Page on which to add this section of options.
);
add_settings_field(
	'guest_checkout_add_captcha_enable_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on Checkout', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'guest_checkout_captcha_enable_check',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'guest_checkout_add_captcha_enable_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'captcha_user_role_enalbe', // ID used to identify the field throughout the theme.
	esc_html__( 'Select User role', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_user_role_call_back',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'captcha_user_role_enalbe',
	array(
		'type'              => 'array',
		'sanitize_callback' => 'ka_sanitize_array',
	)
);

add_settings_field(
	'guest_checkout_add_captcha_field_title', // ID used to identify the field throughout the theme.
	__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'guest_checkout_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'guest_checkout_add_captcha_field_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

// Update: version(1.3.0)
// Added option for captcha position on checkout page.
add_settings_field(
	'ka_grc_captcha_checkout_position', // ID used to identify the field throughout the theme.
	esc_html__( 'Captcha Position', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'ka_grc_captcha_checkout_position_call_back',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'ka_grc_captcha_checkout_position',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'check_out_page_captcha_themes', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'guest_checkout_captcha_reg_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'check_out_page_captcha_themes',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'guest_checkout_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'guest_checkout_size_radio_callback',   // The name of the function responsible for rendering.
	'recaptcha-woo-guest-checkout', // The page on which this option will be displayed.
	'tab_5_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-woo-guest-checkout',
	'guest_checkout_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

function ka_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}

if ( ! function_exists( 'captcha_user_role_call_back' ) ) {
	/**
	 * User role.
	 */
	function captcha_user_role_call_back() {
		global $wp_roles;
		$addf_gr_roles            = $wp_roles->get_names();
		$captcha_user_role_enalbe = get_option( 'captcha_user_role_enalbe' );
		?>

		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select user role which you want to show recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>

		<select class="wc-enhanced-select captcha_user_role_enalbe" name="captcha_user_role_enalbe[]" multiple style="width: 40%";>
			<?php
			foreach ( $addf_gr_roles as $key => $addf_gr_role ) {
				?>
					<option value="<?php echo esc_attr( $key ); ?>"
					<?php
					if ( in_array( $key, (array) $captcha_user_role_enalbe, true ) ) {
						echo 'selected="selected"'; }
					?>
					>
					<?php echo esc_html( $addf_gr_role ); ?></option>
					<?php } ?>
				<option value="guest"  
				<?php
				if ( in_array( 'guest', (array) $captcha_user_role_enalbe, true ) ) {
					echo 'selected="selected"';
				}
				?>
				> <?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?></option>
		</select>
		<p><?php echo esc_html__( 'If empty, captcha will work for all user roles.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
// Update: version(1.3.0)
// Added option for captcha position on checkout page.
if ( ! function_exists( 'ka_grc_captcha_checkout_position_call_back' ) ) {
	/**
	 * Captcha Checkout position.
	 *
	 * @since 1.3.0
	 */
	function ka_grc_captcha_checkout_position_call_back() {
		$grc_position = get_option( 'ka_grc_captcha_checkout_position' );
		if ( empty( $grc_position ) ) {
			$grc_position = 'woocommerce_review_order_before_submit';
		}
		$positions = array(
			'woocommerce_checkout_before_customer_details' => __( 'Before customer details', 'recaptcha_verification' ),
			'woocommerce_before_checkout_billing_form'     => __( 'Before checkout form', 'recaptcha_verification' ),
			'woocommerce_before_order_notes'               => __( 'Before order notes', 'recaptcha_verification' ),
			'woocommerce_after_order_notes'                => __( 'After order notes', 'recaptcha_verification' ),
			'woocommerce_review_order_before_payment'      => __( 'Before payment', 'recaptcha_verification' ),
			'woocommerce_review_order_before_submit'       => __( 'Above order button', 'recaptcha_verification' ),
			'woocommerce_review_order_after_submit'        => __( 'Below order button', 'recaptcha_verification' ),
		);
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select position of captcha on checkout page', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
			<select class="wc-enhanced-select" name="ka_grc_captcha_checkout_position" style="width: 40%";>
			<?php
			foreach ( $positions as $hook => $position ) {
				?>
				<option value="<?php echo esc_attr( $hook ); ?>"
				<?php
				if ( in_array( $hook, (array) $grc_position, true ) ) {
					echo 'selected="selected"'; }
				?>
				>
				<?php echo esc_html( $position ); ?></option>
				<?php } ?>
		</select>
		<?php
	}
}
if ( ! function_exists( 'guest_checkout_captcha_enable_check' ) ) {
	/**
	 * Checkout.
	 */
	function guest_checkout_captcha_enable_check() {
		?>
		<input type="checkbox" name="guest_checkout_add_captcha_enable_check"value="1"<?php checked( get_option( 'guest_checkout_add_captcha_enable_check' ), '1' ); ?>>
		<p><?php echo esc_html__( 'Enable Captcha for checkout page. ', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'guest_checkout_authentication_callback' ) ) {
	/**
	 * Checkout.
	 */
	function guest_checkout_authentication_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Adds Field Title to Recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="guest_checkout_add_captcha_field_title" value="<?php echo esc_attr( get_option( 'guest_checkout_add_captcha_field_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'guest_checkout_captcha_reg_authentication_callback' ) ) {
	/**
	 * Checkout.
	 */
	function guest_checkout_captcha_reg_authentication_callback() {
		?>
		<input type="radio" name="check_out_page_captcha_themes" value="light"<?php checked( get_option( 'check_out_page_captcha_themes' ), 'light' ); ?> checked >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="check_out_page_captcha_themes" value="dark"<?php checked( get_option( 'check_out_page_captcha_themes' ), 'dark' ); ?>   >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'guest_checkout_size_radio_callback' ) ) {
	/**
	 * Checkout.
	 */
	function guest_checkout_size_radio_callback() {
		?>
		<input type="radio" name="guest_checkout_add_captcha_size_radio" value="normal" <?php checked( get_option( 'guest_checkout_add_captcha_size_radio' ), 'normal' ); ?> checked >
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="guest_checkout_add_captcha_size_radio" value="compact"<?php checked( get_option( 'guest_checkout_add_captcha_size_radio' ), 'compact' ); ?>>
		<label for="Dark"><?php echo esc_html__( 'Compact', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}


if ( ! function_exists( 'tab_5_section_callback' ) ) {
	/**
	 * Section.
	 */
	function tab_5_section_callback() {
	}
}
