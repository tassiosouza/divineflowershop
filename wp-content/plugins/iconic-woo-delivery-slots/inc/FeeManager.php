<?php

namespace Iconic_WDS;

use Iconic_WDS\Dto\FeeDto;
use Iconic_WDS\Subscriptions\Boot;
use Iconic_WDS\Subscriptions\RegularProductsFee;
use Iconic_WDS\Subscriptions\SubscriptionProductsFee;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;

/**
 * Fee manager.
 * It will handle the fee for these 6 conditions:
 *
 * Main delivery slots fees: Block checkout
 * Main delivery slots: Classic checkout
 * Subscription: Onetime product fees for Block checkout
 * Subscription: Onetime product fees for Classic checkout
 * Subscription: Subscription product fees for Block checkout
 * Subscription: Subscription product fees for Classic checkout
 */
class FeeManager {
	/**
	 * Initiate.
	 */
	public static function run() {
		// Handle Fee for the Block checkout.
		self::block_checkout_fee();
		// Handle Fee for the Block checkout.
		self::block_checkout_fee_for_subscriptions();

		if ( Boot::has_subscription_product_in_cart() ) {
			self::classic_checkout_hooks_for_subscriptions();
		} else {
			// Handle Fee for the Classic checkout.
			self::classic_checkout_hooks();
		}

	}

	/**
	 * Main fee hooks.
	 */
	public static function classic_checkout_hooks() {
		global $iconic_wds;

		// Add fee at checkout, if required.
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'classic_checkout_check_fee' ), 10 );
		add_action( 'woocommerce_cart_calculate_fees', array( $iconic_wds->fee, 'apply_fee' ), 10 );
	}

	/**
	 * Classic checkout hooks for subscriptions.
	 */
	public static function classic_checkout_hooks_for_subscriptions() {
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'classic_checkout_check_fee_for_subscriptions' ), 10 );

		add_action( 'woocommerce_cart_calculate_fees', array( Boot::$subscription_products_fee, 'apply_fee' ), 10 );
		add_action( 'woocommerce_cart_calculate_fees', array( Boot::$regular_products_fee, 'apply_fee' ), 10 );
	}

	/**
	 * Block checkout fee for non-subscription products.
	 *
	 * Handle the server side of the cart update (extensionCartUpdate).
	 * This is called when the cart is updated via the REST API.
	 */
	public static function block_checkout_fee() {
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'iconic-wds',
				'callback'  => function( $data ) {
					global $iconic_wds;

					$date     = isset( $data['date'] ) ? $data['date'] : '';
					$date_ymd = isset( $data['date_ymd'] ) ? $data['date_ymd'] : '';
					$timeslot = isset( $data['timeslot'] ) ? $data['timeslot'] : '';

					$iconic_wds->fee->update_fees_session( $date, $date_ymd, $timeslot );
				},
			)
		);

		global $iconic_wds;

		add_action( 'woocommerce_store_api_cart_select_shipping_rate', array( $iconic_wds->fee, 'disable_fee_if_shipping_method_not_allowed' ), 10, 3 );
	}

	/**
	 * Block checkout fee for subscriptions.
	 *
	 * Handle the server side of the cart update (extensionCartUpdate).
	 * This is called when the cart is updated via the REST API.
	 */
	public static function block_checkout_fee_for_subscriptions() {
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'iconic-wds-subscriptions',
				'callback'  => function( $data ) {
					$subscription_fee_dto = FeeDto::from_store_api_request_data( $data, SubscriptionProductType::SUBSCRIPTION );
					$regular_fee_dto = FeeDto::from_store_api_request_data( $data, SubscriptionProductType::REGULAR );

					if ( $subscription_fee_dto ) {
						Boot::$subscription_products_fee->check_fee( $subscription_fee_dto );
					}

					if ( $regular_fee_dto ) {
						Boot::$regular_products_fee->check_fee( $regular_fee_dto );
					}
				},
			)
		);

		add_action( 'woocommerce_store_api_cart_select_shipping_rate', array( Boot::$subscription_products_fee, 'disable_fee_if_shipping_method_not_allowed' ), 10, 3 );
		add_action( 'woocommerce_store_api_cart_select_shipping_rate', array( Boot::$regular_products_fee, 'disable_fee_if_shipping_method_not_allowed' ), 10, 3 );
	}

	/**
	 * Check fee, and update the fees session.
	 *
	 * When WooCommerce runs the update_order_review AJAX function,
	 * check if our slot has a fee applied to it, then add/remove it
	 *
	 * @param string $post_data Post data.
	 */
	public static function classic_checkout_check_fee( $post_data ) {
		global $iconic_wds;

		$fee_dto = FeeDto::from_posted_data( $post_data );
		if ( $fee_dto ) {
			$iconic_wds->fee->update_fees_session( $fee_dto->date, $fee_dto->date_ymd, $fee_dto->timeslot );
		}
	}

	/**
	 * Check fee for subscriptions.
	 *
	 * @param string $post_data Post data.
	 */
	public static function classic_checkout_check_fee_for_subscriptions( $post_data ) {
		$subscription_fee_dto = FeeDto::from_subscription_posted_data( $post_data, SubscriptionProductType::SUBSCRIPTION );
		if ( $subscription_fee_dto ) {
			Boot::$subscription_products_fee->check_fee( $subscription_fee_dto );
		}

		$regular_fee_dto = FeeDto::from_subscription_posted_data( $post_data, SubscriptionProductType::REGULAR );
		if ( $regular_fee_dto ) {
			Boot::$regular_products_fee->check_fee( $regular_fee_dto );
		}
	}

	/**
	 * Get fee class.
	 *
	 * @param string $type Type of product.
	 *
	 * @return string
	 */
	public static function get_fee_class( $type ) {
		return SubscriptionProductType::REGULAR === $type ? RegularProductsFee::class : SubscriptionProductsFee::class;
	}

	/**
	 * Get fee handler.
	 *
	 * @param string $type Type of product.
	 *
	 * @return string
	 */
	public static function get_fee_handler( $type ) {
		$class_name = self::get_fee_class( $type );
		return new $class_name();
	}
}
