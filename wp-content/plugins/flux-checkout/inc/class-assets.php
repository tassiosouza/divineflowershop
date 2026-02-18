<?php
/**
 * Iconic_Flux_Assets.
 *
 * Load the plugin assets.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Assets.
 *
 * Register/enqueue frontend and backend scripts.
 *
 * @class    Iconic_Flux_Assets
 * @version  1.0.0
 */
class Iconic_Flux_Assets {
	/**
	 * Run.
	 */
	public static function run() {
		$max_priority = defined( 'PHP_INT_MAX' ) ? PHP_INT_MAX : 2147483647;
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ), $max_priority );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'wpsf_before_settings_iconic_flux', array( __CLASS__, 'admin_custom_css' ) );
		add_action( 'wp_footer', array( __CLASS__, 'dequeue_core_blocks_inline_css' ) );
	}

	/**
	 * Frontend assets.
	 */
	public static function frontend_assets() {
		if ( ! defined( 'IS_FLUX_CHECKOUT' ) || ! IS_FLUX_CHECKOUT ) {
			return;
		}

		global $wp, $wp_scripts, $wp_styles;

		$settings = Iconic_Flux_Core_Settings::$settings;
		$theme    = Iconic_Flux_Core::get_theme();

		/**
		 * Choose which sources are allowed at checkout.
		 * You can pass exact source URLs or regex patterns (e.g. "/^https:\\/\\/example\\.com\\//i").
		 *
		 * @since 2.0.0
		 */
		$allowed_sources = apply_filters( 'flux_checkout_allowed_sources', array() );

		foreach ( $wp_scripts->queue as $key => $name ) {
			$src = isset( $wp_scripts->registered[ $name ]->src ) ? $wp_scripts->registered[ $name ]->src : '';
			if ( $src && ! self::is_source_allowed( $src, $allowed_sources ) && strpos( $src, '/themes/' ) ) {
				wp_dequeue_script( $name );
			}
		}

		foreach ( $wp_styles->queue as $key => $name ) {
			$src = isset( $wp_styles->registered[ $name ]->src ) ? $wp_styles->registered[ $name ]->src : '';
			// The twenty-x themes have custom CSS within woo.
			if ( $src && ! self::is_source_allowed( $src, $allowed_sources ) && ( strpos( $src, '/themes/' ) || strpos( $src, '/twenty' ) ) ) {
				wp_dequeue_style( $name );
			}
		}

		$rtl_suffix = is_rtl() ? '.rtl' : '';
		wp_enqueue_style( 'flux-checkout-theme', ICONIC_FLUX_URL . 'assets/frontend/css/templates/' . $theme . '/main' . $rtl_suffix . '.css', array(), ICONIC_FLUX_VERSION, false );
		wp_enqueue_style( 'flux-checkout-elements', ICONIC_FLUX_URL . 'assets/blocks/css/main' . $rtl_suffix . '.css', array(), ICONIC_FLUX_VERSION, false );

		if ( Iconic_Flux_Core::is_flux_template() || Iconic_Flux_Core::is_thankyou_page() ) {
			wp_add_inline_style( 'flux-checkout-theme', self::get_dynamic_styles( $settings ) );

			if ( ! Iconic_Flux_Helpers::is_modern_theme() ) {
				wp_enqueue_style( 'flux-google-font-base', '//fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap', array(), ICONIC_FLUX_VERSION, false );

				if ( $settings['styles_header_background'] && $settings['styles_header_header_font_family'] ) {
					$fonts = Iconic_Flux_Helpers::get_google_fonts();
					if ( isset( $fonts[ $settings['styles_header_header_font_family'] ] ) ) {
						wp_enqueue_style( 'flux-google-font-header', '//fonts.googleapis.com/css?family=' . esc_attr( $settings['styles_header_header_font_family'] ), array(), ICONIC_FLUX_VERSION, false );
					}
				}
			}
		}

		$deps = array(
			'jquery',
			'jquery-blockui',
			'select2',
			'wc-checkout',
			'wc-country-select',
			'wc-address-i18n',
			'wp-hooks',
			'wp-html-entities',
		);

		if ( isset( Iconic_Flux_Core_Settings::$settings['general_general_international_phone'] ) && '1' === Iconic_Flux_Core_Settings::$settings['general_general_international_phone'] ) {
			wp_enqueue_script( 'flux-internation-phone-js', ICONIC_FLUX_URL . 'assets-vendor/intl-tel-input/js/intlTelInputWithUtils.js', array(), ICONIC_FLUX_VERSION, false );
			wp_enqueue_style( 'flux-internation-phone-css', ICONIC_FLUX_URL . 'assets-vendor/intl-tel-input/css/intlTelInput.min.css', array(), ICONIC_FLUX_VERSION );
			$deps[] = 'flux-internation-phone-js';
		}

		wp_enqueue_script( 'flux-checkout', ICONIC_FLUX_URL . 'assets/frontend/js/main.js', $deps, ICONIC_FLUX_VERSION, true );

		if ( Iconic_Flux_Helpers::use_autocomplete() || Iconic_Flux_Core::is_thankyou_page() ) {
			$api_key = $settings['integrations_integrations_google_api_key'];

			wp_enqueue_script(
				'google-autocomplete',
				'//maps.googleapis.com/maps/api/js?libraries=places&loading=async&key=' . esc_attr( $api_key ) . '&callback=flux_checkout_init_address_search',
				array( 'flux-checkout' ),
				ICONIC_FLUX_VERSION,
				array(
					'in_footer' => true,
					'strategy'  => 'async',
				)
			);
		}

		/**
		 * Flux checkout script localized data.
		 *
		 * @since 2.4.0
		 */
		$flux_script_data = apply_filters(
			'flux_checkout_script_data',
			array(
				'allowed_countries'         => array_map( 'strtolower', array_keys( WC()->countries->get_allowed_countries() ) ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'is_user_logged_in'         => is_user_logged_in(),
				'localstorage_fields'       => self::get_localstorage_fields(),
				'international_phone'       => isset( $settings['general_general_international_phone'] ) ? $settings['general_general_international_phone'] : '',
				'allow_login_existing_user' => isset( $settings['general_user_existing_user'] ) ? $settings['general_user_existing_user'] : '',
				'steps'                     => Iconic_Flux_Steps::get_steps_hashes(),
				'shipping_destination'      => get_option( 'woocommerce_ship_to_destination' ),
				'i18n'                      => array(
					'error'              => __( 'Please fix all errors and try again.', 'flux-checkout' ),
					'errorAddressSearch' => __( 'Please search for an address and try again.', 'flux-checkout' ),
					'login'              => __( 'Login', 'flux-checkout' ),
					'pay'                => __( 'Pay', 'flux-checkout' ),
					'coupon_success'     => __( 'Coupon has been removed.', 'flux-checkout' ),
					'account_exists'     => __( 'An account is already registered with this email address. Would you like to log in?', 'flux-checkout' ),
					'login_successful'   => __( 'Login successful', 'flux-checkout' ),
					'error_occured'      => __( 'An error occurred', 'flux-checkout' ),
					'cross_sell_add_btn' => __( '+ Add', 'flux-checkout' ),
					'enter_location'     => __( 'Enter a location', 'flux-checkout' ),
					'phone'              => array(
						'invalid' => __( 'Please enter a valid phone number.', 'flux-checkout' ),
					),
				),
				'update_cart_nonce'         => wp_create_nonce( 'update_cart' ),
				'shop_page'                 => Iconic_Flux_Helpers::get_shop_page_url(),
				'base_country'              => WC()->countries->get_base_country(),
				'intl_util_path'            => plugins_url( 'assets-vendor/intl-tel-input/js/utils.js', ICONIC_FLUX_FILE ),
				'separate_street_number'    => isset( Iconic_Flux_Core_Settings::$settings['general_general_separate_street_number_field'] ) ? Iconic_Flux_Core_Settings::$settings['general_general_separate_street_number_field'] : false,
			)
		);

		wp_localize_script( 'flux-checkout', 'flux_checkout_vars', $flux_script_data );

		/**
		 * Modify script data.
		 *
		 * @since 2.0.0
		 */
		$params = apply_filters(
			'woocommerce_get_script_data',
			array(
				'ajax_url'                  => WC()->ajax_url(),
				'wc_ajax_url'               => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
				'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
				'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
				'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
				'checkout_url'              => WC_AJAX::get_endpoint( 'checkout' ),
				'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
				'debug_mode'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
			),
			'wc-checkout'
		);

		wp_localize_script( 'flux-checkout', 'wc_checkout_params', $params );
	}

	/**
	 * Get CSS File.
	 *
	 * @param array $settings Settings.
	 *
	 * @return string
	 */
	public static function get_css_file( $settings ) {
		$default = 'material.min.css';

		if ( 'custom' === $settings['styles_checkout_use_custom_colors'] ) {
			return $default;
		}

		$primary = $settings['styles_checkout_primary_color'];
		$accent  = $settings['styles_checkout_accent_color'];
		$colors  = Iconic_Flux_Helpers::get_classic_pallet();

		if ( $primary && $accent && $primary !== $accent ) {
			return 'material.' . $colors[ $primary ] . '-' . $colors[ $accent ] . '.min.css';
		}

		return $default;
	}

	/**
	 * Return list of fields for which data is persistently stored on the browser.
	 *
	 * @return array
	 */
	public static function get_localstorage_fields() {
		$fields = array( 'jckwds-delivery-time', 'jckwds-delivery-date', 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_company', 'billing_email', 'billing_country', 'billing_street_number', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_country', 'shipping_street_number', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode' );

		/**
		 * List of fields which are stored in the browser's local storage.
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'flux_localstorage_fields', $fields );
	}

	/**
	 * Check if a given source URL is allowed.
	 *
	 * Supports exact matches and regex patterns (e.g. "/^https:\\/\\/example\\.com\\//i").
	 *
	 * @param string $source_url       The script/style source URL.
	 * @param array  $allowed_sources  A list of allowed sources or regex patterns.
	 *
	 * @return bool
	 */
	public static function is_source_allowed( $source_url, $allowed_sources ) {
		if ( empty( $source_url ) ) {
			return false;
		}

		if ( empty( $allowed_sources ) || ! is_array( $allowed_sources ) ) {
			return false;
		}

		// Fast path: direct string match.
		if ( in_array( $source_url, $allowed_sources, true ) ) {
			return true;
		}

		foreach ( $allowed_sources as $pattern ) {
			if ( ! is_string( $pattern ) ) {
				continue;
			}

			$first_char = substr( $pattern, 0, 1 );
			$last_char  = substr( $pattern, -1 );

			// Treat strings wrapped with '/' as regex; allow modifiers at the end.
			if ( '/' === $first_char && false !== strpos( substr( $pattern, 1 ), '/' ) ) {
				// Suppress errors from invalid patterns, treat as non-match.
				$match = @preg_match( $pattern, $source_url );
				if ( 1 === $match ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Get Dynamic Styles.
	 *
	 * @param array $settings Settings.
	 *
	 * @return string
	 */
	public static function get_dynamic_styles( $settings ) {
		$theme             = Iconic_Flux_Core::get_theme();
		$use_custom_colors = $settings['styles_checkout_use_custom_colors'];
		$primary           = $settings['styles_checkout_primary_color'];
		$accent            = $settings['styles_checkout_accent_color'];
		$settings_css      = $settings['styles_checkout_custom_css'];

		if ( 'custom' === $use_custom_colors ) {
			$primary = $settings['styles_checkout_custom_primary_color'];
			$accent  = $settings['styles_checkout_custom_accent_color'];
		}

		ob_start();

		/**
		 * We are using a style sheet tag so we have nice markup,
		 * but we are not rendering it, output buffer comes after
		 * the start and before the end.
		 */
		?>
		<?php
		if ( 'classic' === $theme ) {
			?>

			<?php if ( $primary ) : ?>
				:root {
					--flux-checkout-primary-color: <?php echo esc_attr( $primary ); ?>
				}

				.flux-checkout .flux-stepper .flux-stepper__indicator,
				.flux-checkout input:checked:before,
				.flux-checkout .flux-checkout__header {
				background-color: <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout .form-row label,
				.flux-checkout .form-row label .required {
				color: <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout .form-row label abbr.required {
				border-bottom-color: <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout #order_review.woocommerce-checkout-review-order ul.woocommerce-shipping-methods input[type="radio"]:checked + label:before {
				background-color: <?php echo esc_attr( $primary ); ?>;
				border: 1px solid <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout .woocommerce-form.woocommerce-form-login input[type="radio"]:checked + label:after,
				.flux-checkout .form-row input[type="radio"]:checked + label:after,
				.flux-checkout #payment .payment_methods > li:not(.woocommerce-notice) input[type="radio"]:checked + label:after {
				background-color: <?php echo esc_attr( $primary ); ?>;
				border: 1px solid <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout .woocommerce-account-fields .form-row.create-account label.woocommerce-form__label input:checked:before,
				.flux-checkout .woocommerce-shipping-fields__wrapper #ship-to-different-address label.woocommerce-form__label input:checked:before,
				.flux-checkout .woocommerce-additional-fields__wrapper #show-additional-fields label.woocommerce-form__label input:checked:before {
				background-color: <?php echo esc_attr( $primary ); ?>;
				}

				.flux-checkout .woocommerce-account-fields .form-row.create-account label.woocommerce-form__label input:checked:after,
				.flux-checkout .woocommerce-shipping-fields__wrapper #ship-to-different-address label.woocommerce-form__label input:checked:after,
				.flux-checkout .woocommerce-additional-fields__wrapper #show-additional-fields label.woocommerce-form__label input:checked:after {
				background-color: <?php echo esc_attr( Iconic_Flux_Helpers::hex2rgba( $primary, 0.5 ) ); ?>;
				}
			<?php endif; ?>

			<?php if ( $accent ) : ?>
				.flux-checkout table.shop_table a,
				.flux-checkout table.shipping_table a,
				.flux-checkout .flux-step a,
				.flux-checkout .flux-checkout__login-button,
				.flux-checkout button[data-login-cancel],
				.flux-checkout .lost_password a,
				.flux-checkout .flux-address-button,
				.flux-checkout #enter_coupon_button,
				.flux-checkout button[name="apply_coupon"],
				.flux-checkout .flux-checkout__login-button:hover,
				.flux-checkout button[data-login-cancel]:hover,
				.flux-checkout .lost_password a:hover,
				.flux-checkout .flux-address-button:hover,
				.flux-checkout #enter_coupon_button:hover,
				.flux-checkout button[name="apply_coupon"]:hover,
				.flux-checkout #payment .payment_methods > li:not(.woocommerce-notice) input[type=radio]:first-child:checked + label:before,
				.flux-checkout #payment .payment_methods > li .payment_box a, .flux-checkout.flux-checkout--classic #payment .form-row.place-order a {
				color: <?php echo esc_attr( $accent ); ?> !important;
				}

				.flux-checkout button.woocommerce-button.button.woocommerce-form-login__submit,
				.flux-checkout .flux-button:not(.flux-button--reverse),
				.flux-checkout button#place_order.button[name="woocommerce_checkout_place_order"],
				.flux-checkout button.button[name="woocommerce_checkout_place_order"] {
				background-color: <?php echo esc_attr( $accent ); ?> !important;
				}
			<?php endif; ?>

			<?php if ( $settings['styles_header_background'] ) { ?>
				.flux-checkout .header__title {
				<?php if ( $settings['styles_header_header_font_family'] ) { ?>
					font-family: <?php echo esc_attr( str_replace( '~', "'", $settings['styles_header_header_font_family'] ) ); ?>;
				<?php } ?>
				<?php if ( $settings['styles_header_header_font_size'] ) { ?>
					font-size: <?php echo esc_attr( $settings['styles_header_header_font_size'] ); ?>;
				<?php } ?>
				color: <?php echo esc_attr( $settings['styles_header_header_font_colour'] ); ?>;
				}
			<?php } ?>

			<?php if ( $settings['styles_header_background'] ) { ?>
				.flux-checkout .flux-checkout__header {
				background: <?php echo esc_attr( $settings['styles_header_background'] ); ?>;
				background: -webkit-linear-gradient(to left, <?php echo esc_attr( $settings['styles_header_background'] ); ?>);
				background: linear-gradient(to left, <?php echo esc_attr( $settings['styles_header_background'] ); ?>);
				}
			<?php } ?>

			<?php if ( 'custom' === $settings['styles_header_header_background'] && $settings['styles_header_custom_header_color'] ) : ?>
				.flux-checkout .flux-checkout__header {
				background: <?php echo esc_attr( $settings['styles_header_custom_header_color'] ); ?>;
				}
			<?php endif; ?>

			<?php if ( 'primary-color' === $settings['styles_header_header_background'] && $primary ) : ?>
				.flux-checkout .flux-checkout__header {
				background: <?php echo esc_attr( $primary ); ?>;
				}
			<?php endif; ?>

			<?php if ( $settings['styles_header_cart_icon_color'] ) : ?>
				.flux-checkout .header__link {
				color: <?php echo esc_attr( $settings['styles_header_cart_icon_color'] ); ?>;
				}
			<?php endif; ?>
			<?php
		} elseif ( 'modern' === $theme ) {
			if ( $settings['styles_checkout_modern_custom_placeholder_color'] ) {
				?>
				.flux-checkout ::-webkit-input-placeholder {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}

				.flux-checkout ::-moz-placeholder {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}

				.flux-checkout ::-ms-input-placeholder {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}

				.flux-checkout ::placeholder {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}

				.flux-checkout :-ms-input-placeholder {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?> !important;
				}

				.flux-checkout p.form-row label:not(.checkbox) {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}

				.flux-checkout p.form-row label:not(.checkbox) abbr,
				.flux-checkout p.form-row label:not(.checkbox) span {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_placeholder_color'] ); ?>;
				}
				<?php
			}

			if ( $settings['styles_checkout_modern_custom_link_color'] ) {
				?>
				.flux-checkout a,
				.lost_password a {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				}

				.flux-checkout a:hover,
				.lost_password a:hover {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				filter: brightness( 80% );
				}

				.flux-checkout.flux-checkout--modern .flux-checkout__login-button {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				}

				.flux-checkout.flux-checkout--modern .flux-checkout__login-button:hover {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				filter: brightness( 80% );
				}

				.flux-checkout #payment .payment_methods li.wc_payment_method > input[type=radio]:checked + label:after,
				.flux-checkout input[type=radio]:checked + label:after,
				.flux-checkout input[type=radio]:checked + label:after {
				background: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				border-color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				}

				.flux-review-customer__buttons a[data-stepper-goto] {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				}

				.flux-review-customer__buttons a[data-stepper-goto]:hover {
				color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_link_color'] ); ?>;
				}
				<?php
			}

			if ( $settings['styles_checkout_modern_custom_secondary_button_color'] ) {
				?>
				.flux-checkout .button:not(.wc-forward,.woocommerce-MyAccount-downloads-file),
				.flux-checkout .button:not(.wc-forward,.woocommerce-MyAccount-downloads-file):hover {
				background-color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_secondary_button_color'] ); ?>;
				}
				<?php
			}

			if ( $settings['styles_checkout_modern_custom_primary_button_color'] ) {
				?>
				.flux-checkout .flux-button,
				.flux-checkout button#place_order,
				.flux-checkout .flux-button:hover ,
				.flux-checkout button#place_order:hover {
				background-color: <?php echo esc_attr( $settings['styles_checkout_modern_custom_primary_button_color'] ); ?>;
				}
				<?php
			}
		}

		$css  = ob_get_clean();
		$css .= $settings_css;
		$css  = wp_strip_all_tags( $css );

		return $css;
	}

	/**
	 * Admin assets.
	 */
	public static function admin_assets() {
		$rtl_suffix = is_rtl() ? '.rtl' : '';

		wp_enqueue_style( 'iconic_flux_edit_style', ICONIC_FLUX_URL . 'assets/admin/css/main' . $rtl_suffix . '.css', array(), ICONIC_FLUX_VERSION );
		wp_enqueue_style( 'flux-checkout-elements', ICONIC_FLUX_URL . 'assets/blocks/css/main' . $rtl_suffix . '.css', array(), ICONIC_FLUX_VERSION, false );

		$current_screen = get_current_screen();

		if ( 'checkout_elements' !== $current_screen->id && false === strpos( $current_screen->base, 'iconic-flux-settings' ) ) {
			return;
		}

		wp_enqueue_style( 'iconic_flux_select2', ICONIC_FLUX_URL . 'assets-vendor/select2/select2.min.css', array(), ICONIC_FLUX_VERSION );
		wp_enqueue_style( 'v-select-css', ICONIC_FLUX_URL . 'assets-vendor/vue-select/vue-select.css', array(), ICONIC_FLUX_VERSION );

		wp_enqueue_media();

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			wp_enqueue_script( 'vuejs', 'https://unpkg.com/vue@3.4.16/dist/vue.global.js', array( 'jquery' ), ICONIC_FLUX_VERSION, true );
		} else {
			wp_enqueue_script( 'vuejs', 'https://unpkg.com/vue@3.4.16/dist/vue.global.prod.js', array( 'jquery' ), ICONIC_FLUX_VERSION, true );
		}

		wp_enqueue_script( 'v-select-js', ICONIC_FLUX_URL . 'assets-vendor/vue-select/vue-select.umd.js', array( 'vuejs' ), ICONIC_FLUX_VERSION, true );
		wp_enqueue_script( 'iconic_flux_edit_script', ICONIC_FLUX_URL . 'assets/admin/js/main.js', array(), ICONIC_FLUX_VERSION, true );
		wp_enqueue_script( 'ace_editor', ICONIC_FLUX_URL . 'assets/admin/js/ace-min-noconflict/ace.js', array(), ICONIC_FLUX_VERSION, true );
		wp_enqueue_script( 'iconic_flux_select2', ICONIC_FLUX_URL . 'assets-vendor/select2/select2.min.js', array( 'jquery' ), ICONIC_FLUX_VERSION, true );
		wp_localize_script(
			'iconic_flux_edit_script',
			'iconic_flux_checkout',
			array(
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'   => wp_create_nonce( 'search-products' ),
				'search_categories_nonce' => wp_create_nonce( 'search-categories' ),
				'flux_url'                => ICONIC_FLUX_URL,
				'placeholder_img_url'     => wc_placeholder_img_src( 'woocommerce_thumbnail' ),
				'i18n'                    => array(
					'product_required'     => __( 'Please select Product(s).', 'flux-checkout' ),
					'product_cat_required' => __( 'Please select Product Category.', 'flux-checkout' ),
					'cart_total_required'  => __( 'Please enter a value.', 'flux-checkout' ),
					'user_role_required'   => __( 'Please enter a value.', 'flux-checkout' ),
				),
				'literals'                => array(
					'user_role'   => _x( 'User Role', 'checkout element', 'flux-checkout' ),
					'product'     => _x( 'Product(s)', 'checkout element', 'flux-checkout' ),
					'product_cat' => _x( 'Product Category(s)', 'checkout element', 'flux-checkout' ),
					'cart_total'  => _x( 'Cart Total', 'checkout element', 'flux-checkout' ),
					'is'          => _x( 'is', 'checkout element', 'flux-checkout' ),
					'is_not'      => _x( 'is not', 'checkout element', 'flux-checkout' ),
					'lt'          => _x( 'is less than', 'checkout element', 'flux-checkout' ),
					'lte'         => _x( 'is less than or equal to', 'checkout element', 'flux-checkout' ),
					'gt'          => _x( 'is more than', 'checkout element', 'flux-checkout' ),
					'gte'         => _x( 'is more than or equal to', 'checkout element', 'flux-checkout' ),
					'and'         => _x( 'and', 'checkout element', 'flux-checkout' ),
					'in_cart'     => _x( 'in cart:', 'checkout element', 'flux-checkout' ),
					'not_in_cart' => _x( 'not in cart:', 'checkout element', 'flux-checkout' ),
				),
			)
		);
	}

	/**
	 * Add CSS.
	 *
	 * @return void
	 */
	public static function admin_custom_css() {
		?>
		<style>
			iframe#thankyou_thankyou_content_ifr {
				min-height: 400px;
			}
		</style>
		<?php
	}

	/**
	 * Dequeue core blocks inline CSS as it causes conflicts with Flux elements.
	 *
	 * @return void
	 */
	public static function dequeue_core_blocks_inline_css() {
		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return;
		}

		wp_dequeue_style( 'core-block-supports' );
	}
}
