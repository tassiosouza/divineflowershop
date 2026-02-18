<?php
/**
 * Front file of Module
 *
 * @package Google_reCaptcha_for_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Frontend_Ka_Add_Recaptcha' ) ) {
	/**
	 * Front class of module.
	 */
	class Frontend_Ka_Add_Recaptcha {
		/**
		 * Construct function.
		 */
		public function __construct() {
			add_action( 'wp_loaded', array( $this, 'ka_captcha_woocommerce_init' ) );
			// Update: version (1.3.0) Shortcode hook.
			add_shortcode( 'captcha_shortcode', array( $this, 'ka_grc_captcha_shortcode' ) );
			// Update: version (1.3.0) No-Conflict Mode.
			add_action( 'wp_footer', array( $this, 'ka_grc_recaptcha_no_conflict' ) );
			// Update: version (1.3.0) Contact form 7 shortcode.
			add_filter( 'wpcf7_form_elements', 'do_shortcode' );
			// Update: version (1.3.0) Formidable forms compatibility.
			add_action( 'frm_entry_form', array( $this, 'ka_grc_captcha_form' ) );
			// Update: version (1.3.0) WPforms compatibility.
			add_action( 'wpforms_display_submit_before', array( $this, 'ka_grc_captcha_form' ) );
			// Update: version (1.3.0) Ninja form compatibility.
			add_filter( 'ninja_forms_display_fields', array( $this, 'ka_grc_ninja_forms_captcha' ), 10, 2 );
			// Update: version (1.3.0) Ultimate Member plugin compatibility.
			add_action( 'um_after_login_fields', array( $this, 'ka_grc_captcha_form' ) );
			add_action( 'um_after_register_fields', array( $this, 'ka_grc_captcha_form' ) );
			add_action( 'um_after_profile_fields', array( $this, 'ka_grc_captcha_member_profile' ) );
			add_action( 'um_after_password_reset_fields', array( $this, 'ka_grc_captcha_form' ) );
			add_action( 'um_account_page_hidden_fields', array( $this, 'ka_grc_captcha_form' ) );
			// Update: version (1.3.0) Jetpack forms compatibility.
			add_filter( 'grunion_contact_form_field_html', array( $this, 'ka_grc_jetpack' ) );
			add_action('wp_enqueue_scripts', array( $this, 'ka_grc_rate_limiting_script' ) );
		}

		public function ka_grc_validate_recaptcha_submission( $username = '', $email = '', $validation_errors = null ) {
			if ( ! is_wp_error( $validation_errors ) ) {
				$validation_errors = new WP_Error();
			}

			// Check if CAPTCHA response and nonce are present
			if ( isset( $_POST['g-recaptcha-response'], $_POST['recaptcha_nonce'] ) ) {
				$nonce = sanitize_text_field( $_POST['recaptcha_nonce'] );

				// Verify the nonce
				if ( ! wp_verify_nonce( $nonce, 'recaptcha_action' ) ) {
					$validation_errors->add( 'recaptcha_nonce_invalid', 'Nonce verification failed.' );
					return $validation_errors;
				}

				// Sanitize and validate reCAPTCHA token
				$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );
				if ( empty( $recaptcha_response ) ) {
					$validation_errors->add( 'recaptcha_empty', 'Captcha response is empty.' );
					return $validation_errors;
				}

				// Get secret key from options
				$secret_key = get_option( 'add_captcha_secret_key_field' );
				if ( empty( $secret_key ) ) {
					$validation_errors->add( 'recaptcha_error', 'Captcha secret key is not configured.' );
					return $validation_errors;
				}

				// Sanitize remote IP properly and check if set
				$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';

				// Verify response with Google
				$verify_response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
					'body' => array(
						'secret'   => $secret_key,
						'response' => $recaptcha_response,
						'remoteip' => $remote_ip,
					),
				) );

				// Check for server errors
				if ( is_wp_error( $verify_response ) ) {
					$validation_errors->add( 'recaptcha_error', 'Could not contact reCAPTCHA server.' );
					return $validation_errors;
				}

				// Parse and evaluate response
				$response_body = json_decode( wp_remote_retrieve_body( $verify_response ), true );
				if ( empty( $response_body['success'] ) ) {
					$validation_errors->add( 'recaptcha_error', 'Captcha verification failed. Please try again.' );
					return $validation_errors;
				}

			} else {
				// Missing response
				$validation_errors->add( 'recaptcha_empty', 'Please complete the captcha.' );
			}

			return $validation_errors;
		}




		/**
		 * Adds reCaptcha to Jetpack form.
		 *
		 * @param array $field Returns field.
		 * @return array
		 */
		public function ka_grc_jetpack( $field ) {
			ob_start();
			$field = $this->ka_grc_captcha_form() . ob_get_clean() . $field;
			remove_filter( 'grunion_contact_form_field_html', array( $this, 'ka_grc_jetpack' ) );
			return $field;
		}

		/**
		 * Adds reCaptcha to ninja forms.
		 *
		 * @param array $fields Returns all field.
		 *
		 * @return array
		 */
		public function ka_grc_ninja_forms_captcha( $fields ) {
			$current_country_excluded = $this->ka_grc_validate_countries();
			if ( true === $current_country_excluded ) {
				return;
			}

			if ( 'v3' === get_option( 'captcha_type_option' ) ) {
				ob_start();
				$this->ka_grc_render_captcha_v3();
				$captcha = ob_get_clean();
			} else {
				$captcha = '<div id="ka-grc-ninja-recaptcha"></div>';
			}
			foreach ( $fields as $key => $field ) {
				if ( 'submit' === $field['type'] ) {
					// Modify the configuration for the submit field.
					$fields[ $key ] = array(
						'objectType'        => 'Field',
						'objectDomain'      => 'fields',
						'editActive'        => false,
						'order'             => $field['order'],
						'type'              => 'submit',
						'label'             => $field['label'],
						'key'               => $field['key'],
						'default'           => '',
						'admin_label'       => '',
						'drawerDisabled'    => false,
						'id'                => $field['id'],
						'beforeField'       => $captcha,
						'afterField'        => '',
						'value'             => '',
						'label_pos'         => 'above',
						'parentType'        => 'textbox',
						'element_templates' => array(
							'submit',
							'button',
							'input',
						),
						'old_classname'     => '',
						'wrap_template'     => 'wrap-no-label',
					);
				}
			}
			return $fields;
		}

		/**
		 * Renders shortcode captcha for UM profile.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_captcha_member_profile() {
			if ( false === UM()->fields()->editing ) {
				return;
			}
			$this->ka_grc_captcha_form();
		}

		/**
		 * Renders shortcode captcha.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_captcha_shortcode() {
			$user_role_found          = $this->ka_grc_user_roles( 'ka_grc_sc_captcha_user_role_enable' );
			$current_country_excluded = $this->ka_grc_validate_countries();
			if ( true === $current_country_excluded || false === $user_role_found ) {
				return;
			}

			if ( (int) get_option( 'ka_grc_shortcode_captcha_check' ) === 1 ) {
				ob_start();
				if ( 'v3' === get_option( 'captcha_type_option' ) ) {
					$this->ka_grc_render_captcha_v3();
				} else {
					$cap_title = get_option( 'ka_grc_shortcode_captcha_title' );
					$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

					$cap_themes       = get_option( 'ka_grc_shortcode_captcha_theme_fields' );
					$cap_size         = get_option( 'ka_grc_shortcode_captcha_size_radio' );
					$grc_class        = 'g-recaptcha';
					$success_callback = 'ka_captcha_validation_success';
					$button_name      = 'Submit';
					?>
					<div class="ka_google_recaptcha">
						<?php
						$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
						?>
					</div>
					<?php
				}
				return ob_get_clean();
			}
		}

		/**
		 * Renders Form captcha
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_captcha_form() {
			$current_country_excluded = $this->ka_grc_validate_countries();
			if ( true === $current_country_excluded ) {
				return;
			}

			$action = current_action();
			if ( 'wpcf7_form_elements' === $action || 'ninja_forms_display_fields' === $action ) {
				ob_start();
			}
			if ( 'v3' === get_option( 'captcha_type_option' ) ) {
				$this->ka_grc_render_captcha_v3();
			} else {
				$success_callback = 'ka_captcha_validation_success';
				if ( 'grunion_contact_form_field_html' === $action ) {
					$success_callback = 'ka_captcha_validation_success_jetpack';
				}
				$title       = '';
				$cap_themes  = 'light';
				$cap_size    = 'normal';
				$grc_class   = 'g-recaptcha';
				$button_name = 'Submit';

				if ( 'grunion_contact_form_field_html' === $action || 'um_account_page_hidden_fields' === $action ) {
					if ( 'grunion_contact_form_field_html' === $action ) {
						?>
					<div class="ka_google_recaptcha ka_grc_jetpack">
						<?php
					} else {
						?>
					<div class="ka_google_recaptcha">
						<?php
					}
				}
					$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
				if ( 'grunion_contact_form_field_html' === $action || 'um_account_page_hidden_fields' === $action ) {
					?>
					</div>
					<?php
				}
			}
			if ( 'wpcf7_form_elements' === $action || 'ninja_forms_display_fields' === $action ) {
				return ob_get_clean();
			}
		}

		/**
		 * Validates user roles for a specific option.
		 *
		 * @param string $option The option name for user role.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_user_roles( $option ) {
			$user_role       = get_option( $option );
			$user_role_found = false;

			if ( empty( $user_role ) ) {
				$user_role_found = true;
			} elseif ( ! is_user_logged_in() ) {
				if ( in_array( 'guest', (array) $user_role, true ) ) {
					$user_role_found = true;
				}
			} else {
				$current_user   = wp_get_current_user();
				$curr_user_role = current( $current_user->roles );
				if ( in_array( (string) $curr_user_role, (array) $user_role, true ) ) {
					$user_role_found = true;
				}
			}
			return $user_role_found;
		}

		/**
		 * Validates countries for a specific option.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_validate_countries() {
			$exluded_country = false;
			/**
			 * WooCommerce.
			 *
			 * @since 1.0.0
			 */
			if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				$location                   = WC_Geolocation::geolocate_ip();
				$gr_visitor_current_country = $location['country'];
				$gr_selected_country        = (array) get_option( 'captcha_country_select_opt' );

				if ( ! empty( $gr_visitor_current_country ) && ! empty( $gr_selected_country ) && in_array( $gr_visitor_current_country, $gr_selected_country, true ) ) {
					$exluded_country = true;
					return $exluded_country;
				} else {
					$exluded_country = false;
				}
			}

			$captcha_ip_range_opt = get_option( 'captcha_ip_range_opt' );
			$captcha_ip_range_opt = str_replace( '.', ',', $captcha_ip_range_opt );
			$captcha_ip_range     = explode( ',', $captcha_ip_range_opt );

			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
				$ip_address = str_replace( '.', ',', $ip_address );
				$ip_address = explode( ',', $ip_address );

				if ( array_intersect( $captcha_ip_range, $ip_address ) ) {
					$exluded_country = true;
					return $exluded_country;
				}
			}

			return $exluded_country;
		}

		/**
		 * Captcha.
		 */
		public function ka_captcha_woocommerce_init() {
			// Update: version (1.3.0) Countries validation moved to its own function.
			$current_country_excluded = $this->ka_grc_validate_countries();
			if ( true === $current_country_excluded ) {
				return;
			}

			if ( 'v3' === get_option( 'captcha_type_option' ) ) {
				
				/**
				 * WooCommerce.
				 *
				 * @since 1.0.0
				 */
				if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
					if ( (int) get_option( 'add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_register_form', array( $this, 'ka_grc_render_captcha_v3' ) );// woo registration.
					}
					if ( (int) get_option( 'product_review_add_captcha_enable_check' ) === 1 ) {
						add_filter( 'woocommerce_product_review_comment_form_args', array( $this, 'ka_gr_v3_edit_review_users_review' ) ); // product_review.
					}
					if ( (int) get_option( 'login_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_login_form', array( $this, 'captcha_v3_woo_login' ) );// woo login.
					}
					if ( (int) get_option( 'woo_lpass_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_lostpassword_form', array( $this, 'ka_grc_render_captcha_v3' ) );// woo lost password.
					}
					if ( (int) get_option( 'guest_checkout_add_captcha_enable_check' ) === 1 ) {
						// Update: version (1.3.0) checkout position for WC Checkout captcha.
						$checkout_position = get_option( 'ka_grc_captcha_checkout_position' );
						add_action( $checkout_position, array( $this, 'captcha_v3_woo_checkout' ) );
					}// checkout.
					if ( (int) get_option( 'pay_order_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_pay_order_before_submit', array( $this, 'captcha_wp_v3_pay_for_order' ) );// pay for order.
					}
					if ( (int) get_option( 'p_method_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_review_order_before_payment', array( $this, 'captcha_v3_woo_payment_method' ) );
					}
				}

				if ( (int) get_option( 'wp_comment_captcha_check' ) === 1 ) {
					add_action( 'comment_form', array( $this, 'captcha_v3_wordpress_comment' ) );// WordPress comment.
				}
				if ( (int) get_option( 'wp_login_captcha_check' ) === 1 ) {
					add_action( 'login_form', array( $this, 'ka_grc_render_captcha_v3' ) );// WP login form.
				}
				if ( (int) get_option( 'wp_regs_captcha_check' ) === 1 ) {
					add_action( 'register_form', array( $this, 'ka_grc_render_captcha_v3' ) );// WP registration form.
				}
				if ( (int) get_option( 'wp_lpass_page_enable_check' ) === 1 ) {
					add_action( 'lostpassword_form', array( $this, 'ka_grc_render_captcha_v3' ) );// WP Lost Password.
				}
			} else {
				/**
				 * WooCommerce.
				 *
				 * @since 1.0.0
				 */
				if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
					if ( (int) get_option( 'add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_register_form', array( $this, 'captcha_woo_registration' ) );// woo registration.
					}
					if ( (int) get_option( 'product_review_add_captcha_enable_check' ) === 1 ) {
						add_filter( 'woocommerce_product_review_comment_form_args', array( $this, 'ka_gr_v2_edit_review' ) );
					}
					if ( (int) get_option( 'login_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_login_form', array( $this, 'captcha_woo_login' ) );// woo login.
					}
					if ( (int) get_option( 'woo_lpass_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_lostpassword_form', array( $this, 'captcha_woo_lostpassword' ) );// woo lost password.
					}
					if ( (int) get_option( 'guest_checkout_add_captcha_enable_check' ) === 1 ) {
						$checkout_position = get_option( 'ka_grc_captcha_checkout_position' );
						add_action( $checkout_position, array( $this, 'captcha_woo_guestcheckout' ) );
					}// checkout.
					if ( (int) get_option( 'pay_order_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_pay_order_before_submit', array( $this, 'captcha_wp_pay_for_order' ) );// pay for order.
					}
					if ( (int) get_option( 'p_method_add_captcha_enable_check' ) === 1 ) {
						add_action( 'woocommerce_review_order_before_payment', array( $this, 'captcha_woo_payment_method' ) );
					}
				}

				if ( (int) get_option( 'wp_comment_captcha_check' ) === 1 ) {
					add_action( 'comment_form', array( $this, 'captcha_wordpress_comment' ) );// WordPress comment.
				}
				if ( (int) get_option( 'wp_login_captcha_check' ) === 1 ) {
					add_action( 'login_form', array( $this, 'wp_captcha_show_login' ) );// WP login form.
				}
				if ( (int) get_option( 'wp_regs_captcha_check' ) === 1 ) {
					add_action( 'register_form', array( $this, 'captcha_wp_registration' ) );// WP registration form.
				}
				if ( (int) get_option( 'wp_lpass_page_enable_check' ) === 1 ) {
					add_action( 'lostpassword_form', array( $this, 'captcha_wp_lostpassword' ) );// wp lost pawword form.
				}
				add_action( 'woocommerce_register_post', array( $this, 'ka_grc_validate_recaptcha_submission' ), 10, 3 );
				add_action( 'woocommerce_login_post', array( $this, 'ka_grc_validate_recaptcha_submission' ), 10, 2 );
			}
		}

		/**
		 * Edit review.
		 *
		 * @param WP_Comment $comment_form The Comment Form object representing the review to be edited.
		 */
		public function ka_gr_v3_edit_review_users_review( $comment_form ) {
			$user_role_found = $this->ka_grc_user_roles( 'captcha_product_review_user_role' );
			if ( $user_role_found ) {
				$comment_form['comment_field'] .= '<p class="comment-form-comment"><input type= "hidden" value="true" name="grecaptcha_required" class="grecaptcha_required"></input> </p>';

				if ( ! wp_script_is( 'ka_cap_v3_admin_scr' ) ) {
					$this->ka_captcha_my_load_scripts_v3();
				}
			}
			return $comment_form;
		}

		/**
		 * Comment.
		 */
		public function captcha_v3_wordpress_comment() {
			global $post;
			if ( ( $post && $post->ID && 'product' === get_post_type( $post->ID ) ) || wp_script_is( 'ka_cap_v3_admin_scr' ) ) {
				return;
			}

			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'wp_comment_captcha_user_role' );
			if ( $user_role_found ) {
				?>
				<input type= "hidden" value="true" name="grecaptcha_required" class="grecaptcha_required"></input>
				<?php
				$this->ka_captcha_my_load_scripts_v3();
			}
		}

		/**
		 * Edit review.
		 *
		 * @param WP_Comment $comment_form The Comment Form object representing the review to be edited.
		 */
		public function ka_gr_v2_edit_review( $comment_form ) {
			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'captcha_product_review_user_role' );
			$cap_title       = get_option( 'product_review_add_captcha_field_title' );
			$title           = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$success_callback = 'post_coment_ka_captcha_validation_success';
			$grc_class        = 'word_post_button';
			$button_name      = 'Post Comment button';
			$cap_themes       = get_option( 'product_review_page_captcha_themes' );
			$cap_size         = get_option( 'product_review_add_captcha_size_radio' );

			ob_start();
			?>
			<div class="ka_google_recaptcha">
				<?php
				$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
				?>
			</div>
			<?php
			$result = ob_get_clean();
			if ( $user_role_found ) {
				$comment_form['comment_field'] .= $result;
			}
			return $comment_form;
		}

		/**
		 * Comment.
		 */
		public function captcha_wordpress_comment() {
			global $post;
			if ( ( $post && $post->ID && 'product' === get_post_type( $post->ID ) ) || wp_script_is( 'ka_cap_v3_admin_scr' ) ) {
				return;
			}

			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'wp_comment_captcha_user_role' );
			if ( ! $user_role_found ) {
				return;
			}

			$cap_title = get_option( 'wp_comment_captcha_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$success_callback = 'post_coment_ka_captcha_validation_success';
			$grc_class        = 'word_post_button';
			$button_name      = 'Post Comment button';
			$cap_themes       = get_option( 'wp_comment_captcha_theme_fields' );
			$cap_size         = get_option( 'wp_comment_add_captcha_size_radio' );
			?>
			<div class="ka_google_recaptcha">
				<?php
				$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
				?>
			</div>
			<?php
		}

		/**
		 * Load scripts.
		 */
		public function ka_captcha_my_load_scripts() {
			$source = get_option( 'ka_grc_captcha_source_option' );
			$api    = 'https://www.' . $source . '/recaptcha/api.js';
			wp_enqueue_script( 'jquery' );

			$checkout_captcha_enabled = 0;
			$payment_captcha_enabled  = 0;
			$is_checkout              = 0;

			if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				if ( (int) get_option( 'guest_checkout_add_captcha_enable_check' ) === 1 ) {
					$checkout_captcha_enabled = 1;
				}
				if ( (int) get_option( 'p_method_add_captcha_enable_check' ) === 1 ) {
					$payment_captcha_enabled = 1;
				}
				if ( is_checkout() ) {
					$is_checkout = 1;
				}
			}

			wp_enqueue_script( 'ka_cap_admin_api', $api, array(), '1.0', true );
			wp_enqueue_script( 'ka_cap_admin_scr', KA_GRC_URL . '/assets/js/ka-grc-front-captchav2.js', array( 'jquery' ), '1.0.2', true );

			$sitekey = get_option( 'add_captcha_site_key_field' );

			$localize_data = array(
				'ka_grc_checkout_captcha_enabled' => $checkout_captcha_enabled,
				'ka_grc_payment_captcha_enabled'  => $payment_captcha_enabled,
				'ka_site_key'                     => $sitekey,
				'ka_grc_is_checkout'              => $is_checkout,
				'ka_grc_recaptcha_nonce'          => wp_create_nonce( 'recaptcha_action' ),
			);
			wp_localize_script( 'ka_cap_admin_scr', 'ka_grc_captcha_v2_vars', $localize_data );
		}


		/**
		 * Create a front-end field for a form.
		 *
		 * @param array    $themes                    An array of themes for the field.
		 * @param string   $size                      The size of the field.
		 * @param string   $field_title               The title or label for the field.
		 * @param string   $grc_class                 The CSS class for styling the field.
		 * @param callable $success_callback             A callback function to populate the field's data.
		 * @param string   $button_name               The name of the submit button associated with this field.
		 *
		 * @return string HTML representation of the front-end field.
		 */
		public function front_end_field( $themes, $size, $field_title, $grc_class, $success_callback, $button_name ) {
			/**
			 * WPML Language.
			 *
			 * @since 1.0.0
			 */
			$current_language_code = apply_filters( 'wpml_current_language', null );

			$sitekey                       = get_option( 'add_captcha_site_key_field' );
			$ka_captcha_validation_expired = 'ka_captcha_validation_expired';
			$ka_captcha_validation_failed  = 'ka_captcha_validation_failed';
			$checkout_position             = get_option( 'ka_grc_captcha_checkout_position' );
			?>
			<label><?php echo esc_html( $field_title ); ?></label>
			<div class="g-recaptcha <?php echo esc_html( $grc_class ); ?>" data-sitelang = "h1=<?php echo esc_html( $current_language_code ); ?>" data-sitekey="<?php echo esc_html( $sitekey ); ?>" data-callback="<?php echo esc_html( $success_callback ); ?>" data-expired-callback="<?php echo esc_html( $ka_captcha_validation_expired ); ?>"  data-error-callback="<?php echo esc_html( $ka_captcha_validation_failed ); ?>" data-theme="<?php echo esc_html( $themes ); ?>" data-size="<?php echo esc_html( $size ); ?>"></div>
			<div id="error_message_div">
				<label id="ka_captcha_failed"><?php esc_html( 'Please check reCAPTCHA to enable ' . $button_name . '*' ); ?></label>
			</div>
			<?php
			if ( function_exists( 'is_ajax' ) && is_ajax() ) {
				$source = get_option( 'ka_grc_captcha_source_option' );
				$api    = 'https://www.' . $source . '/recaptcha/api.js';
				?>
				<script>
					// var script = document.createElement('script');
					// script.setAttribute('src', 'https://www.google.com/recaptcha/api.js');

					// jQuery(document).find(".g-recaptcha").after( script );
					jQuery('div.g-recaptcha').closest('form').find('button[type="submit"]').click(function( event ) {
						event.preventDefault();}).prop("disabled", true);
					jQuery('div.g-recaptcha').closest('form').find('input[type="submit"]').click(function( event ) {
						event.preventDefault();}).prop("disabled", true);

					// var reCaptchaScript = document.createElement('script');
					// reCaptchaScript.src = "https://www.google.com/recaptcha/api.js?hl=" + current_language_code;
					// document.head.appendChild(reCaptchaScript);
					let reCaptchaScript = document.createElement('script');
					reCaptchaScript.src = "<?php echo esc_url( $api ); ?>"; 
					document.head.appendChild(reCaptchaScript);

					let script = document.createElement('script');
					script.src = "<?php echo esc_url( $api . '?hl=' . $current_language_code ); ?>"; 
					document.head.appendChild(script);
				</script>
				<?php
				return;
			}

			if ( wp_script_is( 'ka_cap_admin_api' ) ) {
				return;
			} else {
				$this->ka_captcha_my_load_scripts();
			}
		}

		/**
		 * Login.
		 */
		public function captcha_woo_login() {
			$cap_title = get_option( 'woo_login_add_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$success_callback = 'login_ka_captcha_validation_success';
			$grc_class        = 'woo_login';
			$button_name      = 'login button';
			$cap_themes       = get_option( 'login_add_captcha_authentication_key_fields' );
			$cap_size         = get_option( 'login_add_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Registration.
		 */
		public function captcha_woo_registration() {
			$cap_title = get_option( 'woo_regs_enable_captcha' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = 'woo_regs';
			$success_callback = 'regs_ka_captcha_validation_success';
			$button_name      = 'registration button';
			$cap_themes       = get_option( 'woo_regs_add_captcha_authentication_key_fields' );
			$cap_size         = get_option( 'add_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Password.
		 */
		public function captcha_woo_lostpassword() {
			$cap_title = get_option( 'lpass_add_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = '';
			$success_callback = 'ka_captcha_validation_success';
			$button_name      = 'lost password button';
			$cap_themes       = get_option( 'woo_lpass_add_captcha_authentication_key_fields' );
			$cap_size         = get_option( 'woo_lpass_add_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Checkout.
		 */
		public function captcha_woo_guestcheckout() {
			$cap_title = get_option( 'guest_checkout_add_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = 'woo_checkout';
			$success_callback = 'ka_checkout_captcha_validation_success';
			$button_name      = 'place order button';
			$cap_themes       = get_option( 'check_out_page_captcha_themes' );
			$cap_size         = get_option( 'guest_checkout_add_captcha_size_radio' );

			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'captcha_user_role_enalbe' );
			if ( $user_role_found ) {
				$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
			}
		}

		/**
		 * Login.
		 */
		public function wp_captcha_show_login() {
			$cap_title = get_option( 'wp_login_captcha_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = '';
			$success_callback = 'ka_captcha_validation_success';
			$button_name      = 'login button';
			$cap_themes       = get_option( 'wp_login_captcha_theme_fields' );
			$cap_size         = get_option( 'wp_login_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Registration.
		 */
		public function captcha_wp_registration() {
			$cap_title = get_option( 'wp_regs_captcha_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = '';
			$success_callback = 'ka_captcha_validation_success';
			$button_name      = 'registration button';
			$cap_themes       = get_option( 'wp_regs_captcha_theme_fields' );
			$cap_size         = get_option( 'wp_regs_add_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Password.
		 */
		public function captcha_wp_lostpassword() {
			$cap_title = get_option( 'wp_lpass_add_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = '';
			$success_callback = 'ka_captcha_validation_success';
			$button_name      = 'lost password button';
			$cap_themes       = get_option( 'wp_lpass_add_captcha_authentication_key_fields' );
			$cap_size         = get_option( 'wp_lpass_add_captcha_size_radio' );

			$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
		}

		/**
		 * Order.
		 */
		public function captcha_wp_pay_for_order() {
			$cap_title = get_option( 'add_p_order_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$success_callback = 'ka_captcha_validation_success';
			$grc_class        = '';
			$button_name      = 'pay for order button';
			$cap_themes       = get_option( 'p_order_authentication_key_radio' );
			$cap_size         = get_option( 'p_order_add_captcha_size_radio' );

			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'pay_order_add_captcha_user_role' );
			if ( $user_role_found ) {
				$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
			}
		}

		/**
		 * Payment.
		 */
		public function captcha_woo_payment_method() {
			$cap_title = get_option( 'add_p_method_captcha_field_title' );
			$title     = empty( $cap_title ) ? get_option( 'captcha_general_title_option' ) : $cap_title;

			$grc_class        = 'woo_payment';
			$success_callback = 'pay_ka_captcha_validation_success';
			$button_name      = 'payment methods';
			$cap_themes       = get_option( 'p_method_captcha_themes' );
			$cap_size         = get_option( 'p_method_add_captcha_size_radio' );

			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'p_method_add_captcha_user_role' );
			if ( $user_role_found ) {
				$this->front_end_field( $cap_themes, $cap_size, $title, $grc_class, $success_callback, $button_name );
			}
		}

		/**
		 * Scripts.
		 */
		public function ka_captcha_my_load_scripts_v3() {
			/**
			 * WPML Language.
			 *
			 * @since 1.0.0
			 */

			$ka_current_page       =is_checkout() ? 'checkout' : '' ;
			$current_language_code = apply_filters( 'wpml_current_language', null );
			$sitekey               = get_option( 'add_captcha_site_key_field' );
			// Update: version (1.3.0) reCaptcha support.
			$source = get_option( 'ka_grc_captcha_source_option' );
			$api    = 'https://www.' . $source . '/recaptcha/api.js?render=' . $sitekey;
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'ka_cap_v3_admin_api', $api, array(), '1.0', true );
			// Update: version (1.3.0) Enqueued script using PHP global variables.
			wp_enqueue_script( 'ka_cap_v3_front_scr', KA_GRC_URL . '/assets/js/ka-grc-front-captchav3.js', false, '1.0', false );
			// Update: version (1.3.0) Passed $api as argument to JS.
			$localize_data = array(
				'nonce'           => wp_create_nonce( 'recaptcha-ajax-nonce' ),
				'admin_url'       => admin_url( 'admin-ajax.php' ),
				'v3_sitekey'      => $sitekey,
				'v3_lang'         => $current_language_code,
				'ka_grc_api'      => $source,
				'ka_current_page' => $ka_current_page,
			);
			wp_localize_script( 'ka_cap_v3_front_scr', 'ka_grc_php_vars', $localize_data );
		}

		/**
		 * Registration.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_render_captcha_v3() {
			?>
				<input type= "hidden" value="true" name="grecaptcha_required" class="grecaptcha_required"></input>
			<?php
			if ( wp_script_is( 'ka_cap_v3_admin_scr' ) ) {
				return;
			} else {
				$this->ka_captcha_my_load_scripts_v3();
			}
		}

		/**
		 * Login.
		 */
		public function captcha_v3_woo_login() {
			if ( is_checkout() ) {
				return;
			}
			// Update: version (1.3.0) Duplicated code moved to a new function.
			$this->ka_grc_render_captcha_v3();
		}

		/**
		 * Checkout.
		 */
		public function captcha_v3_woo_checkout() {
			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'captcha_user_role_enalbe' );
			if ( $user_role_found ) {
				// Update: version (1.3.0) Duplicated code moved to a new function.
				$this->ka_grc_render_captcha_v3();
			}
		}

		/**
		 * Order.
		 */
		public function captcha_wp_v3_pay_for_order() {
			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'pay_order_add_captcha_user_role' );
			if ( $user_role_found ) {
				// Update: version (1.3.0) Duplicated code moved to a new function.
				$this->ka_grc_render_captcha_v3();
			}
		}

		/**
		 * Payment.
		 */
		public function captcha_v3_woo_payment_method() {
			// Update: version (1.3.0) User Role validation.
			$user_role_found = $this->ka_grc_user_roles( 'p_method_add_captcha_user_role' );
			if ( $user_role_found ) {
				// Update: version (1.3.0) Duplicated code moved to a new function.
				$this->ka_grc_render_captcha_v3();
			}
		}

		/**
		 * No-Conflict mode.
		 *
		 * @since 1.3.0
		 */
		public function ka_grc_recaptcha_no_conflict() {
			?>
			<style type="text/css">
				body .grecaptcha-badge{
					visibility: visible!important;
				}
			</style>
			<?php
			if ( empty( get_option( 'ka_grc_captcha_no_conflict' ) ) ) {
				return;
			}
			$scripts = wp_scripts();
			$urls    = array( 'hcaptcha.com/1', 'challenges.cloudflare.com/turnstile' );

			foreach ( $scripts->queue as $handle ) {
				foreach ( $urls as $url ) {
					if ( str_contains( $scripts->registered[ $handle ]->src, $url ) ) {
						wp_dequeue_script( $handle );
						wp_deregister_script( $handle );
						break;
					}
				}
			}
		}

		public function ka_grc_rate_limiting_script() {
			$disable_ips_address = get_option('ka_grc_rate_disable_ips');
			$disable_ips_address = array_map('trim', explode(',', $disable_ips_address));
			$ip_address          = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';

			$is_disabled            = get_transient( "disable_checkout_button_{$ip_address}" );
			$enable_rate_limit      = get_option('ka_grc_checkout_rate_limit_check');
			$exclude_user_role      = (array) get_option('ka_grc_rate_exclude_user_role', array());
			$exclude_customer_email = get_option('ka_grc_rate_exclude_by_email');
			$exclude_customer_email = array_map('trim', explode(',', $exclude_customer_email));
			$disable_time_minutes   = get_option('ka_grc_disable_checkout_button');
			$attempt_per_limit      = (array) get_option('ka_grc_set_rate_limit');
			$attempt_seconds        = (array) get_option('ka_grc_set_rate_seconds');
			// $attempt_per_limit = max($attempt_per_limit);
			// $attempt_seconds = max($attempt_seconds);
			$ka_grc_exclude_ips_address = get_option('ka_grc_rate_exclude_ips');
			$ka_grc_exclude_ips_address = array_map('trim', explode(',', $ka_grc_exclude_ips_address));

			if (1 !== (int) $enable_rate_limit) {
				return;
			}

			if ( empty($attempt_per_limit) || empty($attempt_seconds) ) {
				return;
			}

			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$user_roles   = $current_user->roles;
				$user_email   = $current_user->user_email;
				foreach ($user_roles as $role) {
					if (in_array($role, $exclude_user_role, true)) {
						return;
					}
				}
				if (in_array($user_email, $exclude_customer_email, true)) {
					return;
				}
			} elseif (! is_user_logged_in() ) {
				if ( in_array( 'guest', (array) $exclude_user_role, true ) ) {
					return;
				}
			}
			if (in_array($ip_address, $ka_grc_exclude_ips_address, true)) {
			return;
			}
			$is_checkout = 0;
			if (is_checkout()) {
				$is_checkout = 1;
			}

			$rate_sections = array();

			foreach ($attempt_per_limit as $index => $limit) {
				if (isset($attempt_seconds[ $index ])) {
					$rate_sections[] = array(
						'limit'   => (int) $limit,
						'seconds' => (int) $attempt_seconds[ $index ],
					);
				}
			}
			wp_enqueue_script( 'ka_disable_order_button', KA_GRC_URL . 'assets/js/ka-grc-disable-ip.js', array( 'jquery' ), '1.0.0', true );
			wp_localize_script( 'ka_disable_order_button', 'ka_excluded_ips', array(
				'ipAddress'              => $ip_address,
				'excludedIps'            => $disable_ips_address,
				'is_disabled'            => $is_disabled ? 'yes' : 'no',
				'disbale_time'           => $disable_time_minutes ? $disable_time_minutes : 1,
				'attemp_per'             => $attempt_per_limit,
				'attempt_seconds'        => $attempt_seconds,
				'ka_grc_is_checkout'     => $is_checkout,
				'ka_grc_enbale_checkout' => get_option( 'guest_checkout_add_captcha_enable_check' ) == 1 ? 1 : 0,
				'ka_grc_enable_payment'  => get_option('p_method_add_captcha_enable_check') == 1 ? 1 :0,
				'admin_url'              => admin_url( 'admin-ajax.php' ),
				'nonce'                  => wp_create_nonce( 'recaptcha-ajax-nonce' ),
				'rate_limiter'           => $rate_sections,
			)   
			);
		}
	}
	new Frontend_Ka_Add_Recaptcha();
}
