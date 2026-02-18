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
	'recaptcha-product-review-settings'                           // Page on which to add this section of options.
);
add_settings_field(
	'product_review_add_captcha_enable_check', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha on Product Review', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'product_review_captcha_enable_clbk',   // The name of the function responsible for rendering.
	'recaptcha-product-review-settings', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-product-review-settings',
	'product_review_add_captcha_enable_check',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'captcha_product_review_user_role', // ID used to identify the field throughout the theme.
	esc_html__( 'Select User role', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'captcha_product_review_user_role_call_back',   // The name of the function responsible for rendering.
	'recaptcha-product-review-settings', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-product-review-settings',
	'captcha_product_review_user_role',
	array(
		'type'              => 'array',
		'sanitize_callback' => 'klw_sanitize_array',
	)
);


add_settings_field(
	'product_review_add_captcha_field_title', // ID used to identify the field throughout the theme.
	__( 'Recaptcha Field Title', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'product_review_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-product-review-settings', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-product-review-settings',
	'product_review_add_captcha_field_title',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'product_review_page_captcha_themes', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Themes', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'product_review_themes_authentication_callback',   // The name of the function responsible for rendering.
	'recaptcha-product-review-settings', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-product-review-settings',
	'product_review_page_captcha_themes',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

add_settings_field(
	'product_review_add_captcha_size_radio', // ID used to identify the field throughout the theme.
	esc_html__( 'Recaptcha Size', 'recaptcha_verification' ), // The label to the left of the option interface element.
	'product_review_size_radio_callback',   // The name of the function responsible for rendering.
	'recaptcha-product-review-settings', // The page on which this option will be displayed.
	'tab_8_section'// The name of the section to which this field belongs.
);
register_setting(
	'captcha-product-review-settings',
	'product_review_add_captcha_size_radio',
	array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	)
);


function klw_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}


if ( ! function_exists( 'captcha_product_review_user_role_call_back' ) ) {
	/**
	 * Callback.
	 */
	function captcha_product_review_user_role_call_back() {
		global $wp_roles;
		$addf_pr_roles                    = $wp_roles->get_names();
		$captcha_product_review_user_role = get_option( 'captcha_product_review_user_role' );
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Select user role which you want to show recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<select class="wc-enhanced-select captcha_product_review_user_role" name="captcha_product_review_user_role[]" multiple style="width: 40%";>
			<?php
			foreach ( $addf_pr_roles as $key => $addf_pr_role ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"
					<?php
					if ( in_array( $key, (array) $captcha_product_review_user_role, true ) ) {
						echo 'selected="selected"';
					}
					?>
					>
					<?php echo esc_html( $addf_pr_role ); ?>			
				</option>
			<?php } ?>
			<option value="guest"  
			<?php
			if ( in_array( 'guest', (array) $captcha_product_review_user_role, true ) ) {
				echo 'selected="selected"'; }
			?>
			> <?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?>
			</option>
		</select>
		<p><?php echo esc_html__( 'If empty, captcha will work for all user roles.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'product_review_captcha_enable_clbk' ) ) {
	/**
	 * Callback.
	 */
	function product_review_captcha_enable_clbk() {
		?>
		<input type="checkbox" name="product_review_add_captcha_enable_check"value="1"<?php checked( get_option( 'product_review_add_captcha_enable_check' ), '1' ); ?>><p><?php echo esc_html__( 'Enable Captcha for product review page.', 'recaptcha_verification' ); ?></p>
		<?php
	}
}
if ( ! function_exists( 'product_review_authentication_callback' ) ) {
	/**
	 * Callback.
	 */
	function product_review_authentication_callback() {
		?>
		<span class="ka-grc-tooltip" data-tooltip="<?php echo esc_html__( 'Adds Field Title to Recaptcha.', 'recaptcha_verification' ); ?>"><span class="ka-grc-tooltip-trigger">?</span></span>
		<input type="text" name="product_review_add_captcha_field_title" value="<?php echo esc_attr( get_option( 'product_review_add_captcha_field_title' ) ); ?>">
		<?php
	}
}
if ( ! function_exists( 'product_review_themes_authentication_callback' ) ) {
	/**
	 * Callback.
	 */
	function product_review_themes_authentication_callback() {
		?>
		<input type="radio" name="product_review_page_captcha_themes" value="light"<?php checked( get_option( 'product_review_page_captcha_themes' ), 'light' ); ?> checked >
		<label for="Light"><?php echo esc_html__( 'Light', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="product_review_page_captcha_themes" value="dark"<?php checked( get_option( 'product_review_page_captcha_themes' ), 'dark' ); ?>   >
		<label for="Dark"><?php echo esc_html__( 'Dark', 'recaptcha_verification' ); ?></label>
		<br>
		<?php
	}
}
if ( ! function_exists( 'product_review_size_radio_callback' ) ) {
	/**
	 * Callback.
	 */
	function product_review_size_radio_callback() {
		?>
		<input type="radio" name="product_review_add_captcha_size_radio" value="normal" <?php checked( get_option( 'product_review_add_captcha_size_radio' ), 'normal' ); ?> checked >
		<label for="Light"><?php echo esc_html__( 'Normal', 'recaptcha_verification' ); ?></label>
		<br>
		<input type="radio" name="product_review_add_captcha_size_radio" value="compact"<?php checked( get_option( 'product_review_add_captcha_size_radio' ), 'compact' ); ?>>
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
