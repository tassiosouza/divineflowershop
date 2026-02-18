<?php
/**
 * Store the data needed to calculate the fee.
 *
 * @package Iconic_WDS\Dto
 */

namespace Iconic_WDS\Dto;

use Iconic_WDS\Subscriptions\SubscriptionSession;

class FeeDto {
	/**
	 * Date.
	 *
	 * @var string
	 */
	public $date;

	/**
	 * Date YMD.
	 *
	 * @var string
	 */
	public $date_ymd;

	/**
	 * Timeslot.
	 *
	 * @var string
	 */
	public $timeslot;

	/**
	 * From session.
	 *
	 * @param string $type Type.
	 *
	 * @return FeeDto|null
	 */
	public static function from_session( string $type ) {
		$fee_dto = new self();

		$session = SubscriptionSession::from_woo_session( $type );

		if ( ! $session ) {
			return null;
		}

		$formatted_session = $session->to_array_formatted();

		$fee_dto->date     = $formatted_session['date_formatted'];
		$fee_dto->date_ymd = $formatted_session['date_ymd'];
		$fee_dto->timeslot = $formatted_session['timeslot_value'];

		return $fee_dto;
	}

	/**
	 * From store API request data.
	 *
	 * @param array  $data Data.
	 * @param string $type Type.
	 *
	 * @return FeeDto|null
	 */
	public static function from_store_api_request_data( $data, $type ) {
		$fee_dto = new self();

		if ( ! isset( $data[ $type ] ) ) {
			return null;
		}

		$fee_dto->date     = $data[ $type ]['date'];
		$fee_dto->date_ymd = $data[ $type ]['date_ymd'];
		$fee_dto->timeslot = $data[ $type ]['timeslot'];

		return $fee_dto;
	}

	/**
	 * From posted data.
	 *
	 * @param array|string $posted_data Posted data.
	 *
	 * @return FeeDto|null
	 */
	public static function from_posted_data( $posted_data ) {
		$fee_dto = new self();

		if ( is_string( $posted_data ) ) {
			parse_str( $posted_data, $posted_data );
		}

		$fee_dto->date     = isset( $posted_data['jckwds-delivery-date'] ) ? $posted_data['jckwds-delivery-date'] : false;
		$fee_dto->date_ymd = isset( $posted_data['jckwds-delivery-date-ymd'] ) ? $posted_data['jckwds-delivery-date-ymd'] : false;
		$fee_dto->timeslot = isset( $posted_data['jckwds-delivery-time'] ) ? $posted_data['jckwds-delivery-time'] : false;

		return $fee_dto;
	}

	/**
	 * From subscription posted data.
	 *
	 * @param array|string $posted_data Posted data.
	 * @param string       $type        Type.
	 *
	 * @return FeeDto|null
	 */
	public static function from_subscription_posted_data( $posted_data, $type ) {
		$fee_dto = new self();

		if ( is_string( $posted_data ) ) {
			parse_str( $posted_data, $posted_data );
		}

		// If the subscription fields are still loading, return null.
		// Returning object with empty values will result in the fee being removed from the cart.
		if ( isset( $posted_data['iconic-wds-subscription-loading'] ) ) {
			return null;
		}

		$fee_dto->date     = isset( $posted_data['iconic-wds-subscription-date'][ $type ] ) ? $posted_data['iconic-wds-subscription-date'][ $type ] : false;
		$fee_dto->date_ymd = isset( $posted_data['iconic-wds-subscription-date-ymd'][ $type ] ) ? $posted_data['iconic-wds-subscription-date-ymd'][ $type ] : false;
		$fee_dto->timeslot = isset( $posted_data['iconic-wds-subscription-timeslot'][ $type ] ) ? $posted_data['iconic-wds-subscription-timeslot'][ $type ] : false;

		return $fee_dto;
	}
}

