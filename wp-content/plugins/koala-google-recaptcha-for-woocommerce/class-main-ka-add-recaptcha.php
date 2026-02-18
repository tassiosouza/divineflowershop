<?php
/**
 * Plugin Name:       Google reCaptcha for WooCommerce
 * Requires Plugins: woocommerce
 * Plugin URI:        https://woocommerce.com/products/koala-google-recaptcha-for-woocommerce
 * Description:       Add reCaptcha to your WooCommerce and WordPress login, registration, reset password and other pages.
 * Version:           1.5.4
 * Author:            KoalaApps
 * Developed By:      KoalaApps
 * Author URI:        https://woocommerce.com/vendor/koalaapps/
 * Support:           https://woocommerce.com/vendor/koalaapps/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:       /languages
 * Text Domain:       recaptcha_verification
 * WC requires at least: 4.0
 * WC tested up to: 10.*.*
 * Requires at least: 6.5
 * Tested up to: 6.*.*
 * Requires PHP: 7.4
 *
 * @package Google_reCaptcha_for_WooCommerce
 * Woo: 7673745:724159e27ebb90c2e770cafffa65ec6a

 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Main_Ka_Add_Recaptcha' ) ) {
	/**
	 * This class initializes and manages the core functionality of the plugin.
	 */
	class Main_Ka_Add_Recaptcha {
		/**
		 * Function construct start.
		 */
		public function __construct() {
			$this->ka_grc_global_constents_vars();
			add_action( 'init', array( $this, 'ka_grc_admin_init' ) );
			// HOPS compatibility.
			add_action( 'before_woocommerce_init', array( $this, 'ka_grc_hops_compatibility' ) );
		
			add_action( 'woocommerce_blocks_loaded', array( $this, 'ka_gr_block_init' ) );
			register_activation_hook( __FILE__, array( $this, 'ka_grc_register_settings' ) );
		}

		/**
		 * Hooks into necessary actions to set up plugin.
		 */
		public function ka_grc_admin_init() {
			$options_to_update = array(
				'captcha_type_option'                   => array( 'v2', 'v3' ),
				'check_out_page_captcha_themes'         => array( 'light', 'dark' ),
				'guest_checkout_add_captcha_size_radio' => array( 'normal', 'compact' ),
				'login_add_captcha_authentication_key_fields' => array( 'light', 'dark' ),
				'login_add_captcha_size_radio'          => array( 'normal', 'compact' ),
				'woo_lpass_add_captcha_authentication_key_fields' => array( 'light', 'dark' ),
				'woo_lpass_add_captcha_size_radio'      => array( 'normal', 'compact' ),
				'p_order_authentication_key_radio'      => array( 'light', 'dark' ),
				'p_order_add_captcha_size_radio'        => array( 'normal', 'compact' ),
				'p_method_captcha_themes'               => array( 'light', 'dark' ),
				'p_method_add_captcha_size_radio'       => array( 'normal', 'compact' ),
				'product_review_page_captcha_themes'    => array( 'light', 'dark' ),
				'product_review_add_captcha_size_radio' => array( 'normal', 'compact' ),
				'woo_regs_add_captcha_authentication_key_fields' => array( 'light', 'dark' ),
				'add_captcha_size_radio'                => array( 'normal', 'compact' ),
				'wp_comment_captcha_theme_fields'       => array( 'light', 'dark' ),
				'wp_comment_add_captcha_size_radio'     => array( 'normal', 'compact' ),
				'wp_login_captcha_theme_fields'         => array( 'light', 'dark' ),
				'wp_login_captcha_size_radio'           => array( 'normal', 'compact' ),
				'wp_lpass_add_captcha_authentication_key_fields' => array( 'light', 'dark' ),
				'wp_lpass_add_captcha_size_radio'       => array( 'normal', 'compact' ),
				'wp_regs_captcha_theme_fields'          => array( 'light', 'dark' ),
				'wp_regs_add_captcha_size_radio'        => array( 'normal', 'compact' ),
			);

			foreach ( $options_to_update as $option => $values ) {
				if ( get_option( $option ) === (string) 1 ) {
					update_option( $option, $values[0] );
				} elseif ( get_option( $option ) === (string) 0 ) {
					update_option( $option, $values[1] );
				}
			}

			add_action( 'after_setup_theme', array( $this, 'ka_grc_textdomain' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'ka_grc_plugin_action_links' ) );

			include_once KA_PLUGIN_DIR . 'includes/class-ajax-controller-ka-add-recaptcha.php';
			if ( is_admin() ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
				include_once KA_PLUGIN_DIR . '/includes/admin/class-admin-ka-add-recaptcha.php';
				include_once KA_PLUGIN_DIR . '/includes/admin/checkout-rate-log.php';

			} else {
				include_once KA_PLUGIN_DIR . '/includes/front/class-frontend-ka-add-recaptcha.php';
			}
		}

		public function ka_grc_register_settings() {

			if ( empty(get_option('ka_grc_disable_checkout_button')) ) {
				update_option('ka_grc_disable_checkout_button', 1, true);
			}

			if ( empty( get_option('captcha_custom_score_opt')) ) {
				update_option('captcha_custom_score_opt', 0.4, true);
			}
		}

		/**
		 * HOPS compatibility.
		 */
		public function ka_grc_hops_compatibility() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		/**
		 * Function to define global constants.
		 */
		public function ka_grc_global_constents_vars() {
			if ( ! defined( 'KA_GOOGLE_RECAPTCHA_BLOCK_VERSION' ) ) {
				$ka_up_block_version = get_file_data( __FILE__, array( 'version' => 'version' ) );
				define( 'KA_GOOGLE_RECAPTCHA_BLOCK_VERSION', $ka_up_block_version['version'] );
			}
			if ( ! defined( 'KA_GRC_URL' ) ) {
				define( 'KA_GRC_URL', plugin_dir_url( __FILE__ ) );
			}
			if ( ! defined( 'KA_BASENAME' ) ) {
				define( 'KA_BASENAME', plugin_basename( __FILE__ ) );
			}
			if ( ! defined( 'KA_PLUGIN_DIR' ) ) {
				define( 'KA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
		}

		/**
		 * Function to load textdomain.
		 */
		public function ka_grc_textdomain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'recaptcha_verification', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Add custom action links on plugin screen.
		 *
		 * @param mixed $actions Plugin Actions Links.
		 * @return array
		 */
		public function ka_grc_plugin_action_links( $actions ) {
			$custom_actions = array(
				'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=ka_captcha' ), __( 'Settings', 'recaptcha_verification' ) ),
			);
			return array_merge( $custom_actions, $actions );
		}

		public function ka_gr_woocommerce_blocks_active() {
			return class_exists( 'Automattic\WooCommerce\Blocks\Package' );
		}

		public function ka_gr_woocommerce_blocks_version_supported() {
			return version_compare(
				\Automattic\WooCommerce\Blocks\Package::get_version(),
				'7.3.0',
				'>='
			);
		}
		public function af_recaptcha_data_to_order_meta_data_block( $order, $request ) {
			
			$body                          = $request->get_body();
			$decodededarray                =json_decode($body, true);
			$checkout_enable_check         = get_option('guest_checkout_add_captcha_enable_check');
			$checkout_enable_check_payment = get_option('p_method_add_captcha_enable_check');
			$kgr_user_role_found           = $this->ka_grc_user_roles('captcha_user_role_enalbe');
			$kgr_user_role_found_payment   = $this->ka_grc_user_roles('p_method_add_captcha_user_role');
			if (isset($decodededarray['extensions']['kgr_google_recaptcha'])) {

				$captchaData = $decodededarray['extensions']['kgr_google_recaptcha'];
				// Validate Checkout reCAPTCHA if present
				if (1 == $checkout_enable_check && true == $kgr_user_role_found ) {

					if (!empty($captchaData['kgr_checkout_recaptcha'])) {
						$recaptchaCheck = $this->block_captcha_check($captchaData['kgr_checkout_recaptcha']);
						if ('error' === $recaptchaCheck) {
							throw new Exception('Google Recaptcha is not valid');
						}
					} else {
						throw new Exception('Google Recaptcha is Required');
					}
				}

				// Validate Payment reCAPTCHA if present
				if (1 == $checkout_enable_check_payment && true == $kgr_user_role_found_payment ) {
					if (!empty($captchaData['kgr_payment_recaptcha'])) {
						$recaptchaCheck = $this->block_captcha_check($captchaData['kgr_payment_recaptcha']);
						if ('error' === $recaptchaCheck) {
							throw new Exception('Google Recaptcha is not valid');
						}
					} else {
						throw new Exception('Google Recaptcha is Required');
					}
				}

			} else {
				throw new Exception('Google Recaptcha is Required');
			}
		}
		public function block_captcha_check( $res ) {

			$secret = get_option('add_captcha_secret_key_field');
			
			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $res);
			
			$responseData = json_decode($verifyResponse);
			if ($responseData->success) {
			return 'success';
			} else {
			return 'error';
			}
		}

		public function ka_gr_block_init() {
			$checkout_enable_check         = get_option('guest_checkout_add_captcha_enable_check');
			$checkout_enable_check_payment = get_option('p_method_add_captcha_enable_check');
			$checkout_v2_captcha           = get_option('captcha_type_option');
			$kgr_user_role_found           = $this->ka_grc_user_roles('captcha_user_role_enalbe');
			$kgr_user_role_found_payment   = $this->ka_grc_user_roles('p_method_add_captcha_user_role');

			$current_country_excluded = $this->ka_grc_validate_countries();
			if ( true === $current_country_excluded ) {
					return;
			}
			if ( ( 1 == $checkout_enable_check && true == $kgr_user_role_found ) || ( 1 == $checkout_enable_check_payment && true == $kgr_user_role_found_payment )) {
				if ('v2' == $checkout_v2_captcha ) {
				add_action('woocommerce_store_api_checkout_update_order_from_request', array( $this, 'af_recaptcha_data_to_order_meta_data_block' ), 10, 2);
				}
			}

			if (( $this->ka_gr_woocommerce_blocks_active() )&&( $this->ka_gr_woocommerce_blocks_version_supported() ) ) { 

				require_once KA_PLUGIN_DIR . '/blocks-compatibility/ka-gr-checkout-block/ka-gr-block-integration-checkout.php';
			
			
				if ( class_exists( 'Koalaaps_Google_Recaptcha_Checkout_Blocks_Integration' ) ) {
					add_action(
					'woocommerce_blocks_checkout_block_registration',
						function ( $integration_registry ) {
						$integration_registry->register( new Koalaaps_Google_Recaptcha_Checkout_Blocks_Integration() );
						}
					);

				}

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
		public function ka_grc_validate_countries() {
			$exluded_country = false;
			if ( ! is_multisite() && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				$location                   = WC_Geolocation::geolocate_ip();
				$gr_visitor_current_country = $location['country'];
				$gr_selected_country        = (array) get_option( 'captcha_country_select_opt' );

				if ( ! empty( $gr_visitor_current_country ) && ! empty( $gr_selected_country ) && in_array( $gr_visitor_current_country, $gr_selected_country, true ) ) {
					$exluded_country = true;
					return $exluded_country;
				} elseif ( ! empty( $gr_visitor_current_country ) && empty( $gr_selected_country ) ) {
					return $exluded_country;
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
				} else {
					return $exluded_country;
				}
			}
		}
	}
	new Main_Ka_Add_Recaptcha();
}
