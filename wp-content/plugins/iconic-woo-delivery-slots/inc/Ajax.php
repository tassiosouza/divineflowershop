<?php
/**
 * WDS Ajax class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS Ajax class.
 */
class Ajax {
	/**
	 * Init
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// Example: `iconic_wds_{event} => nopriv`.
		$ajax_events = array(
			'get_chosen_shipping_method'   => true,
			'reserve_slot'                 => true,
			'remove_reserved_slot'         => true,
			'get_slots_on_date'            => true,
			'get_slots_on_date_json'       => true,
			'get_upcoming_bookable_dates'  => true,
			'get_reserved_slot'            => true,
			'get_address_shipping_methods' => true,
			'get_reservation_table_data'   => true,
			'check_fee_difference'         => true,
			'create_sub_order'             => true,
			'update_order_slot'            => true,
			'get_all_shipping_methods'     => false,
			'delete_order_slot'            => false,
			'admin_delete_order_slot'      => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_iconic_wds_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_iconic_wds_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get chosen shipping method
	 */
	public static function get_chosen_shipping_method() {
		$data = array(
			'chosen_method' => Iconic_WDS::get_chosen_shipping_method(),
		);

		wp_send_json( $data );
	}

	/**
	 * Reserve a slot
	 */
	public static function reserve_slot() {
		global $iconic_wds, $iconic_wds_dates;

		$slot_id          = filter_input( INPUT_POST, 'slot_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$slot_date        = filter_input( INPUT_POST, 'slot_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$slot_start_time  = filter_input( INPUT_POST, 'slot_start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$slot_end_time    = filter_input( INPUT_POST, 'slot_end_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$timeslot_enabled = filter_input( INPUT_POST, 'timeslot_enabled', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$date_time        = false;

		if ( 'false' === $timeslot_enabled ) {
			$slot_start_time = '0000';
			$slot_end_time   = '0000';
			$slot_id         = $slot_date;

			$is_available = $iconic_wds_dates->get_orders_remaining_for_day( $slot_date );

			if ( ! $is_available ) {
				wp_send_json( array( 'success' => false ) );
			}
		} else {

			// Check if slot is still available before reserving it.
			$slot_id_exploded = explode( '_', $slot_id );

			if ( ! isset( $slot_id_exploded[1] ) ) {
				wp_send_json_error();
			}

			$timeslot        = $iconic_wds->get_timeslot_data( $slot_id_exploded[1] );
			$slots_available = $iconic_wds->get_slots_available_count( array( $timeslot ), $slot_date );
			$is_available    = ! empty( $slots_available[ $slot_id_exploded[1] ] );

			if ( empty( $timeslot ) ) {
				wp_send_json_error();
			}

			if ( ! $is_available ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Slot not available.', 'jckwds' ),
					)
				);
			}

			$date_time = sprintf( '%s|%s', $slot_id_exploded[1], $timeslot['fee']['value'] );
		}

		$date_formatted = date_i18n( Helpers::date_format(), strtotime( $slot_date ) );
		$iconic_wds->fee->update_fees_session( $date_formatted, $slot_date, $date_time );

		$iconic_wds->add_reservation(
			array(
				'datetimeid' => $slot_id,
				'date'       => $slot_date,
				'starttime'  => $slot_start_time,
				'endtime'    => $slot_end_time,
				'asap'       => strpos( $slot_id, 'asap' ) !== false,
			)
		);

		wp_send_json( array( 'success' => true ) );
	}

	/**
	 * Remove a reserved slot
	 */
	public static function remove_reserved_slot() {
		global $wpdb, $jckwds;

		$wpdb->delete(
			$jckwds->reservations_db_table_name,
			array(
				'processed' => 0,
				'user_id'   => $jckwds->user_id,
			),
			array(
				'%d',
				'%s',
			)
		);

		wp_send_json( array( 'success' => true ) );
	}

	/**
	 * Get available timeslots on posted date
	 *
	 * Date format is always Ymd to cater for multiple languages. This
	 * is set when a date is selected via the datepicker script
	 */
	public static function get_slots_on_date() {
		global $iconic_wds, $iconic_wds_dates;

		$response = array(
			'success'     => false,
			'reservation' => false,
		);

		check_ajax_referer( Iconic_WDS::$slug, 'nonce' );

		if ( empty( $_POST['date'] ) ) {
			wp_send_json( $response );
		}

		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$dates_manager = $iconic_wds_dates;

		if ( ! empty( $order_id ) ) {
			$dates_manager = new Dates( array( 'order_id' => $order_id ) );

			if ( empty( $dates_manager ) ) {
				wp_send_json( $response );
			}
		}

		$posted_date = filter_input( INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$timeslots   = $dates_manager->slots_available_on_date( $posted_date );

		if ( $timeslots ) {
			$response['success'] = true;

			$response['html'] = '';

			$available_slots = array();

			foreach ( $timeslots as $timeslot ) {
				$response['html'] .= '<option value="' . esc_attr( $timeslot['value'] ) . '">' . $timeslot['formatted_with_fee'] . '</option>';
			}

			$response['slots'] = $timeslots;
		}

		$reservation = $iconic_wds->get_reserved_slot( $order_id );

		if ( $reservation && ! empty( $reservation['time'] ) ) {
			$response['reservation'] = $reservation['time']['value'];
		}

		wp_send_json( $response );
	}

	/**
	 * Get upcoming bookable dates
	 */
	public static function get_upcoming_bookable_dates() {
		global $iconic_wds_dates;

		$format = filter_input( INPUT_POST, 'format' ) ? filter_input( INPUT_POST, 'format' ) : Helpers::date_format();

		$response = array(
			'success'        => true,
			'bookable_dates' => $iconic_wds_dates->get_upcoming_bookable_dates( $format ),
		);

		wp_send_json( $response );
	}

	/**
	 * Get the reserved slot for Reservation table shortcode.
	 */
	public static function get_reserved_slot() {
		global $jckwds;

		$reserved = $jckwds->get_reserved_slot();

		if ( $reserved ) {
			wp_send_json_success( $reserved );
		}

		wp_send_json_error();
	}

	/**
	 * Reservaion table: Get shipping methods based the address provided.
	 */
	public static function get_address_shipping_methods() {
		global $iconic_wds;

		check_ajax_referer( Iconic_WDS::$slug, 'nonce' );

		$country  = filter_input( INPUT_POST, 'calc_shipping_country', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$state    = filter_input( INPUT_POST, 'calc_shipping_state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$city     = filter_input( INPUT_POST, 'calc_shipping_city', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$postcode = filter_input( INPUT_POST, 'calc_shipping_postcode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$data = Helpers::get_allowed_shipping_methods_for_address( $country, $state, $city, $postcode );

		if ( empty( $data['shipping_methods'] ) ) {
			wp_send_json_error( $data );
		}

		// Start session if it doesn't already exist.
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );

			// If new session has been created then we need to generate a new nonce.
			$data['new_nonce'] = wp_create_nonce( Iconic_WDS::$slug );
		}

		$customer = WC()->customer;
		$customer->set_shipping_country( $country );
		$customer->set_shipping_state( $state );
		$customer->set_shipping_city( $city );
		$customer->set_shipping_postcode( $postcode );
		$customer->save();

		ReservationTable::calculate_shipping();

		WC()->session->set( 'shipping_method_counts', array( count( $data['shipping_methods'] ) ) );
		WC()->session->set( 'previous_shipping_methods', array( array_keys( $data['shipping_methods'] ) ) );

		wp_send_json_success( $data );
	}

	/**
	 * Load reservation table for the given shipping method.
	 */
	public static function get_reservation_table_data() {
		global $iconic_wds;

		check_ajax_referer( Iconic_WDS::$slug, 'nonce' );

		$shipping_method = (array) filter_input( INPUT_POST, 'shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$context         = filter_input( INPUT_POST, 'context', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$date_offset     = filter_input( INPUT_POST, 'ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $shipping_method ) ) {
			wp_send_json_error();
		}

		$old_shipping_method = WC()->session->get( 'chosen_shipping_methods' );

		$dates_manager = new Dates( array( 'shipping_method' => $shipping_method[0] ) );

		// Save shipping method in the session.
		WC()->session->set( 'chosen_shipping_methods', $shipping_method );

		self::opt_out_if_shipping_method_not_allowed( $shipping_method, $old_shipping_method );

		// Get bookable dates.
		$ignore_slots   = ! (bool) $iconic_wds->settings['reservations_reservations_hide_unavailable_dates'];
		$ignore_slots   = 'admin' === $context ? false : $ignore_slots;
		$bookable_dates = $dates_manager->get_upcoming_bookable_dates( 'array', $ignore_slots );
		$bookable_dates = ReservationTable::add_fees_to_dates( $bookable_dates );
		$bookable_dates = Helpers::remove_duplicates_in_assoc_by_key( $bookable_dates, 'ymd' );

		$timeslot_output = array();
		$batch_dates     = array();

		/*
		For the first 10 bookable dates fetch the timeslot as well.
		$batch_dates = First 10 bookable dates. If offset is present
		then next 10 dates counting from the offset.
		*/
		if ( empty( $date_offset ) ) {
			$batch_dates = array_slice( $bookable_dates, 0, 10 );
		} else {
			$offset_array_index = false;
			foreach ( $bookable_dates as $index => $bookable_date ) {
				if ( $date_offset === $bookable_date['ymd'] ) {
					$offset_array_index = $index;
				}
			}

			$offset_array_index = $offset_array_index ? $offset_array_index : 0;
			$batch_dates        = array_slice( $bookable_dates, $offset_array_index, 10 );
		}

		$timeslot_output = ReservationTable::get_timeslot_data( $batch_dates, $dates_manager );

		wp_send_json_success(
			array(
				'dates'                => $bookable_dates,
				'timeslots'            => $timeslot_output,
				'shipping_method_type' => 'delivery' === Helpers::get_shipping_method_type( $shipping_method[0] ) ? esc_html_x( 'Delivery', 'Reservation calendar', 'jckwds' ) : esc_html_x( 'Collection', 'Reservation calendar', 'jckwds' ),
				'message'              => empty( $bookable_dates ) ? esc_html__( 'No bookable dates found.', 'jckwds' ) : '',
			)
		);
	}

	/**
	 * Check Allowed shipping methods in WDS Settings > general tab
	 * Opt out if not an allowed shipping method.
	 *
	 * @param array $new_shipping_method New Shipping method.
	 * @param array $old_shipping_method Old Shipping method.
	 */
	public static function opt_out_if_shipping_method_not_allowed( $new_shipping_method, $old_shipping_method ) {
		global $iconic_wds;

		$allowed_shipping_methods = $iconic_wds->settings['general_setup_shipping_methods'];

		if ( in_array( 'any', $allowed_shipping_methods, true ) || in_array( $new_shipping_method[0], $allowed_shipping_methods, true ) ) {
			return;
		}

		$message = sprintf(
			// Translators: Optional string for timeslot.
			__( 'This shipping method does not require a date %s to be selected.', 'jckwds' ),
			$iconic_wds->settings['timesettings_timesettings_setup_enable'] ? esc_html__( 'or time slot', 'jckwds' ) : '',
			wc_get_page_permalink( 'shop' )
		);

		wp_send_json_success(
			array(
				'message'                               => $message,
				'datetime_required_for_selected_method' => false,
				'dates'                                 => array(),
			)
		);
		wp_die();
	}

	/**
	 * Get all shipping methods.
	 */
	public static function get_all_shipping_methods() {
		global $iconic_wds;
		$all_shipping_method_options = $iconic_wds->get_shipping_method_options();
		wp_send_json_success( $all_shipping_method_options );
	}

	/**
	 * Admin: Update slot for the given order.
	 *
	 * @return void
	 */
	public static function update_order_slot() {
		global $iconic_wds;

		check_ajax_referer( $iconic_wds::$slug, 'security' );

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order    = wc_get_order( $order_id );
		$response = array();
		$context  = filter_input( INPUT_POST, 'context', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$data     = array();

		if ( ! EditTimeslots::can_current_user_edit_timeslot( $order, $context ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Cannot update the order.', 'jckwds' ),
				)
			);
		}

		$date_ymd = filter_input( INPUT_POST, 'date_ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$date = new DateTime( $date_ymd, wp_timezone() );

		if ( ! $date ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid date.', 'jckwds' ),
				)
			);
		}

		$fees = (float) filter_input( INPUT_POST, 'fee', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$data = array(
			'jckwds-delivery-date'     => wp_date( Helpers::date_format(), $date->getTimestamp() ),
			'jckwds-delivery-date-ymd' => filter_input( INPUT_POST, 'date_ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			'jckwds-delivery-time'     => filter_input( INPUT_POST, 'timeslot', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			'jckwds-date-changed'      => '1',
			'shipping_method'          => filter_input( INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
		);
		
		Order::update_order_meta( $order_id, $data );

		$order->update_meta_data( '_jckwds_timeslot_id', filter_input( INPUT_POST, 'timeslot', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		$order->update_meta_data( '_jckwds_override_rules', filter_input( INPUT_POST, 'override_rules', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

		// Add/Update fees.
		$order = Admin::save_fee_to_order( $fees, $order );

		$order->calculate_taxes();
		$order->calculate_totals( false );
		$order->save();

		/**
		 * Fired after timeslot is updated from the frontend as well as from the admin dashboard.
		 *
		 * @param WC_Order $order   Order.
		 * @param string   $context If the timeslot is updated from the admin dashboard it is 'admin'
		 * else 'frontend' if updated by customer from the frontend.
		 *
		 * @since 1.25.0
		 */
		do_action( 'iconic_wds_order_timeslot_updated', $order, $context );

		ob_start();
		include dirname( WC_PLUGIN_FILE ) . '/includes/admin/meta-boxes/views/html-order-items.php';
		$response['html'] = ob_get_clean();

		wp_send_json_success( $response );
	}

	/**
	 * Delete timeslot for an order.
	 */
	public static function admin_delete_order_slot() {
		check_ajax_referer( 'order-item', 'security' );

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		if ( empty( $order_id ) ) {
			wp_send_json_error();
		}

		$order    = wc_get_order( $order_id );
		$response = array();

		Order::cancel_order( $order_id );

		// Delete fee.
		$order = Admin::save_fee_to_order( 0, $order );

		ob_start();
		include dirname( WC_PLUGIN_FILE ) . '/includes/admin/meta-boxes/views/html-order-items.php';
		$response['html'] = ob_get_clean();

		wp_send_json_success( $response );
	}

	/**
	 * Check Fee difference.
	 *
	 * @return void
	 */
	public static function check_fee_difference() {
		$date     = filter_input( INPUT_POST, 'jckwds-delivery-date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$ymd      = filter_input( INPUT_POST, 'jckwds-delivery-date-ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$time     = filter_input( INPUT_POST, 'jckwds-delivery-time', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $order_id ) || empty( $ymd ) || empty( $date ) ) {
			wp_send_json_error();
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			wp_send_json_error();
		}

		global $iconic_wds;

		$difference_arr     = EditTimeslots::get_fee_difference( $order, $date, $ymd, $time );
		$prev_fee           = $difference_arr['prev_fee'];
		$new_fee            = $difference_arr['new_fee'];
		$new_fee_sum        = array_sum( $new_fee );
		$difference         = $difference_arr['difference'];
		$threshold          = (float) $iconic_wds->settings['general_customer_threshold'];
		$charge_fee_enabled = '1' === $iconic_wds->settings['general_customer_charge_fee_difference'];

		if ( ! $charge_fee_enabled || $difference <= $threshold || $difference < 0 ) {
			Order::update_order_meta(
				$order->get_id(),
				array(
					'jckwds-date-changed' => 1,
				)
			);

			$timeslot = $iconic_wds->get_timeslot_data( $time );

			wp_send_json_success(
				array(
					'slot_updated'   => true,
					'success_notice' => esc_html__( 'Your delivery slot has been updated!', 'jckwds' ),
					'new_timeslot'   => sprintf( '%s %s', $date, ! empty( $timeslot ) ? $timeslot['formatted'] : '' ),
				)
			);
		}

		// Translators: fee difference to be paid.
		$msg  = sprintf( esc_html__( 'This slot costs an additional %s', 'jckwds' ), wc_price( $difference ) );
		$html = sprintf( '<div class="wds-edit-slot-popup__msg"><div class="wds-edit-slot-popup__msg-text">%s</div></div>', $msg );

		wp_send_json_success(
			array(
				'html'            => $html,
				'show_pay_button' => true,
			)
		);
	}

	/**
	 * Create sub order.
	 */
	public static function create_sub_order() {
		$date     = filter_input( INPUT_POST, 'jckwds-delivery-date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$ymd      = filter_input( INPUT_POST, 'jckwds-delivery-date-ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$time     = filter_input( INPUT_POST, 'jckwds-delivery-time', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $order_id ) || empty( $ymd ) || empty( $date ) ) {
			wp_send_json_error();
		}

		$parent_order = wc_get_order( $order_id );
		$session_key  = 'jckwds_sub_order_' . $parent_order->get_id();
		$session_data = WC()->session->get( $session_key );

		// Check if order already exists.
		if ( ! empty( $session_data ) && ! empty( $session_data['sub_order_id'] ) ) {
			$sub_order = wc_get_order( $session_data['sub_order_id'] );

			// Check if payment is already made for this sub order.
			if ( ! empty( $sub_order ) && 'pending' === $sub_order->get_status() ) {
				EditTimeslots::prepare_sub_order( $parent_order, $sub_order, $date, $ymd, $time );

				wp_send_json_success(
					array(
						'payment_url' => $sub_order->get_checkout_payment_url(),
					)
				);
			}
		}

		$sub_order = EditTimeslots::prepare_sub_order( $parent_order, null, $date, $ymd, $time );

		WC()->session->set(
			$session_key,
			array(
				'parent_order_id' => $parent_order->get_id(),
				'ymd'             => $ymd,
				'time'            => $time,
				'sub_order_id'    => $sub_order->get_id(),
			)
		);

		wp_send_json_success(
			array(
				'payment_url' => $sub_order->get_checkout_payment_url(),
			)
		);
	}
}
