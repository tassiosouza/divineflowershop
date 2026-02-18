<?php
/**
 * Subscription order meta data value object.
 *
 * @package Iconic_WDS\Subscriptions\Dto
 */

namespace Iconic_WDS\Subscriptions\Dto;

use DateTime;
use Iconic_WDS\Helpers;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionOrderMetaKey;
use WC_Order;

/**
 * Subscription order meta data value object.
 *
 * @package Iconic_WDS\Subscriptions\Dto
 */
class SubscriptionOrderMetaData {

	/**
	 * Order.
	 *
	 * @var WC_Order
	 */
	public ?WC_Order $order;

	/**
	 * Date.
	 *
	 * @var DateTime
	 */
	public ?DateTime $date = null;

	/**
	 * Date YMD.
	 *
	 * @var string
	 */
	public string $date_ymd;

	/**
	 * Timeslot.
	 *
	 * @var string
	 */
	public string $timeslot;

	/**
	 * Timeslot ID.
	 *
	 * @var string
	 */
	public string $timeslot_id;

	/**
	 * Timeslot anchor day.
	 *
	 * @var int
	 */
	public int $timeslot_anchor_day;

	/**
	 * Shipping method.
	 *
	 * @var string
	 */
	public string $shipping_method;

	/**
	 * Get instance from order ID.
	 *
	 * @param int $order_id Order ID.
	 * @return self|false
	 */
	public static function from_order_id( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		return self::from_order( $order );
	}

	/**
	 * Get instance from order.
	 *
	 * @param WC_Order $order Order.
	 * @return self
	 */
	public static function from_order( $order ) {
		$obj = new self();

		$obj->order    = $order;
		$obj->date_ymd = $order->get_meta( SubscriptionOrderMetaKey::DATE_YMD );

		if ( ! empty( $obj->date_ymd ) ) {
			$obj->date = DateTime::createFromFormat( 'Ymd', $obj->date_ymd, wp_timezone() );
		}

		$obj->timeslot            = $order->get_meta( SubscriptionOrderMetaKey::TIMESLOT );
		$obj->timeslot_id         = $order->get_meta( SubscriptionOrderMetaKey::TIMESLOT_ID );
		$obj->shipping_method     = $order->get_meta( SubscriptionOrderMetaKey::SHIPPING_METHOD );
		$obj->timeslot_anchor_day = (int) $order->get_meta( SubscriptionOrderMetaKey::TIMESLOT_ANCHOR_DAY );

		return $obj;
	}

	/**
	 * Get formatted date.
	 *
	 * @return string
	 */
	public function get_formatted_date() {
		if ( ! $this->date ) {
			return '';
		}

		return $this->date->format( Helpers::date_format() );
	}

	/**
	 * Get formatted timeslot.
	 *
	 * @return string
	 */
	public function get_formatted_timeslot() {
		return $this->timeslot;
	}

	/**
	 * Get shipping type.
	 *
	 * @return string
	 */
	public function get_shipping_label_type() {
		return Helpers::get_shipping_method_label_type( $this->shipping_method );
	}

	/**
	 * Get shipping method name.
	 *
	 * @return string
	 */
	public function get_shipping_method() {
		return $this->shipping_method;
	}
}
