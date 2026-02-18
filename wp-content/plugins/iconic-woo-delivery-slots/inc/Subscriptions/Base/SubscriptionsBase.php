<?php
/**
 * Subscriptions Integration base class.
 *
 * @package Iconic_WDS\Subscriptions\Base
 */

namespace Iconic_WDS\Subscriptions\Base;

use Iconic_WDS\Helpers;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionOrderMetaKey;
use WC_Product;
use WC_Order;
use DateTime;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;

/**
 * Iconic WDS - Subscriptions compatibility abstract class.
 */
abstract class SubscriptionsBase {
	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Constructor.
	 *
	 * @param string $slug Slug.
	 * @param string $name Name.
	 */
	public function __construct( $slug, $name ) {
		$this->slug = $slug;
		$this->name = $name;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() : string {
		return $this->slug;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() : string {
		return $this->name;
	}

	/**
	 * Run.
	 */
	abstract public function run();

	/**
	 * Is plugin active.
	 *
	 * @return bool
	 */
	abstract public function is_plugin_active() : bool;

	/**
	 * Is subscription product.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return bool
	 */
	abstract public function is_subscription_product( WC_Product $product ): bool;

	/**
	 * Update order meta.
	 *
	 * @param WC_Order $order Order.
	 * @param DateTime $date Date.
	 * @param WC_Order $parent_order Parent order to copy data from.
	 */
	public function update_order_meta( WC_Order $order, DateTime $date, ?WC_Order $parent_order = null ) {
		$formatted_date = wp_date( Helpers::date_format(), strtotime( $date->format( 'Ymd' ) ) );
		$timestamp      = $date->getTimestamp();

		// If the timeslot id is set, update the date with the timeslot data.
		$timeslot_id = $order->get_meta( SubscriptionOrderMetaKey::TIMESLOT_ID );
		if ( $timeslot_id ) {
			global $iconic_wds;
			$timeslot_data = $iconic_wds->get_timeslot_data( $timeslot_id );

			$iconic_wds->add_timestamp_order_meta( $date->format( 'Ymd' ), $timeslot_data, $order->get_id(), SubscriptionProductType::SUBSCRIPTION );
		}

		$order->update_meta_data( SubscriptionOrderMetaKey::DATE, $formatted_date );
		$order->update_meta_data( SubscriptionOrderMetaKey::DATE_YMD, $date->format( 'Ymd' ) );
		$order->update_meta_data( SubscriptionOrderMetaKey::TIMESTAMP_META_KEY, $timestamp );

		// Copy timeslot data.
		if ( $parent_order ) {
			$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT, $parent_order->get_meta( SubscriptionOrderMetaKey::TIMESLOT ) );
			$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT_ID, $parent_order->get_meta( SubscriptionOrderMetaKey::TIMESLOT_ID ) );
			$order->update_meta_data( SubscriptionOrderMetaKey::SHIPPING_METHOD, $parent_order->get_meta( SubscriptionOrderMetaKey::SHIPPING_METHOD ) );
			$order->update_meta_data( SubscriptionOrderMetaKey::TIMESLOT_ANCHOR_DAY, $parent_order->get_meta( SubscriptionOrderMetaKey::TIMESLOT_ANCHOR_DAY ) );
		}

		$order->save();
	}

	/**
	 * Delete subscription order meta.
	 *
	 * @param WC_Order $order Order.
	 */
	public function delete_subscription_order_meta( WC_Order $order ) {
		$order->delete_meta_data( SubscriptionOrderMetaKey::DATE );
		$order->delete_meta_data( SubscriptionOrderMetaKey::DATE_YMD );
		$order->delete_meta_data( SubscriptionOrderMetaKey::TIMESTAMP_META_KEY );
		$order->save();
	}
}
