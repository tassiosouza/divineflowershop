<?php
/**
 * Woo Subscriptions.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions\Integrations;

use Iconic_WDS\Subscriptions\Dto\SubscriptionOrderMetaData;
use Iconic_WDS\Subscriptions\Base\SubscriptionsBase;
use Iconic_WDS\Subscriptions\RecurringSlotCalculator;
use Iconic_WDS\Subscriptions\SubscriptionProductsFee;
use WC_Subscription;
use WC_Order;
use WC_Product;
use DateTime;
use WCS_ATT_Product_Schemes;
use WCS_ATT_Cart;

defined( 'ABSPATH' ) || exit;

/**
 * Woo Subscriptions.
 */
class WooSubscriptions extends SubscriptionsBase {

	/**
	 * This function is called on `plugins_loaded` hook if the subscription plugin is active.
	 * i.e. is_plugin_active() returns true.
	 *
	 * @return void
	 */
	public function run() {
		add_filter( 'wcs_renewal_order_created', array( $this, 'on_renewal_order_created' ), 10, 2 );
		add_filter( 'woocommerce_subscriptions_is_recurring_fee', array( $this, 'is_recurring_fee' ), 10, 3 );
	}

	/**
	 * Is plugin active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() : bool {
		return class_exists( 'WC_Subscriptions' );
	}

	/**
	 * Is subscription product.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return bool
	 */
	public function is_subscription_product( WC_Product $product ): bool {
		$is_subscription = $product->is_type( 'subscription' ) || $product->is_type( 'subscription_variation' );

		if ( $is_subscription ) {
			return true;
		}

		// Check Woo All Products for Subscription compatibility.
		if ( ! class_exists( 'WCS_ATT_Product_Schemes' ) ) {
			return false;
		}

		$schema = $this->get_apfs_scheme_data( $product );
		return ! empty( $schema );
	}

	/**
	 * Get subscription period.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 */
	public function get_subscription_period( WC_Product $product ): string {
		$woo_sub_period =  $product->get_meta( '_subscription_period' );

		if ( ! empty( $woo_sub_period ) ) {
			return $woo_sub_period;
		}

		// Check for Woo All Products for Subscription compatibility.
		if ( ! class_exists('WCS_ATT_Product_Schemes') || !class_exists('WCS_ATT_Cart') ) {
			return '';
		}

		$schema = $this->get_apfs_scheme_data( $product );

		if ( empty( $schema ) ) {
			return '';
		}

		return $schema->get_period();
	}

	/**
	 * Find product in cart.
	 *
	 * Prioritize cart items with an active subscription scheme.
	 * If no active subscription scheme is found, return the first cart item that matches the product ID
	 * or null if no product found.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return array|null
	 */
	public function find_product_in_cart( $product ) {
		$find_product_id = $product->get_parent_id() ?? $product->get_id();
		$fallback_cart_item = null;
		
		if ( empty( WC()->cart ) ) {
			return null;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$parent_product_id = $cart_item['data']->get_parent_id() ?? $cart_item['data']->get_product_id();
			
			if ( $find_product_id === $parent_product_id ) {
				// First priority: Return immediately if this cart item has an active subscription scheme.
				if ( ! empty( $cart_item['wcsatt_data']['active_subscription_scheme'] ) ) {
					return $cart_item;
				}
				
				// Store as fallback option (product ID matches but no active subscription scheme)
				if ( null === $fallback_cart_item ) {
					$fallback_cart_item = $cart_item;
				}
			}
		}

		// Return fallback cart item (product ID match without active subscription scheme) or null if no product found
		return $fallback_cart_item;
	}

	/**
	 * Get subscription interval.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 */
	public function get_subscription_interval( WC_Product $product ): string {
		$woo_sub_interval = $product->get_meta( '_subscription_period_interval' );

		if ( ! empty( $woo_sub_interval ) ) {
			return $woo_sub_interval;
		}

		// Check for Woo All Products for Subscription compatibility.
		if ( ! class_exists('WCS_ATT_Product_Schemes') || !class_exists('WCS_ATT_Cart') ) {
			return '';
		}

		$schema = $this->get_apfs_scheme_data( $product );

		if ( empty( $schema ) ) {
			return '';
		}

		return $schema->get_interval();
	}

	/**
	 * On renewal order created.
	 *
	 * @param WC_Order        $renewal_order Renewal order.
	 * @param WC_Subscription $subscription  Subscription.
	 *
	 * @return WC_Order
	 */
	public function on_renewal_order_created( WC_Order $renewal_order, WC_Subscription $subscription ) {
		$order_meta_data = SubscriptionOrderMetaData::from_order( $renewal_order );

		$recurring_slot_calculator = RecurringSlotCalculator::from_array(
			array(
				'anchor_day'      => $order_meta_data->timeslot_anchor_day,
				'period'          => $subscription->get_billing_period(),
				'interval'        => $subscription->get_billing_interval(),
				'start_date'      => new DateTime( $subscription->get_date( 'date_created' ), wp_timezone() ),
				'subscription_id' => $renewal_order->get_id(),
			)
		);

		$next_bookable_date = $recurring_slot_calculator->calculate_adjusted_delivery_date( new DateTime() );

		if ( null === $next_bookable_date ) {
			$this->delete_subscription_order_meta( $renewal_order );
		} else {
			$this->update_order_meta( $renewal_order, $next_bookable_date );
		}

		return $renewal_order;
	}

	/**
	 * Mark delivery slot fees as recurring.
	 *
	 * @param bool   $is_recurring Whether the fee is recurring.
	 * @param object $fee_line     Fee line item.
	 * @param object $order        Order object.
	 *
	 * @return bool
	 */
	public function is_recurring_fee( $is_recurring, $fee_line, $order ) {
		$subscription_fee = new SubscriptionProductsFee();
		$fee_name = $subscription_fee->get_fee_name();

		// Mark as recurring if this is our delivery slot fee
		if ( $fee_line->name === $fee_name ) {
			return true;
		}
		
		return $is_recurring;
	}

	/**
	 * Get APFS scheme data from the cart item.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return object|null
	 */
	public function get_apfs_scheme_data( WC_Product $product ) {
		$cart_item            = $this->find_product_in_cart( $product );

		if ( empty( $cart_item ) ) {
			return null;
		}

		$subscription_schemes = WCS_ATT_Product_Schemes::get_subscription_schemes( $product );
		$subscription_scheme  = WCS_ATT_Cart::get_subscription_scheme( $cart_item );
		
		if ( empty( $subscription_scheme ) || empty( $subscription_schemes[$subscription_scheme] ) ) {
			return null;
		}

		return $subscription_schemes[$subscription_scheme];
	}
}
