<?php
/**
 * WDS Shortcode class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcodes.
 *
 * @class    Shortcodes
 * @version  1.0.0
 */
class Shortcodes {
	/**
	 * Run.
	 */
	public static function run() {
		add_shortcode( 'iconic-wds-next-delivery-date', array( __CLASS__, 'next_delivery_date' ) );
		add_shortcode( 'iconic-wds-get-order-date', array( __CLASS__, 'get_order_date' ) );
		add_shortcode( 'iconic-wds-get-order-time', array( __CLASS__, 'get_order_time' ) );
		add_shortcode( 'iconic-wds-get-order-date-time', array( __CLASS__, 'get_order_date_time' ) );
		add_shortcode( 'iconic-wds-reservation-table', array( __CLASS__, 'reservation_table' ) );
		add_shortcode( 'jckwds', array( __CLASS__, 'reservation_table' ) );
		add_shortcode( 'iconic-wds-reserved-slot', array( __CLASS__, 'reserved_slot' ) );
		add_shortcode( 'iconic-wds-lead-time', array( __CLASS__, 'lead_time' ) );
		add_shortcode( 'iconic-wds-allowed-days', array( __CLASS__, 'allowed_days' ) );
		add_shortcode( 'iconic-wds-fields', array( __CLASS__, 'checkout_fields' ) );
	}

	/**
	 * Output next available delivery date.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string
	 */
	public static function next_delivery_date( $atts ) {
		global $jckwds;

		$atts = shortcode_atts(
			array(
				'format' => 'admin_formatted',
			),
			$atts,
			'iconic-wds-next-delivery-date'
		);

		$upcoming_bookable_dates = $jckwds->get_upcoming_bookable_dates();

		$date = isset( $upcoming_bookable_dates[0][ $atts['format'] ] ) ? $upcoming_bookable_dates[0][ $atts['format'] ] : '';

		return apply_filters( 'iconic_wds_next_delivery_date', $date, $upcoming_bookable_dates );
	}

	/**
	 * Get order date.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string|void
	 */
	public static function get_order_date( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => Helpers::get_order_id(),
			),
			$atts,
			'iconic-wds-get-order-date'
		);

		if ( empty( $atts['id'] ) ) {
			return;
		}

		$order = wc_get_order( $atts['id'] );
		$data  = Order::get_order_date_time( $order );

		if ( empty( $data ) ) {
			return;
		}

		/**
		 * Get order time shortcode output.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'iconic_wds_shortcode_get_order_date', wp_kses( $data['date'], Helpers::get_kses_allowed_tags() ), $atts ); // nosemgrep
	}

	/**
	 * Get order time.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string|void
	 */
	public static function get_order_time( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => Helpers::get_order_id(),
			),
			$atts,
			'iconic-wds-get-order-time'
		);

		if ( empty( $atts['id'] ) ) {
			return;
		}

		$order = wc_get_order( $atts['id'] );
		$data  = Order::get_order_date_time( $order );

		if ( empty( $data ) ) {
			return;
		}

		/**
		 * Get order time shortcode output.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'iconic_wds_shortcode_get_order_time', wp_kses( $data['time'], Helpers::get_kses_allowed_tags() ), $atts ); // nosemgrep
	}

	/**
	 * Get order time.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string|void
	 */
	public static function get_order_date_time( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => Helpers::get_order_id(),
			),
			$atts,
			'iconic-wds-get-order-date-time'
		);

		if ( empty( $atts['id'] ) ) {
			return;
		}

		global $iconic_wds;

		$order = wc_get_order( $atts['id'] );

		if ( empty( $order ) ) {
			return;
		}

		$date     = Helpers::get_order_meta( $order, $iconic_wds->date_meta_key );
		$timeslot = Helpers::get_order_meta( $order, $iconic_wds->timeslot_meta_key );

		if ( empty( $date ) ) {
			return;
		}

		$return = $date;

		if ( ! empty( $timeslot ) ) {
			$return .= sprintf( ' %s %s', __( 'at', 'jckwds' ), $timeslot );
		}

		/**
		 * Shortcode output for [iconic-wds-get-order-date-time]
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'iconic_wds_shortcode_get_order_date_time', wp_kses( $return, Helpers::get_kses_allowed_tags() ), $atts, $date, $timeslot ); // nosemgrep
	}

	/**
	 * Display reservation table.
	 *
	 * @param array $args Attributes.
	 *
	 * @return string|void
	 */
	public static function reservation_table( $args ) {
		global $jckwds;

		$defaults = array(
			'shipping_method' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		return ReservationTable::generate_reservation_table( $args ); // nosemgrep
	}

	/**
	 * Display current reserved slot.
	 *
	 * @return string|void
	 */
	public static function reserved_slot() {
		global $iconic_wds;

		$slot = $iconic_wds->get_reserved_slot();

		if ( empty( $slot ) ) {
			return;
		}

		$return = '';

		if ( $slot['date'] ) {
			$return .= esc_html( $slot['date']['formatted'] );
		}

		if ( $slot['time'] ) {
			$return .= sprintf( ' %s', esc_html( $slot['time']['formatted_with_fee'] ) );
		}

		return $return; // nosemgrep: audit.php.wp.security.xss.shortcode-attr
	}

	/**
	 * Get lead time.
	 *
	 * @param array $args Shortcode arguments. Accepts one argument i.e. product_id.
	 *
	 * @return string|false
	 */
	public static function lead_time( $args ) {
		$defaults = array(
			'product_id' => -1,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( -1 === $args['product_id'] ) {
			global $product;
			if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$args['product_id'] = $product->get_id();
		}

		if ( empty( $args['product_id'] ) ) {
			return;
		}

		$lead_time = OverrideSettings::get_lead_time_for_product( $args['product_id'] );

		if ( false === $lead_time ) {
			global $iconic_wds;
			$lead_time = $iconic_wds->settings['datesettings_datesettings_minimum'];
		}

		return $lead_time; // nosemgrep
	}

	/**
	 * Get allowed days for the given product.
	 *
	 * @param array $args Shortcode arguments. Allowed arguments are product_id and type. Type can be 'comma-seperated' or 'list', default is 'comma-seperated'.
	 *
	 * @return false|string
	 */
	public static function allowed_days( $args ) {
		$defaults = array(
			'product_id' => -1,
			'type'       => 'comma-seperated',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( -1 === $args['product_id'] ) {
			global $product;
			if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$args['product_id'] = $product->get_id();
		}

		if ( empty( $args['product_id'] ) ) {
			return;
		}

		$allowed_days = OverrideSettings::get_product_specific_days_setting_for_product( $args['product_id'] );

		if ( false === $allowed_days ) {
			// Get global setting.
			global $iconic_wds;
			$allowed_days = $iconic_wds->settings['datesettings_datesettings_days'];
		}

		global $wp_locale;
		$allowed_days_human_readable = array();

		foreach ( $allowed_days as $allowed_day ) {
			$allowed_days_human_readable[] = $wp_locale->get_weekday( $allowed_day );
		}

		$result = '';
		if ( 'list' === $args['type'] ) {
			$result .= '<ul>';
			foreach ( $allowed_days_human_readable as $day ) {
				$result .= sprintf( '<li>%s</li>', $day );
			}
			$result .= '</ul>';
		} else {
			$result .= implode( ', ', $allowed_days_human_readable );
		}

		return $result; // nosemgrep: audit.php.wp.security.xss.shortcode-attr
	}

	/**
	 * Fields shortcode.
	 *
	 * @return string
	 */
	public static function checkout_fields() {
		global $iconic_wds_dates;

		ob_start();
		$iconic_wds_dates->display_checkout_fields();
		return ob_get_clean(); // nosemgrep: audit.php.wp.security.xss.shortcode-attr
	}
}
