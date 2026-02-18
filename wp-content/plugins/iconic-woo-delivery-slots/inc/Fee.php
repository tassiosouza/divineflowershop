<?php
/**
 * Handles the fee related functionality.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

/**
 * Fee class.
 */
class Fee {
	/**
	 * Key for the day fee.
	 *
	 * @var string
	 */
	public $day_fee_key = 'jckwds_day_fee';

	/**
	 * Key for the next day fee.
	 *
	 * @var string
	 */
	public $next_day_fee_key = 'jckwds_next_day_fee';

	/**
	 * Key for the same day fee.
	 *
	 * @var string
	 */
	public $same_day_fee_key = 'jckwds_same_day_fee';

	/**
	 * Key for the timeslot fee.
	 *
	 * @var string
	 */
	public $timeslot_fee_key = 'jckwds_timeslot_fee';

	/**
	 * Add timeslot fee at checkout
	 */
	public function apply_fee() {
		$fees = array(
			WC()->session->get( $this->timeslot_fee_key ),
			WC()->session->get( $this->same_day_fee_key ),
			WC()->session->get( $this->next_day_fee_key ),
			WC()->session->get( $this->day_fee_key ),
		);

		/**
		 * Fees total amount during cart fee calculation.
		 *
		 * @since 1.24.0
		 */
		$fee = apply_filters( 'iconic_wds_fee_amount', array_sum( $fees ) );

		if ( $fee > 0 ) {
			global $iconic_wds;

			/**
			 * Tax class name.
			 *
			 * @since 1.20.0
			 */
			$tax_class = apply_filters( 'iconic_wds_fee_tax_class', '' );

			WC()->cart->add_fee( $this->get_fee_name(), $fee, $iconic_wds->settings['timesettings_timesettings_calculate_tax'], $tax_class );
		}
	}

	/**
	 * Get name of delivery slots Fees.
	 *
	 * @return string
	 */
	public function get_fee_name() {
		/**
		 * The name of the fee.
		 *
		 * @param string $name Name of the fee.
		 *
		 * @since 1.20.0
		 */
		return apply_filters( 'iconic_wds_fee_name', __( 'Delivery Fee', 'jckwds' ) );
	}

	/**
	 * Set fees session.
	 *
	 * @param string $delivery_date     Formatted delivery date.
	 * @param string $delivery_date_ymd Delivery Date in Ymd.
	 * @param string $delivery_time     Timeslot ID including fees. Ex: cqo75frlvjk/0|1.00.
	 */
	public function update_fees_session( $delivery_date, $delivery_date_ymd, $delivery_time ) {
		$dates_manager = new Dates();
		$fees          = $dates_manager->get_calculated_fees_data( $delivery_date, $delivery_date_ymd, $delivery_time, $this );

		if ( empty( $fees ) ) {
			WC()->session->__unset( $this->timeslot_fee_key );
			WC()->session->__unset( $this->same_day_fee_key );
			WC()->session->__unset( $this->next_day_fee_key );
			WC()->session->__unset( $this->day_fee_key );
			return;
		}

		foreach ( $fees as $fee_index => $fee ) {
			if ( is_bool( $fee ) && false === $fee ) {
				WC()->session->__unset( $fee_index );
			} else {
				WC()->session->set( $fee_index, $fee );
			}
		}
	}

	/**
	 * Disable fee if shipping method is not allowed.
	 * 
	 * @param int $package_id Package ID.
	 * @param string $rate_id Rate ID.
	 * @param object $request Request object.
	 */
	public function disable_fee_if_shipping_method_not_allowed( $package_id, $rate_id, $request ) {
		$selected_shipping_method = $request->get_param( 'rate_id' );
		$dates_manager = new Dates( array( 'shipping_method' => $selected_shipping_method ) );
		$allowed = $dates_manager->is_delivery_slots_allowed();

		if ( ! $allowed ) {
			WC()->session->set( $this->timeslot_fee_key, false );
			WC()->session->set( $this->same_day_fee_key, false );
			WC()->session->set( $this->next_day_fee_key, false );
			WC()->session->set( $this->day_fee_key, false );
		}
	}
}
