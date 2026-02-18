<?php
/**
 * Session Timeslot.
 *
 * @package Iconic_WDS\Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use DateTime;

/**
 * Session Timeslot.
 */
class SessionTimeslot {
	/**
	 * Timeslot ID.
	 *
	 * @var string
	 */
	private ?string $timeslot_id;

	/**
	 * Timeslot data.
	 *
	 * @var array
	 */
	private $timeslot_data;

	/**
	 * Date.
	 *
	 * @var DateTime
	 */
	private ?DateTime $date;

	/**
	 * Create the object from session array.
	 *
	 * @param array $session_array Session array.
	 *
	 * @return self
	 */
	public static function from_session_array( $session_array ) {
		global $iconic_wds;

		if ( ! $session_array['timeslot'] ) {
			return null;
		}

		$obj = new self();

		if ( str_contains( $session_array['timeslot'], '|' ) ) {
			$session_array['timeslot'] = explode( '|', $session_array['timeslot'] )[0];
		}

		$obj->timeslot_id   = $session_array['timeslot'];
		$obj->date          = $session_array['date_ymd'] ? new DateTime( $session_array['date_ymd'], wp_timezone() ) : null;
		$obj->timeslot_data = $iconic_wds->get_timeslot_data( $session_array['timeslot'] ) ?? null;

		return $obj;
	}

	/**
	 * Get the end timestamp.
	 *
	 * @return int|null
	 */
	public function is_timeslot_in_past() {
		if ( ! $this->date || ! $this->timeslot_data ) {
			return null;
		}

		global $iconic_wds;
		return $iconic_wds->is_timeslot_in_past( $this->timeslot_data, $this->date->format( 'Ymd' ) );
	}

	/**
	 * Get timeslot cutoff based on.
	 *
	 * @return string
	 */
	public function get_timeslot_cutoff_based_on() {
		return $this->timeslot_data['cutoff_based_on'] ?? 'from';
	}
}
