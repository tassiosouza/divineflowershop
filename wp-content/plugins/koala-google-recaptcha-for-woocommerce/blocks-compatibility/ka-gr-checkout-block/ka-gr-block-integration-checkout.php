<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

	/**
	* Class for integrating with WooCommerce Blocks
	*/
class Koalaaps_Google_Recaptcha_Checkout_Blocks_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ka_gr_checkout_block';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {


		$this->register_ka_gr_upload_file_block__block_frontend_scripts();
		$this->register_ka_gr_upload_file_block__block_editor_scripts();
		$this->register_main_integration();

		add_action('wp_enqueue_scripts', array( $this, 'kgr_recaptca_localize' ));
	}

	public function kgr_recaptca_localize() {
		wp_enqueue_script( 'kgr-blocks-frontj', plugins_url( '/src/js/ka-gr-checkout-block/block.js', __FILE__ ), '', '1.0', false );
		wp_enqueue_script( 'jquery' );

		$kgr_captcha_enable               = get_option('guest_checkout_add_captcha_enable_check');
		$kgr_captcha_enable_payment       = get_option('p_method_add_captcha_enable_check');
		$ka_grc_captcha_checkout_position = get_option('ka_grc_captcha_checkout_position');
		$ka_grc_title                     = get_option('guest_checkout_add_captcha_field_title');
		$ka_grc_payment_title             = get_option('add_p_method_captcha_field_title');
		$ka_grc_payment_theme_color       = get_option('p_method_captcha_themes');
		$ka_grc_theme_color               = get_option('check_out_page_captcha_themes');
		$ka_grc_size                      = get_option('guest_checkout_add_captcha_size_radio');
		$ka_grc_payment_size              = get_option('p_method_add_captcha_size_radio');
		$kgr_site_key                     = get_option('add_captcha_site_key_field');
		$kgr_user_role                    = get_option('captcha_user_role_enalbe');
		

			
		$kgr_user_role_found = $this->ka_grc_user_roles_block('captcha_user_role_enalbe');

		$kgr_user_role_found_payment =$this->ka_grc_user_roles_block('p_method_add_captcha_user_role');

		$current_country_excluded = $this->ka_grc_validate_countries();
		if ( false === $current_country_excluded ) {
				$localized_data = array();

				// Checkout reCAPTCHA
			if ( 1 == $kgr_captcha_enable ) {
				if ( ( true == $kgr_user_role_found ) && ( 'v2' === get_option( 'captcha_type_option') ) && !empty( $kgr_site_key ) ) {
					$localized_data['checkout'] = array(
						'admin_url'               => admin_url( 'admin-ajax.php' ),
						'google_recapta_site_key' => $kgr_site_key,
						'ka_captcha_position'     => $ka_grc_captcha_checkout_position,
						'title'                   => $ka_grc_title,
						'themeColor'              => $ka_grc_theme_color,
						'size'                    => $ka_grc_size,
						'dynamicCallback'         => 'ka_checkout_captcha_validation_success',
						'ka_grc_classes'          => 'g-recaptcha woo_checkout',
					);
				}
			}

				// Payment reCAPTCHA
			if ( 1 == $kgr_captcha_enable_payment ) {
				if ( ( true == $kgr_user_role_found_payment ) && ( 'v2' === get_option( 'captcha_type_option') ) && !empty( $kgr_site_key ) ) {
					$localized_data['payment'] = array(
						'admin_url'               => admin_url( 'admin-ajax.php' ),
						'google_recapta_site_key' => $kgr_site_key,
						'ka_captcha_position'     => 'woocommerce_review_order_before_payment',
						'title'                   => $ka_grc_payment_title,
						'themeColor'              => $ka_grc_payment_theme_color,
						'size'                    => $ka_grc_payment_size,
						'dynamicCallback'         => 'pay_ka_captcha_validation_success',
						'ka_grc_classes'          => 'g-recaptcha woo_payment',
					);
				}
			}

				// Send localized data to JavaScript
			if ( ! empty( $localized_data ) ) {
				wp_localize_script( 'kgr-blocks-frontj', 'kgr_php_vars', $localized_data );
			} else {
				wp_localize_script( 'kgr-blocks-frontj', 'kgr_php_vars', array() );
			}
		} else {
			wp_localize_script( 'kgr-blocks-frontj', 'kgr_php_vars', array() );
		}
		
		if ( false === $current_country_excluded && 'v3' == get_option( 'captcha_type_option') && !empty($kgr_site_key) ) {
			if ( ( 1 == $kgr_captcha_enable && 1 == $kgr_captcha_enable_payment ) || 1 == $kgr_captcha_enable ) { 
				if ( true == $kgr_user_role_found ) {
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
			} elseif ( empty( $kgr_captcha_enable ) && ( 1 == $kgr_captcha_enable_payment ) && ( true == $kgr_user_role_found_payment ) ) {
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
		}
	}


	public function ka_grc_user_roles_block( $option ) {
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
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	public function register_main_integration() {
		$script_path       = '/build/index.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_path ),
			);



		wp_register_script(
			'ka-gr-checkout-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'ka-gr-checkout-blocks-integration',
			'ka_gr_checkout_block',
			__DIR__ . '/languages'
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'ka-gr-checkout-blocks-integration', 'ka-gr-checkout-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'ka-gr-checkout-blocks-integration', 'ka-gr-checkout-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
	}

	
	public function register_ka_gr_upload_file_block__block_editor_scripts() {
		$script_path       = '/build/ka-gr-checkout-block.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/ka-gr-checkout-block.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'ka-gr-checkout-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'ka-gr-checkout-block-editor',
			'ka_gr_checkout_block',
			__DIR__ . '/languages'
		);
	}

	

	public function register_ka_gr_upload_file_block__block_frontend_scripts() {
		$script_path       = '/build/ka-gr-checkout-block-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/ka-gr-checkout-block-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'ka-gr-checkout-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'ka-gr-checkout-block-frontend',
			'ka_gr_checkout_block',
			__DIR__ . '/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return KA_GOOGLE_RECAPTCHA_BLOCK_VERSION;
	}

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
//      if ( ! empty( $gr_visitor_current_country ) && empty( $gr_selected_country ) ) {
			return $exluded_country;
//      }
	}
}
