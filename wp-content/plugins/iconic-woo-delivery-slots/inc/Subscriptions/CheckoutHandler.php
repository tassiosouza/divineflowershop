<?php

namespace Iconic_WDS\Subscriptions;

/**
 * Internal dependencies.
 */
use Iconic_WDS\Cart;
use Iconic_WDS\Helpers;
use Iconic_WDS\Dates;
use Iconic_WDS\Subscriptions\SubscriptionSession;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionOrderMetaKey;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;
use Iconic_WDS\Subscriptions\SubscriptionProductsFee;
use Iconic_WDS\Subscriptions\RegularProductsFee;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionPeriod;
use Iconic_WDS\Checkout;

/**
 * External dependencies.
 */
use DateTime;
use Iconic_WDS;
use WP_Error;

/**
 * Checkout field handler class.
 */
class CheckoutHandler {

	/**
	 * Cart.
	 *
	 * @var Cart
	 */
	private $cart;

	/**
	 * Field handler.
	 *
	 * @var SubscriptionField
	 */
	public $field_handler;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// By default cart object will be created from the Woo session.
		$this->cart          = new Cart();
		$this->field_handler = new SubscriptionField( $this->cart );

		// Validation.
		add_action( 'woocommerce_checkout_process', array( $this, 'checkout_validation' ) ); // For classic checkout.
		add_action( 'woocommerce_checkout_validate_order_before_payment', array( $this, 'block_checkout_validation' ), 10, 2 );

		// Update order meta.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta_classic_checkout' ), 10, 1 ); // For classic checkout.
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'update_order_meta_block_checkout' ), 20, 1 ); // For block checkout -- run after validation.

		// Clear session on thank you page.
		add_action( 'woocommerce_thankyou', array( $this, 'clear_session' ), 10, 1 );

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// If the last subscription product is removed from the cart,
		// clear the subscription's date and timeslot data from the session.
		// Similarly, if the last regular/one-time product is removed from the cart.
		add_action( 'woocommerce_cart_item_removed', array( $this, 'reset_subscription_session' ), 10 );
	}

	/**
	 * Add checkout field.
	 */
	public function add_checkout_field() {
		$this->field_handler->add_subscription_field();
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		global $post;

		// Only enqueue scripts for the classic checkout.
		if ( Helpers::is_block_checkout() ) {
			return;
		}

		$asset_file = ICONIC_WDS_PATH . 'blocks/build/subscriptions-frontend.asset.php';
		$default    = array(
			'dependencies' => array(),
			'version'      => Iconic_WDS::$version,
		);
		$asset_data = file_exists( $asset_file ) ? include $asset_file : $default;

		global $iconic_wds;
		
		wp_enqueue_script( 'iconic-wds-subscriptions-frontend', ICONIC_WDS_URL . 'blocks/build/subscriptions-frontend.js', $asset_data['dependencies'], $iconic_wds::$version, true );
	}

	/**
	 * Block checkout validation.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function block_checkout_validation( $order, $validation_errors ) {
		$cart                = Cart::from_order( $order );
		$this->field_handler = new SubscriptionField( $cart );

		$subscription_product = $this->field_handler->find_subscription_product_in_cart();
		if ( ! $subscription_product ) {
			return;
		}

		$subscription_validation_errors = $this->validate_subscription_slot();
		if ( is_wp_error( $subscription_validation_errors ) ) {
			$validation_errors->add( 'error', $subscription_validation_errors->get_error_message() );
			return;
		}

		// proceed only if regular product is in cart
		if ( ! $this->field_handler->find_regular_products_in_cart() ) {
			return;
		}

		$regular_validation_errors = $this->validate_regular_slot();
		if ( is_wp_error( $regular_validation_errors ) ) {
			$validation_errors->add( 'error', $regular_validation_errors->get_error_message() );
		}
	}

	/**
	 * Process checkout field.
	 */
	public function checkout_validation() {
		$cart          = new Cart();
		$field_handler = new SubscriptionField( $cart );

		$subscription_product = $field_handler->find_subscription_product_in_cart();

		// Do not run validation if there is no subscription product in cart.
		if ( ! $subscription_product ) {
			return;
		}

		if ( is_wp_error( $this->validate_subscription_slot() ) ) {
			wc_add_notice( $this->validate_subscription_slot()->get_error_message(), 'error' );

			return;
		}

		// Do not run validation if there is no regular product in cart.
		if ( ! $field_handler->find_regular_products_in_cart() ) {
			return;
		}

		if ( is_wp_error( $this->validate_regular_slot() ) ) {
			wc_add_notice( $this->validate_regular_slot()->get_error_message(), 'error' );
			return;
		}
	}

	/**
	 * Validate subscription slot.
	 */
	public function validate_subscription_slot(): ?WP_Error {
		$session = SubscriptionSession::from_woo_session( SubscriptionProductType::SUBSCRIPTION );

		$dates_manager = new Dates(
			array(
				'shipping_method' => $session->shipping_method_id,
			)
		);

		if ( ! $dates_manager->is_delivery_slots_allowed() ) {
			return null;
		}

		if ( empty( $session->date_ymd ) ) {
			return new WP_Error( 'error', __( 'Please select a delivery date for your subscription.', 'jckwds' ) );
		}

		global $iconic_wds;

		if ( '1' === $iconic_wds->settings['timesettings_timesettings_setup_mandatory'] ) {
			if ( empty( $session->timeslot ) ) {
				return new WP_Error( 'error', __( 'Please select delivery time for your subscription.', 'jckwds' ) );
			}
		}

		return Checkout::get_checkout_validation_errors( $session->date_ymd, $session->timeslot );
	}

	/**
	 * Validate regular (one-time) slot.
	 */
	public function validate_regular_slot(): ?WP_Error {
		$session = SubscriptionSession::from_woo_session( SubscriptionProductType::REGULAR );
		$validation_errors = new WP_Error();

		$dates_manager = new Dates(
			array(
				'shipping_method' => $session->shipping_method_id,
			)
		);

		if ( ! $dates_manager->is_delivery_slots_allowed() ) {
			return null;
		}

		global $iconic_wds;

		if ( empty( $session->date_ymd ) && '1' === $iconic_wds->settings['datesettings_datesettings_setup_mandatory'] ) {
			$validation_errors->add( 'error', __( 'Please select a delivery date for your one-time purchase.', 'jckwds' ) );
			return $validation_errors;
		}

		global $iconic_wds;

		if ( '1' === $iconic_wds->settings['timesettings_timesettings_setup_mandatory'] ) {
			if ( empty( $session->timeslot ) ) {
				$validation_errors->add( 'error', __( 'Please select a time slot for your one-time purchase.', 'jckwds' ) );
				return $validation_errors;
			}
		}

		return Checkout::get_checkout_validation_errors( $session->date_ymd, $session->timeslot );
	}

	/**
	 * Update order meta for block checkout.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function update_order_meta_block_checkout( $order ) {
		$this->update_order_meta( $order );
	}

	/**
	 * Update order meta for classic checkout.
	 *
	 * @param int $order_id Order ID.
	 */
	public function update_order_meta_classic_checkout( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$this->update_order_meta( $order );
	}

	/**
	 * Update order meta.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function update_order_meta( $order ) {
		if ( ! $order ) {
			return;
		}

		$session = SubscriptionSession::from_woo_session( 'subscription' );

		if ( empty( $session->date_ymd ) ) {
			return;
		}

		$this->save_subscription_order_meta( $order, $session );
		$session = SubscriptionSession::from_woo_session( SubscriptionProductType::REGULAR );

		if ( empty( $session ) || empty( $session->date_ymd ) ) {
			return;
		}

		$this->save_regular_order_meta( $order, $session );
	}

	/**
	 * Save order meta.
	 *
	 * @param WC_Order            $order   Order object.
	 * @param SubscriptionSession $session Session data.
	 */
	public function save_subscription_order_meta( $order, $session ) {
		global $iconic_wds;

		$timeslot = $session->get_timeslot_data();

		$iconic_wds->add_reservation(
			array(
				'user_id'    => $iconic_wds->user_id,
				'datetimeid' => $session->get_timeslot_id(),
				'date'       => Helpers::convert_date_for_database( $session->date_ymd ),
				'starttime'  => isset( $timeslot['timefrom']['stripped'] ) ? $timeslot['timefrom']['stripped'] : null,
				'endtime'    => isset( $timeslot['timeto']['stripped'] ) ? $timeslot['timeto']['stripped'] : null,
				'processed'  => 1,
				'order_id'   => $order->get_id(),
			)
		);

		$formatted_date = wp_date( Helpers::date_format(), strtotime( $session->date_ymd ) );

		$order->update_meta_data( SubscriptionOrderMetaKey::DATE, $formatted_date );
		$order->update_meta_data( SubscriptionOrderMetaKey::DATE_YMD, $session->date_ymd );
		$order->update_meta_data( SubscriptionOrderMetaKey::SHIPPING_METHOD, $session->shipping_method_id );

		if ( ! empty( $timeslot ) ) {
			$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT, $timeslot['formatted'] );
			$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT_ID, $timeslot['value'] );
		}

		$iconic_wds->add_timestamp_order_meta( $session->date_ymd, $timeslot, $order->get_id(), SubscriptionProductType::SUBSCRIPTION );

		$this->save_anchor_date( $order, $session );

		$order->save();
	}

	/**
	 * Save regular order meta.
	 *
	 * @param WC_Order            $order   Order object.
	 * @param SubscriptionSession $session Session data.
	 */
	public function save_regular_order_meta( $order, $session ) {
		global $iconic_wds;

		$timeslot = $session->get_timeslot_data();

		$iconic_wds->add_reservation(
			array(
				'user_id'    => $iconic_wds->user_id,
				'datetimeid' => $session->get_timeslot_id(),
				'date'       => Helpers::convert_date_for_database( $session->date_ymd ),
				'starttime'  => isset( $timeslot['timefrom']['stripped'] ) ? $timeslot['timefrom']['stripped'] : null,
				'endtime'    => isset( $timeslot['timeto']['stripped'] ) ? $timeslot['timeto']['stripped'] : null,
				'processed'  => 1,
				'order_id'   => $order->get_id(),
			)
		);

		$formatted_date = wp_date( Helpers::date_format(), strtotime( $session->date_ymd ) );
		$order->update_meta_data( $iconic_wds->date_meta_key, esc_attr( $formatted_date ) );
		$order->update_meta_data( $iconic_wds->date_meta_key . '_ymd', esc_attr( $session->date_ymd ) );
		$order->update_meta_data( $iconic_wds->shipping_method_meta_key, $session->shipping_method_id );

		if ( ! empty( $timeslot ) ) {
			$order->update_meta_data( $iconic_wds->timeslot_meta_key, esc_attr( $timeslot['formatted'] ) );
			$order->update_meta_data( $iconic_wds->timeslot_meta_key . '_id', esc_attr( $timeslot['value'] ) );
		}

		$iconic_wds->add_timestamp_order_meta( $session->date_ymd, $timeslot, $order->get_id(), SubscriptionProductType::REGULAR );

		$order->save();
	}

	/**
	 * Clear session on thank you page.
	 */
	public function clear_session() {
		SubscriptionSession::clear_session();
		SubscriptionSession::clear_fee_session( SubscriptionProductsFee::class );
		SubscriptionSession::clear_fee_session( RegularProductsFee::class );
	}


	/**
	 * When a product is removed from the cart, clear the session for that product
	 * i.e. remove the data/timeslot etc.
	 *
	 * @param string $cart_item_key The cart item key of the item being removed.
	 */
	public static function reset_subscription_session( $cart_item_key ) {
		// Removed product ID.
		$cart_item = WC()->cart->removed_cart_contents[ $cart_item_key ] ?? null;

		if ( ! $cart_item || ! isset( $cart_item['product_id'] ) ) {
			return;
		}

		$removed_product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
		SubscriptionSession::clear_session_for_product( $removed_product_id );
	}

	/**
	 * Get the maximum anchor date for the subscription.
	 *
	 * @param string $period   The period of the subscription.
	 * @param int    $interval The interval of the subscription.
	 *
	 * @return int The maximum anchor date.
	 */
	public static function get_max_anchor_date( string $period, int $interval ) : int {
		switch ( $period ) {
			case SubscriptionPeriod::MONTH:
				return 30 * $interval;
			case SubscriptionPeriod::WEEK:
				return 7 * $interval;
			case SubscriptionPeriod::DAY:
				return $interval;
			case SubscriptionPeriod::YEAR:
				return 365 * $interval;
			default:
				return 1;
		}
	}

	/**
	 * Save anchor date to the order meta.
	 *
	 * @param WC_Order            $order   Order object.
	 * @param SubscriptionSession $session Session data.
	 */
	public function save_anchor_date( $order, $session ) {
		$product = wc_get_product( $session->get_product_id() );

		if ( ! $product ) {
			return;
		}

		$period          = Boot::get_active_integration()->get_subscription_period( $product );
		$interval        = Boot::get_active_integration()->get_subscription_interval( $product );
		$max_anchor_date = self::get_max_anchor_date( $period, $interval );

		$today         = new DateTime();
		$delivery_date = new DateTime( $session->date_ymd );

		$today->setTime( 0, 0, 0 );
		$delivery_date->setTime( 0, 0, 0 );

		$diff = $delivery_date->diff( $today );

		$anchor_day = $diff->days > $max_anchor_date ? $max_anchor_date : $diff->days;

		$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT_ANCHOR_DAY, $anchor_day );

		$order->save();
	}
}
