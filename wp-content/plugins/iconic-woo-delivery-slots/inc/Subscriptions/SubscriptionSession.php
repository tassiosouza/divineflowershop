<?php
/**
 * Handles subscription session data.
 *
 * @package Iconic_WDS/Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Cart;
use Iconic_WDS\Helpers;
use Iconic_WDS\Fee;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;
use DateTime;
use Iconic_WDS\FeeManager;

/**
 * Subscription session class for managing delivery slot data.
 *
 * @package Iconic_WDS\Subscriptions
 */

/**
 * Subscription session class.
 *
 * @package Iconic_WDS/Subscriptions
 */
class SubscriptionSession {
	/**
	 * Type - either 'subscription' or 'regular'
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Product ID.
	 *
	 * @var int
	 */
	public $product_id;

	/**
	 * Date YMD.
	 *
	 * @var string
	 */
	public $date_ymd;

	/**
	 * Timeslot.
	 *
	 * @var string
	 */
	public $timeslot;

	/**
	 * Shipping method ID.
	 *
	 * @var string
	 */
	public $shipping_method_id;

	/**
	 * Session ID constant.
	 *
	 * @var string
	 */
	public const WOO_SESSION_ID = 'iconic_wds_subscription_data';

	/**
	 * Create a new instance from a REST request.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return self|null
	 */
	public static function from_rest_request( $request ) {
		$obj = new self();

		$obj->type       = $request->get_param( 'type' );
		$obj->product_id = $request->get_param( 'product_id' );
		$obj->date_ymd   = $request->get_param( 'date_ymd' );
		$obj->timeslot   = $request->get_param( 'timeslot' );

		if ( empty( $obj->type ) ) {
			return null;
		}

		global $iconic_wds;

		// Get shipping method from WooCommerce session.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$obj->shipping_method_id = ! empty( $chosen_shipping_methods ) ? $chosen_shipping_methods[0] : '';
		$timeslot_enabled        = '1' === $iconic_wds->settings['timesettings_timesettings_setup_enable'];
		$timeslot_mandatory      = '1' === $iconic_wds->settings['timesettings_timesettings_setup_mandatory'];

		if ( $timeslot_enabled && $timeslot_mandatory && ! $obj->validate_timeslot( $obj->timeslot ) ) {
			return null;
		}

		return $obj;
	}

	/**
	 * Create a new instance from an array.
	 *
	 * @param array $data Data.
	 *
	 * @return self
	 */
	public static function from_array( $data ) {
		$obj             = new self();
		$obj->type       = $data['type'];
		$obj->product_id = $data['product_id'];
		$obj->date_ymd   = $data['date_ymd'];
		$obj->timeslot   = $data['timeslot'];

		return $obj;
	}

	/**
	 * Get subscription data from the session for specific type and shipping method.
	 *
	 * @param string $type Product type ('subscription' or 'regular').
	 * @param string $shipping_method_id Shipping method ID.
	 *
	 * @return self|null
	 */
	public static function from_woo_session( $type = SubscriptionProductType::SUBSCRIPTION, $shipping_method_id = '' ) {
		global $iconic_wds_dates;
		$session_data = WC()->session->get( self::WOO_SESSION_ID );

		$obj       = new self();
		$obj->type = $type;

		// If specific shipping method not provided, get current one.
		if ( empty( $shipping_method_id ) ) {
			$shipping_method_id = SubscriptionProductType::SUBSCRIPTION === $type ? $iconic_wds_dates->cart->get_subscription_shipping_method_id() : $iconic_wds_dates->cart->get_regular_shipping_method_id();
		}

		$obj->shipping_method_id = $shipping_method_id;

		if ( empty( $session_data ) || ! isset( $session_data[ $type ] ) ) {
			return $obj;
		}

		// Try with current shipping method first.
		if ( ! $shipping_method_id || empty( $session_data[ $type ][ $shipping_method_id ] ) ) {
			return $obj;
		}

		$data = $session_data[ $type ][ $shipping_method_id ];

		if ( empty( $data ) ) {
			return $obj;
		}

		$obj->product_id = $data['product_id'];
		$obj->date_ymd   = $data['date_ymd'];
		$obj->timeslot   = $data['timeslot'];

		return $obj;
	}

	/**
	 * Get all saved data from session
	 *
	 * @return array
	 */
	public static function get_all_session_data() {
		$session_data = WC()->session->get( self::WOO_SESSION_ID );

		if ( empty( $session_data ) ) {
			return array(
				'subscription' => array(),
				'regular'      => array(),
			);
		}

		return $session_data;
	}

	/**
	 * Validate timeslot.
	 *
	 * @param string $timeslot Timeslot.
	 *
	 * @return bool
	 */
	public static function validate_timeslot( $timeslot ) {
		if ( empty( $timeslot ) ) {
			return false;
		}

		$timeslot_exploded = explode( '|', $timeslot );

		if ( count( $timeslot_exploded ) !== 2 ) {
			return false;
		}

		if ( ! isset( $timeslot_exploded[0] ) ) {
			return false;
		}

		global $iconic_wds;

		$timeslot = $iconic_wds->get_timeslot_data( $timeslot_exploded[0] );

		if ( empty( $timeslot ) ) {
			return false;
		}

		return $timeslot;
	}

	/**
	 * Set subscription data in the session.
	 */
	public function save() {
		$current_data = self::get_all_session_data();

		// Make sure the product type exists in the session data.
		if ( ! isset( $current_data[ $this->type ] ) ) {
			$current_data[ $this->type ] = array();
		}

		// Save data for this shipping method.
		$current_data[ $this->type ][ $this->shipping_method_id ] = array(
			'product_id' => $this->product_id,
			'date_ymd'   => $this->date_ymd,
			'timeslot'   => $this->timeslot,
		);

		WC()->session->set( self::WOO_SESSION_ID, $current_data );
	}

	/**
	 * Get the subscription data as an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'type'               => $this->type,
			'product_id'         => $this->product_id,
			'date_ymd'           => $this->date_ymd,
			'timeslot'           => $this->timeslot,
			'shipping_method_id' => $this->shipping_method_id,
		);
	}

	/**
	 * Get the reserved slot data.
	 * Returns false if the shipping method has changed since the slot was saved.
	 *
	 * @return array|false
	 */
	public function to_array_formatted() {
		// Check if current shipping method matches the saved one.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$current_shipping_method = ! empty( $chosen_shipping_methods ) ? $chosen_shipping_methods[0] : '';

		// Return false if shipping methods don't match.
		if ( ! empty( $this->shipping_method_id ) && $current_shipping_method !== $this->shipping_method_id ) {
			return false;
		}

		$date           = $this->date_ymd;
		$date_format    = Helpers::date_format();
		$date_formatted = $date ? wp_date( $date_format, strtotime( $date ) ) : null;

		$timeslot      = $this->timeslot;
		$timeslot_data = $this->validate_timeslot( $timeslot );

		return array(
			'type'               => $this->type,
			'date_ymd'           => $date,
			'date_formatted'     => $date_formatted,
			'timeslot_value'     => $timeslot,
			'timeslot_formatted' => ! empty( $timeslot_data ) ? $timeslot_data['formatted'] : null,
			'shipping_method_id' => $this->shipping_method_id,
		);
	}

	/**
	 * Get timeslot data.
	 *
	 * @return array
	 */
	public function get_timeslot_data() {
		global $iconic_wds;

		$timeslot_id = $iconic_wds->extract_timeslot_id_from_option_value( $this->timeslot );

		if ( empty( $timeslot_id ) ) {
			return null;
		}

		return $iconic_wds->get_timeslot_data( $timeslot_id );
	}

	/**
	 * Get the timeslot ID.
	 *
	 * @return string The timeslot ID.
	 */
	public function get_timeslot_id() {
		global $iconic_wds;

		$id = $this->date_ymd;

		if ( empty( $this->timeslot ) ) {
			return $id;
		}

		$timeslot_id = $iconic_wds->extract_timeslot_id_from_option_value( $this->timeslot );

		if ( empty( $timeslot_id ) ) {
			return $id;
		}

		return $id . '_' . $timeslot_id;
	}

	/**
	 * Clear the session.
	 */
	public static function clear_session() {
		WC()->session->set( self::WOO_SESSION_ID, null );
	}

	/**
	 * Clear specific product type data from session.
	 *
	 * @param string $type Product type ('subscription' or 'regular').
	 * @param string $shipping_method_id Specific shipping method or empty for all.
	 */
	public static function clear_type_data( $type, $shipping_method_id = '' ) {
		$session_data = self::get_all_session_data();

		if ( isset( $session_data[ $type ] ) ) {
			if ( $shipping_method_id ) {
				// Clear only specific shipping method.
				if ( isset( $session_data[ $type ][ $shipping_method_id ] ) ) {
					unset( $session_data[ $type ][ $shipping_method_id ] );
				}
			} else {
				// Clear all data for this product type.
				$session_data[ $type ] = array();
			}

			WC()->session->set( self::WOO_SESSION_ID, $session_data );
		}
	}

	/**
	 * Clear session data for a specific product ID.
	 *
	 * Example: If the last subscription product is removed from the cart,
	 * clear the subscription's date and timeslot data from the session.
	 *
	 * Similarly, if the last regular/one-time product is removed from the cart,
	 * clear the regular's date and timeslot data from the session.
	 *
	 * @param int $product_id The product ID to clear session data for.
	 * @return bool True if session was updated, false otherwise.
	 */
	public static function clear_session_for_product( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		global $iconic_wds;

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return false;
		}

		// If the product is not a subscription product, clear the fee session.
		if ( ! Boot::get_active_integration()->is_subscription_product( $product ) ) {
			$cart = new Cart();
			// check if we still have any non-subscription product in the cart.
			if ( ! $cart->has_non_subscription_product() ) {
				self::clear_fee_session( $iconic_wds->fee );
				self::clear_type_data( SubscriptionProductType::REGULAR );
			}

			return true;
		}

		// If the product is a subscription product, clear the fee session for the subscription product.
		$session_data = self::get_all_session_data();
		$updated      = false;

		// Loop through both types (subscription and regular).
		foreach ( array( SubscriptionProductType::SUBSCRIPTION, SubscriptionProductType::REGULAR ) as $type ) {
			if ( isset( $session_data[ $type ] ) && is_array( $session_data[ $type ] ) ) {
				foreach ( $session_data[ $type ] as $method_id => $data ) {
					if ( isset( $data['product_id'] ) && in_array( $product_id, $data['product_id'] ) ) {
						// Found the product in session, remove it.
						unset( $session_data[ $type ][ $method_id ] );
						$updated = true;

						self::clear_fee_session( FeeManager::get_fee_class( $type ) );
					}
				}
			}
		}

		// Only update session if something was removed.
		if ( $updated ) {
			WC()->session->set( self::WOO_SESSION_ID, $session_data );
		}

		return $updated;
	}

	/**
	 * Clear fee session data.
	 *
	 * @param string $fee_class Class name of the fee class.
	 */
	public static function clear_fee_session( $fee_class ) {
		if ( ! $fee_class instanceof Fee ) {
			$instance = new $fee_class();
		} else {
			$instance = $fee_class;
		}

		WC()->session->__unset( $instance->day_fee_key );
		WC()->session->__unset( $instance->next_day_fee_key );
		WC()->session->__unset( $instance->same_day_fee_key );
		WC()->session->__unset( $instance->timeslot_fee_key );
	}

	/**
	 * Get the product ID.
	 *
	 * @return int
	 */
	public function get_product_id() {
		return ( is_array( $this->product_id ) && ! empty( $this->product_id ) ) ? $this->product_id[0] : $this->product_id;
	}
}
