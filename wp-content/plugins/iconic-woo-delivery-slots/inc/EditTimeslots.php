<?php
/**
 * WDS Thank you page class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;
use WC_Order, WC_Product_Simple;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS Ajax class.
 */
class EditTimeslots {

	/**
	 * Run.
	 *
	 * @return void
	 */
	public static function run() {
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'maybe_display_checkout_fields' ), 10, 1 );
		add_action( 'woocommerce_before_pay_action', array( __CLASS__, 'after_suborder_pay' ) );
		add_action( 'template_redirect', array( __CLASS__, 'dont_show_order_again_button_for_child_orders' ) );

		add_filter( 'woocommerce_thankyou_order_received_text', array( __CLASS__, 'change_order_received_text' ), 10, 2 );
		add_filter( 'iconic_wds_my_account_show_edit_timeslot_button', array( __CLASS__, 'show_edit_timeslot_button' ), 10, 2 );
	}

	/**
	 * Modifies the order received text for sub orders.
	 * This checks if the given order is a sub order. if it is then a translated text is returned, indicating that a new date/timeslot has been updated.
	 *
	 * @param string $text The original order received text.
	 * @param object $order The order object.
	 *
	 * @return string
	 **/
	public static function change_order_received_text( $text, $order ) {
		$parent_order_id = self::is_sub_order( $order );

		if ( empty( $parent_order_id ) ) {
			return $text;
		}

		$parent_order = wc_get_order( $parent_order_id );

		$allowed_tags = array(
			'a' => array(
				'href' => true,
			),
		);

		// Translators: Order ID.
		echo sprintf( esc_html__( 'Thank you. New timeslot has been updated for the order %s', 'jckwds' ), wp_kses( "<a href='{$parent_order->get_view_order_url()}'>#$parent_order_id</a>", $allowed_tags ) );
		return '';
	}

	/**
	 * Prevents the display of the "Order Again" for child orders.
	 *
	 * @global WP_Query $wp The WordPress query object.
	 *
	 * @return void
	 */
	public static function dont_show_order_again_button_for_child_orders() {
		global $wp;

		if ( empty( $wp->query_vars['order-received'] ) ) {
			return;
		}

		$order = wc_get_order( $wp->query_vars['order-received'] );

		if ( self::is_sub_order( $order ) ) {
			remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );
		}
	}

	/**
	 * Consider the Admin settings, Editing window and Order status to determine if the user can update time slots
	 * for the given order.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool
	 */
	public static function can_customer_update_timeslot( $order ) {
		global $iconic_wds;

		if ( empty( $order ) ) {
			return false;
		}

		/**
		 * Customers can update timeslot for orders beloning to these statuses only.
		 *
		 * @since 1.25.0
		 */
		$allowed_order_status = apply_filters( 'iconic_wds_edit_timeslot_statuses', array( 'processing', 'pending', 'on-hold' ) );

		if ( ! current_user_can( 'pay_for_order', $order->get_id() ) || ! in_array( $order->get_status(), $allowed_order_status, true ) ) {
			/**
			 * Can customer update the timeslot.
			 *
			 * @param bool     $can_update Can update.
			 * @param WC_Order $order Order
			 *
			 * @since 1.25.0
			 */
			return apply_filters( 'iconic_wds_can_customer_update_timeslot', false, $order );
		}

		if ( self::has_timeslot_window_passed( $order ) ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'iconic_wds_can_customer_update_timeslot', false, $order );
		}

		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		return apply_filters( 'iconic_wds_can_customer_update_timeslot', true, $order );
	}

	/**
	 * Maybe display checkout fields.
	 *
	 * @param int $order Order.
	 */
	public static function maybe_display_checkout_fields( $order = false ) {
		if ( is_int( $order ) ) {
			$order = wc_get_order( $order );
		}

		// If $order is empty then get order id from URL arguments.
		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			$order_received = get_query_var( 'order-received' );
			$view_order     = get_query_var( 'view-order' );

			if ( empty( $order_id ) && empty( $view_order ) ) {
				return;
			}

			$order_id = $view_order ? $view_order : $order_received;

			$order = wc_get_order( $order_id );
		}

		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		global $iconic_wds;

		if (
			Helpers::is_thankyou_page()
			&&
			'reminders_only' === $iconic_wds->settings['general_customer_display_on_thankyou_page']
			&&
			empty( filter_input( INPUT_GET, 'reminder', FILTER_SANITIZE_SPECIAL_CHARS ) )
		) {
			return;
		}

		if ( Helpers::is_my_account_order_page() && 'allow' !== $iconic_wds->settings['general_customer_display_on_myaccount_order_page'] ) {
			return;
		}

		if ( self::is_sub_order( $order ) ) {
			return;
		}

		$date_manager = new Dates( array( 'order_id' => $order->get_id() ) );

		if ( empty( $date_manager ) ) {
			return;
		}

		$active = $date_manager->is_delivery_slots_allowed();

		if ( ! $active ) {
			return;
		}

		$fields            = $date_manager->get_checkout_fields_data( $order );
		$bookable_dates    = $date_manager->get_upcoming_bookable_dates( Helpers::date_format() );
		$show_save_button  = Helpers::is_thankyou_page() || Helpers::is_my_account_order_page();
		$order_id          = $order->get_id();
		$has_timeslot_data = Order::get_order_date_time( $order );
		$show_update_btn   = self::can_customer_update_timeslot( $order );

		include ICONIC_WDS_PATH . 'templates/edit-timeslot/timeslot-details.php';
	}

	/**
	 * Has timeslot window passed.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool
	 */
	public static function has_timeslot_window_passed( $order ) {
		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		global $iconic_wds;

		$delivery_timestamp    = $order->get_meta( $iconic_wds->timestamp_meta_key );
		$delivery_timeslot     = $order->get_meta( $iconic_wds->timeslot_meta_key );
		$updation_allowed_till = self::get_update_window_in_seconds();
		$current_timestamp     = time();

		// User can always update ASAP orders.
		if ( is_string( $delivery_timeslot ) && 'ASAP' === $delivery_timeslot ) {
			return false;
		}

		if ( is_bool( $updation_allowed_till ) || empty( $delivery_timestamp ) ) {
			return false;
		}

		return $delivery_timestamp - $current_timestamp < $updation_allowed_till;
	}

	/**
	 * Get the number of seconds before which we close the delivery update window.
	 *
	 * @return array
	 */
	public static function get_update_window_in_seconds() {
		$window = Settings::get_timeslot_editing_window();

		if ( empty( $window ) ) {
			return false;
		}

		$map = array(
			'minutes' => 1,
			'hours'   => 60,
			'days'    => 1440,
		);

		$minutes = floatval( $map[ $window['unit'] ] ) * floatval( $window['number'] );

		return $minutes * 60;
	}

	/**
	 * Get WDS fee amount for the given order, returns false if there is not WDS fee.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return float|false
	 */
	public static function get_wds_fee_amount( $order ) {
		global $iconic_wds;

		$fees             = $order->get_fees();
		$search_fee_label = $iconic_wds->fee->get_fee_name();
		foreach ( $fees as $fee ) {
			if ( $search_fee_label === $fee->get_name() ) {
				return $fee->get_amount();
			}
		}

		return false;
	}

	/**
	 * Get fee difference between the newly selected timeslot and previosly selected timeslot (new_fee - prev_fee).
	 *
	 * @param WC_Order $order Previous/Parent Order ID.
	 * @param string   $date  Newly selected date.
	 * @param string   $ymd   Newly selected date in Ymd.
	 * @param string   $time  Newly selected time.
	 *
	 * @return float
	 */
	public static function get_fee_difference( $order, $date, $ymd, $time ) {
		global $iconic_wds;

		$order_fee    = self::get_wds_fee_amount( $order );
		$prev_fee     = empty( $order_fee ) ? 0 : (float) $order_fee;
		$date_manager = new Dates( array( 'order_id' => $order->get_id() ) );
		$timeslot     = $iconic_wds->get_timeslot_data( $time );

		if ( empty( $timeslot ) ) {
			$timeslot = array(
				'value' => '',
			);
		}

		$fee_handler = FeeManager::get_fee_handler( SubscriptionProductType::REGULAR );
		$new_fee = $date_manager->get_calculated_fees_data( $date, $ymd, $timeslot['value'], $fee_handler );

		if ( ! $new_fee ) {
			return array(
				'difference' => 0,
				'prev_fee'   => $prev_fee,
				'new_fee'    => 0,
			);
		}

		$new_fee_sum = array_sum( $new_fee );
		$difference  = $new_fee_sum - $prev_fee;

		return array(
			'difference' => $difference,
			'prev_fee'   => $prev_fee,
			'new_fee'    => $new_fee,
		);
	}

	/**
	 * Create Sub order based on the given Order ID and date/time.
	 *
	 * @param WC_Order $parent_order Parent Order.
	 * @param WC_Order $sub_order  Child Order.
	 * @param string   $date         Date.
	 * @param string   $ymd          Ymd.
	 * @param string   $time         Time.
	 *
	 * @return WC_Order sub order.
	 */
	public static function prepare_sub_order( $parent_order, $sub_order, $date, $ymd, $time ) {
		$diff_arr   = self::get_fee_difference( $parent_order, $date, $ymd, $time );
		$difference = $diff_arr['difference'];

		// Prepare product.
		$product = new WC_Product_Simple();
		$product->set_name( esc_html__( 'Fees Difference', 'jckwds' ) );
		$product->set_price( $difference );

		if ( empty( $sub_order ) ) {
			$sub_order = wc_create_order();
		} else {
			$sub_order->remove_order_items();
		}

		// Prepare Order.
		$sub_order->add_product( $product, 1 );
		$sub_order->set_address( $parent_order->get_address() );
		$sub_order->set_customer_id( $parent_order->get_customer_id() );
		$sub_order->set_status( 'pending' );
		$sub_order->calculate_totals();
		$sub_order->update_meta_data( '_jckwds_parent_order_id', $parent_order->get_id() );

		$sub_order->update_meta_data( 'jckwds_date', $date );
		$sub_order->update_meta_data( 'jckwds_date_ymd', $ymd );

		if ( ! empty( $time ) ) {
			global $iconic_wds;

			$timeslot_id = $iconic_wds->extract_timeslot_id_from_option_value( $time );
			$timeslot    = $iconic_wds->get_timeslot_data( $timeslot_id );

			if ( ! empty( $timeslot ) ) {
				$sub_order->update_meta_data( $iconic_wds->timeslot_meta_key, $timeslot['formatted'] );
				$sub_order->update_meta_data( $iconic_wds->timeslot_meta_key . '_id', $timeslot_id );
				$sub_order->update_meta_data( '_jckwds_timeslot_value', $time );
			}
		}

		$sub_order->save();

		return $sub_order;
	}

	/**
	 * After payment is done for sub order.
	 *
	 * @param sub_order_id $sub_order_id Sub order ID.
	 */
	public static function after_suborder_pay( $sub_order_id ) {
		$sub_order    = wc_get_order( $sub_order_id );
		$parent_order = $sub_order->get_meta( '_jckwds_parent_order_id' );

		if ( empty( $parent_order ) ) {
			return false;
		}

		$data = array(
			'jckwds-delivery-date'     => $sub_order->get_meta( 'jckwds_date' ),
			'jckwds-delivery-date-ymd' => $sub_order->get_meta( 'jckwds_date_ymd' ),
			'jckwds-delivery-time'     => $sub_order->get_meta( '_jckwds_timeslot_value' ),
			'jckwds-date-changed'      => '1',
		);

		Order::update_order_meta( $parent_order, $data );
	}

	/**
	 * Is sub order.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool
	 */
	public static function is_sub_order( $order ) {
		if ( empty( $order ) ) {
			return false;
		}

		return $order->get_meta( '_jckwds_parent_order_id' );
	}

	/**
	 * Get child orders.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return array|false
	 */
	public static function get_child_orders( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		static $child_orders = array();

		if ( array_key_exists( $order->get_id(), $child_orders ) ) {
			return $child_orders[ $order->get_id() ];
		}

		/**
		 * Allowed order statuses for child orders.
		 *
		 * @since 2.0.0
		 */
		$allowed_status = apply_filters( 'iconic_wds_get_child_orders_statuses', array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed' ) );

		$orders = array();

		if ( Helpers::is_cot_enabled() ) {
			$orders = wc_get_orders(
				array(
					'limit'      => 20,
					'return'     => 'ids',
					'status'     => $allowed_status,
					'meta_query' => array(
						array(
							'field' => '_jckwds_parent_order_id',
							'value' => $order->get_id(),
						),
					),
				)
			);
		} else {
			$orders = get_posts(
				array(
					'post_type'      => 'shop_order',
					'posts_per_page' => 20,
					'post_status'    => $allowed_status,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => '_jckwds_parent_order_id',
							'value' => $order->get_id(),
						),
					),
				)
			);
		}

		$child_orders[ $order->get_id() ] = $orders;

		return $child_orders[ $order->get_id() ];
	}

	/**
	 * If the order is more than 1 month old then don't show the button.
	 *
	 * @param bool     $show  Show.
	 * @param WC_Order $order Order.
	 *
	 * @return bool
	 */
	public static function show_edit_timeslot_button( $show, $order ) {
		// if the order is more than 1 month old then don't show the edit timeslot button.
		$month_old = strtotime( '-1 month', time() );
		if ( strtotime( $order->get_date_created() ) < $month_old ) {
			return false;
		}

		return $show;
	}

	/**
	 * Check if current user can edit timeslot for the given order.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $context Context.
	 *
	 * @return bool
	 */
	public static function can_current_user_edit_timeslot( $order, $context ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		if ( 'admin' === $context && current_user_can( 'edit_shop_order', $order->get_id() ) ) {
			return true;
		}

		return self::can_customer_update_timeslot( $order );
	}
}
