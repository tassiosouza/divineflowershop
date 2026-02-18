<?php
/**
 * WDS API Interface class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS API Interface class.
 */
class Api {
	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( __CLASS__, 'prepare_shop_order' ), 10, 3 );
		add_filter( 'woocommerce_api_order_response', array( __CLASS__, 'prepare_legacy_shop_order' ), 10, 4 );
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Prepare shop order API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WC_Data          $object   Object data.
	 * @param WP_REST_Request  $request  Request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function prepare_shop_order( $response, $object, $request ) {
		if ( empty( $response->data ) ) {
			return $response;
		}

		global $iconic_wds;

		$data = Order::get_order_date_time( $object );

		$response->data['iconic_delivery_meta'] = $data;

		return $response;
	}

	/**
	 * Add delivery date meta to legacy API.
	 *
	 * @param array    $order_data Order Data.
	 * @param WC_Order $order      Order object.
	 * @param array    $fields     Request fields.
	 * @param object   $server     Server object.
	 *
	 * @return mixed
	 */
	public static function prepare_legacy_shop_order( $order_data, $order, $fields, $server ) {
		$order_data['iconic_delivery_meta'] = Order::get_order_date_time( $order );

		return $order_data;
	}

	public static function load_cart() {
		WC()->frontend_includes();
		wc_load_cart();
	}

	/**
	 * Register endpoints.
	 */
	public static function register_endpoints() {
		if ( empty( WC()->cart ) ) {
			self::load_cart();
		}

		$restricted_endpoints = array(
			'get_bookable_dates' => array( __CLASS__, 'get_bookable_dates' ),
		);

		$open_endpoints = array(
			'get_slots_on_date' => array( __CLASS__, 'get_slots_on_date' ),
			'get_checkout_data' => array( __CLASS__, 'get_checkout_data' ),
		);

		foreach ( $restricted_endpoints as $endpoint => $callback ) {
			register_rest_route(
				'iconic_wds/v1',
				$endpoint,
				array(
					'methods'             => 'GET',
					'callback'            => $callback,
					/**
					 * Filter the permission callback for the WDS REST API endpoints.
					 *
					 * @since 1.20.0
					 */
					'permission_callback' => apply_filters( 'iconic_wds_rest_api_permission_callback', 'is_user_logged_in', $endpoint ),
				)
			);
		}

		foreach ( $open_endpoints as $endpoint => $callback ) {
			register_rest_route(
				'iconic_wds/v1',
				$endpoint,
				// nosemgrep: audit.php.wp.security.rest-route.permission-callback.return-true
				array(
					'methods'             => 'GET',
					'callback'            => $callback,
					'permission_callback' => '__return_true', 
				)
			);
		}

	}

	/**
	 * Get bookable dates endpoint callback.
	 *
	 * @return array.
	 */
	public static function get_bookable_dates() {
		global $iconic_wds;

		$selected_shipping_method    = Settings::get_shipping_methods();
		$all_shipping_method_options = $iconic_wds->get_shipping_method_options();

		// Shipping methods to process.
		$shipping_methods = array();
		$result           = array(
			'success'          => true,
			'shipping_methods' => array(),
			'dates'            => array(),
		);

		if ( in_array( 'any', $selected_shipping_method, true ) ) {
			unset( $all_shipping_method_options['any'] );
			$shipping_methods = array_keys( $all_shipping_method_options );
		} else {
			$shipping_methods = $selected_shipping_method;
		}

		$result['shipping_methods'] = $shipping_methods;

		// So we can set 'chosen_shipping_methods' session.
		wc_load_cart();

		foreach ( $shipping_methods as $shipping_method_id ) {
			/**
			 * Allow custom plugin to modify shipping method ID.
			 *
			 * @param string $shipping_method_id Shipping method ID.
			 * @param bool   $deprecated         Deprecated.
			 *
			 * @since 1.25.0
			 * @since 2.8.0 Second parameter is deprecated.
			 */
			$shipping_method_id = apply_filters( 'iconic_wds_rest_get_bookable_dates_shipping_method_id', $shipping_method_id, false );
			WC()->session->set( 'chosen_shipping_methods', array( $shipping_method_id ) );
			$dates_manager = new Dates( array( 'shipping_method' => $shipping_method_id ) );

			$result['dates'][ $shipping_method_id ] = $dates_manager->get_upcoming_bookable_dates( 'array', false, true );
		}

		return $result;
	}

	/**
	 * Get slots on date endpoint callback.
	 *
	 * @param WP_REST_Request $request WP Rest Request.
	 *
	 * @return array
	 */
	public static function get_slots_on_date( $request ) {
		$date            = $request->get_param( 'date' );
		$shipping_method = $request->get_param( 'shipping_method' );
		$context         = $request->get_param( 'context' );

		if ( empty( $date ) ) {
			return array(
				'success' => false,
				'message' => __( 'Required parameter "date" missing', 'jckwds' ),
			);
		}

		if ( empty( $shipping_method ) ) {
			return array(
				'success' => false,
				'message' => __( 'Required parameter "shipping_method" missing', 'jckwds' ),
			);
		}

		if ( ! is_numeric( $date ) || 8 !== strlen( $date ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid date format. Date must be in Ymd format.', 'jckwds' ),
			);
		}

		// So we can set 'chosen_shipping_methods' session.
		wc_load_cart();
		WC()->session->set( 'chosen_shipping_methods', array( $shipping_method ) );

		$dates_manager = new Dates( array( 'shipping_method' => $shipping_method ) );

		$slots = $dates_manager->slots_available_on_date( $date );
		if ( 'subscription' === $context && is_array( $slots ) ) {
			// filter out ASAP slots.
			$slots = array_filter(
				$slots,
				function( $slot ) {
					return 'asap' !== $slot['id'];
				}
			);

			$slots = array_values( $slots );
		}

		return array(
			'success' => true,
			'data'    => array(
				'timeslot' => $slots,
			),
		);
	}

	/**
	 * Get checkout data.
	 *
	 * @return array
	 */
	public static function get_checkout_data() {
		$shipping_method = filter_input( INPUT_GET, 'shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return self::prepare_checkout_data( $shipping_method );
	}

	/**
	 * Prepare checkout data.
	 *
	 * @param string $shipping_method The shipping method.
	 *
	 * @return array
	 */
	public static function prepare_checkout_data( $shipping_method ) {
		global $iconic_wds;

		$dates_manager = new Dates(
			array(
				'shipping_method' => $shipping_method,
			)
		);

		$allowed  = $dates_manager->is_delivery_slots_allowed();
		$conflict = OverrideSettings::get_conflict_if_exists( $dates_manager->cart->get_products_ids() );

		$data = array(
			'slots_allowed'  => $allowed,
			'bookable_dates' => $allowed ? $dates_manager->get_upcoming_bookable_dates( 'array' ) : false,
			'settings'       => $iconic_wds->settings,
			'conflict'       => Helpers::get_conflict_error_message( $conflict ),
			'labels'         => Helpers::get_label(),
			'needs_shipping' => $dates_manager->needs_shipping(),
		);

		/**
		 * Filter the response data for `get_checkout_data` rest API endpoint.
		 *
		 * @param array  $data The response data.
		 * @param string $shipping_method The shipping method.
		 * @param Dates  $dates_manager The dates manager instance.
		 *
		 * @since 2.8.0
		 */
		return apply_filters( 'iconic_wds_api_get_checkout_data', $data, $shipping_method, $dates_manager );
	}
}
