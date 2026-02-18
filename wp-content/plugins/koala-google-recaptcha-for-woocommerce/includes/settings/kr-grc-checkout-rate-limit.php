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
	'ka_grc_checkout_rate_section',
	'Checkout Rate Limiter Setting',
	'ka_grc_checkout_rate_section_callback',
	'ka_grc_checkout_rate_limit'
);

add_settings_field(
	'ka_grc_checkout_rate_limit_check',
	esc_html__( 'Enable Checkout Rate Limiter', 'recaptcha_verification' ),
	'ka_grc_checkout_rate_limit_check_setting',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_checkout_rate_limit_check',
array(
	'type'              => 'string',
	'sanitize_callback' => 'sanitize_text_field',
) );

add_settings_field(
	'ka_grc_set_rate_limit',
	esc_html__( 'Set Rate Limiter', 'recaptcha_verification' ),
	'ka_grc_set_rate_limit_setting',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_set_rate_limit', array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_limit_sanitize_array',
) );
register_setting( 'ka_grc_checkout_rate', 'ka_grc_set_rate_seconds', array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_limit_sanitize_array',
) );

add_settings_field(
	'ka_grc_disable_checkout_button',
	esc_html__( 'Disable Button', 'recaptcha_verification' ),
	'ka_grc_disable_checkout_button_setting',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_disable_checkout_button', array(
	'type'              => 'string',
	'sanitize_callback' => 'sanitize_text_field',
) );

add_settings_field(
	'ka_grc_rate_error_message',
	esc_html__( 'Error Message', 'recaptcha_verification' ),
	'ka_grc_rate_error_message_cb',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_rate_error_message', array(
	'type'              => 'string',
	'sanitize_callback' => 'sanitize_text_field',
) );

add_settings_field(
	'ka_grc_rate_disable_ips',
	esc_html__( 'Disable IPs', 'recaptcha_verification' ),
	'ka_grc_rate_disable_ips_cb',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_rate_disable_ips',
array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_number_sanitize_array',
) );

add_settings_field(
	'ka_grc_rate_exclude_ips',
	esc_html__( 'Exclude IPs', 'recaptcha_verification' ),
	'ka_grc_rate_exclude_ips_cb',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_rate_exclude_ips',
array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_number_sanitize_array',
) );

add_settings_field(
	'ka_grc_rate_exclude_by_email',
	esc_html__( 'Exclude Customers By Email', 'recaptcha_verification' ),
	'ka_grc_rate_exclude_by_email_cb',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_rate_exclude_by_email',
array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_mail_sanitize_array',
) );

add_settings_field(
	'ka_grc_rate_exclude_user_role',
	esc_html__( 'Exclude User Role', 'recaptcha_verification' ),
	'ka_grc_rate_exclude_user_role_cb',
	'ka_grc_checkout_rate_limit',
	'ka_grc_checkout_rate_section'
);
register_setting( 'ka_grc_checkout_rate', 'ka_grc_rate_exclude_user_role',
array(
	'type'              => 'array',
	'sanitize_callback' => 'ka_rate_limit_sanitize_array',
) );

function ka_rate_limit_sanitize_array( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'sanitize_text_field', $input );
	}
	return array();
}
function ka_rate_number_sanitize_array( $input ) {
	$ips       = array_map( 'trim', explode( ',', $input ) );
	$valid_ips = array_filter( $ips, function ( $ip ) {
		return filter_var( $ip, FILTER_VALIDATE_IP );
	});
	return implode( ', ', $valid_ips );
}
function ka_rate_mail_sanitize_array( $input ) {
		$emails = array_map( 'trim', explode( ',', $input ) );

	// Filter valid email addresses
	$valid_emails = array_filter( $emails, function ( $email ) {
		return filter_var( $email, FILTER_VALIDATE_EMAIL );
	});

	// Join back as a comma-separated list
	return implode( ', ', $valid_emails );
}
function ka_grc_checkout_rate_section_callback() {
	?>
	<p><?php echo esc_html__('Checkout rate limiter allows merchants to control how many place order attempts can be made within a specified period.', 'recaptcha_verification'); ?></p>
	<?php
}

if ( ! function_exists( 'ka_grc_checkout_rate_limit_check_setting' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_checkout_rate_limit_check_setting() {
		?>
		<input class="ka-grc-limit-checkbox" type="checkbox" name="ka_grc_checkout_rate_limit_check" value="1"<?php checked( get_option( 'ka_grc_checkout_rate_limit_check' ), '1' ); ?>>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_set_rate_limit_setting' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_set_rate_limit_setting() {

	$ka_set_rate_limit   = (array) get_option( 'ka_grc_set_rate_limit' );
	$ka_set_rate_seconds = (array) get_option( 'ka_grc_set_rate_seconds' );
		if ( empty( $ka_set_rate_limit ) ) {
			$ka_set_rate_limit   = array( '' );
			$ka_set_rate_seconds = array( '' );
		}
		?>
		<div class="ka-attempt-limits">
			<table>
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Attempt per', 'recaptcha_verification' ); ?></th>
						<th><?php echo esc_html__( 'Seconds', 'recaptcha_verification' ); ?></th>
						<th><?php echo esc_html__( 'Action', 'recaptcha_verification' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Loop through each set of rate limit values (attempt and seconds)
					foreach ( $ka_set_rate_limit as $index => $attempt ) {
						$seconds = isset( $ka_set_rate_seconds[ $index ] ) ? $ka_set_rate_seconds[ $index ] : '';
						?>
						<tr class="ka-rate-limit-item" data-index="<?php echo esc_html( $index ); ?>">
							<td class="ka-attempt-per">
								<input class="ka-grc-limit" type="number" name="ka_grc_set_rate_limit[<?php echo esc_html( $index ); ?>]" value="<?php echo esc_attr( $attempt ); ?>">
							</td>
							<td class="ka-second-per">
								<input class="ka-grc-limit" type="number" name="ka_grc_set_rate_seconds[<?php echo esc_html( $index ); ?>]" value="<?php echo esc_attr( $seconds ); ?>">
							</td>
							<td>
								<button type="button" class="button ka-remove-rate-limit button-primary"><?php echo esc_html__( 'Remove', 'recaptcha_verification' ); ?></button>
							</td>
						</tr>
						<?php
					}

					// If there are no rate limits set yet, show a blank row for adding new limits
					if ( empty( $ka_set_rate_limit ) ) {
						?>
						<tr class="ka-rate-limit-item" data-index="0">
							<td class="ka-attempt-per">
								<input class="ka-grc-limit" type="number" name="ka_grc_set_rate_limit[0]" value="">
							</td>
							<td class="ka-second-per">
								<input class="ka-grc-limit" type="number" name="ka_grc_set_rate_seconds[0]" value="">
							</td>
							<td></td>
						</tr>
						<?php
					}
					?>
					<tr class="ka-rate-add_new">
						<td colspan="3">
							<button type="button" class="button ka-add-rate-limit button-primary"><?php echo esc_html__( 'Add new', 'recaptcha_verification' ); ?></button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<p><?php echo esc_html__('Set the number of allowed tries and Specify the time range for allowed attempts In seconds.', 'recaptcha_verification'); ?></p>
	<?php
	}
}
if ( ! function_exists( 'ka_grc_rate_error_message_cb' ) ) {
	function ka_grc_disable_checkout_button_setting() {
		$value = get_option( 'ka_grc_disable_checkout_button' );
		?>
		<input type="number" name="ka_grc_disable_checkout_button" id="ka_grc_disable_checkout_button" value="<?php echo esc_attr($value); ?>" min="1">
		<p><?php echo esc_html__('Set time(in minutes) for which the place order button will be disabled once all attempts are consumed.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_rate_error_message_cb' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_rate_error_message_cb() {
		$value = get_option( 'ka_grc_rate_error_message' ) ? get_option( 'ka_grc_rate_error_message' ) : 'You have reached the checkout limit. Please try again after {remaining_time} minutes.';
		?>
		<textarea class="ka-admin-textarea" id="ka_grc_rate_error_message" name="ka_grc_rate_error_message"><?php echo esc_textarea( $value ); ?></textarea>
		<p><?php echo esc_html__('Add error message to display when rate limiter is triggered. Add {remaining_time} to show time left until next login attempt.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_rate_disable_ips_cb' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_rate_disable_ips_cb() {
		$value = get_option( 'ka_grc_rate_disable_ips' );
		?>
		<textarea class="ka-admin-textarea" id="ka_grc_rate_disable_ips" name="ka_grc_rate_disable_ips"><?php echo esc_textarea( $value ); ?></textarea>
		<p><?php echo esc_html__('Order button will remain disabled for these IPs. Add multiple IPs & separate them using commas.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_rate_exclude_ips_cb' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_rate_exclude_ips_cb() {
		$value = get_option( 'ka_grc_rate_exclude_ips' );
		?>
		<textarea class="ka-admin-textarea" id="ka_grc_rate_exclude_ips" name="ka_grc_rate_exclude_ips"><?php echo esc_textarea( $value ); ?></textarea>
		<p><?php echo esc_html__('Add IPs addresses on which checkout rate limiter will not be applied. Add multiple IPs & separate them using commas.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_rate_exclude_by_email_cb' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_rate_exclude_by_email_cb() {
		$value = get_option( 'ka_grc_rate_exclude_by_email' );
		?>
		<textarea class="ka-admin-textarea" id="ka_grc_rate_exclude_by_email" name="ka_grc_rate_exclude_by_email"><?php echo esc_textarea( $value ); ?></textarea>
		<p><?php echo esc_html__('Add emails for customer on which checkout rate limiter will not be applied. Add multiple email & separate them using commas.', 'recaptcha_verification'); ?></p>
		<?php
	}
}

if ( ! function_exists( 'ka_grc_rate_exclude_user_role_cb' ) ) {
	/**
	 * Callback.
	 */
	function ka_grc_rate_exclude_user_role_cb() {
		$sel_roles = (array) get_option( 'ka_grc_rate_exclude_user_role' );
		global $wp_roles;
		$roles = $wp_roles->get_names();
		?>
		<select id="ka_grc_rate_exclude_user_role" name="ka_grc_rate_exclude_user_role[]" multiple class="ka-admin-textarea">
			<?php foreach ( $roles as $key => $value ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( !empty($sel_roles) && in_array( (string) $key, $sel_roles, true ) ) ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
			<?php } ?>
			<option value="guest" <?php echo ( !empty( $sel_roles ) && in_array( 'guest', $sel_roles, true ) ) ? 'selected' : ''; ?>><?php echo esc_html__( 'Guest', 'recaptcha_verification' ); ?></option>
		</select>
		<p><?php echo esc_html__('Select user roles to exclude from checkout rate limiter.', 'recaptcha_verification'); ?></p>
		<?php
	}
}