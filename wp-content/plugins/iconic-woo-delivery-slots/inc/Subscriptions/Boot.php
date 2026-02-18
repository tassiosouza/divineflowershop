<?php
/**
 * Subscriptions Boot.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Subscriptions\CheckoutBlock\SubscriptionCheckoutBlock;
use Iconic_WDS\Subscriptions\Base\SubscriptionsBase;
use Iconic_WDS\Api;
use Iconic_WDS\FeeManager;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;
use Iconic_WDS\Subscriptions\SubscriptionSession;
use Iconic_WDS\Subscriptions\Dto\SubscriptionOrderMetaData;
use Iconic_WDS\Subscriptions\Admin;
use Iconic_WDS\Subscriptions\Integrations\StellarPaySubscriptions;
use Iconic_WDS\Subscriptions\Integrations\WooSubscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Subscriptions Boot.
 */
class Boot {

	/**
	 * Integrations.
	 *
	 * @var array
	 */
	private static $integrations = array();

	/**
	 * Active integration.
	 *
	 * @var object
	 */
	private static ?SubscriptionsBase $active_integration = null;

	/**
	 * Checkout handler.
	 *
	 * @var CheckoutHandler
	 */
	public static ?CheckoutHandler $checkout_handler = null;

	/**
	 * Rest API.
	 *
	 * @var RestApi
	 */
	public static ?RestApi $rest_api = null;

	/**
	 * Instance.
	 *
	 * @var Boot
	 */
	public static $instance;

	/**
	 * Subscription products fee handler.
	 *
	 * @var SubscriptionProductsFee
	 */
	public static ?SubscriptionProductsFee $subscription_products_fee = null;

	/**
	 * Regular products fee handler.
	 *
	 * @var RegularProductsFee
	 */
	public static ?RegularProductsFee $regular_products_fee = null;

	/**
	 * Get instance.
	 *
	 * @return Boot
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Run the subscriptions module.
	 */
	public static function run() {
		self::$subscription_products_fee = new SubscriptionProductsFee();
		self::$regular_products_fee      = new RegularProductsFee();

		add_action( 'plugins_loaded', array( self::class, 'on_plugins_loaded' ), 10 );
	}

	/**
	 * Get subscriptions.
	 *
	 * @return array
	 */
	public static function get_subscriptions_classes() {
		/**
		 * Allow third party to add compatibility with their subscriptions products. By adding a new class to the array,
		 * you can add compatibility with your subscriptions products.
		 *
		 * The object must extend `Iconic_WDS\Subscriptions\Base\SubscriptionsBase` class and override the abstract methods.
		 *
		 * @since 2.7.0
		 *
		 * @param array $subscriptions Associative array of subscriptions classes.
		 */
		return apply_filters(
			'iconic_wds_subscriptions_objects',
			array(
				'woo-subscriptions'        => new WooSubscriptions( 'woo-subscriptions', 'Woo Subscriptions' ),
				'stellarpay-subscriptions' => new StellarPaySubscriptions( 'stellarpay', 'StellarPay' ),
			)
		);
	}

	/**
	 * Load subscription compatibility.
	 */
	public static function on_plugins_loaded() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		foreach ( self::get_subscriptions_classes() as $key => $subscription_object ) {
			self::$integrations[ $key ] = $subscription_object;
		}

		foreach ( self::$integrations as $key => $integration ) {
			if ( ! method_exists( $integration, 'is_plugin_active' ) || ! $integration->is_plugin_active() ) {
				continue;
			}

			if ( ! method_exists( $integration, 'run' ) ) {
				continue;
			}

			self::$active_integration = $integration;
			$integration->run();
			break;
		}

		if ( ! self::$active_integration ) {
			return;
		}

		// Ensure that the cart is loaded before initializing the checkout handler.
		add_action( 'wp_loaded', array( self::class, 'initialize' ), 5 );

		add_filter( 'wpsf_register_settings_jckwds', array( self::class, 'add_settings' ), 20 );
	}

	/**
	 * Initialize.
	 */
	public static function initialize() {
		if ( ! WC()->cart && self::is_woo_store_api_request() ) {
			Api::load_cart();
		}

		self::$checkout_handler = new CheckoutHandler();
		self::$rest_api         = new RestApi();
		Admin::run();

		// Add checkout field to classic checkout.
		add_action( 'woocommerce_checkout_order_review', array( self::$checkout_handler, 'add_checkout_field' ), 1 );
		// Check for past timeslots and remove them from session.
		add_action( 'wp_loaded', array( self::class, 'cleanup_past_timeslots' ), 20 );

		add_filter( 'woocommerce_get_order_item_totals', array( __CLASS__, 'add_to_order_details' ), 10, 3 );

	}

	/**
	 * Check for past timeslots in session and remove them.
	 */
	public static function cleanup_past_timeslots() {
		if ( is_admin() ) {
			return;
		}

		if ( ! is_object( WC()->session ) ) {
			return;
		}

		$session_data = SubscriptionSession::get_all_session_data();

		// Check both subscription and regular product types.
		foreach ( array( SubscriptionProductType::SUBSCRIPTION, SubscriptionProductType::REGULAR ) as $type ) {
			if ( empty( $session_data[ $type ] ) || ! is_array( $session_data[ $type ] ) ) {
				continue;
			}

			foreach ( $session_data[ $type ] as $shipping_method_id => $data ) {
				if ( empty( $data['date_ymd'] ) ) {
					continue;
				}

				$session_timeslot = SessionTimeslot::from_session_array( $data );

				if ( ! $session_timeslot ) {
					continue;
				}

				if ( $session_timeslot->is_timeslot_in_past() ) {
					SubscriptionSession::clear_type_data( $type, $shipping_method_id );
					SubscriptionSession::clear_fee_session( FeeManager::get_fee_class( $type ) );
				}
			}
		}
	}

	/**
	 * Get all integrations.
	 *
	 * @return array
	 */
	public static function get_integrations() {
		return self::$integrations;
	}

	/**
	 * Get active integration.
	 *
	 * @return object
	 */
	public static function get_active_integration() {
		return self::$active_integration;
	}

	/**
	 * Has subscription product in cart.
	 *
	 * @return bool
	 */
	public static function has_subscription_product_in_cart() {
		$active_integration = self::get_active_integration();
		if ( ! $active_integration ) {
			return false;
		}

		return self::$checkout_handler->field_handler->find_subscription_product_in_cart();
	}

	/**
	 * Is Woo batch REST API request.
	 *
	 * @return bool
	 */
	public static function is_woo_store_api_request() {
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
		return $request_uri && str_contains( $request_uri, '/wp-json/wc/store/v1' );
	}

	/**
	 * Add subscription delivery date and time to the order details.
	 *
	 * @param array    $total_rows The total rows.
	 * @param WC_Order $order      The order.
	 * @param string   $tax_display The tax display.
	 *
	 * @return array Modified total rows.
	 */
	public static function add_to_order_details( $total_rows, $order, $tax_display ) {
		$meta = SubscriptionOrderMetaData::from_order( $order );

		if ( ! $meta || empty( $meta->date ) ) {
			return $total_rows;
		}

		$label_type = $meta->get_shipping_label_type();

		$total_rows['subscription_date'] = array(
			'label' => 'collection' === $label_type ? __( 'Subscription Pickup Date:', 'iconic-wds' ) : __( 'Subscription Delivery Date:', 'iconic-wds' ),
			'value' => $meta->get_formatted_date(),
		);

		$total_rows['subscription_timeslot'] = array(
			'label' => 'collection' === $label_type ? __( 'Subscription Pickup Time:', 'iconic-wds' ) : __( 'Subscription Delivery Time:', 'iconic-wds' ),
			'value' => $meta->get_formatted_timeslot(),
		);

		return $total_rows;
	}

	/**
	 * Check if the order has a subscription product.
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return bool
	 */
	public static function does_order_have_subscription_product( $order ) {
		if ( ! self::get_active_integration() ) {
			return false;
		}

		$integration = self::get_active_integration();
		$items       = $order->get_items();

		foreach ( $items as $item ) {
			$item_product = $item->get_product();
			if ( $integration->is_subscription_product( $item_product ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the order has a regular product.
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return bool
	 */
	public static function does_order_have_regular_product( $order ) {
		if ( ! self::get_active_integration() ) {
			return true;
		}

		$integration = self::get_active_integration();
		$items       = $order->get_items();

		foreach ( $items as $item ) {
			$item_product = $item->get_product();

			if ( ! $integration->is_subscription_product( $item_product ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add subscription settings to the general settings tab.
	 *
	 * @param array $settings The existing settings array.
	 * @return array Modified settings array.
	 */
	public static function add_settings( $settings ) {
		if ( empty( $settings ) ) {
			return $settings;
		}

		$subscription_settings = array(
			array(
				'tab_id'              => 'general',
				'section_id'          => 'subscription_settings',
				'section_title'       => __( 'Subscription Settings', 'jckwds' ),
				'section_description' => '',
				'section_order'       => 0,
				'fields'              => array(
					array(
						'id'          => 'unavailable_date_handling',
						'title'       => __( 'Unavailable Delivery Date Handling', 'jckwds' ),
						'subtitle'    => __( 'Choose how to handle recurring subscription delivery dates that fall on unavailable* days.<br><br>*Not a bookable day or available time slots have been filled.', 'jckwds' ),
						'type'        => 'select',
						'default'     => 'allow_except_holiday',
						'placeholder' => '',
						'choices'     => array(
							'allow_all'            => __( 'Allow delivery on any unavailable day, including holidays', 'jckwds' ),
							'allow_except_holiday' => __( 'Allow delivery on unavailable days, except holidays', 'jckwds' ),
							'reschedule'           => __( 'Reschedule to the next available delivery date', 'jckwds' ),
						),
					),
					array(
						'id'       => 'max_date_handling',
						'title'    => __( 'Limit delivery dates by subscription length', 'jckwds' ),
						'subtitle' => __( 'Enable this setting to only let customers choose delivery dates that match how often their subscription renews (e.g. within 7 days for weekly, 30 days for monthly).<br><br>If disabled, customers can select any available future date up to the global Maximum Selectable Date.', 'jckwds' ),
						'type'     => 'select',
						'default'  => 'yes',
						'choices'  => array(
							'no'  => __( 'No', 'jckwds' ),
							'yes' => __( 'Yes', 'jckwds' ),
						),
					),
				),
			),
		);

		$settings['sections'] = array_merge( $settings['sections'], $subscription_settings );

		return $settings;
	}
}
