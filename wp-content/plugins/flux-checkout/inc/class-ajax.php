<?php
/**
 * Iconic_Flux_AJAX.
 *
 * Handle AJAX events.
 *
 * @package Iconic_Flux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flux AJAX class.
 */
class Iconic_Flux_Ajax {
	/**
	 * Run.
	 */
	public static function run() {
		$actions = array(
			// 'flux_$event' => norpiv.
			'check_for_inline_errors'     => true,
			'check_for_inline_error'      => true,
			'login'                       => true,
			'elements_welcome_guide_seen' => false,
			'get_variation'               => true,
			'get_product_details'         => true,
		);

		foreach ( $actions as $event => $nopriv ) {
			add_action( 'wp_ajax_flux_' . $event, array( __CLASS__, $event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_flux_' . $event, array( __CLASS__, $event ) );
			}
		}
	}


	/**
	 * Check for inline errors.
	 */
	public static function check_for_inline_errors() {
		$fields   = filter_input( INPUT_POST, 'fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$messages = array();

		switch_to_locale( get_locale() );

		foreach ( $fields as $field ) {

			if ( ! is_array( $field ) ) {
				continue;
			}

			$messages[ $field['key'] ] = Iconic_Flux_Core::render_inline_errors( '', $field['key'], $field['args'], $field['value'], $field['country'] );
		}

		$messages['fragments'] = array(
			'.flux-review-customer' => Iconic_Flux_Steps::get_review_customer_fragment(),
		);

		/**
		 * Filter the inline errors messages on step change.
		 *
		 * @param array $messages The inline errors.
		 *
		 * @since 2.18.0
		 */
		$messages = apply_filters( 'flux_checkout_check_for_inline_errors', $messages );

		wp_send_json_success( $messages );
		exit;
	}

	/**
	 * Check for inline error for the given field.
	 *
	 * @return void
	 */
	public static function check_for_inline_error() {
		Iconic_Flux_Core::render_inline_errors();
	}

	/**
	 * Login.
	 *
	 * @throws Exception On login error.
	 */
	public static function login() {
		check_admin_referer( 'woocommerce-login' );

		try {
			$username   = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
			$password   = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
			$rememberme = filter_input( INPUT_POST, 'rememberme', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			$creds = array(
				'user_login'    => trim( $username ),
				'user_password' => $password,
				'remember'      => ! empty( $rememberme ),
			);

			$validation_error = new WP_Error();

			/**
			 * Process login Validation Error.
			 *
			 * @since 2.3.0.
			 */
			$validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

			if ( $validation_error->get_error_code() ) {
				throw new Exception( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $validation_error->get_error_message() );
			}

			if ( empty( $creds['user_login'] ) ) {
				throw new Exception( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Username is required.', 'woocommerce' ) );
			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// Perform the login.

			/**
			 * Login credentials.
			 *
			 * @since 2.3.0.
			 */
			$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

			if ( is_wp_error( $user ) ) {
				throw new Exception( $user->get_error_message() );
			} else {
				wp_send_json_success();
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Mark Checkout Elements welcome guide as seen.
	 *
	 * @return void
	 */
	public static function elements_welcome_guide_seen() {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'fce_welcome_guide_seen', true );
		wp_send_json_success();
	}

	/**
	 * Cross-sell: Get variation.
	 *
	 * @return void
	 */
	public static function get_variation() {
		check_ajax_referer( 'update-order-review', '_ajax_nonce' );

		if ( empty( $_REQUEST['product_id'] ) ) {
			wp_send_json_error();
		}

		$product      = wc_get_product( absint( $_REQUEST['product_id'] ) );
		$variation_id = false;

		if ( ! $product ) {
			wp_send_json_error();
		}

		$data_store   = WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $product, wp_unslash( $_REQUEST ) );
		$variation    = $variation_id ? $product->get_available_variation( $variation_id ) : false;

		if ( empty( $variation ) ) {
			wp_send_json_error();
		} else {
			wp_send_json_success( $variation );
		}
	}

	/**
	 * Get product details.
	 *
	 * @return void
	 */
	public static function get_product_details() {
		$product_ids = filter_input( INPUT_POST, 'product_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$products    = array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( empty( $product ) ) {
				continue;
			}

			$img = wp_get_attachment_url( $product->get_image_id() );
			if ( ! $img ) {
				$img = wc_placeholder_img_src( 'woocommerce_thumbnail' );
			}

			$products[] = array(
				'id'                => $product->get_id(),
				'name'              => $product->get_title(),
				'price_html'        => $product->get_price_html(),
				'short_description' => $product->get_description(),
				'images'            => array(
					array(
						'src' => $img,
					),
				),
			);
		}

		wp_send_json_success( $products );
	}
}
