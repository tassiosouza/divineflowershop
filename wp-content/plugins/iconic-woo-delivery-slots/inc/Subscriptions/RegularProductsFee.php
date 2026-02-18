<?php
/**
 * Handles the regular products fee. A child class of Iconic_WDS\Fee.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Dto\FeeDto;
use Iconic_WDS\Fee;

/**
 * Responsible for managing the fee of the regular products.
 */
class RegularProductsFee extends Fee {
	
	/**
	 * Get name of delivery slots Fees.
	 *
	 * @return string
	 */
	public function get_fee_name() {
		/**
		 * The name of the regular products fee.
		 *
		 * @param string $name Name of the fee.
		 *
		 * @since 2.8.0
		 */
		return apply_filters( 'iconic_wds_woo_subscriptions_regular_products_fee_name', __( 'Fee - One-time Delivery', 'iconic-wds' ) );
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
