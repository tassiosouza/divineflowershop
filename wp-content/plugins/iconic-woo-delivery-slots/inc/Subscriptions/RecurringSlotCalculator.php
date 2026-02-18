<?php
/**
 * Recurring slot calculator.
 *
 * @package Iconic_WDS\Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionPeriod;
use DateTime;
use Iconic_WDS\Dates;
use Iconic_WDS\Subscriptions\Dto\SubscriptionDateRange;

defined( 'ABSPATH' ) || exit;

/**
 * Assign dates to recurring orders.
 */
class RecurringSlotCalculator {
	/**
	 * Anchor day.
	 *
	 * @var int
	 */
	private int $anchor_day;

	/**
	 * Period.
	 *
	 * @var string
	 */
	private string $period;

	/**
	 * Interval.
	 *
	 * @var int
	 */
	private int $interval;

	/**
	 * The first delivery date.
	 *
	 * @var DateTime
	 */
	private DateTime $start_date;

	/**
	 * The subscription order id.
	 *
	 * @var int
	 */
	private int $subscription_id;

	/**
	 * Constructor.
	 *
	 * @param int      $anchor_day Anchor day.
	 * @param string   $period Period.
	 * @param int      $interval Interval.
	 * @param DateTime $start_date Start date.
	 * @param int      $subscription_id Subscription id.
	 */
	private function __construct( int $anchor_day, string $period, int $interval, DateTime $start_date, int $subscription_id ) {
		$this->anchor_day      = $anchor_day;
		$this->period          = $period;
		$this->interval        = $interval;
		$this->start_date      = $this->remove_time( $start_date );
		$this->subscription_id = $subscription_id;
	}

	/**
	 * Create a new instance from an array.
	 *
	 * @param array $data Data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ) : self {
		return new self(
			$data['anchor_day'],
			$data['period'],
			$data['interval'],
			$data['start_date'],
			$data['subscription_id']
		);
	}

	/**
	 * Calculate the current period. From the passed date, the function will try to determine the current running period
	 * i.e. the start and end date of the period.
	 *
	 * @param DateTime|null $current_date The current date.
	 *
	 * @return SubscriptionDateRange|null The current period.
	 */
	public function calculate_current_period( ?DateTime $current_date = null ) : ?SubscriptionDateRange {
		if ( null === $current_date ) {
			$current_date = new DateTime( 'now', wp_timezone() );
		}

		$period_start = $this->remove_time( $this->start_date );
		$current_date = $this->remove_time( $current_date );
		$period_end   = clone $period_start;

		$period_end->modify( sprintf( '+%d %s', $this->interval, $this->period ) );
		$period_end->modify( '-1 day' );

		if ( SubscriptionPeriod::DAY === $this->period && 1 === $this->interval ) {
			return new SubscriptionDateRange( $current_date, $current_date );
		}

		// If current date is before the start date, no period exists.
		if ( $current_date < $period_start ) {
			return null;
		}

		$iteration_count = 0;
		$max_iterations  = 100;

		do {
			if ( $iteration_count >= $max_iterations ) {
				return null; // Safety exit to prevent infinite loops.
			}

			// If current date falls within this period, we've found our range.
			if ( $current_date->getTimestamp() >= $period_start->getTimestamp() && $current_date->getTimestamp() <= $period_end->getTimestamp() ) {
				return new SubscriptionDateRange( $period_start, $period_end );
			}

			// Move to next period.
			$period_start = $this->get_next_interval_date( $period_start );
			$period_end   = $this->get_next_interval_date( $period_end );

			$iteration_count++;

		} while ( $period_start <= $current_date );

		return null;
	}

	/**
	 * Check if a date is the last day of the month.
	 *
	 * @param DateTime $date The date to check.
	 *
	 * @return bool True if the date is the last day of the month, false otherwise.
	 */
	public function is_month_end( DateTime $date ) : bool {
		// if date is the last day of the month, return true.
		$last_day_of_month = gmdate( 't', $date->getTimestamp() );
		return $date->format( 'd' ) === $last_day_of_month;
	}

	/**
	 * Get the next interval date from a given date. It add the subscription interval to the given date but also handles
	 * the complexity of the subscription period.
	 *
	 * For example: If the delivery slots date was on the last day of the month, the next interval date will also be the last day of the month.
	 *
	 * @param DateTime $date The date to get the next interval date for.
	 *
	 * @return DateTime The next interval date.
	 */
	public function get_next_interval_date( DateTime $date ) : DateTime {
		$next_date = clone $date;

		// If we're adding months and we're at the end of the month.
		if ( SubscriptionPeriod::MONTH === $this->period && $this->is_month_end( $date ) ) {
			// Move to the first of this month, then add the interval, then back to the last day.
			$next_date->modify( 'first day of this month' );
			$next_date->modify( sprintf( '+%d month', $this->interval ) );
			$next_date->modify( 'last day of this month' );
		} else {
			$next_date->modify( sprintf( '+%d %s', $this->interval, $this->period ) );
		}

		return $next_date;
	}

	/**
	 * Calculate the delivery slot date based by adding the subscription period and
	 * anchor day to the current period start date.
	 *
	 * @param DateTime $order_date       The order date.
	 * @param bool     $adjust_past_date If true and the calculated recurring date is in the past, the function will return the date by adding the anchor day to the current date.
	 *
	 * @return DateTime|null The recurring delivery date.
	 */
	public function calculate_recurring_delivery_date( DateTime $order_date, $adjust_past_date = true ) : ?DateTime {
		$current_period = $this->calculate_current_period( $order_date );

		if ( null === $current_period ) {
			// If no current period is found, we'll use the order date as the start date and the next interval date as the end date.
			$current_period = new SubscriptionDateRange( $order_date, $this->get_next_interval_date( $order_date ) );
		}

		$anchor_day   = $this->anchor_day;
		$period_start = $current_period->get_start_date();

		$timeslot_date = clone $period_start;
		$timeslot_date->modify( sprintf( '+%d days', $anchor_day ) );

		$now = new DateTime( 'now', wp_timezone() );
		if ( $timeslot_date < $now && $adjust_past_date ) {
			return $now->modify( sprintf( '+%d days', $anchor_day ) );
		}

		return $timeslot_date;
	}

	/**
	 * Calculate the actual delivery date considering availability and settings.
	 *
	 * @param DateTime $order_date The order date. The function will automatically determine the current running period
	 *                             and calculate the recurring delivery date based on the subscription settings.
	 *
	 * @return DateTime|null The adjusted delivery date based on availability.
	 */
	public function calculate_adjusted_delivery_date( DateTime $order_date ) : ?DateTime {
		global $iconic_wds;

		$date_manager = new Dates( array( 'order_id' => $this->subscription_id ) );

		// The Delivery slot date which is determined by adding the subscription interval and anchor date
		// to the current period start date.
		$recurring_date = $this->calculate_recurring_delivery_date( $order_date );

		if ( null === $recurring_date ) {
			return null;
		}

		// Get the handling setting.
		$handling = $iconic_wds->settings['general_subscription_settings_unavailable_date_handling'];

		// If we allow all dates, return immediately without expensive checks.
		if ( 'allow_all' === $handling ) {
			return $recurring_date;
		}

		// If we allow unavailable days except holidays, check holiday status first.
		if ( 'allow_except_holiday' === $handling ) {
			$is_holiday = $date_manager->is_holiday( $recurring_date->getTimestamp() );

			// If not a holiday, return the recurring date without expensive availability check.
			if ( ! $is_holiday ) {
				return $recurring_date;
			}
		}

		// For 'reschedule' option, we need to check availability.
		$available_dates = $date_manager->get_upcoming_bookable_dates( 'array' );

		// No exact match found, pick the next bookable date.
		return $this->find_next_bookable_date( $available_dates, $recurring_date );
	}

	/**
	 * If the recurring date is not bookable, find the next bookable date from the
	 * given array of available dates.
	 *
	 * @param array     $available_dates Array of available dates.
	 * @param \DateTime $recurring_date  The recurring date to compare against.
	 *
	 * @return \DateTime|null The next bookable date or null if no suitable date found.
	 */
	private function find_next_bookable_date( array $available_dates, DateTime $recurring_date ) : ?DateTime {
		if ( empty( $available_dates ) ) {
			return null;
		}

		$recurring_ymd = $recurring_date->format( 'Ymd' );
		$first_date    = reset( $available_dates );
		$last_date     = end( $available_dates );

		// If all dates are less than the recurring date, return null.
		if ( $last_date['ymd'] < $recurring_ymd ) {
			return null;
		}

		// If all dates are greater than the recurring date, return the first date.
		if ( $first_date['ymd'] > $recurring_ymd ) {
			return DateTime::createFromFormat( 'Ymd', $first_date['ymd'], wp_timezone() );
		}

		// Find the next available date.
		foreach ( $available_dates as $date ) {
			$date_ymd = $date['ymd'];

			if ( $date_ymd === $recurring_ymd ) {
				return DateTime::createFromFormat( 'Ymd', $date_ymd, wp_timezone() );
			}

			if ( $date_ymd > $recurring_ymd ) {
				return DateTime::createFromFormat( 'Ymd', $date_ymd, wp_timezone() );
			}
		}

		return null;
	}

	/**
	 * Remove the time component from a DateTime object.
	 *
	 * @param DateTime $date The date to get the date only from.
	 *
	 * @return DateTime The date only.
	 */
	public function remove_time( DateTime $date ) : DateTime {
		return DateTime::createFromFormat( 'Ymd', $date->format( 'Ymd' ) );
	}
}
