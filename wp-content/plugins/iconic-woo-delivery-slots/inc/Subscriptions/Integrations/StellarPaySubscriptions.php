<?php
/**
 * Compatibility with StellarPay Subscriptions.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions\Integrations;

use Iconic_WDS\Subscriptions\Dto\SubscriptionOrderMetaData;
use Iconic_WDS\Subscriptions\Base\SubscriptionsBase;
use Iconic_WDS\Subscriptions\RecurringSlotCalculator;
use StellarPay\Subscriptions\Models\Subscription as StellarSubscription;
use StellarPay\Integrations\WooCommerce\Factories\ProductFactory;
use WC_Order;
use WC_Product;
use DateTime;
use SimplePie\Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle compatibility with StellarPay Subscriptions.
 */
class StellarPaySubscriptions extends SubscriptionsBase {

	/**
	 * This function is called on `plugins_loaded` hook if the subscription plugin is active.
	 * i.e. is_plugin_active() returns true.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'stellarpay_wc_stripe_renewal_order_created', array( $this, 'on_renewal_order_created' ), 10, 2 );
	}

	/**
	 * Is plugin active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() : bool {
		return function_exists( 'stellarPay' );
	}

	/**
	 * Is subscription product.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return bool
	 */
	public function is_subscription_product( WC_Product $product ): bool {
		try {
			$sp_product = ProductFactory::makeFromProduct( $product );
			return ! empty( $sp_product );
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * Get subscription period.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 */
	public function get_subscription_period( WC_Product $product ): string {
		try {
			$product = ProductFactory::makeFromProduct( $product );
			if ( empty( $product ) ) {
				return false;
			}

			return $product->getPeriod();
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * Get subscription interval.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 */
	public function get_subscription_interval( WC_Product $product ): string {
		try {
			$product = ProductFactory::makeFromProduct( $product );
			if ( empty( $product ) ) {
				return false;
			}
			return $product->getFrequency();
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * On renewal order created.
	 *
	 * @param WC_Order            $renewal_order Renewal order.
	 * @param StellarSubscription $subscription  Subscription.
	 *
	 * @return void
	 */
	public function on_renewal_order_created( WC_Order $renewal_order, StellarSubscription $subscription ) {
		$parent_order    = wc_get_order( $renewal_order->get_parent_id() );
		$order_meta_data = SubscriptionOrderMetaData::from_order( $parent_order );

		$recurring_slot_calculator = RecurringSlotCalculator::from_array(
			array(
				'anchor_day'      => $order_meta_data->timeslot_anchor_day,
				'period'          => $subscription->period->getValue(),
				'interval'        => $subscription->frequency,
				'start_date'      => $subscription->startedAt, // phpcs:ignore
				'subscription_id' => $renewal_order->get_id(),
			)
		);

		$next_bookable_date = $recurring_slot_calculator->calculate_adjusted_delivery_date( new DateTime() );

		if ( null === $next_bookable_date ) {
			$this->delete_subscription_order_meta( $renewal_order );
		} else {
			$this->update_order_meta( $renewal_order, $next_bookable_date, $parent_order );
		}
	}
}
