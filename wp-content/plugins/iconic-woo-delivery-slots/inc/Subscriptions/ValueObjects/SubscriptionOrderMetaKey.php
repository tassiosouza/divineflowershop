<?php
/**
 * Subscription order meta key class.
 *
 * @package Iconic_WDS\Subscriptions\ValueObjects
 */

namespace Iconic_WDS\Subscriptions\ValueObjects;

/**
 * Subscription meta key class.
 */
class SubscriptionOrderMetaKey {
	public const DATE_YMD            = '_iconic_wds_subscription_date_ymd';
	public const DATE                = '_iconic_wds_subscription_date';
	public const TIMESLOT            = '_iconic_wds_subscription_timeslot';
	public const TIMESLOT_ID         = '_iconic_wds_subscription_timeslot_id';
	public const SHIPPING_METHOD     = '_iconic_wds_subscription_shipping_method';
	public const TIMESTAMP_META_KEY  = '_iconic_wds_subscription_timestamp';
	public const TIMESLOT_ANCHOR_DAY = '_iconic_wds_subscription_anchor_day';
}
