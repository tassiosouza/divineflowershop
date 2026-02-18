<?php
/**
 * WDS Reservation Table class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use WC_Validation;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Reservation table Class
 *
 * All methods to do with reservations table.
 */
class ReservationTable {

	/**
	 * Output the reservation table.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public static function generate_reservation_table( $args ) {
		ob_start();
		include ICONIC_WDS_PATH . 'templates/reservation-table.php';
		return ob_get_clean();
	}

	/**
	 * Set address to WooCommerce session and calculate shipping.
	 *
	 * @throws Exception When some data is invalid.
	 */
	public static function calculate_shipping() {
		try {
			WC()->shipping()->reset_shipping();

			$address = array();

			$address['country']  = Helpers::get_filtered_input( 'calc_shipping_country' );
			$address['state']    = Helpers::get_filtered_input( 'calc_shipping_state' );
			$address['postcode'] = Helpers::get_filtered_input( 'calc_shipping_postcode' );
			$address['city']     = Helpers::get_filtered_input( 'calc_shipping_city' );

			$address = apply_filters( 'woocommerce_cart_calculate_shipping_address', $address );

			if ( $address['postcode'] && ! WC_Validation::is_postcode( $address['postcode'], $address['country'] ) ) {
				throw new Exception( __( 'Please enter a valid postcode / ZIP.', 'woocommerce' ) );
			} elseif ( $address['postcode'] ) {
				$address['postcode'] = wc_format_postcode( $address['postcode'], $address['country'] );
			}

			if ( $address['country'] ) {
				if ( ! WC()->customer->get_billing_first_name() ) {
					WC()->customer->set_billing_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
				}
				WC()->customer->set_shipping_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
			} else {
				WC()->customer->set_billing_address_to_base();
				WC()->customer->set_shipping_address_to_base();
			}

			WC()->customer->set_calculated_shipping( true );
			WC()->customer->save();

			do_action( 'woocommerce_calculated_shipping' );

			return true;
		} catch ( Exception $e ) {
			if ( ! empty( $e ) ) {
				return false;
			}
		}
	}

	/**
	 * Get reserved slot data that is passed to reservation table on page load.
	 *
	 * @return string
	 */
	public static function get_reserved_slot_formatted() {
		global $iconic_wds;

		$reservation = $iconic_wds->has_reservation();

		if ( empty( $reservation ) ) {
			return false;
		}

		$result = wp_date( 'D, jS M', strtotime( $reservation->date ) );

		// If timeslot not enabled, return only the formatted date.
		if ( '0' !== $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) {
			$start_time  = $iconic_wds->format_time( $reservation->starttime, 'Hi' );
			$endtime     = $iconic_wds->format_time( $reservation->endtime, 'Hi' );
			$time_string = $start_time !== $endtime ? sprintf( '%s - %s', $start_time, $endtime ) : $start_time;
			$result      = $result . ' @ ' . $time_string;
		}

		return $result;
	}

	/**
	 * Add "fee" and "fee_formatted" to bookable dates.
	 *
	 * @param array $bookable_dates Bookable dates.
	 *
	 * @return array.
	 */
	public static function add_fees_to_dates( $bookable_dates ) {
		global $iconic_wds;

		foreach ( $bookable_dates as &$bookable_date ) {
			$bookable_date['fee'] = 0;

			if ( ! empty( $bookable_date['same_day'] ) ) {
				$bookable_date['fee'] += floatval( $iconic_wds->settings['datesettings_fees_same_day'] );
			}

			if ( empty( $bookable_date['next_day'] ) ) {
				$bookable_date['fee'] += floatval( $iconic_wds->settings['datesettings_fees_next_day'] );
			}

			if ( ! empty( $iconic_wds->settings['datesettings_fees_days'][ $bookable_date['weekday_number'] ] ) ) {
				$bookable_date['fee'] += floatval( $iconic_wds->settings['datesettings_fees_days'][ $bookable_date['weekday_number'] ] );
			}

			$bookable_date['fee_formatted'] = ( 0 === intval( $bookable_date['fee'] ) ) ? esc_html__( 'Free', 'jckwds' ) : wc_price( $bookable_date['fee'] );
		}

		return $bookable_dates;
	}

	/**
	 * Get Timeslot data.
	 *
	 * @param array $batch_dates     Dates for which timeslots are requested.
	 * @param array $shipping_method Shipping method.
	 *
	 * @return array
	 */
	public static function get_timeslot_data( $batch_dates, $dates_manager ) {
		global $iconic_wds;

		// If timeslots are not enabled.
		if ( '1' !== $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) {
			return apply_filters( 'iconic_wds_reservation_table_get_timeslot_data', array(), $batch_dates );
		}

		foreach ( $batch_dates as $date ) {
			$timeslot_output[ $date['ymd'] ] = $dates_manager->slots_available_on_date( $date['ymd'] );
		}

		/**
		 * Filter to modify timeslot data for the reservation table.
		 *
		 * @since 1.16.0
		 */
		return apply_filters( 'iconic_wds_reservation_table_get_timeslot_data', $timeslot_output, $batch_dates );
	}

	/**
	 * Get timeslots for the selected shipping methods.
	 *
	 * @param bool $shipping_methods Selected shipping methods.
	 *
	 * @return array|false
	 */
	public static function get_timeslot_data_by_shipping_method( $shipping_methods = false ) {
		global $iconic_wds;

		$timeslots = $iconic_wds->get_timeslot_data();

		if ( ! is_array( $shipping_methods ) || empty( $shipping_methods ) ) {
			return $timeslots;
		}

		$result = array();
		foreach ( $timeslots as $timeslot ) {
			$intersect = array_intersect( $timeslot['shipping_methods'], $shipping_methods );
			if ( ! in_array( 'any', $timeslot['shipping_methods'], true ) && empty( $intersect ) ) {
				continue;
			}

			$result[] = $timeslot;
		}

		return $result;
	}
}
