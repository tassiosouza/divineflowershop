<?php
/**
 * Checkout block integration.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

/**
 * Class CheckoutBlock.
 */
class CheckoutBlock {
	/**
	 * On blocks loaded.
	 */
	public static function on_blocks_loaded() {
		self::extend_store_api();
		add_action( 'woocommerce_blocks_checkout_block_registration', array( __CLASS__, 'register_integration' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'save_delivery_slot_information_on_checkout' ), 10, 2 );

		add_filter( 'render_block', array( __CLASS__, 'maybe_add_wds_block_to_checkout' ), 10, 2 );
	}

	/**
	 * Add WDS block to checkout.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block         Block.
	 *
	 * @return string
	 */
	public static function maybe_add_wds_block_to_checkout( $block_content, $block ) {
		// No need to add the block if it already exists.
		if ( Helpers::checkout_page_has_wds_block() ) {
			return $block_content;
		}

		$position = self::get_block_position();

		if ( ! $position || $block['blockName'] !== $position['block'] ) {
			return $block_content;
		}

		if ( 'before' === $position['position'] ) {
			return self::get_wds_block_html() . $block_content;
		}

		if ( 'after' === $position['position'] ) {
			return $block_content . self::get_wds_block_html();
		}

		return $block_content;
	}

	/**
	 * Get block position.
	 *
	 * @return array
	 */
	public static function get_block_position() {
		global $iconic_wds;

		$position = array();

		switch ( $iconic_wds->settings['general_setup_position'] ) {
			case 'woocommerce_checkout_before_customer_details':
				$position['block']    = 'woocommerce/checkout-contact-information-block';
				$position['position'] = 'before';
				break;

			case 'woocommerce_checkout_billing':
				$position['block']    = 'woocommerce/checkout-billing-address-block';
				$position['position'] = 'after';
				break;

			case 'woocommerce_checkout_shipping':
				$position['block']    = 'woocommerce/checkout-shipping-address-block';
				$position['position'] = 'after';
				break;

			case 'woocommerce_checkout_after_customer_details':
				$position['block']    = 'woocommerce/checkout-contact-information-block';
				$position['position'] = 'after';
				break;

			case 'woocommerce_checkout_before_order_review':
				$position['block']    = 'woocommerce/checkout-order-summary-cart-items-block';
				$position['position'] = 'after';
				break;

			case 'woocommerce_checkout_order_review':
				$position['block']    = 'woocommerce/checkout-order-summary-cart-items-block';
				$position['position'] = 'after';
				break;

			case 'woocommerce_checkout_after_order_review':
				$position['block']    = 'woocommerce/checkout-order-summary-block';
				$position['position'] = 'after';
				break;

			case 'add_manually':
				return false;
		}

		return $position;
	}

	/**
	 * Get WDS block HTML.
	 *
	 * @return string
	 */
	public static function get_wds_block_html() {
		return '<div data-block-name="iconic-wds/iconic-wds" class="wp-block-iconic-wds-iconic-wds"></div>';
	}

	/**
	 * Register integration.
	 *
	 * @param IntegrationRegistry $integration_registry Integration registry.
	 *
	 * @return void
	 */
	public static function register_integration( $integration_registry ) {
		$integration_registry->register( new CheckoutBlockIntegration() );
	}

	/**
	 * Extends the cart schema to include the delivery slots data.
	 */
	public static function extend_store_api() {
		$extend = \Automattic\WooCommerce\StoreApi\StoreApi::container()->get( \Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
		if ( is_callable( array( $extend, 'register_endpoint_data' ) ) ) {
			$extend->register_endpoint_data(
				array(
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => 'iconic-wds',
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
			'date'     => array(
				'description' => __( 'Delivery date', 'jckwds' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'optional'    => true,
			),
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

	/**
	 * Save delivery slot information on checkout.
	 *
	 * @param \WC_Order        $order   Order.
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return void
	 */
	public static function save_delivery_slot_information_on_checkout( \WC_Order $order, \WP_REST_Request $request ) {
		if ( empty( $request['extensions']['iconic-wds'] ) ) {
			return;
		}

		$dates_manager = new Dates(
			array(
				'order_id' => $order->get_id(),
			)
		);

		$allowed = $dates_manager->is_delivery_slots_allowed();

		if ( ! $allowed ) {
			return;
		}

		$request_data = $request['extensions']['iconic-wds'];

		$date     = isset( $request_data['date'] ) ? $request_data['date'] : '';
		$date_ymd = isset( $request_data['date_ymd'] ) ? $request_data['date_ymd'] : '';
		$timeslot = isset( $request_data['timeslot'] ) ? $request_data['timeslot'] : '';

		if ( empty( $date ) || empty( $date_ymd ) ) {
			return;
		}

		$data         = array(
			'jckwds-delivery-date'     => $date,
			'jckwds-delivery-date-ymd' => $date_ymd,
			'jckwds-delivery-time'     => $timeslot,
		);

		Order::update_order_meta( $order, $data );
	}
}
