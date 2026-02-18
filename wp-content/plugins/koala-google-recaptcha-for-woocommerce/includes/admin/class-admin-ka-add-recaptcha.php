<?php
/**
 * Captcha Admin settings.
 *
 * @package Google_reCaptcha_for_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Restrict direct access
}

if ( ! class_exists( 'Admin_Ka_Add_Recaptcha' ) ) {
	class Admin_Ka_Add_Recaptcha extends Main_Ka_Add_Recaptcha {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_recaptcha_menu' ) );
			add_action( 'admin_init', array( $this, 'ka_add_register_fields' ) );
			add_action( 'all_admin_notices', array( $this, 'ka_recaptcha_render_tabs' ), 5 );
			add_action( 'admin_enqueue_scripts', array( $this, 'gr_enque_js_call_back' ) );
			add_action( 'admin_init', array( $this, 'ka_grc_chekout_log_delete' ) );
		}

		// Enqueue JS/CSS only on our page
		public function gr_enque_js_call_back() {
			global $current_screen;
			if ( 'toplevel_page_ka_captcha' === $current_screen->base ) {
				wp_enqueue_script( 'ka-grc-select2-js', KA_GRC_URL . '/assets/js/select2.js', array( 'jquery' ), '1.0', true );
				wp_enqueue_style( 'ka-grc-select2-css', KA_GRC_URL . '/assets/css/select2.css', array(), '1.0' );

				wp_enqueue_script( 'gr_search_user_role', KA_GRC_URL . '/assets/js/ka-grc-admin.js', array( 'jquery' ), '1.0', true );
				wp_enqueue_style( 'ka-grc-admin', KA_GRC_URL . '/assets/css/ka-grc-admin.css', array(), '1.0' );
			}
		}

		// Add menu
		public function add_recaptcha_menu() {
			add_menu_page(
				'reCaptcha',
				esc_html__( 'reCaptcha', 'recaptcha_verification' ),
				'manage_options',
				'ka_captcha',
				array( $this, 'create_recaptcha_setting_page' ),
				KA_GRC_URL . '/assets/img/ka-grc-logo.png',
				55
			);
		}

		// Get current tab
		public function get_current_tab() {
			return get_current_screen()->id === 'toplevel_page_ka_captcha' ? 'general' : '';
		}

		public function get_tab_screen_ids() {
			return array( 'toplevel_page_ka_captcha' );
		}

		// Render Tabs
		public function ka_recaptcha_render_tabs() {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->id, $this->get_tab_screen_ids(), true ) ) {

				$tabs = array(
					'captcha_general_setting' => array(
						'title' => __( 'General Setting', 'recaptcha_verification' ),
						'url'   => admin_url( 'admin.php?page=ka_captcha' ),
					),
					'wcrecaptcha' => array(
						'title' => __( 'WC Setting', 'recaptcha_verification' ),
						'url'   => admin_url( 'admin.php?page=ka_captcha&tab=wcrecaptcha&subtab_recaptcha=registration_tab' ),
					),
					'wprecaptcha' => array(
						'title' => __( 'WP Setting', 'recaptcha_verification' ),
						'url'   => admin_url( 'admin.php?page=ka_captcha&tab=wprecaptcha&subtab_wprecaptcha=wpregistration_tab' ),
					),
					'screcaptcha' => array(
						'title' => __( 'Shortcode', 'recaptcha_verification' ),
						'url'   => admin_url( 'admin.php?page=ka_captcha&tab=screcaptcha' ),
					),
					'checkout_rate_limit' => array(
						'title' => __( 'Checkout Rate Limiter', 'recaptcha_verification' ),
						'url'   => admin_url( 'admin.php?page=ka_captcha&tab=checkout_rate_limit' ),
					),
				);

				// Remove WooCommerce tab if not active
				if ( ! is_multisite() && ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
					unset( $tabs['wcrecaptcha'] );
				}

				$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'captcha_general_setting';
				$current_tab = $this->get_current_tab() === 'general' ? $active_tab : $this->get_current_tab();

				echo '<div class="wrap woocommerce"><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
				foreach ( $tabs as $id => $tab_data ) {
					$class = ( $id === $current_tab ) ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' );
					$nonce_url = wp_nonce_url( $tab_data['url'], 'ka_grc_url_nonce' );
					printf(
						'<a href="%1$s" class="%2$s">%3$s</a>',
						esc_url( $nonce_url ),
						implode( ' ', array_map( 'sanitize_html_class', $class ) ),
						esc_html( $tab_data['title'] )
					);
				}
				echo '</h2></div>';
			}
		}

		// Create setting page with nonce validation
		public function create_recaptcha_setting_page() {
			global $active_tab;

			// Verify nonce only when tab is set
			if ( isset( $_GET['tab'] ) ) {
				$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
				if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ka_grc_url_nonce' ) ) {
					wp_die( esc_html__( 'Security Violated.', 'recaptcha_verification' ) );
				}
			}

			$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'captcha_general_setting';
			$active_tab_recaptcha = isset( $_GET['subtab_recaptcha'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab_recaptcha'] ) ) : 'registration_tab';
			$active_tab_wprecaptcha = isset( $_GET['subtab_wprecaptcha'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab_wprecaptcha'] ) ) : 'wpregistration_tab';
			$active_subtab_rate = isset( $_GET['subtab_rate'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab_rate'] ) ) : 'rate_limiter';

			?>

			<div>
				<h2><?php echo esc_html__( 'Recaptcha Setting', 'recaptcha_verification' ); ?></h2>
				<?php settings_errors(); ?>
				<form method="POST" action="options.php">

					<?php
					// General tab
					if ( 'captcha_general_setting' === $active_tab ) {
						settings_fields( 'wp_captcha_general' );
						do_settings_sections( 'wp_recaptcha_general' );
					}

					// WooCommerce tab
					if ( 'wcrecaptcha' === $active_tab ) {
						$tabs_arr = array(
							'registration_tab'   => __( 'WC Registration', 'recaptcha_verification' ),
							'login_tab'          => __( 'WC Login', 'recaptcha_verification' ),
							'lost_password_tab'  => __( 'WC Lost Password', 'recaptcha_verification' ),
							'checkout_tab'       => __( 'WC Checkout', 'recaptcha_verification' ),
							'payment_tab'        => __( 'WC Payment Method', 'recaptcha_verification' ),
							'payorder_tab'       => __( 'WC Pay for order', 'recaptcha_verification' ),
							'product_review_tab' => __( 'WC Product Review', 'recaptcha_verification' ),
						);
						echo '<ul class="subsubsub">';
						foreach ( $tabs_arr as $key => $label ) {
							$subtab_url = wp_nonce_url( '?page=ka_captcha&tab=wcrecaptcha&subtab_recaptcha=' . $key, 'ka_grc_url_nonce' );
							printf(
								'<li><a href="%1$s" class="%2$s">%3$s</a> %4$s</li>',
								esc_url( $subtab_url ),
								esc_attr( $active_tab_recaptcha === $key ? 'current' : '' ),
								esc_html( $label ),
								end( $tabs_arr ) !== $label ? ' | ' : ''
							);
						}
						echo '</ul>';

						$fields_registration = array(
							'registration_tab'   => array( 'captcha-woo-regs', 'recaptcha-woo-regs' ),
							'login_tab'          => array( 'captcha-woo-login', 'recaptcha-woo-login' ),
							'lost_password_tab'  => array( 'captcha-woo-lpass', 'recaptcha-woo-lpass' ),
							'checkout_tab'       => array( 'captcha-woo-guest-checkout', 'recaptcha-woo-guest-checkout' ),
							'payment_tab'        => array( 'captcha-p_method-settings', 'recaptcha-p_method-settings' ),
							'payorder_tab'       => array( 'captcha-p-order-settings', 'recaptcha-p-order-settings' ),
							'product_review_tab' => array( 'captcha-product-review-settings', 'recaptcha-product-review-settings' ),
						);

						foreach ( $fields_registration as $key => $reg_and_sec_arr ) {
							if ( $key === $active_tab_recaptcha ) {
								settings_fields( current( $reg_and_sec_arr ) );
								do_settings_sections( end( $reg_and_sec_arr ) );
							}
						}
					}

					// WP tab
					if ( 'wprecaptcha' === $active_tab ) {
						$tabs_wparr = array(
							'wpregistration_tab'  => __( 'WP Registration', 'recaptcha_verification' ),
							'wplogin_tab'         => __( 'WP Login', 'recaptcha_verification' ),
							'wplost_password_tab' => __( 'WP Lost Password', 'recaptcha_verification' ),
							'wp_coment_meta_tab'  => __( 'WP Comment', 'recaptcha_verification' ),
						);
						echo '<ul class="subsubsub">';
						foreach ( $tabs_wparr as $key => $label ) {
							$subtab_url = wp_nonce_url( '?page=ka_captcha&tab=wprecaptcha&subtab_wprecaptcha=' . $key, 'ka_grc_url_nonce' );
							printf(
								'<li><a href="%1$s" class="%2$s">%3$s</a> %4$s</li>',
								esc_url( $subtab_url ),
								esc_attr( $active_tab_wprecaptcha === $key ? 'current' : '' ),
								esc_html( $label ),
								end( $tabs_wparr ) !== $label ? ' | ' : ''
							);
						}
						echo '</ul>';

						$wpfields_registration = array(
							'wpregistration_tab'  => array( 'wp_captcha_registration', 'wp_recaptcha_registration' ),
							'wplogin_tab'         => array( 'wp_captcha_login', 'wp_recaptcha_login' ),
							'wplost_password_tab' => array( 'wp_captcha_lostpassword', 'wp_recaptcha_lostpassword' ),
							'wp_coment_meta_tab'  => array( 'wp_captcha_coment_meta', 'wp_recaptcha_coment_meta' ),
						);

						foreach ( $wpfields_registration as $key => $wpreg_and_sec_arr ) {
							if ( $key === $active_tab_wprecaptcha ) {
								settings_fields( current( $wpreg_and_sec_arr ) );
								do_settings_sections( end( $wpreg_and_sec_arr ) );
							}
						}
					}

					// Shortcode tab
					if ( 'screcaptcha' === $active_tab ) {
						settings_fields( 'ka_grc_captcha_shortcode' );
						do_settings_sections( 'ka_grc_recaptcha_shortcode' );
					}

					// Checkout Rate Limiter tab
					if ( 'checkout_rate_limit' === $active_tab ) {
						$active_subtab = $active_subtab_rate;
						echo '<ul class="subsubsub" style="float: none!important;">';
						echo '<li><a href="' . esc_url( wp_nonce_url( '?page=ka_captcha&tab=checkout_rate_limit&subtab_rate=rate_limiter', 'ka_grc_url_nonce' ) ) . '" class="' . ( 'rate_limiter' === $active_subtab ? 'current' : '' ) . '">' . esc_html__( 'Checkout Rate Limiter', 'recaptcha_verification' ) . '</a> |</li>';
						echo '<li><a href="' . esc_url( wp_nonce_url( '?page=ka_captcha&tab=checkout_rate_limit&subtab_rate=rate_log', 'ka_grc_url_nonce' ) ) . '" class="' . ( 'rate_log' === $active_subtab ? 'current' : '' ) . '">' . esc_html__( 'Checkout Rate Log', 'recaptcha_verification' ) . '</a></li>';
						echo '</ul>';

						if ( 'rate_limiter' === $active_subtab ) {
							settings_fields( 'ka_grc_checkout_rate' );
							do_settings_sections( 'ka_grc_checkout_rate_limit' );
						} elseif ( 'rate_log' === $active_subtab ) {
							$this->ka_display_rate_limit_logs();
						}
					}
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		public function ka_display_rate_limit_logs() {
			$logs = get_option( 'ka_grc_rate_limit_log', array() );
			echo '<div class="wrap"><h1>' . esc_html__( 'Rate Limit Logs', 'recaptcha_verification' ) . '</h1>';
			if ( empty( $logs ) ) {
				echo '<p>' . esc_html__( 'No logs available.', 'recaptcha_verification' ) . '</p>';
				return;
			}
			$table = new KA_Rate_Limit_Logs_Table( $logs );
			$table->prepare_items();
			$table->display();
			echo '</div>';
		}

		public function ka_add_register_fields() {
			include KA_PLUGIN_DIR . '/includes/settings/general-settings-bckend.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-registration-page-settings.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-product-review-settings.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-login-page-settings.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-lost-password-setting-captcha.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-checkout-page.php';
			include KA_PLUGIN_DIR . '/includes/settings/wp-login-captcha-setting.php';
			include KA_PLUGIN_DIR . '/includes/settings/wp-registration-captcha-setting.php';
			include KA_PLUGIN_DIR . '/includes/settings/wp-comment-captcha-setting.php';
			include KA_PLUGIN_DIR . '/includes/settings/wp-lost-password-page-captcha-settings.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-payment-method-captcha-settings.php';
			include KA_PLUGIN_DIR . '/includes/settings/woo-pay-for-order-captcha.php';
			include KA_PLUGIN_DIR . '/includes/settings/ka-grc-shortcode-captcha.php';
			include KA_PLUGIN_DIR . '/includes/settings/kr-grc-checkout-rate-limit.php';
		}

		public function ka_grc_chekout_log_delete() {
			if ( isset( $_GET['action'], $_GET['log_id'], $_GET['_wpnonce'] ) && 'delete_log' === $_GET['action'] ) {
				$log_id = absint( $_GET['log_id'] );
				$nonce = sanitize_text_field( $_GET['_wpnonce'] );

				if ( wp_verify_nonce( $nonce, 'ka_grc_url_nonce' ) ) {
					$logs = get_option( 'ka_grc_rate_limit_log', array() );
					foreach ( $logs as $key => $log ) {
						if ( isset( $log['id'] ) && $log['id'] === $log_id ) {
							unset( $logs[ $key ] );
							break;
						}
					}
					update_option( 'ka_grc_rate_limit_log', array_values( $logs ) );
					wp_safe_redirect( remove_query_arg( array( 'action', 'log_id', '_wpnonce' ) ) );
					exit;
				} else {
					wp_die( esc_html__( 'Invalid nonce specified.', 'recaptcha_verification' ) );
				}
			}
		}
	}
	new Admin_Ka_Add_Recaptcha();
}

