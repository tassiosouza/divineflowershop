<?php
/**
 * WDS checkout class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS checkout class.
 */
class Checkout {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'checkout_fields' ), 10, 1 );
		add_filter( 'woocommerce_checkout_posted_data', array( __CLASS__, 'checkout_posted_data' ) );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'catch_shipping' ), 10, 2 );
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'classic_checkout_process' ), 10 );
		add_action( 'woocommerce_checkout_validate_order_before_payment', array( __CLASS__, 'block_checkout_process' ), 10, 2 );
		add_action( 'woocommerce_checkout_update_order_meta', array( Order::class, 'update_order_meta' ), 10, 2 );
	}

	/**
	 * Register checkout fields for processing (not display).
	 *
	 * @param array $fields Checkout Fields.
	 *
	 * @return array
	 */
	public static function checkout_fields( $fields ) {
		global $iconic_wds_dates, $iconic_wds;

		if ( $iconic_wds->has_subscription_product_in_cart() ) {
			return $fields; // nosemgrep
		}

		if ( empty( $iconic_wds_dates ) ) {
			return $fields; // nosemgrep
		}

		$checkout_fields_data = $iconic_wds_dates->get_checkout_fields_data();

		if ( empty( $checkout_fields_data ) ) {
			return $fields; // nosemgrep
		}

		$fields['jckwds'] = array();

		foreach ( $checkout_fields_data as $key => $data ) {
			$fields['jckwds'][ $key ] = array(
				'type'     => 'text',
				'label'    => $data['field_args']['label'],
				'required' => $data['field_args']['required'],
			);
		}

		return $fields; // nosemgrep
	}

	/**
	 * Check if shipping is not set when it should be during checkout process.
	 * Prevents orders with empty delivery date.
	 *
	 * @param array    $data   Posts data.
	 * @param WP_Error $errors Errors object.
	 */
	public static function catch_shipping( $data, $errors ) {
		// We don't need to check our virtual setting (Helpers::needs_shipping()),
		// as virtual products don't actually need a shipping method. We only want to check if it
		// *really* needs shipping applied.
		if ( ! WC()->cart->needs_shipping() ) {
			return;
		}

		// If shipping error already exists, do nothing.
		// If shipping method is not empty, do nothing.
		if ( ! empty( $errors->errors['shipping'] ) || ! empty( $data['shipping_method'] ) ) {
			return;
		}

		// Otherwise, throw error and prompt update checkout trigger.
		wc_add_notice(
			__( 'Please select a shipping method.', 'jckwds' ),
			'error',
			array(
				'iconic-wds-update-checkout' => true,
			)
		);
	}

	/**
	 * Remove fields if they are hidden based on shipping method.
	 *
	 * @param array $data Posted Data.
	 *
	 * @return mixed
	 */
	public static function checkout_posted_data( $data ) {
		$fields_hidden = (bool) Helpers::get_filtered_input( 'iconic-wds-fields-hidden' );

		if ( $fields_hidden ) {
			unset( $data['jckwds-delivery-date'], $data['jckwds-delivery-date-ymd'], $data['jckwds-delivery-time'] );

			return $data;
		}

		// Remove 0 value so it is seen as empty.
		$data['jckwds-delivery-time'] = empty( $data['jckwds-delivery-time'] ) ? '' : $data['jckwds-delivery-time'];

		return $data;
	}

	/**
	 * Validate checkout fields.
	 */
	public static function classic_checkout_process() {
		$date_fields_hidden = filter_input( INPUT_POST, 'iconic-wds-fields-hidden', FILTER_SANITIZE_NUMBER_INT );
		$ymd                = filter_input( INPUT_POST, 'jckwds-delivery-date-ymd', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$time               = filter_input( INPUT_POST, 'jckwds-delivery-time', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $ymd ) || false === $date_fields_hidden || ! empty( $date_fields_hidden ) ) {
			return;
		}

		$errors = self::get_checkout_validation_errors( $ymd, $time );
		if ( is_wp_error( $errors ) ) {
			wc_add_notice( $errors->get_error_message(), 'error', $errors->get_error_data() );
			return;
		}
	}

	/**
	 * Validate checkout fields for blocks checkout.
	 */
	public static function block_checkout_process( $order, $validation_errors ) {
		global $iconic_wds;

		$ymd                = $order->get_meta( 'jckwds_date_ymd' );
		$time               = $order->get_meta( 'jckwds_timeslot_id' );

		// This doesn't need to run if there are subscription products in the cart.
		// The validation is handled by the subscription checkout handler.
		$subscription_products = $iconic_wds->has_subscription_product_in_cart();
		if ( $subscription_products ) {
			return;
		}

		$dates_manager = new Dates(
			array(
				'order_id' => $order->get_id(),
			)
		);

		$allowed = $dates_manager->is_delivery_slots_allowed();
		if ( ! $allowed ) {
			return $validation_errors;
		}

		$errors = self::get_checkout_validation_errors( $ymd, $time );
		if ( is_wp_error( $errors ) ) {
			$validation_errors->add( 'error', $errors->get_error_message(), $errors->get_error_data() );
			return $validation_errors;
		}

		return $validation_errors;
	}

	/**
	 * Get checkout validation errors.
	 *
	 * @param string $ymd Delivery date.
	 * @param string $time Delivery time.
	 *
	 * @return WP_Error|null
	 */
	public static function get_checkout_validation_errors( $ymd, $time ) {
		global $iconic_wds, $iconic_wds_dates;

		$max_calculation_method = $iconic_wds->settings['general_setup_max_order_calculation_method'];
		$date_field_mandatory   = $iconic_wds->settings['datesettings_datesettings_setup_mandatory'];
		$timeslot_mandatory     = $iconic_wds->settings['timesettings_timesettings_setup_mandatory'];

		if ( $date_field_mandatory && empty( $ymd ) ) {
			return new WP_Error(
				'iconic_wds_date_not_available',
				__( 'Please select a delivery date.', 'jckwds' ),
			);
		}

		if ( $timeslot_mandatory && empty( $time ) ) {
			return new WP_Error(
				'iconic_wds_date_not_available',
				__( 'Please select a time slot.', 'jckwds' ),
				array(
					'iconic-wds-clear-time' => true,
				)
			);
		}

		// No validation for empty date.
		if ( empty( $ymd ) ) {
			return;
		}

		$orders_remaining_for_day = $iconic_wds_dates->get_orders_remaining_for_day( $ymd );

		$expires = strtotime( '+10 minutes', time() );

		// Ensure number of products in the cart are under the day's limit (max order).
		if ( 'products' === $max_calculation_method && Helpers::get_cart_count() > $iconic_wds_dates->get_orders_remaining_for_day( $ymd ) ) {
			if ( empty( $orders_remaining_for_day ) ) {
				return new WP_Error(
					'iconic_wds_date_not_available',
					__( 'Sorry, the selected date is no longer available.', 'jckwds' ),
					array(
						'iconic-wds-clear-date' => true,
					)
				);
			} else {
				return new WP_Error(
					'iconic_wds_max_order_quantity',
					sprintf( __( 'Sorry, we cannot accept a quantity of more than %d for the selected date. Please change the date or reduce the purchase quantity.', 'jckwds' ), $orders_remaining_for_day ),
					array(
						'iconic-wds-clear-date' => true,
					)
				);
			}
		}

		// Check if date is booked up.
		if ( 'orders' === $max_calculation_method && ! $orders_remaining_for_day ) {
			return new WP_Error(
				'iconic_wds_date_not_available',
				__( 'Sorry, the selected date is no longer available.', 'jckwds' ),
				array(
					'iconic-wds-clear-date' => true,
					'iconic-wds-clear-time' => true,
				)
			);
		}

		// Check if selected date is allowed based on same & next day cutoff time.
		$date_formatted = date_i18n( 'D, jS M', strtotime( $ymd ) );
		$same_day_date  = $iconic_wds_dates->get_same_day_date( 'D, jS M' );
		$next_day_date  = $iconic_wds_dates->get_next_day_date( 'D, jS M' );

		if (
			( $date_formatted === $same_day_date && $iconic_wds_dates->is_same_day_allowed() === $date_formatted )
			||
			( $date_formatted === $next_day_date && $iconic_wds_dates->is_next_day_allowed() === $date_formatted )
		) {
			return new WP_Error(
				'iconic_wds_date_not_available',
				__( 'Sorry, the selected date is no longer available.', 'jckwds' ),
				array(
					'iconic-wds-clear-time' => true,
				)
			);
		}

		// These conditions only apply if time slots are enabled and selected.
		if ( empty( $time ) || ! $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) {
			// Add 10 minute reservation to prevent double booking.
			$iconic_wds->add_reservation(
				array(
					'datetimeid' => $ymd,
					'date'       => Helpers::convert_date_for_database( $ymd ),
					'processed'  => 0,
					'expires'    => $expires,
				)
			);

			return;
		}

		// Check if date has any slots available.
		$available_slots = $iconic_wds_dates->slots_available_on_date( $ymd );

		if ( empty( $available_slots ) ) {
			return new WP_Error(
				'iconic_wds_date_not_available',
				__( 'Sorry, there are no longer any slots available on the selected date.', 'jckwds' ),
				array(
					'iconic-wds-clear-date' => true,
				)
			);
		} else {
			// Check if the time slot is still available on the selected date.
			$available_slot_values = wp_list_pluck( $available_slots, 'value' );

			if ( $time && ! in_array( $time, $available_slot_values, true ) ) {
				return new WP_Error(
					'iconic_wds_time_slot_not_available',
					__( 'Sorry, the selected time slot is no longer available.', 'jckwds' ),
					array(
						'iconic-wds-clear-time' => true,
					)
				);
			} else {
				$timeslot_id = $iconic_wds->extract_timeslot_id_from_option_value( $time );
				$slot_id     = sprintf( '%s_%s', $ymd, $timeslot_id );
				$timeslot    = self::search_by_slot_id( $slot_id, $available_slots );

				// Ensure number of products in this cart are under the slot's limit (max order).
				if ( 'products' === $max_calculation_method && is_numeric( $timeslot['slots_available_count'] ) && Helpers::get_cart_count() > $timeslot['slots_available_count'] ) {
					if ( empty( $timeslot['slots_available_count'] ) ) {
						return new WP_Error(
							'iconic_wds_time_slot_not_available',
							__( 'Sorry, the selected time slot is no longer available.', 'jckwds' ),
							array(
								'iconic-wds-clear-date' => true,
								'iconic-wds-clear-time' => true,
							)
						);
					} else {
						return new WP_Error(
							'iconic_wds_max_order_quantity',
							sprintf( __( 'Sorry, we cannot accept a quantity of more than %d for the selected time slot. Please change the date/time slot or reduce the purchase quantity.', 'jckwds' ), $timeslot['slots_available_count'] ),
							array(
								'iconic-wds-clear-date' => true,
								'iconic-wds-clear-time' => true,
							)
						);
					}
				}

				if ( $timeslot ) {
					// Add 10 minute reservation to prevent double booking.
					$iconic_wds->add_reservation(
						array(
							'datetimeid' => $slot_id,
							'date'       => Helpers::convert_date_for_database( $ymd ),
							'starttime'  => $timeslot['timefrom']['stripped'],
							'endtime'    => $timeslot['timeto']['stripped'],
							'processed'  => 0,
							'expires'    => $expires,
							'asap'       => isset( $timeslot['asap'] ) ? $timeslot['asap'] : false,
						)
					);
				}
			}
		}
	}


	/**
	 * Whether to display delivery slots for virtual products which
	 * normally don't require shipping.
	 *
	 * @return bool
	 */
	public static function display_for_virtual_products() {
		global $jckwds;

		return (bool) apply_filters( 'iconic_wds_display_for_virtual', $jckwds->settings['general_setup_display_for_virtual'] );
	}

	/**
	 * Check if date/time fields should be active
	 * for the current shipping method
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_shipping_method( $shipping_method ) {
		$allowed_methods = Settings::get_shipping_methods();

		if ( $allowed_methods && ! empty( $allowed_methods ) ) {
			if ( in_array( 'any', $allowed_methods, true ) ) {
				return true;
			}

			foreach ( $allowed_methods as $allowed_method ) {
				$allowed_method = str_replace( 'wc_shipping_', '', $allowed_method );

				if ( $shipping_method === $allowed_method ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Search timeslot data by slot ID.
	 *
	 * @param string $slot_id         Timeslot ID, example: 20200903_0.
	 * @param array  $available_slots Available slots.
	 *
	 * @return array|bool
	 */
	public static function search_by_slot_id( $slot_id, $available_slots ) {
		foreach ( $available_slots as $loop_slot ) {
			if ( $slot_id === $loop_slot['slot_id'] ) {
				return $loop_slot;
			}
		}

		return false;
	}
}
