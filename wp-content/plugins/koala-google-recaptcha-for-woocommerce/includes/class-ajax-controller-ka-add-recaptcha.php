<?php
/**
 * Class responsible for handling Ajax calls.
 *
 * @package Google_reCaptcha_for_WooCommerce
 * @since 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! class_exists( 'Ajax_Controller_Ka_Add_Recaptcha' ) ) {
	/**
	 * Class responsible for handling Ajax calls.
	 */
	class Ajax_Controller_Ka_Add_Recaptcha {
		/**
		 * Hooks into necessary actions to set up ajax functionality.
		 */
		public function __construct() {
			add_action( 'wp_ajax_validation_captchav3', array( $this, 'validation_captchav3' ) );
			add_action( 'wp_ajax_nopriv_validation_captchav3', array( $this, 'validation_captchav3' ) );

			add_action('wp_ajax_ka_grc_block_limiter_time', array( $this, 'ka_grc_block_limiter_time' ) );
			add_action('wp_ajax_nopriv_ka_grc_block_limiter_time', array( $this, 'ka_grc_block_limiter_time' ) );
		}

		/**
		 * Validates captchav3.
		 */
		public function validation_captchav3() {

			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'recaptcha-ajax-nonce' ) ) {
				wp_send_json( array(
					'success' => false,
					'message' => 'Failed security check.',
				) );
				wp_die();
			}

			if ( isset( $_POST['captcha_token'] ) ) {

				$captcha_token      = sanitize_text_field( $_POST['captcha_token'] );
				$recaptcha_url      = 'https://www.google.com/recaptcha/api/siteverify';
				$recaptcha_secret   = get_option( 'add_captcha_secret_key_field' );
				$recaptcha_response = $captcha_token;

				$recaptcha = wp_remote_get( $recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response );

				if ( is_array( $recaptcha ) && ! is_wp_error( $recaptcha ) ) {
					$response_body    = wp_remote_retrieve_body( $recaptcha );
					$recaptcha_data   = json_decode( $response_body );
					$captcha_custom_score_opt = floatval( get_option( 'captcha_custom_score_opt', 0.5 ) );

					$score = isset( $recaptcha_data->score ) ? floatval( $recaptcha_data->score ) : 0;
				

					if ( isset( $recaptcha_data->success ) && $recaptcha_data->success && $score >= $captcha_custom_score_opt ) {
						wp_send_json( array( 'success' => true ) );
						wp_die();
					}
				}
			}

			wp_send_json( array(
				'success' => false,
				'message' => 'Captcha validation failed.',
			) );
			wp_die();
		}

		public function ka_grc_block_limiter_time() {

			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'recaptcha-ajax-nonce' ) ) {
				die( 'Failed ajax security check!' );
			}

			$current_user = wp_get_current_user();
			$ip_address   = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
			$user_email   = '';

			if (!empty($current_user) && is_user_logged_in() ) {
				$user_roles = $current_user->roles;
				$user_email = $current_user->user_email;
			} elseif (isset($_POST['billing_email']) ) {
				$user_email = sanitize_email($_POST['billing_email']);
			}

			$log_key = 'ka_grc_rate_limit_log';
			$logs    = get_option( $log_key, array() );
			$next_id = count( $logs ) + 1;
			$logs[]  = array(
				'id'         => $next_id,
				'ip_address' => $ip_address,
				'user_role'  => !empty($user_roles) ? $user_roles : array( 'guest' ),
				'user_email' => $user_email,
				'timestamp'  => current_time( 'mysql' ),
			);

			// Keep only the last 100 logs
			if ( count( $logs ) > 100 ) {
			array_shift( $logs );
			}

			update_option( $log_key, $logs );

			$disable_time_minutes = get_option('ka_grc_disable_checkout_button');

			$block_duration = $disable_time_minutes ? $disable_time_minutes : 1;

			$ka_error_message = get_option('ka_grc_rate_error_message');

			$error_message = str_replace('{remaining_time}', $block_duration, $ka_error_message);
			wp_send_json_error(array( 'message' => $error_message ));
			exit;
		}
	}
	new Ajax_Controller_Ka_Add_Recaptcha();
}
