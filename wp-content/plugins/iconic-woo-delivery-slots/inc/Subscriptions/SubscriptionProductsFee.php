<?php
/**
 * Responsible for managing the fee of the subscription products.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Dto\FeeDto;
use Iconic_WDS\Fee;

/**
 * Subscription products fee class.
 */
class SubscriptionProductsFee extends Fee {
	/**
	 * Day fee key.
	 *
	 * @var string
	 */
	public $day_fee_key = 'iconic_wds_subscriptions_day_fee';

	/**
	 * Next day fee key.
	 *
	 * @var string
	 */
	public $next_day_fee_key = 'iconic_wds_subscriptions_next_day_fee';

	/**
	 * Same day fee key.
	 *
	 * @var string
	 */
	public $same_day_fee_key = 'iconic_wds_subscriptions_same_day_fee';

	/**
	 * Timeslot fee key.
	 *
	 * @var string
	 */
	public $timeslot_fee_key = 'iconic_wds_subscriptions_timeslot_fee';

	/**
	 * Get name of delivery slots Fees.
	 *
	 * @return string
	 */
	public function get_fee_name() {
		/**
		 * The name of the subscription fee.
		 *
		 * @param string $name Name of the fee.
		 *
		 * @since 2.8.0
		 */
		return apply_filters( 'iconic_wds_subscription_fee_name', __( 'Fee - Recurring delivery', 'iconic-wds' ) );
	}

	/**
	 * Check fee.
	 *
	 * @param FeeDto $fee_dto Fee DTO.
	 */
	public function check_fee( FeeDto $fee_dto ) {
		$this->update_fees_session( $fee_dto->date, $fee_dto->date_ymd, $fee_dto->timeslot );
	}
}
