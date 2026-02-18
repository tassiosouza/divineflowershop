<?php
/**
 * Subscription Rest API.
 *
 * @package Iconic_WDS\Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Subscriptions\SubscriptionSession;
use Iconic_WDS\Api;
use Iconic_WDS\Dates;
use Iconic_WDS\Helpers;
use Iconic_WDS\Cart;

/**
 * Rest API class.
 */
class RestApi {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( // nosemgrep
			'iconic_wds/v1',
			'/get_cart_subscription_data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_cart_subscription_data' ),
				'permission_callback' => '__return_true', // nosemgrep: audit.php.wp.security.rest-route.permission-callback.return-true
			)
		);

		register_rest_route( // nosemgrep 
			'iconic_wds/v1',
			'/save_subscription_slot_session',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_subscription_slot_session' ),
				'permission_callback' => '__return_true',  // nosemgrep: audit.php.wp.security.rest-route.permission-callback.return-true
			)
		);

		register_rest_route( // nosemgrep
			'iconic_wds/v1',
			'/get_subscription_checkout_data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_subscription_checkout_data' ),
				'permission_callback' => '__return_true',  // nosemgrep: audit.php.wp.security.rest-route.permission-callback.return-true
			)
		);
	}

	/**
	 * Get subscription and regular products data from the cart.
	 */
	public function get_cart_subscription_data() {
		$cart                       = new Cart();
		$subscription_field_handler = new SubscriptionField( $cart );

		$response_data = array(
			'subscription' => array(),
			'regular'      => array(),
		);

		// Get subscription product data.
		$subscription_product = $subscription_field_handler->find_subscription_product_in_cart();
		if ( $subscription_product ) {
			$response_data['subscription'] = $subscription_field_handler->get_subscription_field_data( $subscription_product, 'subscription' );
		}

		// Get regular products data.
		$regular_products = $subscription_field_handler->find_regular_products_in_cart();
		if ( ! empty( $regular_products ) ) {
			$response_data['regular'] = $subscription_field_handler->get_regular_field_data( $regular_products );
		}

		if ( empty( $response_data ) ) {
			return new \WP_Error( 'no_products', __( 'No subscription or regular products found in cart', 'iconic-wds' ) );
		}

		return rest_ensure_response( $response_data );
	}

	/**
	 * Save subscription slot session.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function save_subscription_slot_session( $request ) {
		$dto = SubscriptionSession::from_rest_request( $request );

		if ( ! $dto ) {
			return new \WP_Error( 'invalid_timeslot', __( 'Invalid timeslot', 'iconic-wds' ) );
		}

		$dto->save();

		return rest_ensure_response( $dto->to_array_formatted() );
	}

	/**
	 * Get subscription checkout data.
	 */
	public function get_subscription_checkout_data() {
		$regular_shipping_method      = filter_input( INPUT_GET, 'regular_shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$subscription_shipping_method = filter_input( INPUT_GET, 'subscription_shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$data = array(
			'regular'      => Api::prepare_checkout_data( $regular_shipping_method ),
			'subscription' => Api::prepare_checkout_data( $subscription_shipping_method ),
		);

		$subscription_field_handler = new SubscriptionField( new Cart() );

		$data = $this->populate_regular_cart_data( $data, $subscription_field_handler );
		$data = $this->populate_subscription_cart_data( $data, $subscription_field_handler );

		$data['subscription']['formatted_interval'] = $this->get_formatted_interval( $subscription_shipping_method );

		return rest_ensure_response( $data );
	}

	/**
	 * Get formatted interval.
	 *
	 * @param string $shipping_method The subscription shipping method.
	 *
	 * @return string The formatted interval.
	 */
	public function get_formatted_interval( $shipping_method ) {
		$dates_manager        = new Dates(
			array(
				'shipping_method' => $shipping_method,
			)
		);
		$shipping_method_type = Helpers::get_shipping_method_type( $shipping_method );
		$field_handler        = new SubscriptionField( $dates_manager->cart );
		$subscription_product = $field_handler->find_subscription_product_in_cart();

		if ( ! $subscription_product ) {
			return '';
		}

		return $field_handler->get_formatted_interval( $subscription_product, $shipping_method_type );
	}

	/**
	 * Populate regular cart data.
	 *
	 * @param array             $data The data.
	 * @param SubscriptionField $subscription_field_handler The subscription field handler.
	 *
	 * @return array The data.
	 */
	public function populate_regular_cart_data( array $data, SubscriptionField $subscription_field_handler ) {
		$regular_products = $subscription_field_handler->find_regular_products_in_cart();
		if ( ! empty( $regular_products ) ) {
			$data['regular']         = $data['regular'] ? $data['regular'] : array();
			$data['regular']['cart'] = $subscription_field_handler->get_regular_field_data( $regular_products );
		}

		return $data;
	}

	/**
	 * Populate subscription cart data.
	 *
	 * @param array             $data The data.
	 * @param SubscriptionField $subscription_field_handler The subscription field handler.
	 *
	 * @return array The data.
	 */
	public function populate_subscription_cart_data( array $data, SubscriptionField $subscription_field_handler ) {
		$subscription_product = $subscription_field_handler->find_subscription_product_in_cart();
		if ( $subscription_product ) {
			$data['subscription']         = $data['subscription'] ? $data['subscription'] : array();
			$data['subscription']['cart'] = $subscription_field_handler->get_subscription_field_data( $subscription_product );
		}

		return $data;
	}
}
