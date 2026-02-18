<?php

namespace Iconic_WDS\Subscriptions\Dto;

use DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription date range.
 */
class SubscriptionDateRange {

	/**
	 * Start date.
	 *
	 * @var DateTime
	 */
	public DateTime $start_date;

	/**
	 * End date.
	 *
	 * @var DateTime
	 */
	public DateTime $end_date;

	/**
	 * Constructor.
	 *
	 * @param DateTime $start_date Start date.
	 * @param DateTime $end_date End date.
	 */
	public function __construct( DateTime $start_date, DateTime $end_date ) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
	}

	public function get_start_date() : DateTime {
		return $this->start_date;
	}

	public function get_end_date() : DateTime {
		return $this->end_date;
	}
}
