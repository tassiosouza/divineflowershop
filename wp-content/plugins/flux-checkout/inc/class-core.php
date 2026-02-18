<?php
/**
 * Iconic_Flux_Core.
 *
 * The main jumping off point for the plugin.
 *
 * @package Iconic_Flux
 */

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Core.
 *
 * @class    Iconic_Flux_Core.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Core {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ), 0 );
		add_action( 'init', array( __CLASS__, 'checkout_load_plugin_textdomain' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_optimize_for_digital' ) );
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'wp', array( __CLASS__, 'wp' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'remove_checkout_shipping' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'show_admin_bar' ) );
		add_filter( 'template_include', array( __CLASS__, 'include_template' ), 100 );
		add_filter( 'woocommerce_update_order_review_fragments', array( __CLASS__, 'override_empty_cart_fragment' ) );
		add_action( 'woocommerce_checkout_before_customer_details', array( __CLASS__, 'express_checkout_button_wrap' ) );
		add_filter( 'woocommerce_checkout_posted_data', array( __CLASS__, 'swap_shipping_billing_data' ) );
	}

	/**
	 * Init.
	 *
	 * Hooks that must run after plugins loaded.
	 *
	 * @return void
	 */
	public static function plugins_loaded() {
		// Ajax.
		Iconic_Flux_Ajax::run();

		// Compatibility.
		Iconic_Flux_Compat_Astra::run();
		Iconic_Flux_Compat_Avada::run();
		Iconic_Flux_Compat_Flatsome::run();
		Iconic_Flux_Compat_Breakdance::run();
		Iconic_Flux_Compat_Germanized::run();
		Iconic_Flux_Compat_Martfury::run();
		Iconic_Flux_Compat_Neve::run();
		Iconic_Flux_Compat_Sales_Booster::run();
		Iconic_Flux_Compat_Sendcloud::run(); // @todo: BLOCKED Cannot test yet, need live environment for connection.
		Iconic_Flux_Compat_Shopkeeper::run();
		Iconic_Flux_Compat_Shoptimizer::run(); // @todo: Needs checkout-bar removing.
		Iconic_Flux_Compat_Siteground::run(); // @todo: BLOCKED Cannot test yet, needs to be on siteground server.
		Iconic_Flux_Compat_Social_Login::run();
		Iconic_Flux_Compat_Tokoo::run(); // @todo: BLOCKED Cannot test yet, do not current have this theme.
		Iconic_Flux_Compat_Virtue::run();
		Iconic_Flux_Compat_Woodmart::run(); // @todo: Needs search-full-screen and core-message removing.
		Iconic_Flux_Compat_Delivery_Slots::run();
		Iconic_Flux_Compat_Advanced_Nocaptcha::run();
		Iconic_Flux_Compat_Divi::run();
		Iconic_Flux_Compat_Salient::run();
		Iconic_Flux_Compat_Gift_Vouchers_Codemenschen::run();
		Iconic_Flux_Compat_Checkout_Field_Editor_For_WooCommerce::run();
		Iconic_Flux_Compat_Auros::run();
		Iconic_Flux_Compat_Fastcart::run();
		Iconic_Flux_Compat_Kadence::run();
		Iconic_Flux_Compat_Blocksy::run();
		Iconic_Flux_Compat_WooCommerce_Subscriptions::run();
		Iconic_Flux_Compat_Force_Sells::run();
		Iconic_Flux_Compat_Sala::run();
		Iconic_Flux_Compat_Stripe_Express_Checkout::run();
		Iconic_Flux_Compat_Pymntpl_Paypal_Woocommerce::run();
		Iconic_Flux_Compat_Mailchimp::run();
		Iconic_Flux_Checkout_Elements::run();
		Iconic_Flux_Compat_Elementor::run();
		Iconic_Flux_Checkout_Patterns::run();
		Iconic_Flux_Compat_Checkout_Block::run();
		Iconic_Flux_Compat_Smart_Home::run();
		Iconic_Flux_Compat_Woo_Payments::run();
		Iconic_Flux_Compat_Bricks::run();
		Iconic_Flux_Compat_Beaver_Builder::run();
		Iconic_Flux_Compat_Visual_Composer::run();
		Iconic_Flux_Compat_Revolut::run();
		Iconic_Flux_Compat_Stellarpay::run();
		Iconic_Flux_Compat_AuthorizeNet::run();
		Iconic_Flux_Compat_Bacs::run();
		Iconic_Flux_Compat_Tiered_Pricing::run();

		// Includes.
		Iconic_Flux_Thankyou::run();
		Iconic_Flux_Order::run();
		Iconic_Flux_Coupon::run();

		// Hooks.
		add_filter( 'woocommerce_update_order_review_fragments', array( __CLASS__, 'update_order_review_framents' ) );
		add_filter( 'body_class', array( __CLASS__, 'update_body_class' ) );

		// Add street number.
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'maybe_add_billing_street_number_field' ), 10 );
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'maybe_add_shipping_street_number_field' ), 10 );

		// Set priorities.
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'custom_override_checkout_fields' ), 100 );
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'custom_override_billing_field_priorities' ), 100 );
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'custom_override_shipping_field_priorities' ), 100 );

		// Additonal JS Patterns.
		add_filter( 'woocommerce_form_field_args', array( __CLASS__, 'field_args' ), 10, 3 );

		// Remove placeholders.
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'custom_override_default_fields' ) );
		add_filter( 'woocommerce_get_country_locale_base', array( __CLASS__, 'remove_empty_placeholders' ), 100 );
		add_filter( 'woocommerce_form_field', array( __CLASS__, 'remove_empty_placeholders_html' ), 10, 4 );
		add_filter( 'woocommerce_form_field_text', array( __CLASS__, 'modify_form_field_replace_placeholder' ) );
		add_filter( 'woocommerce_form_field_tel', array( __CLASS__, 'modify_form_field_replace_placeholder' ) );
		add_filter( 'woocommerce_form_field_email', array( __CLASS__, 'modify_form_field_replace_placeholder' ) );

		// Locate template.
		add_filter( 'woocommerce_locate_template', array( __CLASS__, 'woocommerce_locate_template' ), 100, 3 );

		// On save.
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'prepend_street_number_to_address_1' ), 10, 2 );

		// Unhook Default Coupon Form.
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		// Add inline errors.
		add_filter( 'woocommerce_form_field', array( __CLASS__, 'render_inline_errors' ), 10, 5 );

		// Apply coupon via URL param.
		add_action( 'template_redirect', array( __CLASS__, 'apply_coupon_via_url' ) );

		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'replace_phone_number_on_submit' ), 10, 3 );

		// Do Sidebar.
		Iconic_Flux_Sidebar::run();
	}

	/**
	 * WP Hook.
	 *
	 * Earliest we can check if it's the checkout page.
	 *
	 * @return void
	 */
	public static function wp() {
		if ( ! self::is_flux_template() ) {
			return;
		}

		// Better x-theme compatibility.
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	}

	/**
	 * Maybe optimize for digital products.
	 *
	 * @return void
	 */
	public static function maybe_optimize_for_digital() {
		if ( empty( Iconic_Flux_Core_Settings::$settings['general_general_optimize_digital'] ) ) {
			return;
		}

		add_filter( 'flux_custom_steps', array( __CLASS__, 'disable_address_step' ) );
		add_filter( 'flux_checkout_details_fields', array( __CLASS__, 'move_address_fields_to_step_1' ) );
	}

	/**
	 * Remove checkout shipping fields as we add them ourselves.
	 */
	public static function remove_checkout_shipping() {
		remove_action( 'woocommerce_checkout_shipping', array( WC_Checkout::instance(), 'checkout_form_shipping' ) );
	}

	/**
	 * Hide admin bar when flux is active.
	 *
	 * @param bool $show_admin_bar Show admin bar.
	 *
	 * @return bool
	 */
	public static function show_admin_bar( $show_admin_bar ) {
		return $show_admin_bar && ! ( self::is_flux_template() || self::is_thankyou_page() );
	}

	/**
	 * Include Template.
	 *
	 * @param string $template Template Path.
	 *
	 * @return string
	 */
	public static function include_template( $template ) {
		if ( ! self::is_flux_template() ) {
			return $template;
		}

		$theme = self::get_theme();

		remove_action( 'woocommerce_before_shop_loop', 'wc_print_notices', 10 );
		remove_action( 'woocommerce_before_single_product', 'wc_print_notices', 10 );

		define( 'IS_FLUX_CHECKOUT', true );

		global $flux_shipping_prefix;
		$flux_shipping_prefix = '';

		return self::plugin_path() . '/templates/template-' . $theme . '.php';
	}

	/**
	 * Is Desktop.
	 *
	 * Check to see if this we are in desktop mode.
	 *
	 * @return boolean
	 */
	public static function is_desktop() {
		return ! wp_is_mobile();
	}

	/**
	 * Is this the checkout page?
	 *
	 * Must run on `wp` hook at the earliest.
	 *
	 * @param bool $force_early Force early check by getting the post ID from the URL.
	 *
	 * @return bool
	 */
	public static function is_checkout( $force_early = false ) {
		if ( $force_early ) {
			$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$page_id     = url_to_postid( home_url( $request_uri ) );

			return wc_get_page_id( 'checkout' ) === $page_id;
		}

		if ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) {
			return false;
		}

		if ( is_checkout() ) {
			return true;
		}

		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'update_order_review' === $wc_ajax ) {
			return true;
		}

		$queried_object = get_queried_object();

		if ( empty( $queried_object ) || ! isset( $queried_object->ID ) ) {
			return false;
		}

		$checkout_page_id = wc_get_page_id( 'checkout' );

		if ( empty( $queried_object ) || ! method_exists( $queried_object, 'is_main_query' ) ) {
			return false;
		}

		return $checkout_page_id === $queried_object->ID && $queried_object->is_main_query();
	}

	/**
	 * Check if current page is a Thank you page.
	 *
	 * @return bool
	 */
	public static function is_thankyou_page() {
		$force = filter_input( INPUT_GET, 'flux_force_ty', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( '1' !== $force && empty( Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] ) ) {
			return false;
		}

		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Maybe add billing street number field.
	 *
	 * @param array $fields Checkout Fields.
	 *
	 * @return array
	 */
	public static function maybe_add_billing_street_number_field( $fields ) {
		$settings = Iconic_Flux_Core_Settings::$settings;

		if ( ! $settings['general_general_separate_street_number_field'] ) {
			return $fields;
		}

		$fields['billing_street_number'] = self::get_street_number_field();
		return $fields;
	}

	/**
	 * Maybe add shipping street number field.
	 *
	 * @param array $fields Checkout Fields.
	 *
	 * @return array
	 */
	public static function maybe_add_shipping_street_number_field( $fields ) {
		$settings = Iconic_Flux_Core_Settings::$settings;

		if ( ! $settings['general_general_use_autocomplete'] || ! $settings['general_general_separate_street_number_field'] ) {
			return $fields;
		}

		$fields['shipping_street_number'] = self::get_street_number_field();
		return $fields;
	}

	/**
	 * Get street number field.
	 *
	 * @return array
	 */
	public static function get_street_number_field() {
		return array(
			'type'     => 'text',
			'label'    => esc_html__( 'House Number/Name', 'flux-checkout' ),
			'required' => true,
			'class'    => array( 'required' ),
		);
	}

	/**
	 * Disable the address step.
	 *
	 * @param array $steps Checkout Fields.
	 *
	 * @return array
	 */
	public static function disable_address_step( $steps ) {
		if ( ! self::is_virtual_only_cart() ) {
			return $steps;
		}

		unset( $steps[1] );

		return array_values( $steps );
	}

	/**
	 * Move address fields to step 1.
	 *
	 * @param array $details_fields Fields.
	 *
	 * @return array
	 */
	public static function move_address_fields_to_step_1( $details_fields ) {
		if ( ! self::is_virtual_only_cart() ) {
			return $details_fields;
		}

		return array_merge( $details_fields, array( 'billing_state', 'billing_postcode', 'billing_country' ) );
	}

	/**
	 * Override checkout fields.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function custom_override_checkout_fields( $fields ) {
		// Company Field.
		$company_field_status = self::company_field_status();

		if ( 'hidden' === $company_field_status ) {
			unset( $fields['billing']['billing_company'] );
		} elseif ( 'required' === $company_field_status ) {
			$fields['billing']['billing_company']['required'] = true;
		} else {
			$fields['billing']['billing_company']['required'] = false;
		}

		// Phone Field.
		$phone_field_status = self::phone_field_status();

		if ( 'hidden' === $phone_field_status ) {
			unset( $fields['billing']['billing_phone'] );
		} elseif ( 'required' === $phone_field_status ) {
			$fields['billing']['billing_phone']['required'] = true;
		} else {
			$fields['billing']['billing_phone']['required'] = false;
		}

		// Set labels.
		if ( empty( $fields['billing']['billing_address_2']['label'] ) ) {
			$fields['billing']['billing_address_2']['label'] = __( 'Apartment, suite, unit etc.', 'flux-checkout' );
		}

		if ( empty( $fields['shipping']['shipping_address_2']['label'] ) ) {
			$fields['shipping']['shipping_address_2']['label'] = __( 'Apartment, suite, unit etc.', 'flux-checkout' );
		}

		$remove_placeholder = array( 'address_1', 'address_2', 'state', 'country', 'city', 'postcode', 'first_name', 'last_name', 'username', 'password' );

		foreach ( $remove_placeholder as $field_name ) {
			if ( isset( $fields['billing'][ 'billing_' . $field_name ] ) ) {
				$fields['billing'][ 'billing_' . $field_name ]['placeholder'] = '';
			}

			if ( isset( $fields['shipping'][ 'shipping_' . $field_name ] ) ) {
				$fields['shipping'][ 'shipping_' . $field_name ]['placeholder'] = '';
			}

			if ( isset( $fields['account'][ 'account_' . $field_name ] ) ) {
				$fields['account'][ 'account_' . $field_name ]['placeholder'] = '';
			}
		}

		$fields['billing']['billing_first_name']['class'][]    = 'required';
		$fields['billing']['billing_last_name']['class'][]     = 'required';
		$fields['shipping']['shipping_first_name']['required'] = true;
		$fields['shipping']['shipping_last_name']['required']  = true;
		$fields['shipping']['shipping_first_name']['class'][]  = 'required';
		$fields['shipping']['shipping_last_name']['class'][]   = 'required';

		if ( isset( Iconic_Flux_Core_Settings::$settings['general_general_international_phone'] ) && '1' === Iconic_Flux_Core_Settings::$settings['general_general_international_phone'] && isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['class'][] = 'flux-intl-phone';
		}

		unset( $fields['billing']['billing_address_2']['label_class'] );
		unset( $fields['shipping']['shipping_address_2']['label_class'] );

		if ( ! empty( Iconic_Flux_Core_Settings::$settings['general_general_optimize_digital'] ) ) {
			if ( self::is_virtual_only_cart() ) {
				unset( $fields['billing']['billing_company'] );
				unset( $fields['billing']['billing_address_1'] );
				unset( $fields['billing']['billing_address_2'] );
				unset( $fields['billing']['billing_city'] );
				unset( $fields['billing']['billing_phone'] );
				unset( $fields['order']['order_comments'] );

				// Remove the first class from the state field.
				if ( isset( $fields['billing']['billing_state'] ) && isset( $fields['billing']['billing_state']['class'] ) ) {
					$search = array_search( 'form-row-first', $fields['billing']['billing_state']['class'] ); // get the key of the value to be removed

					if ( false !== $search ) {
						unset( $fields['billing']['billing_state']['class'][ $search ] ); // remove the item from the array using its key
					}
				}

				// Remove the last class from the postcode field.
				if ( isset( $fields['billing']['billing_postcode'] ) && isset( $fields['billing']['billing_postcode']['class'] ) ) {
					$search = array_search( 'form-row-last', $fields['billing']['billing_postcode']['class'] ); // get the key of the value to be removed

					if ( false !== $search ) {
						unset( $fields['billing']['billing_postcode']['class'][ $search ] ); // remove the item from the array using its key
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Check if the cart only contains virtual products.
	 *
	 * @return bool
	 */
	public static function is_virtual_only_cart() {
		static $only_virtual = null;

		if ( null !== $only_virtual ) {
			return $only_virtual;
		}

		if ( empty( WC()->cart ) ) {
			return false;
		}

		$only_virtual = true;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			// Check if there are non-virtual products.
			if ( ! $cart_item['data']->is_virtual() ) {
				$only_virtual = false;
				break;
			}
		}

		return $only_virtual;
	}

	/**
	 * Override billing field priorities.
	 *
	 * We override the priorities in `woocommerce_billing_fields` instead of
	 * `flux_custom_override_checkout_fields` because plugins such as
	 * Checkout Field Editor for WooCommerce by ThemeHigh get the defaults
	 * from the earlier hook.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function custom_override_billing_field_priorities( $fields ) {
		self::set_field_priority( $fields, 'billing_email', 5 );
		self::set_field_priority( $fields, 'billing_first_name', 10 );
		self::set_field_priority( $fields, 'billing_last_name', 20 );
		self::set_field_priority( $fields, 'billing_company', 30 );
		self::set_field_priority( $fields, 'billing_country', 50 );
		self::set_field_priority( $fields, 'billing_street_number', 55 );
		self::set_field_priority( $fields, 'billing_address_1', 60 );
		self::set_field_priority( $fields, 'billing_address_2', 70 );
		self::set_field_priority( $fields, 'billing_city', 80 );
		self::set_field_priority( $fields, 'billing_state', 90 );
		self::set_field_priority( $fields, 'billing_postcode', 100 );
		return $fields;
	}

	/**
	 * Override shipping field priorities.
	 *
	 * We override the priorities in `woocommerce_shipping_fields` instead of
	 * `flux_custom_override_checkout_fields` because plugins such as
	 * Checkout Field Editor for WooCommerce by ThemeHigh get the defaults
	 * from the earlier hook.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function custom_override_shipping_field_priorities( $fields ) {
		self::set_field_priority( $fields, 'shipping_first_name', 110 );
		self::set_field_priority( $fields, 'shipping_last_name', 120 );
		self::set_field_priority( $fields, 'shipping_company', 130 );
		self::set_field_priority( $fields, 'shipping_street_number', 135 );
		self::set_field_priority( $fields, 'shipping_address_1', 140 );
		self::set_field_priority( $fields, 'shipping_address_2', 150 );
		self::set_field_priority( $fields, 'shipping_city', 160 );
		self::set_field_priority( $fields, 'shipping_state', 170 );
		self::set_field_priority( $fields, 'shipping_postcode', 180 );
		return $fields;
	}

	/**
	 * Check if field exits, if it does then set the priority of given field.
	 *
	 * @param array  $fields_group Field group.
	 * @param string $field_id     Field ID.
	 * @param int    $priority     Priority.
	 */
	public static function set_field_priority( &$fields_group, $field_id, $priority ) {
		if ( isset( $fields_group[ $field_id ] ) ) {
			$fields_group[ $field_id ]['priority'] = $priority;
		}
	}

	/**
	 * Override default fields.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function custom_override_default_fields( $fields ) {
		$fields_to_remove_placeholder = array( 'street_number', 'address_1', 'address_2', 'state', 'country', 'postcode', 'first_name', 'last_name' );
		$fields['address_2']['label'] = __( 'Apartment, suite, unit etc.', 'flux-checkout' );

		// Otherwise remove the placeholders.
		foreach ( $fields_to_remove_placeholder as $field_name ) {
			if ( isset( $fields[ $field_name ] ) ) {
				$fields[ $field_name ]['placeholder'] = '';
			}
		}

		return $fields;
	}

	/**
	 * Field Args.
	 *
	 * @param array  $data Data.
	 * @param string $key Key.
	 * @param string $value Value.
	 *
	 * @return array
	 */
	public static function field_args( $data, $key, $value ) {
		if ( 'billing_phone' === $key ) {
			$data['custom_attributes']['pattern'] = '^(\(?\+?[0-9]*\)?)?[0-9_\- \(\)]*$';
		}

		return $data;
	}

	/**
	 * Remove empty placeholder attributes on checkout fields.
	 *
	 * @param array $locale_base Locale Base.
	 *
	 * @return array
	 */
	public static function remove_empty_placeholders( $locale_base ) {
		if ( empty( $locale_base ) || ! is_array( $locale_base ) ) {
			return $locale_base;
		}

		foreach ( $locale_base as $key => $data ) {
			if ( ! isset( $data['placeholder'] ) || ! empty( $data['placeholder'] ) ) {
				continue;
			}

			unset( $locale_base[ $key ]['placeholder'] );
		}

		return $locale_base;
	}

	/**
	 * Remove empty placeholders from the HTML.
	 *
	 * @param string $field Field.
	 * @param string $key Key.
	 * @param array  $args Args.
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public static function remove_empty_placeholders_html( $field, $key, $args, $value ) {
		if ( strpos( $field, 'placeholder=""' ) === false ) {
			return $field;
		}

		return str_replace( 'placeholder=""', '', $field );
	}

	/**
	 * Modify form field HTML.
	 *
	 * @param string $field Field.
	 * @param string $key Key.
	 * @param array  $args Args.
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public static function modify_form_field_html( $field, $key, $args, $value ) {
		$field_required = __( 'This field is required', 'flux-checkout' );
		$valid_number   = __( 'Please enter a valid phone number', 'flux-checkout' );

		if ( 'billing_phone' === $key || 'shipping_phone' === $key ) {
			return str_replace( '</p>', "<span class=\".error\">$valid_number</span></p>", $field );
		}

		if ( 'billing_first_name' === $key || 'billing_last_name' === $key || 'billing_email' === $key ) {
			return str_replace( '</p>', "<span class=\".error\">$field_required</span></p>", $field );
		}

		return $field;
	}

	/**
	 * Modify Form Field Replace Placeholder.
	 *
	 * @param string $field Field.
	 *
	 * @return string
	 */
	public static function modify_form_field_replace_placeholder( $field ) {
		$field = str_replace( 'placeholder=""', '', $field );
		return str_replace( 'placeholder ', '', $field );
	}

	/**
	 * Gets the absolute path to this plugin directory.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( ICONIC_FLUX_PATH );
	}

	/**
	 * Prepend street number to billing and shipping address_1 field when order is created.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Posted Data.
	 *
	 * @return void
	 */
	public static function prepend_street_number_to_address_1( $order, $data ) {
		$current_billing_address = $order->get_billing_address_1();
		$billing_street_no       = isset( $data['billing_street_number'] ) ? $data['billing_street_number'] : '';

		if ( $billing_street_no ) {
			$new_billing_address = sprintf( '%s, %s', $billing_street_no, $current_billing_address );

			/**
			 * Filter checkout billing address 1 before creating an order.
			 *
			 * @param string   $new_billing_address     New billing address.
			 * @param string   $current_billing_address Current billing address.
			 * @param string   $billing_street_no       Billing street number.
			 * @param WC_Order $order                   Order object.
			 *
			 * @return string
			 *
			 * @since 2.0.0
			 */
			$new_billing_address = apply_filters( 'checkout_billing_address_1_before_create_order', $new_billing_address, $current_billing_address, $billing_street_no, $order );
			$order->set_billing_address_1( $new_billing_address );
		}

		$current_shipping_address = $order->get_shipping_address_1();
		$shipping_street_no       = isset( $data['shipping_street_number'] ) ? $data['shipping_street_number'] : '';

		if ( $shipping_street_no ) {
			$new_shipping_address = sprintf( '%s, %s', $shipping_street_no, $current_shipping_address );

			/**
			 * Filter checkout shipping address 1 before creating an order.
			 *
			 * @param string   $new_shipping_address     New shipping address.
			 * @param string   $current_shipping_address Current shipping address.
			 * @param string   $shipping_street_no       Shipping street number.
			 * @param WC_Order $order                    Order object.
			 *
			 * @return string
			 *
			 * @since 2.0.0
			 */
			$new_shipping_address = apply_filters( 'checkout_shipping_address_1_before_create_order', $new_shipping_address, $current_shipping_address, $shipping_street_no, $order );
			$order->set_shipping_address_1( $new_shipping_address );
		}
	}

	/**
	 * Set plugin textdomain.
	 */
	public static function checkout_load_plugin_textdomain() {
		load_plugin_textdomain( 'flux-checkout', false, basename( dirname( ICONIC_FLUX_FILE ) ) . '/languages/' );
	}

	/**
	 * Render Inline Errors
	 *
	 * @param string $field Field.
	 * @param string $key Key.
	 * @param array  $args Arguments.
	 * @param string $value Value.
	 * @param string $country Country.
	 *
	 * @return string
	 */
	public static function render_inline_errors( $field = '', $key = '', $args = array(), $value = '', $country = '' ) {
		$allowed_ajax_actions = array( 'flux_check_for_inline_errors', 'flux_check_for_inline_error' );

		if ( ! self::is_flux_template() && ! Iconic_Flux_Helpers::is_ajax_action( $allowed_ajax_actions ) ) {
			return $field;
		}

		$called_inline = false;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $key ) ) {
			$called_inline = true;
		}

		// If we are doing AJAX, get the parameters from POST request.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! $called_inline ) {
			$key     = filter_input( INPUT_POST, 'key', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$args    = filter_input( INPUT_POST, 'args', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$value   = filter_input( INPUT_POST, 'value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$country = filter_input( INPUT_POST, 'country', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}

		$message        = '';
		$message_type   = 'error';
		$global_message = false;
		$custom         = false;

		if ( (bool) $args['required'] ) {
			/* translators: %s: field name */
			$message = sprintf( __( '%s is a required field.', 'flux-checkout' ), esc_html( $args['label'] ) );
			/**
			 * Filters the required field error message.
			 *
			 * @filter flux_required_field_error_msg
			 * @param string $message Message.
			 * @param string $key     Key.
			 * @param array  $args    Arguments.
			 * 
			 * @return string
			 *
			 * @since 2.3.3
			 */
			$message = apply_filters( 'flux_required_field_error_msg', $message, $key, $args );
		}

		if ( (bool) $args['required'] && $value ) {

			if ( 'country' === $args['type'] && property_exists( WC()->countries, 'country_exists' ) && WC()->countries && ! WC()->countries->country_exists( $value ) ) {
				/* translators: ISO 3166-1 alpha-2 country code */
				$message = sprintf( __( "'%s' is not a valid country code.", 'flux-checkout' ), esc_html( $args['label'] ) );
				$custom  = true;
			}

			if ( 'postcode' === $args['type'] && ! WC_Validation::is_postcode( $value, $country ) ) {
				switch ( $country ) {
					case 'IE':
						/* translators: %1$s: field name, %2$s finder.eircode.ie URL */
						$message = sprintf( __( '%1$s is not valid. You can look up the correct Eircode <a target="_blank" href="%2$s">here</a>.', 'flux-checkout' ), esc_html( $args['label'] ), 'https://finder.eircode.ie' );
						$custom  = true;
						break;
					default:
						/* translators: %s: field name */
						$message = sprintf( __( '%s is not a valid postcode / ZIP.', 'flux-checkout' ), esc_html( $args['label'] ) );
						$custom  = true;
						break;
				}
			}

			if ( 'phone' === $args['type'] && ! WC_Validation::is_phone( $value ) ) {
				// Translators: Phone.
				$message = sprintf( __( '%s is not a valid phone number.', 'flux-checkout' ), esc_html( $args['label'] ) );
			}

			if ( 'email' === $args['type'] && ! is_email( $value ) ) {
				// Translators: Email.
				$message = sprintf( __( '%s is not a valid email address.', 'flux-checkout' ), esc_html( $args['label'] ) );
			}

			if ( 'email' === $args['type'] && ! is_user_logged_in() && email_exists( $value ) ) {
				/**
				 * Filter text displayed during registration when an email already exists.
				 *
				 * @param string $email Email address.
				 *
				 * @return string
				 *
				 * @since 2.0.0
				 */
				$message      = apply_filters( 'flux_woocommerce_registration_error_email_exists', sprintf( __( 'An account is already registered with this email address. <a href="#" data-login>Would you like to log in</a>?', 'flux-checkout' ), '' ) );
				$message_type = 'info';
			}
		}

		/**
		 * Filters the Inline Error Message.
		 *
		 * @param string $message Message.
		 * @param string $field   Field.
		 * @param string $key     Key.
		 * @param array  $args    Arguments.
		 * @param string $value   Value.
		 * @param string $country Country.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		$message = apply_filters( 'flux_custom_inline_message', $message, $field, $key, $args, $value, $country );

		/**
		 * Filters the Global Error Message.
		 *
		 * @param string|false $message Message.
		 * @param string $field   Field.
		 * @param string $key     Key.
		 * @param array  $args    Arguments.
		 * @param string $value   Value.
		 * @param string $country Country.
		 *
		 * @return string|false
		 *
		 * @since 2.0.0
		 */
		$global_message = apply_filters( 'flux_custom_global_message', $global_message, $field, $key, $args, $value, $country );

		// If we are doing AJAX, just return the message.
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && in_array( $action, array( 'flux_check_for_inline_error', 'flux_check_for_inline_errors' ), true ) ) {

			$response = array(
				'message'       => $message,
				'isCustom'      => $custom,
				'globalMessage' => $global_message,
				'globalData'    => array( 'data-flux-error' => 1 ),
				'messageType'   => $message_type,
			);

			if ( $called_inline ) {
				return $response;
			}

			wp_send_json_success( $response );
			exit;
		}

		$data_attributes  = '<p ';
		$data_attributes .= sprintf( 'data-type="%s"', esc_attr( $args['type'] ) ) . ' ';
		$data_attributes .= sprintf( 'data-label="%s"', esc_attr( $args['label'] ) ) . ' ';

		if ( strpos( $field, '</p>' ) !== false ) {
			$error  = '<span class="error">';
			$error .= $message;
			$error .= '</span>';
			$field  = substr_replace( $field, $error, strpos( $field, '</p>' ), 0 ); // Add before closing paragraph tag.
			$field  = substr_replace( $field, $data_attributes, strpos( $field, '<p>' ), 2 ); // Add to opening paragraph tag.
		}

		return $field;
	}

	/**
	 * Get theme.
	 *
	 * @return string
	 */
	public static function get_theme() {
		$settings = Iconic_Flux_Core_Settings::$settings;
		return isset( $settings['styles_theme_choose_theme'] ) && ! empty( $settings['styles_theme_choose_theme'] ) ? $settings['styles_theme_choose_theme'] : 'classic';
	}

	/**
	 * Update order review fragments.
	 *
	 * @param array $fragments Framents.
	 *
	 * @return array
	 */
	public static function update_order_review_framents( $fragments ) {
		$fragments['.flux-review-customer'] = Iconic_Flux_Steps::get_review_customer_fragment();

		// Heading with cart item count.
		ob_start();
		wc_get_template( 'checkout/cart-heading.php' );
		$fragments['.flux-heading--order-review'] = ob_get_clean();

		$new_fragments = array(
			'total'        => WC()->cart->get_total(),
			'shipping_row' => Iconic_Flux_Steps::get_shipping_row_mobile(),
		);

		if ( isset( $fragments['flux'] ) ) {
			$fragments['flux'] = array_merge( $fragments['flux'], $new_fragments );
		} else {
			$fragments['flux'] = $new_fragments;
		}

		return $fragments;
	}

	/**
	 * Add additional classes to tbe body tag on checkout page.
	 *
	 * @param array $classes Classes.
	 *
	 * @return array
	 */
	public static function update_body_class( $classes ) {
		if ( ! self::is_flux_template() ) {
			return $classes;
		}

		if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
			$classes[] = 'flux-wc-allow-login';
		}

		return $classes;
	}

	/**
	 * Locate templates.
	 *
	 * @param string $template      Template.
	 * @param string $template_name Template Name.
	 * @param string $template_path Template Path.
	 *
	 * @return mixed|string
	 */
	public static function woocommerce_locate_template( $template, $template_name, $template_path ) {
		if ( ! self::is_flux_template() ) {
			return $template;
		}

		/**
		 * Match any templates relating to the checkout, including those from Flux itself.
		 *
		 * If the template contains one of these strings, continue through this function, so we can
		 * either change it to a Flux template, or revert it back to the WooCommerce path.
		 *
		 * We don't want the theme to load any of these templates, as they are all handled by Flux.
		 *
		 * @param array  $templates     Templates.
		 * @param string $template      Template.
		 * @param string $template_name Template name.
		 * @param string $template_path Template path.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		$reset_templates_src = apply_filters(
			'flux_match_checkout_template_sources',
			array(
				'woocommerce/checkout', // Catches any file in the woocommerce/checkout override folder.
				'global/quantity-input.php',
				'templates/checkout/',
				'common/checkout/',
				'notices/',
				'cart/cart-item-data.php',
			),
			$template,
			$template_name,
			$template_path
		);

		if ( ! empty( $reset_templates_src ) ) {
			$reset_template_src_matched = false;

			foreach ( $reset_templates_src as $reset_template_src ) {
				if ( strpos( strtolower( $template ), $reset_template_src ) ) {
					$reset_template_src_matched = true;

					break;
				}
			}

			if ( ! $reset_template_src_matched ) {
				return $template;
			}
		}

		/**
		 * Filter $template_name's which *are* allowed to be overridden by theme.
		 *
		 * @param array  $templates     Array of template names.
		 * @param string $template      Template.
		 * @param string $template_name Template name.
		 * @param string $template_path Template path.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		$allowed_templates = apply_filters(
			'flux_allowed_template_overrides',
			array(),
			$template,
			$template_name,
			$template_path
		);

		if ( in_array( $template_name, $allowed_templates, true ) ) {
			return $template;
		}

		// Get the Flux theme.
		$theme = self::get_theme();

		$plugin_path        = self::plugin_path() . '/woocommerce/' . $theme . '/'; // Flux theme folder.
		$plugin_path_common = self::plugin_path() . '/woocommerce/common/';

		$flux_template = '';

		// Search the Flux theme and common folders for the template.
		if ( file_exists( $plugin_path . $template_name ) ) {
			$flux_template = $plugin_path . $template_name;
		} elseif ( file_exists( $plugin_path_common . $template_name ) ) {
			$flux_template = $plugin_path_common . $template_name;
		}

		// If this template exists in Flux, use it.
		if ( ! empty( $flux_template ) && file_exists( $flux_template ) ) {
			return $flux_template;
		}

		// Otherwise, check in WooCommerce template folder path.
		$woo_template_path = WC()->plugin_path() . '/templates/' . $template_name;
		if ( $template_name && file_exists( $woo_template_path ) ) {
			return $woo_template_path;
		}

		// If not found anywhere else, return the original path.
		return $template;
	}

	/**
	 * Override empty cart fragment.
	 *
	 * @param array $fragments Fragments.
	 *
	 * @return array
	 */
	public static function override_empty_cart_fragment( $fragments ) {
		if ( ! WC()->cart->is_empty() || is_customize_preview() ) {
			return $fragments;
		}

		unset( $fragments['form.woocommerce-checkout'] );

		ob_start();
		include ICONIC_FLUX_PATH . 'woocommerce/common/checkout/empty-cart.php';
		$fragments['flux'] = array(
			'empty_cart' => ob_get_clean(),
		);

		return $fragments;
	}

	/**
	 * Replace phone number - use the phone number saved in the hidden field by intl-tel-input script.
	 *
	 * @param int      $order_id    Order ID.
	 * @param array    $posted_data Posted Data.
	 * @param WC_Order $order       Order.
	 *
	 * @return void
	 */
	public static function replace_phone_number_on_submit( $order_id, $posted_data, $order ) {
		$billing_phone_formated = filter_input( INPUT_POST, 'billing_phone_full_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $billing_phone_formated ) ) {
			return;
		}

		$order->set_billing_phone( $billing_phone_formated );
		$order->save();
	}

	/**
	 * Apply coupon via URL.
	 *
	 * @return void
	 */
	public static function apply_coupon_via_url() {
		$coupon = filter_input( INPUT_GET, 'coupon', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $coupon ) || ! is_checkout() ) {
			return;
		}

		if ( WC()->cart->has_discount( $coupon ) ) {
			return;
		}

		WC()->cart->add_discount( sanitize_text_field( $coupon ) );
	}

	/**
	 * Helper: Is Flux template.
	 *
	 * @return bool
	 */
	public static function is_flux_template() {
		$is_flux_template = self::is_checkout() || self::is_thankyou_page() || is_wc_endpoint_url( 'order-pay' );

		if ( $is_flux_template ) {
			/**
			 * Is flux template.
			 *
			 * @since 2.3.0
			 */
			return apply_filters( 'flux_is_flux_template', true );
		}

		if ( 
			! $is_flux_template && 
			is_wc_endpoint_url( 'order-received' ) && 
			empty( Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] )
		) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'flux_is_flux_template', false );
		}

		if ( ! is_page() ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'flux_is_flux_template', false );
		}

		$page_id      = get_queried_object_id();
		$page_content = get_post_field( 'post_content', $page_id );

		if ( ! empty( $page_content ) && has_block( 'woocommerce/checkout', $page_content ) ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'flux_is_flux_template', true );
		}

		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		return apply_filters( 'flux_is_flux_template', false );
	}

	/**
	 * Express checkout buttons wrap.
	 *
	 * @return void
	 */
	public static function express_checkout_button_wrap() {
		?>
		<div class="flux-express-checkout-wrap"></div>
		<?php
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public static function register_blocks() {
		// Register blocks in the format $dir => $render_callback.
		$blocks = array(
			'cross-sell'           => array( 'Iconic_Flux_Cross_Sell', 'render_block' ),
			'elements-placeholder' => '',
		);

		foreach ( $blocks as $dir => $render_callback ) {
			$args = array();

			if ( ! empty( $render_callback ) ) {
				$args['render_callback'] = $render_callback;
			}

			register_block_type( ICONIC_FLUX_PATH . 'blocks/build/' . $dir, $args );
		}

	}

	/**
	 * Is company field enabled.
	 * @return string 'optional', 'required' or 'hidden'
	 *
	 * @return string 'optional', 'required' or 'hidden'
	 */
	public static function company_field_status() {
		if ( ! function_exists( 'CartCheckoutUtils' ) ) {
			return get_option( 'woocommerce_checkout_company_field', 'hidden' );
		} 

		return CartCheckoutUtils::get_company_field_visibility();
	}

	/**
	 * Is phone field enabled.
	 *
	 * @return string 'optional', 'required' or 'hidden'
	 */
	public static function phone_field_status() {
		if ( ! function_exists( 'CartCheckoutUtils' ) ) {
			return get_option( 'woocommerce_checkout_phone_field', 'optional' );
		}

		return CartCheckoutUtils::get_phone_field_visibility();
	}

	/**
	 * When Shipping destination setting is 'Default to customer shipping address'
	 * then Flux shows the shipping section on top.
	 *
	 * By default, when 'Ship to same address' is selected then we Woo copies to fields of
	 * billing to shipping. This doesn't work after flux makes shipping fields the default option
	 * and moves it to the top. We need to handle it with our custom logic.
	 *
	 * @param array $data Checkout Data.
	 *
	 * @return $array
	 */
	public static function swap_shipping_billing_data( $data ) {
		$shipping = WC()->checkout->get_checkout_fields( 'shipping' );
		$billing  = WC()->checkout->get_checkout_fields( 'billing' );

		$destination = get_option( 'woocommerce_ship_to_destination' );

		// Only run this code when Shipping destination is 'Default to customer shipping address'.
		if ( 'shipping' !== $destination ) {
			return $data;
		}

		// Shipping fields data.
		foreach ( WC()->checkout->get_checkout_fields( 'shipping' ) as $key => $field ) {
			$posted_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$data[ $key ] = $posted_value;
		}

		$billing_same_address = filter_input( INPUT_POST, 'billing_same_billing_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// If 'Use same address for billing?' is checked then copy the shipping fields to billing.
		if ( $billing_same_address ) {
			foreach ( WC()->checkout->get_checkout_fields( 'billing' ) as $billing_key => $field ) {
				$posted_value = filter_input( INPUT_POST, str_replace( 'billing_', 'shipping_', $billing_key ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$shipping_key = 'shipping_' . substr( $billing_key, 8 ); // 8 = remove 'billing_' prefix.
				if ( ! isset( $data[ $shipping_key ] ) ) {
					continue;
				}

				$data[ $billing_key ] = ! empty( $posted_value ) ? $posted_value : $data[ $shipping_key ];
			}
		}

		return $data;
	}

	/**
	 * Use same billing address.
	 *
	 * @return bool
	 */
	public static function use_same_billing_address(): bool {
		return apply_filters( 'flux_same_billing_address', 1 );
	}
}
