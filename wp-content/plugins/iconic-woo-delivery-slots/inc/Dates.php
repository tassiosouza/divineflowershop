<?php
/**
 * Dates calculation.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;
use Iconic_WDS_Core_Settings;
use DateTimeZone;
use DateTime;
use Exception;
use ArrayIterator;

/**
 * WDS dates class.
 */
class Dates {

	/**
	 * Shipping method instance ID.
	 *
	 * @var string.
	 */
	public $shipping_method;

	/**
	 * Cart object.
	 *
	 * @var Cart
	 */
	public $cart;

	/**
	 * Holidays Formatted.
	 *
	 * @var array
	 */
	public $holidays_formatted = array();

	/**
	 * Allowed shipping days.
	 *
	 * @var array
	 */
	public $allowed_delivery_days = array();


	/**
	 * Days to add, min.
	 *
	 * @var boolean
	 */
	public $days_to_add_min = false;

	/**
	 * Days to add, max.
	 *
	 * @var boolean
	 */
	public $days_to_add_max = false;

	/**
	 * Bookable dates.
	 *
	 * @var array
	 */
	public $bookable_dates = array();

	/**
	 * Order ID
	 *
	 * @var WC_Order
	 */
	public $order;

	/**
	 * Constructor.
	 *
	 * @param array $args {
	 *     Optional. Array of argument that can be used to initialize the Dates object.
	 *
	 *     @type string     $shipping_method Shipping method.
	 *     @type array      $cart_products   Associative array of products and qty, in this format: array( product_id_1 => qty_1, product_id_2 => qty_2, ... )
	 *     @type order_id   $order_id        Order ID. If this argument is provided then we will try retreive shipping_method and cart_products from the order, and the other 2 arguments (shipping_method, cart_products) will be skipped.
	 * }
	 */
	public function __construct( $args = array() ) {
		global $iconic_wds;

		// Initiate with empty array.
		$this->cart = new Cart( array() );

		$defaults = array(
			'shipping_method' => '',
			'cart_products'   => array(),
			'order_id'        => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! empty( $args['order_id'] ) ) {
			$this->prepare_from_order_id( $args['order_id'] );
		} else {
			if ( empty( $args['shipping_method'] ) ) {
				$args['shipping_method'] = Iconic_WDS::get_chosen_shipping_method();
			}

			$this->cart            = new Cart( $args['cart_products'] );
			$this->shipping_method = $args['shipping_method'];
		}
	}

	/**
	 * Get date manager.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return bool|null
	 */
	public function prepare_from_order_id( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( empty( $order ) ) {
			return false;
		}

		$this->order = $order;

		// Prepare cart items assoc array in format of [ {$product_id: $quantitiy} ].
		$items    = $order->get_items();
		$products = array();

		foreach ( $items as $item ) {
			$products[ $item->get_product_id() ] = $item->get_quantity();
		}

		$this->cart = new Cart( $products );

		// Prepare shipping method.
		$methods = $order->get_shipping_methods();

		if ( empty( $methods ) ) {
			return false;
		}

		$method             = reset( $methods );
		$shipping_method_id = sprintf( '%s:%s', $method->get_method_id(), $method->get_instance_id() );

		// Set data.
		$this->shipping_method = $shipping_method_id;
	}

	/**
	 * Helper: Get upcoming bookable dates
	 *
	 * @param string $format       Format of results.
	 * @param bool   $ignore_slots Ignore whether there are slots available.
	 * @param bool   $no_cache     Dont use caching.
	 *
	 * @return array
	 */
	public function get_upcoming_bookable_dates( $format = 'array', $ignore_slots = false, $no_cache = false ) {
		$hash = md5( wp_json_encode( array( $format, $ignore_slots ) ) );

		if ( ! $no_cache && isset( $this->bookable_dates[ $hash ] ) ) {
			return $this->bookable_dates[ $hash ];
		}

		global $iconic_wds;

		$min             = $this->get_minmax_delivery_date( 'min' );
		$max             = $this->get_minmax_delivery_date( 'max' );
		$date_range      = $this->create_timestamp_range( $min['timestamp'], $max['timestamp'] );
		$specific_dates  = $this->get_specific_delivery_dates();
		$available_dates = array();
		$allow_same_day  = $this->is_same_day_allowed();
		$allow_next_day  = $this->is_next_day_allowed();
		$conflict        = OverrideSettings::get_conflict_if_exists( $this->cart->get_products_ids() );

		// By default, user profile's locale is set during AJAX.
		// Switch locale to site's default.
		if ( wp_doing_ajax() ) {
			switch_to_locale( get_locale() );
		}

		// Add specific dates to our date range, if set.
		$date_range = array_merge( $date_range, wp_list_pluck( $specific_dates, 'timestamp' ) );

		sort( $date_range, SORT_NUMERIC );

		$date_range = array_unique( array_filter( $date_range ) );

		foreach ( $date_range as $timestamp ) {
			if ( $this->is_holiday( $timestamp ) ) {
				continue;
			}

			$date            = date_i18n( 'D, jS M', $timestamp );
			$ymd             = date_i18n( 'Ymd', $timestamp );
			$order_remaining = $this->get_orders_remaining_for_day( $ymd );

			if ( $allow_next_day === $date || $allow_same_day === $date ) {
				continue;
			}

			if ( $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) {
				$slots_available = $ignore_slots ? true : $this->slots_available_on_date( $ymd );

				// If slots are not available, or if 'asap' is the only slot.
				if ( empty( $slots_available ) || ( is_array( $slots_available ) && 1 === count( $slots_available ) && 'asap' === $slots_available[0]['id'] ) ) {
					continue;
				}
			} else {
				// If timeslot is disabled, still we need to check the the order remaining for the day.
				if ( empty( $order_remaining ) ) {
					continue;
				}
			}

			// Check if number of products in cart are not more than the max limit.
			if ( is_int( $order_remaining ) && 'products' === $iconic_wds->settings['general_setup_max_order_calculation_method'] ) {
				if ( $order_remaining < $this->cart->get_cart_contents_count() ) {
					continue;
				}
			}

			// Check if there is a date conflict between two or more products in cart with different overridden dates.
			// If yes, then only show the common days.
			if ( ! empty( $conflict ) ) {
				$common_days = $conflict['common_days'];
				$day         = (int) date_i18n( 'w', $timestamp );

				if ( ! in_array( $day, $common_days, true ) ) {
					continue;
				}
			}

			if ( 'array' === $format ) {
				$available_dates[] = array(
					'formatted'        => $date,
					'header_formatted' => date_i18n( 'M j', $timestamp ),
					'admin_formatted'  => date_i18n( Helpers::date_format(), $timestamp ),
					'timestamp'        => $timestamp,
					'ymd'              => $ymd,
					'weekday_number'   => date_i18n( 'w', $timestamp ),
					'weekday'          => date_i18n( 'D', $timestamp ),
					'same_day'         => $this->get_same_day_date( 'Ymd' ) === $ymd,
					'next_day'         => $this->get_next_day_date( 'Ymd' ) === $ymd,
					'database'         => Helpers::convert_date_for_database( $ymd ),
				);
			} else {
				$available_dates[] = date_i18n( $format, $timestamp );
			}
		}

		$this->bookable_dates[ $hash ] = $available_dates;

		/**
		 * Available dates.
		 *
		 * @since 1.9.0.
		 */
		return apply_filters( 'iconic_wds_available_dates', $available_dates, $format, $ignore_slots );
	}

	/**
	 * Get allowed delivery date (x) days from now
	 *
	 * @param string $type min/max.
	 *
	 * @return array timestamp, days_to_add
	 */
	public function get_minmax_delivery_date( $type = 'min' ) {
		global $jckwds;

		$days     = 'min' === $type ? $this->get_lead_time() : (int) $jckwds->settings['datesettings_datesettings_maximum'];
		$property = sprintf( 'days_to_add_%s', $type );

		if ( 'min' === $type && $jckwds->days_to_add_min ) {
			return $jckwds->days_to_add_min;
		} elseif ( 'max' === $type && $jckwds->days_to_add_max ) {
			return $jckwds->days_to_add_max;
		}

		$max_days       = 'max' === $type ? $days : false;
		$next_timestamp = $this->get_next_allowed_timestamp( Helpers::get_current_timestamp(), $days, $max_days );

		/**
		 * Min/max delivery date.
		 *
		 * @since 1.0.0.
		 */
		$jckwds->$property = apply_filters(
			"iconic_wds_{$type}_delivery_date",
			array(
				'days_to_add' => $next_timestamp['count'],
				'timestamp'   => $next_timestamp['timestamp'],
			)
		);

		return $jckwds->$property;
	}

	/**
	 * Get lead time.
	 *
	 * @return int
	 */
	public function get_lead_time() {
		$min_days_setting = (int) Iconic_WDS_Core_Settings::get_setting_from_db( 'datesettings_datesettings', 'minimum' );
		$product_ids      = $this->cart->get_products_ids();

		if ( empty( $product_ids ) ) {
			return $min_days_setting;
		}

		static $cached_lead_time = null;

		if ( null !== $cached_lead_time && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			/**
			 * Filter to modify the lead time.
			 *
			 * @since 1.19.0
			 * @param int $cached_lead_time Lead time.
			 */
			return apply_filters( 'iconic_wds_get_lead_time', $cached_lead_time );
		}

		$cart_lead_time = array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( empty( $product ) ) {
				continue;
			}

			$parent_product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			$product_lead_time = OverrideSettings::get_lead_time_for_product( $parent_product_id );

			if ( false !== $product_lead_time ) {
				$cart_lead_time[] = $product_lead_time;
			}
		}

		/**
		 * Sets the lead time for a scenario where there are two or more products in the cart
		 * with conflicting lead times.
		 *
		 * For example if:
		 * - Product A has a lead time of 2 days
		 * - Product B has a lead time of 5 days
		 *
		 * The default lead time will be set to 5, as we pick the maximum one.
		 *
		 * This filter can be used to change the selected lead time from among the available options.
		 *
		 * @since 1.20.0
		 */
		$cached_lead_time = ! empty( $cart_lead_time ) ? apply_filters( 'iconic_wds_conflict_lead_time', max( $cart_lead_time ), $cart_lead_time ) : $min_days_setting;

		/**
		 * Filter to modify the lead time.
		 *
		 * @since 1.19.0
		 * @param int $cached_lead_time Lead time.
		 */
		return apply_filters( 'iconic_wds_get_lead_time', $cached_lead_time );
	}

	/**
	 * Get specific delivery dates.
	 *
	 * @param array $skip_min_max_check Skip min/max date check.
	 *
	 * @return array
	 */
	public function get_specific_delivery_dates( $skip_min_max_check = false ) {
		global $iconic_wds;

		$argument_hash = $skip_min_max_check ? 'skip_min_max_check' : 'dont_skip_min_max_check';

		static $cached_specific_dates = array();

		if ( ! empty( $cached_specific_dates[ $argument_hash ] ) && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			return $cached_specific_dates[ $argument_hash ];
		}

		if ( empty( $iconic_wds->settings ) ) {
			/**
			 * Specific delivery dates.
			 *
			 * @since 1.13.0.
			 */
			return apply_filters( 'iconic_wds_specific_delivery_dates', array(), $skip_min_max_check );
		}

		$specific_dates = (array) $iconic_wds->settings['datesettings_datesettings_specific_days'];

		if ( empty( $specific_dates ) ) {
			/**
			 * Specific delivery dates.
			 *
			 * @since 1.13.0.
			 */
			return apply_filters( 'iconic_wds_specific_delivery_dates', array(), $skip_min_max_check );
		}

		$overridden_days = OverrideSettings::get_product_specific_days_setting( $this->cart->get_products_ids() );

		foreach ( $specific_dates as $index => $specific_date ) {
			if ( empty( $specific_date ) || empty( $specific_date['date'] ) || empty( $specific_date['alt_date'] ) ) {
				unset( $specific_dates[ $index ] );
				continue;
			}

			$timestamp = strtotime( $specific_date['alt_date'] );

			// If we repeat this date yearly, and the date is in the past, get next year's date.
			if ( $specific_date['repeat_yearly'] && strtotime( gmdate( 'Y-m-d' ) ) > $timestamp ) {
				$timestamp = strtotime( '+1 years', $timestamp );
			}

			/**
			 * Remove those specific days which do not comply with the
			 * overriden allowed weekdays.
			 *
			 * For example: if a specific day is Monday, 2 May 2022, and some product
			 * in cart has only Tuesday set as delivery days, then this specific day
			 * should be removed.
			 *
			 * This filter can be used to disable this behaviour.
			 *
			 * @since 1.18.0
			 */
			if ( apply_filters( 'iconic_wds_days_override_setting_apply_for_specific_days', true ) ) {
				$specific_date_weekday = (int) date_i18n( 'w', $timestamp );
				if ( is_array( $overridden_days ) && ! in_array( $specific_date_weekday, $overridden_days, true ) ) {
					unset( $specific_dates[ $index ] );
					continue;
				}
			}

			$specific_dates[ $index ]['ymd']       = str_replace( '-', '', $specific_date['alt_date'] );
			$specific_dates[ $index ]['timestamp'] = $timestamp;
		}

		/**
		 * Sometimes we would want to skip min/max check as it could cause infinite loop.
		 */
		if ( $skip_min_max_check ) {
			$cached_specific_dates[ $argument_hash ] = $specific_dates;
			/**
			 * Specific delivery dates.
			 *
			 * @since 1.13.0.
			 */
			return apply_filters( 'iconic_wds_specific_delivery_dates', $cached_specific_dates[ $argument_hash ], $skip_min_max_check );
		}

		$min_date = $this->get_minmax_delivery_date( 'min' );
		$max_date = $this->get_minmax_delivery_date( 'max' );

		foreach ( $specific_dates as $index => $specific_date ) {
			// Don't use this date if it's before the min selectable date, or
			// it's not set to bypass the maximum selectable date and is past it.
			if ( $specific_date['timestamp'] < $min_date['timestamp'] || ( ! $specific_date['bypass_max'] && $specific_date['timestamp'] >= $max_date['timestamp'] ) ) {
				unset( $specific_dates[ $index ] );
				continue;
			}
		}

		$cached_specific_dates[ $argument_hash ] = $specific_dates;
		/**
		 * Specific delivery dates.
		 *
		 * @since 1.13.0.
		 */
		return apply_filters( 'iconic_wds_specific_delivery_dates', $cached_specific_dates[ $argument_hash ], $skip_min_max_check );
	}

	/**
	 * Is day a holiday?
	 *
	 * @param int $timestamp UTC timestamp of date to check.
	 *
	 * @return bool
	 */
	public function is_holiday( $timestamp ) {
		global $jckwds;

		$holidays = $this->get_formatted_holidays();

		$ymd = date_i18n( 'Ymd', $timestamp );
		$md  = date_i18n( 'md', $timestamp );

		$is_holdiay = in_array( $ymd, $holidays, true ) || in_array( $md, $holidays, true );

		/**
		 * Is given timestamp is a holiday.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'iconic_wds_is_holiday', $is_holdiay, $timestamp, $holidays );
	}

	/**
	 * Helper: Get formatted holidays
	 *
	 * @return array
	 */
	public function get_formatted_holidays() {
		$chosen_shipping_method = $this->shipping_method;

		if ( isset( $this->holidays_formatted[ $chosen_shipping_method ] ) ) {
			return $this->holidays_formatted[ $chosen_shipping_method ];
		}

		global $iconic_wds;

		$holidays           = $iconic_wds->settings['holidays_holidays_holidays'];
		$holidays_formatted = array();

		if ( ! empty( $holidays ) ) {
			$utc_timezone = new DateTimeZone( 'UTC' );

			foreach ( $holidays as $holiday ) {
				if ( empty( $holiday['date'] ) ) {
					continue;
				}

				if ( ! empty( $holiday['shipping_methods'] ) && ! in_array( 'any', $holiday['shipping_methods'], true ) && ! in_array( $this->shipping_method, $holiday['shipping_methods'], true ) ) {
					continue;
				}

				$range        = false;
				$format       = ! empty( $holiday['repeat_yearly'] ) ? 'md' : 'Ymd';
				$holiday_from = isset( $holiday['alt_date'] ) ? $holiday['alt_date'] : $holiday['date'];
				$holiday_to   = isset( $holiday['alt_date_to'] ) ? $holiday['alt_date_to'] : $holiday['date_to'];

				$holiday_from_object = DateTime::createFromFormat( 'd/m/Y H:i:s', $holiday_from . ' 00:00:00', $utc_timezone );

				// If alt_date is empty, use 'date'.
				if ( empty( $holiday_from_object ) ) {
					try {
						$holiday_from_object = new DateTime( $holiday['date'], $utc_timezone );
					} catch ( Exception $e ) {
						$holiday_from_object = false;
					}
				}

				if ( empty( $holiday_from_object ) ) {
					continue;
				}

				if ( ! empty( $holiday_to ) ) {
					$holiday_to_object = DateTime::createFromFormat( 'd/m/Y H:i:s', $holiday_to . ' 00:00:00', $utc_timezone );

					$range = $this->create_timestamp_range( $holiday_from_object->getTimestamp(), $holiday_to_object->getTimestamp(), true );
				}

				if ( $range && ! empty( $range ) ) {
					foreach ( $range as $timestamp ) {
						$holidays_formatted[] = date_i18n( $format, $timestamp );
					}
				} else {
					$holidays_formatted[] = date_i18n( $format, $holiday_from_object->getTimestamp() );
				}
			}
		}

		$this->holidays_formatted[ $chosen_shipping_method ] = $holidays_formatted;

		return $holidays_formatted;
	}

	/**
	 * Check if next day delivery is allowed
	 *
	 * @return mixed Returns true if allowed, or tomorrow's date if not
	 */
	public function is_next_day_allowed() {
		global $iconic_wds;

		/**
		 * Allow plugins/themes to set "is next day delivery allowed".
		 *
		 * @param bool|null $allowed
		 *
		 * @since 1.0.0
		 */
		$allowed = apply_filters( 'iconic_wds_is_next_day_allowed', null );

		if ( null !== $allowed ) {
			return $allowed;
		}

		$next_day_cutoff = isset( $iconic_wds->settings['datesettings_datesettings_nextday_cutoff'] ) ? $iconic_wds->settings['datesettings_datesettings_nextday_cutoff'] : '';
		/**
		 * Next day cutoff.
		 *
		 * @since 1.0.0
		 */
		$next_day_cutoff = apply_filters( 'iconic_wds_next_day_cutoff', $next_day_cutoff );

		if ( empty( $next_day_cutoff ) ) {
			return true;
		}

		$next_day_cutoff_formatted = DateTime::createFromFormat( 'Ymd H:i', sprintf( '%s %s', $iconic_wds->current_ymd, $next_day_cutoff ), wp_timezone() );

		$now     = new DateTime( 'now', wp_timezone() );
		$in_past = $now >= $next_day_cutoff_formatted ? true : false;

		if ( $in_past ) {
			return $this->get_next_day_date( 'D, jS M' );
		} else {
			return true;
		}
	}

	/**
	 * Get next day date.
	 *
	 * Next day should be the next allowed delivery day.
	 *
	 * @param string $format Format.
	 *
	 * @return mixed
	 */
	public function get_next_day_date( $format = 'timestamp' ) {
		global $iconic_wds;

		$min_days           = $this->get_lead_time();
		$next_day_timestamp = $this->get_next_allowed_timestamp( Helpers::get_current_timestamp(), $min_days > 1 ? $min_days : 1 );

		if ( ! $next_day_timestamp ) {
			/**
			 * Get next day date.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'iconic_wds_next_day_date', false, $format, $next_day_timestamp );
		}

		$next_day_formatted = 'timestamp' === $format ? $next_day_timestamp['timestamp'] : date_i18n( $format, $next_day_timestamp['timestamp'] );

		/**
		 * Get next day date.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'iconic_wds_next_day_date', $next_day_formatted, $format, $next_day_timestamp['timestamp'] );
	}

	/**
	 * Get same day date.
	 *
	 * @param string $format Format.
	 *
	 * @return mixed
	 */
	public function get_same_day_date( $format = 'timestamp' ) {
		$same_day_timestamp = $this->get_next_allowed_timestamp( Helpers::get_current_timestamp() );

		if ( ! $same_day_timestamp ) {
			/**
			 * Same day date.
			 *
			 * @since 1.0.0.
			 */
			return apply_filters( 'iconic_wds_same_day_date', false, $format, $same_day_timestamp );
		}

		$same_day_formatted = 'timestamp' === $format ? $same_day_timestamp['timestamp'] : date_i18n( $format, $same_day_timestamp['timestamp'] );

		/**
		 * Same day date.
		 *
		 * @since 1.0.0.
		 */
		return apply_filters( 'iconic_wds_same_day_date', $same_day_formatted, $format, $same_day_timestamp['timestamp'] );
	}

	/**
	 * Check if same day delivery is allowed
	 *
	 * @return mixed Returns true if allowed, or today's date if not
	 */
	public function is_same_day_allowed() {
		global $jckwds;

		/**
		 * Allow plugins/themes to set "is same day delivery allowed".
		 *
		 * @param bool|null $allowed
		 *
		 * @since 1.0.0
		 */
		$allowed = apply_filters( 'iconic_wds_is_same_day_allowed', null );

		if ( null !== $allowed ) {
			return $allowed;
		}

		$same_day_cutoff = isset( $jckwds->settings['datesettings_datesettings_sameday_cutoff'] ) ? $jckwds->settings['datesettings_datesettings_sameday_cutoff'] : '';

		/**
		 * Same day cutoff.
		 *
		 * @since 1.0.0
		 */
		$same_day_cutoff = apply_filters( 'iconic_wds_same_day_cutoff', $same_day_cutoff );

		if ( empty( $same_day_cutoff ) ) {
			return true;
		}

		$same_day_cutoff_formatted = DateTime::createFromFormat( 'Ymd H:i', sprintf( '%s %s', $jckwds->current_ymd, $same_day_cutoff ), wp_timezone() );

		$now     = new DateTime( 'now', wp_timezone() );
		$in_past = $now >= $same_day_cutoff_formatted ? true : false;

		if ( $in_past ) {
			return $this->get_same_day_date( 'D, jS M' );
		} else {
			return true;
		}
	}

	/**
	 * Is this a delivery day.
	 *
	 * @param int  $timestamp   UTC timestamp of date to check.
	 * @param bool $calculation Is this for min/max calculations.
	 *
	 * @return bool
	 */
	public function is_delivery_day( $timestamp, $calculation = false ) {
		global $iconic_wds;

		$specific_dates = $this->get_specific_delivery_dates( true );

		if ( ! empty( $specific_dates ) ) {
			foreach ( $specific_dates as $specific_date ) {
				if ( empty( $specific_date['date'] ) || empty( $specific_date['alt_date'] ) ) {
					continue;
				}

				$specific_date_timestamp = strtotime( $specific_date['alt_date'] );
				$compare                 = $specific_date['repeat_yearly'] ? 'Ymd' : 'md';

				$specific_date_compare = gmdate( $compare, $specific_date_timestamp );
				$date_compare          = gmdate( $compare, $timestamp );

				if ( $specific_date_compare === $date_compare ) {
					/**
					 * Is given timestamp a delivery day.
					 *
					 * @since 1.0.0.
					 */
					return apply_filters( 'iconic_wds_is_delivery_day', true, $timestamp, $calculation );
				}
			}
		}

		$minmax_setting = Settings::get_minmax_method();

		// If we're doing a calculation and minmax method is
		// all, return true. All days are delivery days.
		if ( $calculation && 'all' === $minmax_setting ) {
			/**
			 * Is given timestamp a delivery day.
			 *
			 * @since 1.0.0.
			 */
			return apply_filters( 'iconic_wds_is_delivery_day', true, $timestamp, $calculation );
		}

		// Get day in GMT timezone.
		$day = absint( gmdate( 'w', $timestamp ) );

		// If we're doing a calulcation and minmax method is
		// weekdays, check if the day we're checking is a weekday.
		// If so, return true.
		if ( $calculation && 'weekdays' === $minmax_setting ) {
			$is_delivery_day = in_array( $day, array( 1, 2, 3, 4, 5 ), true );

			return apply_filters( 'iconic_wds_is_delivery_day', $is_delivery_day, $timestamp, $calculation );
		}

		$allowed_days = $this->get_allowed_delivery_days();

		$is_delivery_day = ! empty( $allowed_days[ $day ] );

		/**
		 * Is given timestamp a delivery day.
		 *
		 * @since 1.0.0.
		 */
		return apply_filters( 'iconic_wds_is_delivery_day', $is_delivery_day, $timestamp, $calculation );
	}

	/**
	 * Get the first allowed timestamp starting from the given timestamp.
	 *
	 * @param bool|int $start_timestamp Start timestamp.
	 * @param int      $minimum_days    Minimum number of days to count.
	 * @param bool     $maximum_days    Maximum number of days to count.
	 *
	 * @return bool|array
	 */
	public function get_next_allowed_timestamp( $start_timestamp = false, $minimum_days = 0, $maximum_days = false ) {
		if ( ! $start_timestamp ) {
			return false;
		}

		$count                  = 0;
		$timestamps             = new ArrayIterator( array( $start_timestamp ) );
		$last_allowed_timestamp = $start_timestamp;
		$last_day_of_the_week   = $this->is_current_week_only() ? self::get_last_day_of_the_week() : false;

		foreach ( $timestamps as $timestamp ) {
			$ddate = wp_date( 'd/m/Y l w', $timestamp );
			// If we're only getting dates from this week,
			// and this timestamp is past of the last day
			// of the week, return the last allowed timestamp.
			if ( $last_day_of_the_week && $timestamp > $last_day_of_the_week ) {
				$start_timestamp = $last_allowed_timestamp;
				break;
			}

			// If this is an actual delivery day, store it
			// in the memory.
			if ( $this->is_delivery_day( $timestamp ) ) {
				$last_allowed_timestamp = $timestamp;
			}

			// Check if this is a delivery day according to the
			// min/max calculation method.
			$is_day_allowed = $this->is_day_allowed( $timestamp, true );

			if ( ! $is_day_allowed ) {
				$timestamps->append( strtotime( '+1 day', $timestamp ) );
				continue;
			}

			// If we've counted the minimum number of days,
			// return the timestamp.
			if ( $count === $minimum_days ) {
				$start_timestamp = $timestamp;
				break;
			}

			// Otherwise, add to the count.
			$count ++;

			// If we've reached the maximum days,
			// we're not going to find a suitable date.
			if ( $maximum_days && $count > $maximum_days ) {
				$start_timestamp = $timestamp;
				break;
			}

			// Then, add a new timestamp to check.
			// We're only counting allowed days.
			$timestamps->append( strtotime( '+1 day', $timestamp ) );
		}

		return array(
			'timestamp' => $start_timestamp,
			'count'     => $count,
		);
	}

	/**
	 * Is this day allowed for delivery?
	 *
	 * @param int  $timestamp   UTC timestamp of day to check.
	 * @param bool $calculation Is this to calculate min/max delivery days.
	 *
	 * @return bool
	 */
	public function is_day_allowed( $timestamp, $calculation = false ) {
		global $jckwds;

		$allowed         = false;
		$is_same_day     = Helpers::is_same_day( $timestamp );
		$is_delivery_day = self::is_delivery_day( $timestamp, $calculation );
		$skip_current    = (bool) $jckwds->settings['datesettings_datesettings_skip_current'];
		$is_holiday      = self::is_holiday( $timestamp );

		// If is today and is a delivery day and not a holiday.
		if ( $is_same_day && ( $is_delivery_day && ! $is_holiday ) ) {
			$allowed = true;
		}

		// If is today and is not a delivery and not skip.
		if ( $is_same_day && ( ! $is_delivery_day || $is_holiday ) && ! $skip_current ) {
			$allowed = true;
		}

		// If is not today and is a delivery day and not a holiday.
		if ( ! $is_same_day && ( $is_delivery_day && ! $is_holiday ) ) {
			$allowed = true;
		}

		/**
		 * Is day allowed.
		 *
		 * @since 1.0.0.
		 */
		return apply_filters( 'iconic_wds_is_day_allowed', $allowed, $timestamp, $calculation );
	}

	/**
	 * Create a timestamp range
	 *
	 * @param int $timestamp_from From timestamp.
	 * @param int $timestamp_to   To timestamp.
	 *
	 * @return array
	 */
	public function create_timestamp_range( $timestamp_from, $timestamp_to, $ignore_validation = false ) {
		$range = array();

		if ( $timestamp_to >= $timestamp_from ) {
			if ( $this->is_delivery_day( $timestamp_from ) || $ignore_validation ) {
				array_push( $range, $timestamp_from );
			}

			while ( $timestamp_from < $timestamp_to ) {
				$timestamp_from = $timestamp_from + 86400; // + 1 day (in seconds).

				if ( $this->is_delivery_day( $timestamp_from ) || $ignore_validation ) {
					array_push( $range, $timestamp_from );
				}
			}
		}

		return $range;
	}

	/**
	 * Get allowed days
	 *
	 * @param bool $minmax Mimax or default.
	 *
	 * @return array
	 */
	public function get_allowed_delivery_days( $minmax = false ) {
		$key = $minmax ? 'minmax' : 'default';

		if ( ! empty( $this->allowed_delivery_days[ $key ] ) ) {
			/**
			 * Allowed delivery days.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'iconic_wds_allowed_days', $this->allowed_delivery_days[ $key ], $minmax );
		}

		$this->allowed_delivery_days[ $key ] = array(
			0 => false,
			1 => false,
			2 => false,
			3 => false,
			4 => false,
			5 => false,
			6 => false,
		);

		$mixmax_method = Settings::get_minmax_method();

		if ( ! $minmax || 'allowed' === $mixmax_method ) {
			$chosen_days = Settings::get_delivery_days( 'frontend', $this->cart->get_products_ids() );

			if ( $chosen_days && ! empty( $chosen_days ) ) {
				foreach ( $chosen_days as $day ) {
					$this->allowed_delivery_days[ $key ][ $day ] = true;
				}
			}

			/**
			 * Allowed delivery days.
			 *
			 * @since 1.0.0
			 */
			$this->allowed_delivery_days[ $key ] = apply_filters( 'iconic_wds_allowed_days', $this->allowed_delivery_days[ $key ], $minmax );

			return $this->allowed_delivery_days[ $key ];
		}

		if ( 'all' === $mixmax_method ) {
			$this->allowed_delivery_days[ $key ] = array(
				0 => true,
				1 => true,
				2 => true,
				3 => true,
				4 => true,
				5 => true,
				6 => true,
			);
		} elseif ( 'weekdays' === $mixmax_method ) {
			$this->allowed_delivery_days[ $key ] = array(
				0 => false,
				1 => true,
				2 => true,
				3 => true,
				4 => true,
				5 => true,
				6 => false,
			);
		}

		/**
		 * Allowed delivery days.
		 *
		 * @since 1.0.0
		 */
		$this->allowed_delivery_days[ $key ] = apply_filters( 'iconic_wds_allowed_days', $this->allowed_delivery_days[ $key ], $minmax );

		return $this->allowed_delivery_days[ $key ];
	}

	/**
	 * Is delivery slots allowed.
	 *
	 * @return bool
	 */
	public function is_delivery_slots_allowed() {
		if ( EditTimeslots::is_sub_order( $this->order ) ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'iconic_wds_delivery_slots_allowed', true );
		}

		if ( $this->cart->are_all_products_virtual() && Checkout::display_for_virtual_products() ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', true );
		}

		if ( ! $this->needs_shipping() ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		if ( ! Checkout::is_delivery_slots_allowed_for_shipping_method( $this->shipping_method ) ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		if ( ! $this->cart->is_delivery_slots_allowed_for_category() ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		if ( ! $this->cart->is_delivery_slots_allowed_for_product() ) {
			/**
			 * Is delivery slots allowed i.e. whether to display the delivery slots fields.
			 *
			 * @since 1.0.0
			 */
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		/**
		 * Is delivery slots allowed i.e. whether to display the delivery slots.
		 *
		 * @since 1.0.0
		 */
		return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', true );
	}


	/**
	 * Helper: Get all slots available on a specific date
	 *
	 * @param string $ymd                      Ymd string of date.
	 * @param bool   $include_booked_out_slots Whether to include slots that are booked out.
	 *
	 * @return array
	 */
	public function slots_available_on_date( $ymd, $include_booked_out_slots = false ) {
		global $iconic_wds;

		$available_timeslots      = array();
		$orders_remaining_for_day = $this->get_orders_remaining_for_day( $ymd );

		if ( ! $orders_remaining_for_day ) {
			/**
			 * Slots available on date.
			 *
			 * @since 1.0.0.
			 */
			return apply_filters( 'iconic_wds_slots_available_on_date', $available_timeslots, $ymd );
		}

		$timeslots      = $iconic_wds->get_timeslot_data();
		$datetime       = DateTime::createFromFormat( 'Ymd H:i:s', $ymd . ' 00:00:00', new DateTimeZone( 'UTC' ) );
		$date_timestamp = $datetime->getTimestamp();

		if ( ! $timeslots ) {
			/**
			 * Is given timestamp a delivery day.
			 *
			 * @since 1.0.0.
			 */
			return apply_filters( 'iconic_wds_slots_available_on_date', $available_timeslots, $ymd );
		}

		$slots_available_count = $iconic_wds->get_slots_available_count( $timeslots, $ymd );

		foreach ( $timeslots as $timeslot ) {
			$slot_id = sprintf( '%s_%s', $ymd, $timeslot['id'] );

			$slot_allowed_on_day     = $iconic_wds->is_timeslot_available_on_day( $date_timestamp, $timeslot );
			$in_past                 = $iconic_wds->is_timeslot_in_past( $timeslot, $ymd );
			$slot_allowed_for_method = $this->is_timeslot_allowed_for_method( $timeslot, $this->shipping_method );
			$cart_count              = $this->cart->get_cart_contents_count();

			if ( ! $slot_allowed_on_day || $in_past || ! $slot_allowed_for_method ) {
				continue;
			}

			if ( ! $include_booked_out_slots && $slots_available_count[ $timeslot['id'] ] <= 0 ) {
				continue;
			}

			if (
				'products' === $iconic_wds->settings['general_setup_max_order_calculation_method']
				&& ! is_bool( $slots_available_count[ $timeslot['id'] ] )
				&& $cart_count && intval( $slots_available_count[ $timeslot['id'] ] ) < $cart_count
			) {
				continue;
			}

			$timeslot['slot_id']               = $slot_id;
			$timeslot['slots_available_count'] = $slots_available_count[ $timeslot['id'] ];
			$available_timeslots[]             = $timeslot;
		}

		/**
		 * Is given timestamp a delivery day.
		 *
		 * @since 1.0.0.
		 */
		return apply_filters( 'iconic_wds_slots_available_on_date', $available_timeslots, $ymd );
	}


	/**
	 * Frontend: Display the checkout fields.
	 *
	 * @param int $order_id Order ID. Helpful when display the checkout fields on Thank you page or My accounts page.
	 */
	public function display_checkout_fields( $order_id = false ) {
		$fields         = $this->get_checkout_fields_data();
		$active         = $this->is_delivery_slots_allowed();
		$bookable_dates = $this->get_upcoming_bookable_dates( Helpers::date_format() );

		$packages             = WC()->shipping()->get_packages();
		$available_methods    = ! empty( $packages[0]['rates'] );
		$message              = '';
		$all_products_virtual = $this->cart->are_all_products_virtual();

		/**
		 * Use modern template.
		 *
		 * @since 2.8.0
		 */
		$use_modern_template = apply_filters( 'iconic_wds_checkout_fields_use_modern_field', true );

		if ( $use_modern_template ) {
			include ICONIC_WDS_PATH . 'templates/checkout-fields-modern.php';
		} else {
			include ICONIC_WDS_PATH . 'templates/checkout-fields.php';
		}
	}

	/**
	 * Helper: Get Checkout fields data
	 *
	 * @param WC_Order $order Order, if thank you or my account page.
	 *
	 * @return array
	 */
	public function get_checkout_fields_data( $order = null ) {
		global $iconic_wds;

		$fields   = array();
		$order_id = is_a( $order, 'WC_Order' ) ? $order->get_id() : false;
		$reserved = $iconic_wds->get_reserved_slot( $order_id );

		$fields['jckwds-delivery-date'] = array(
			'value'      => '',
			'field_args' => array(
				'type'              => 'text',
				'label'             => Helpers::get_label( 'date', $order ),
				'required'          => $iconic_wds->settings['datesettings_datesettings_setup_mandatory'],
				'class'             => array( 'jckwds-delivery-date', 'form-row-wide' ),
				'placeholder'       => Helpers::get_label( 'select_date', $order ),
				'custom_attributes' => array( 'readonly' => 'true' ),
				'description'       => $iconic_wds->settings['datesettings_datesettings_setup_show_description'] ? Helpers::get_label( 'choose_date', $order ) : false,
			),
		);

		$fields['jckwds-delivery-date-ymd'] = array(
			'value'      => '',
			'field_args' => array(
				'type'     => 'hidden',
				'label'    => '',
				'required' => false,
			),
		);

		if ( $reserved ) {
			$fields['jckwds-delivery-date']['value']     = $reserved['date']['formatted'];
			$fields['jckwds-delivery-date-ymd']['value'] = $reserved['date']['id'];
		}

		if ( $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) {
			$fields['jckwds-delivery-time'] = array(
				'value'      => '',
				'field_args' => array(
					'type'        => 'select',
					'label'       => Helpers::get_label( 'time_slot', $order ),
					'required'    => $iconic_wds->settings['timesettings_timesettings_setup_mandatory'],
					'class'       => array( 'jckwds-delivery-time', 'form-row-wide' ),
					'options'     => array(
						'' => Helpers::get_label( 'select_date_first' ),
					),
					'description' => $iconic_wds->settings['timesettings_timesettings_setup_show_description'] ? Helpers::get_label( 'choose_time_slot', $order ) : false,
				),
			);

			if ( ! empty( $reserved ) && ! empty( $reserved['time'] ) ) {
				$fields['jckwds-delivery-time']['value']                 = $iconic_wds->get_timeslot_value( $reserved['time'] );
				$fields['jckwds-delivery-time']['field_args']['class'][] = 'jckwds-delivery-time--has-reservation';
				$fields['jckwds-delivery-time']['field_args']['options'] = array(
					'' => Helpers::get_label( 'select_time_slot' ),
				);

				$available_slots = $this->slots_available_on_date( $reserved['date']['id'] );

				if ( $available_slots && ! empty( $available_slots ) ) {
					foreach ( $available_slots as $available_slot ) {
						$fields['jckwds-delivery-time']['field_args']['options'][ $available_slot['value'] ] = $available_slot['formatted_with_fee'];
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Is shipping required at checkout.
	 *
	 * @return bool
	 */
	public function needs_shipping() {
		if ( ! empty( Checkout::display_for_virtual_products() ) ) {
			return true;
		}

		global $iconic_wds;

		return $this->cart->needs_shipping();
	}



	/**
	 * Is timestamp in the current week.
	 *
	 * @param int $timestamp UTC timestamp of date to check.
	 *
	 * @return bool
	 */
	public static function is_in_current_week( $timestamp ) {
		return $timestamp < self::get_last_day_of_the_week();
	}

	/**
	 * Is current week only set?
	 *
	 * @return bool
	 */
	public function is_current_week_only() {
		global $iconic_wds;

		return ! empty( $iconic_wds->settings['datesettings_datesettings_week_limit'] );
	}

	/**
	 * Get last day of the week timestamp.
	 *
	 * @return false|int
	 */
	public static function get_last_day_of_the_week() {
		global $iconic_wds;

		$today            = strtolower( gmdate( 'l', time() ) );
		$last_day_of_week = $iconic_wds->settings['datesettings_datesettings_last_day_of_week'];

		return $today === $last_day_of_week ? strtotime( 'today 23:59:59' ) : strtotime( 'next ' . $last_day_of_week . ' 23:59:59' );
	}

	/**
	 * Returns row_id of the specific date else returns false.
	 *
	 * @param string $ymd Date in Ymd format.
	 *
	 * @return string|false
	 */
	public function is_specific_date( $ymd ) {
		$specific_dates = $this->get_specific_delivery_dates();

		if ( ! is_array( $specific_dates ) ) {
			return false;
		}

		foreach ( $specific_dates as $date ) {
			if ( strval( $date['ymd'] ) === strval( $ymd ) ) {
				return $date;
			}
		}

		return false;
	}

	/**
	 * Get calculated fees bases given delivery date and timeslot.
	 *
	 * @param string $delivery_date     Delivery Date.
	 * @param string $delivery_date_ymd Delivery Date in Ymd format.
	 * @param string $delivery_time     Time.
	 *
	 * @return array
	 */
	public function get_calculated_fees_data( $delivery_date, $delivery_date_ymd, $delivery_time, $fee_handler ) {
		global $iconic_wds;

		$allowed = $this->is_delivery_slots_allowed();

		if ( ! $allowed ) {
			return false;
		}

		$fees = array();

		if ( ! empty( $delivery_time ) ) {
			$timeslot_fee = $iconic_wds->extract_fee_from_option_value( $delivery_time );

			if ( $timeslot_fee > 0 ) {
				$fees[ $fee_handler->timeslot_fee_key ] = $timeslot_fee;
			} else {
				$fees[ $fee_handler->timeslot_fee_key ] = false;
			}
		} else {
			$fees[ $fee_handler->timeslot_fee_key ] = false;
		}

		if ( $iconic_wds->settings['datesettings_fees_same_day'] > 0 ) {
			$same_day = $this->get_same_day_date( Helpers::date_format() );
			if ( ! empty( $delivery_date ) && $delivery_date === $same_day ) {
				$fees[ $fee_handler->same_day_fee_key ] = $iconic_wds->settings['datesettings_fees_same_day'];
			} else {
				$fees[ $fee_handler->same_day_fee_key ] = false;
			}
		} else {
			$fees[ $fee_handler->same_day_fee_key ] = false;
		}

		if ( $iconic_wds->settings['datesettings_fees_next_day'] > 0 ) {
			$next_day = $this->get_next_day_date( Helpers::date_format() );
			if ( ! empty( $delivery_date ) && $delivery_date === $next_day ) {
				$fees[ $fee_handler->next_day_fee_key ] = $iconic_wds->settings['datesettings_fees_next_day'];
			} else {
				$fees[ $fee_handler->next_day_fee_key ] = false;
			}
		} else {
			$fees[ $fee_handler->next_day_fee_key ] = false;
		}

		$day_fees = array_filter( Settings::get_day_fees() );

		if ( ! empty( $day_fees ) ) {
			$ymd = ! empty( $delivery_date_ymd ) ? $delivery_date_ymd : false;

			if ( ! $ymd ) {
				$fees[ $fee_handler->day_fee_key ] = false;
			} else {
				$date = DateTime::createFromFormat( 'Ymd', $ymd, wp_timezone() );
				$day  = $date->format( 'w' );

				if ( isset( $day_fees[ $ymd ] ) ) {
					$fees[ $fee_handler->day_fee_key ] = $day_fees[ $ymd ];
				} elseif ( isset( $day_fees[ $day ] ) ) {
					$fees[ $fee_handler->day_fee_key ] = $day_fees[ $day ];
				} else {
					$fees[ $fee_handler->day_fee_key ] = false;
				}
			}
		} else {
			$fees[ $fee_handler->day_fee_key ] = false;
		}

		return $fees;
	}

	/**
	 * Get number of orders remaining for a specific day.
	 *
	 * @param string $ymd Ymd string of date.
	 *
	 * @return bool|int True if unlimited orders remaining. False if none. Otherwise, actual number of orders remaining.
	 */
	public function get_orders_remaining_for_day( $ymd ) {
		$specific_date = Settings::is_specific_date( $ymd );

		if ( ! $specific_date ) {
			$day_of_the_week   = absint( date_i18n( 'w', strtotime( $ymd ) ) );
			$max_orders_on_day = Settings::get_delivery_days_max_orders( $day_of_the_week );
		} else {
			$max_orders_on_day = '' === $specific_date['lockout'] ? true : absint( $specific_date['lockout'] );
		}

		// If max orders is true (any # of orders allowed), or false (no orders allowed).
		if ( is_bool( $max_orders_on_day ) ) {
			/**
			 * Get orders remaining for day.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'iconic_wds_get_orders_remaining_for_day', $max_orders_on_day, $ymd, null );
		}

		$future_orders_by_date = $this->get_future_orders_by_date();
		$booked_orders_on_day  = Helpers::search_array_by_key_value( 'ymd', $ymd, $future_orders_by_date );

		// If there aren't any bookings on this day.
		if ( empty( $booked_orders_on_day ) ) {
			// phpcs:ignore
			return apply_filters( 'iconic_wds_get_orders_remaining_for_day', $max_orders_on_day, $ymd, $future_orders_by_date );
		}

		$max_orders_on_day -= absint( $booked_orders_on_day['count'] );

		$max_orders_on_day = $max_orders_on_day <= 0 ? false : $max_orders_on_day;

		// phpcs:ignore
		return apply_filters( 'iconic_wds_get_orders_remaining_for_day', $max_orders_on_day, $ymd, $future_orders_by_date );
	}

	/**
	 * Get the number of orders booked/reserved on all upcoming dates.
	 *
	 * @param string $max_order_calculation_method Max order caclulation method.
	 *
	 * @return $booked_dates
	 */
	private function get_future_orders_by_date( $max_order_calculation_method = false ) {
		static $booked_dates = null;

		if ( null !== $booked_dates ) {
			return $booked_dates;
		}

		global $iconic_wds;

		if ( false === $max_order_calculation_method ) {
			$max_order_calculation_method = $iconic_wds->settings['general_setup_max_order_calculation_method'];
		}

		global $wpdb;

		$booked_dates = array();

		$orders_table_name     = Helpers::is_cot_enabled() ? OrdersTableDataStore::get_orders_table_name() : $wpdb->posts;
		$status_column_name    = Helpers::is_cot_enabled() ? 'status' : 'post_status';
		$excluded_order_status = Iconic_WDS::get_excluded_order_statuses();

		$exclude_orders = array( 0 );
		if ( ! empty( $this->order ) ) {
			$exclude_orders[] = esc_sql( $this->order->get_id() );
		}

		/*
		If max order calculation method is 'orders' then we count orders per day,
		else if method is 'products' then we count number of products in orders, per day.
		*/
		if ( 'orders' === $max_order_calculation_method ) {
			$booked_dates = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DATE_FORMAT( date, '%%Y%%m%%d' ) as ymd, COUNT( date ) as count FROM
					{$wpdb->prefix}jckwds wds, " . esc_sql( $orders_table_name ) . ' p
					WHERE
					wds.order_id = p.ID
					AND p.' . esc_sql( $status_column_name ) . ' not in (' .
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped in get_excluded_order_statuses() function.
					$excluded_order_status
					. ')
					AND NOT ( user_id = %s AND processed = 0 )' .
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped
					'AND wds.order_id NOT IN ( ' . implode( ',', $exclude_orders ) . ' )
					AND date >= %s GROUP BY ymd ORDER BY ymd',
					$iconic_wds->user_id,
					current_time( 'Y-m-d 00:00:00' )
				),
				ARRAY_A
			);

		} else {
			$booked_dates = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
						DATE_FORMAT( wds.date, '%%Y%%m%%d' ) as ymd, SUM( meta.meta_value ) as count
					FROM
						{$wpdb->prefix}jckwds wds
						INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON wds.order_id = items.order_id
						INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta ON items.order_item_id = meta.order_item_id
						INNER JOIN " . esc_sql( $orders_table_name ) . " orders ON wds.order_id = orders.ID
					WHERE 
						NOT ( wds.user_id = %s AND wds.processed = 0 )
						AND wds.date >= %s
						AND meta.meta_key = '_qty'
						AND orders." . esc_sql( $status_column_name ) . ' NOT IN ( '
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped in get_excluded_order_statuses() function.
						. $excluded_order_status .
						')' .
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped
						'AND wds.order_id NOT IN ( ' . implode( ',', $exclude_orders ) . ' )
					GROUP BY ymd
					ORDER BY ymd',
					$iconic_wds->user_id,
					current_time( 'Y-m-d 00:00:00' )
				),
				ARRAY_A
			);
		}

		if ( empty( $booked_dates ) || is_wp_error( $booked_dates ) ) {
			$booked_dates = false;
		}

		return $booked_dates;
	}

	/**
	 * Is timeslot allowed for selected shipping method.
	 *
	 * @param array $timeslot Timeslot.
	 * @param string $chosen_method Chosen shipping method.
	 *
	 * @return bool
	 */
	public function is_timeslot_allowed_for_method( $timeslot, $chosen_method ) {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			/**
			 * Is timeslot allowed for the current shipping method.
			 *
			 * @param bool  $is_allowed Is timeslot allowed.
			 * @param array $timeslot   Timeslot data.
			 *
			 * @since 1.9.2
			 */
			return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', true, $timeslot );
		}

		if ( ! $timeslot['shipping_methods'] ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', false, $timeslot );
		}

		if ( in_array( 'any', $timeslot['shipping_methods'], true ) ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', true, $timeslot );
		}

		if ( in_array( 'any_virtual', $timeslot['shipping_methods'], true ) && $this->cart->are_all_products_virtual() ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', true, $timeslot );
		}

		if ( in_array( $chosen_method, $timeslot['shipping_methods'], true ) ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', true, $timeslot );
		}

		foreach ( $timeslot['shipping_methods'] as $timeslot_shipping_method ) {
			if ( $chosen_method && ( strval( $timeslot_shipping_method ) === strval( $chosen_method ) ) ) {
				// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
				return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', true, $timeslot );
			}
		}

		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		return apply_filters( 'iconic_wds_timeslot_shipping_method_allowed', false, $timeslot );
	}
}
