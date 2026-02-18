<?php
/**
 * Checkout block integration.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions\CheckoutBlock;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

/**
 * Class CheckoutBlock.
 */
class SubscriptionCheckoutBlock {
	/**
	 * On blocks loaded.
	 */
	public static function on_blocks_loaded() {
		self::extend_store_api();

		add_action( 'woocommerce_blocks_checkout_block_registration', array( __CLASS__, 'register_integration' ) );
	}

	/**
	 * Register integration.
	 *
	 * @param IntegrationRegistry $integration_registry Integration registry.
	 *
	 * @return void
	 */
	public static function register_integration( $integration_registry ) {
		$integration_registry->register( new SubscriptionsCheckoutBlockIntegration() );
	}

	/**
	 * Extends the cart schema to include the delivery slots subscription data.
	 */
	public static function extend_store_api() {
		$extend = \Automattic\WooCommerce\StoreApi\StoreApi::container()->get( \Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
		if ( is_callable( array( $extend, 'register_endpoint_data' ) ) ) {
			$extend->register_endpoint_data(
				array(
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => 'iconic-wds-subscriptions',
					'schema_callback' => array( __CLASS__, 'extend_checkout_schema' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}
	}

	/**
	 * Extends the checkout schema.
	 *
	 * @return array
	 */
	public static function extend_checkout_schema() {
		return array(
			'date_ymd' => array(
				'description' => __( 'Delivery date YMD', 'jckwds' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'optional'    => true,
			),
			'timeslot' => array(
				'description' => __( 'Timeslot', 'jckwds' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'optional'    => true,
			),
		);
	}
}