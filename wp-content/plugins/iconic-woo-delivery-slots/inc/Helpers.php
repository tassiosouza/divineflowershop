<?php
/**
 * WDS Helper class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use DateTime, DateTimeZone;
use WC_Order;
use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Helpers.
 *
 * @class    Helpers
 * @version  1.0.0
 */
class Helpers {
	/**
	 * Run.
	 */
	public static function run() {
		self::add_filters();
	}

	/**
	 * Add filters.
	 */
	public static function add_filters() {
		add_filter( 'woocommerce_form_field_hidden', array( __CLASS__, 'form_field_hidden' ), 10, 4 );
	}

	/**
	 * Output hidden form field.
	 *
	 * @param string $field Field.
	 * @param string $key   Key.
	 * @param array  $args  Argument.
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public static function form_field_hidden( $field, $key, $args, $value ) {
		global $jckwds;

		// If Woo is >= 4.5.0 (when hidden field was added to Woo).
		if ( version_compare( $jckwds->get_woo_version_number(), '4.5.0', '>=' ) ) {
			return $field;
		}

		$field .= '<input type="hidden" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" />';

		return $field;
	}

	/**
	 * Get time formatted.
	 *
	 * @param string $time Hi formatted time.
	 *
	 * @return string
	 */
	public static function get_time_formatted( $time ) {
		global $jckwds;

		$time_format = $jckwds->settings['timesettings_timesettings_setup_timeformat'];
		$time        = DateTime::createFromFormat( 'Hi', str_pad( $time, 4, '0', STR_PAD_LEFT ), wp_timezone() );

		return $time->format( $time_format );
	}

	/**
	 * Get date formatted.
	 *
	 * @param string $date Y-m-d H:i:s Date.
	 *
	 * @return string
	 */
	public static function get_date_formatted( $date ) {
		$date_format = get_option( 'date_format' );
		$date        = new DateTime( $date );

		return $date->format( $date_format );
	}

	/**
	 * Get email link HTML.
	 *
	 * @param string $email Email.
	 *
	 * @return string
	 */
	public static function get_email_link_html( $email ) {
		return sprintf( '<a href="mailto:%s" target="_blank">%s</a>', esc_url( $email ), $email );
	}

	/**
	 * Get user's name.
	 *
	 * @param WP_User $user User.
	 *
	 * @return string
	 */
	public static function get_user_name( $user ) {
		$full_name = implode(
			' ',
			array(
				$user->first_name,
				$user->last_name,
			)
		);

		return ! empty( $full_name ) ? $full_name : $user->user_login;
	}

	/**
	 * Labels by type.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return array
	 */
	public static function get_labels_by_type( $order = null ) {
		$label_type = self::get_label_type( $order );

		$labels_by_type = apply_filters(
			'iconic_wds_labels_by_type',
			array(
				'delivery'   => array(
					'details'           => apply_filters( 'iconic_wds_delivery_details_text', __( 'Delivery Details', 'jckwds' ), $order ),
					'date'              => apply_filters( 'iconic_wds_delivery_date_text', __( 'Delivery Date', 'jckwds' ), $order ),
					'select_date'       => apply_filters( 'iconic_wds_select_delivery_date_text', __( 'Select a delivery date', 'jckwds' ), $order ),
					'choose_date'       => apply_filters( 'iconic_wds_choose_delivery_date_text', __( 'Please choose a date for your delivery.', 'jckwds' ), $order ),
					'select_date_first' => apply_filters( 'iconic_wds_select_date_first_text', __( 'Please select a date first...', 'jckwds' ) ),
					'time_slot'         => apply_filters( 'iconic_wds_time_slot_text', __( 'Time Slot', 'jckwds' ), $order ),
					'choose_time_slot'  => apply_filters( 'iconic_wds_choose_time_slot_text', __( 'Please choose a time slot for your delivery.', 'jckwds' ), $order ),
					'select_time_slot'  => apply_filters( 'iconic_wds_select_time_slot_text', __( 'Please select a time slot...', 'jckwds' ), $order ),
					'no_time_slots'     => apply_filters( 'iconic_wds_no_slots_available_text', __( 'Sorry, no slots available...', 'jckwds' ) ),
				),
				'collection' => array(
					'details'           => __( 'Collection Details', 'jckwds' ),
					'date'              => __( 'Collection Date', 'jckwds' ),
					'select_date'       => __( 'Select a collection date', 'jckwds' ),
					'choose_date'       => __( 'Please choose a date for your collection.', 'jckwds' ),
					'select_date_first' => __( 'Please select a date first...', 'jckwds' ),
					'time_slot'         => __( 'Time Slot', 'jckwds' ),
					'choose_time_slot'  => __( 'Please choose a time slot for your collection.', 'jckwds' ),
					'select_time_slot'  => __( 'Please select a time slot...', 'jckwds' ),
					'no_time_slots'     => __( 'Sorry, no slots available...', 'jckwds' ),
				),
			),
			$label_type,
			$order
		);

		return isset( $labels_by_type[ $label_type ] ) ? $labels_by_type[ $label_type ] : $labels_by_type['delivery'];
	}

	/**
	 * Get label.
	 *
	 * @param string|bool $type  Type.
	 * @param null        $order Order.
	 *
	 * @return bool|string|array
	 */
	public static function get_label( $type = false, $order = null ) {
		$labels_by_type = self::get_labels_by_type( $order );

		// Keep individual strings filtered for
		// backwards compatibility.
		$labels = apply_filters( 'iconic_wds_labels', $labels_by_type, $order );

		if ( ! $type ) {
			return $labels;
		}

		if ( empty( $labels[ $type ] ) ) {
			return false;
		}

		return $labels[ $type ];
	}

	/**
	 * Get label type.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_label_type( $order = null ) {
		global $jckwds;

		if ( $order ) {
			$shipping_method = Order::get_shipping_method_id( $order );
		} else {
			$shipping_method = $jckwds->get_chosen_shipping_method();
		}

		$type = self::get_shipping_method_label_type( $shipping_method );

		return apply_filters( 'iconic_wds_get_label_type', $type, $order );
	}

	/**
	 * Get shipping method label type.
	 *
	 * @param string $shipping_method Shipping method.
	 *
	 * @return string
	 */
	public static function get_shipping_method_label_type( $shipping_method ) {
		global $iconic_wds;

		$label_type = $iconic_wds->settings['general_setup_labels'];

		if ( empty( $shipping_method ) ) {
			return apply_filters( 'iconic_wds_get_shipping_method_label_type', $label_type );
		}

		$override_label_type    = false;
		$shipping_method_labels = Settings::get_shipping_method_labels();

		if ( ! empty( $shipping_method_labels[ $shipping_method ] ) ) {
			$override_label_type = $shipping_method_labels[ $shipping_method ];
		}

		$label_type = 'default' === $override_label_type ? $label_type : $override_label_type;

		return apply_filters( 'iconic_wds_get_shipping_method_label_type', $label_type );
	}

	/**
	 * Try and get order ID if present.
	 *
	 * @return int|null
	 */
	public static function get_order_id() {
		global $order;

		if ( is_a( $order, 'WC_Order' ) ) {
			return $order->get_id();
		}

		$order_id = absint( filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $order_id ) {
			return $order_id;
		}

		return null;
	}

	/**
	 * Search a multidimensional array by key/value.
	 *
	 * @param mixed $key   The key to find.
	 * @param mixed $value The value to match.
	 * @param array $array A multidimensional array.
	 *
	 * @return bool|mixed
	 */
	public static function search_array_by_key_value( $key, $value, $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return false;
		}

		foreach ( $array as $array_key => $array_value ) {
			if ( (string) $array_value[ $key ] === (string) $value ) {
				return $array[ $array_key ];
			}
		}

		return false;
	}

	/**
	 * Get filtered input.
	 *
	 * @param string $key         Key.
	 * @param string $type        String|int|etc.
	 * @param string $nonce_key   Nonce key.
	 * @param string $nonce_value Nonce Value.
	 *
	 * @return array|int|mixed|string
	 */
	public static function get_filtered_input( $key, $type = 'string', $nonce_key = '', $nonce_value = '' ) {
		if ( $nonce_key ) {
			check_admin_referer( $nonce_key, $nonce_value );
		}

		if ( ! isset( $_POST[ $key ] ) ) {
			return false;
		}

		if ( 'string' === $type ) {
			$input = sanitize_text_field( filter_var( wp_unslash( $_POST[ $key ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		} elseif ( 'int' === $type ) {
			$input = (int) sanitize_text_field( filter_var( wp_unslash( $_POST[ $key ] ), FILTER_SANITIZE_NUMBER_INT ) );
		} elseif ( 'array' === $type ) {
			$input = filter_input( INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		} else {
			$input = filter_var( wp_unslash( $_POST[ $key ] ) );
		}

		return $input;
	}

	/**
	 * Get weekdays.
	 *
	 * @return array.
	 */
	public static function get_weekdays() {
		return array(
			'0' => __( 'Sunday', 'jckwds' ),
			'1' => __( 'Monday', 'jckwds' ),
			'2' => __( 'Tuesday', 'jckwds' ),
			'3' => __( 'Wednesday', 'jckwds' ),
			'4' => __( 'Thursday', 'jckwds' ),
			'5' => __( 'Friday', 'jckwds' ),
			'6' => __( 'Saturday', 'jckwds' ),
		);
	}

	/**
	 * Determine allowed shipping methods for the given address.
	 *
	 * @param string $country  Country.
	 * @param string $state    State.
	 * @param string $city     City.
	 * @param string $postcode Postcode.
	 *
	 * @return array|bool
	 */
	public static function get_allowed_shipping_methods_for_address( $country, $state, $city, $postcode ) {
		$address = array(
			'country'   => ! empty( $country ) ? $country : WC()->countries->get_base_country(),
			'state'     => $state,
			'postcode'  => $postcode,
			'city'      => $city,
			'address'   => '',
			'address_1' => '', // Provide both address and address_1 for backwards compatibility.
			'address_2' => '',
		);

		$packages = WC()->shipping()->calculate_shipping(
			array(
				array(
					'contents'        => array(),
					'contents_cost'   => 0,
					'applied_coupons' => array(),
					'user'            => array(
						'ID' => get_current_user_id(),
					),
					'destination'     => $address,
					'cart_subtotal'   => 0,
				),
			)
		);

		if ( empty( $packages ) ) {
			return false;
		}
		$package               = $packages[0];
		$rates                 = $package['rates'];
		$postcode_methods      = array();
		$formatted_destination = WC()->countries->get_formatted_address( $package['destination'], ', ' );

		foreach ( $rates as $shipping_rate ) {
			$cost = floatval( $shipping_rate->get_cost() );
			$postcode_methods[ $shipping_rate->get_id() ] = array(
				'label' => $shipping_rate->get_label(),
				'cost'  => $cost ? wc_price( $cost ) : esc_html__( 'Free', 'jckwds' ),
			);
		}

		return array(
			'shipping_methods'      => $postcode_methods,
			'formatted_destination' => $formatted_destination,
		);
	}

	/**
	 * Get shipping methods based on current user session data.
	 *
	 * @return array
	 */
	public static function get_allowed_shipping_methods_for_current_session() {
		$return = array();

		if ( empty( WC()->customer ) || empty( WC()->customer->get_shipping_country() ) ) {
			return $return;
		}

		return self::get_allowed_shipping_methods_for_address( WC()->customer->get_shipping_country(), WC()->customer->get_shipping_state(), WC()->customer->get_shipping_city(), WC()->customer->get_shipping_postcode() );
	}

	/**
	 * Return shipping method type (delivery or collection).
	 *
	 * @param string $shipping_method_id Shipping method ID.
	 *
	 * @return string
	 */
	public static function get_shipping_method_type( $shipping_method_id ) {
		global $iconic_wds;

		$default_label_type     = $iconic_wds->settings['general_setup_labels'];
		$shipping_method_labels = Settings::get_shipping_method_labels();

		if ( empty( $shipping_method_labels[ $shipping_method_id ] ) ) {
			return $default_label_type;
		}

		$shipping_method_type = $shipping_method_labels[ $shipping_method_id ];
		$shipping_method_type = 'default' === $shipping_method_type ? $default_label_type : $shipping_method_type;

		return $shipping_method_type;
	}

	/**
	 * Remove duplicate rows from an associative array.
	 *
	 * @param array  $arr Associative Array.
	 * @param string $key Key based on which to remove duplicates.
	 *
	 * @return array
	 */
	public static function remove_duplicates_in_assoc_by_key( $arr, $key ) {
		$result  = array();
		$present = array();

		foreach ( $arr as $k => $val ) {
			if ( ! isset( $present[ $val[ $key ] ] ) ) {
				$result[]                = $val;
				$present[ $val[ $key ] ] = 1;
			}
		}

		return $result;
	}

	/**
	 * Increases or decreases the brightness of a color by a percentage of the current brightness.
	 *
	 * @param string $hex_code        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`.
	 * @param float  $adjust_percent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
	 *
	 * @return  string
	 */
	public static function adjust_color_brightness( $hex_code, $adjust_percent ) {
		$hex_code = ltrim( $hex_code, '#' );

		if ( strlen( $hex_code ) === 3 ) {
			$hex_code = $hex_code[0] . $hex_code[0] . $hex_code[1] . $hex_code[1] . $hex_code[2] . $hex_code[2];
		}

		$hex_code = array_map( 'hexdec', str_split( $hex_code, 2 ) );

		foreach ( $hex_code as & $color ) {
			$adjustable_limit = $adjust_percent < 0 ? $color : 255 - $color;
			$adjust_amount    = ceil( $adjustable_limit * $adjust_percent );

			$color = str_pad( dechex( $color + $adjust_amount ), 2, '0', STR_PAD_LEFT );
		}

		return '#' . implode( $hex_code );
	}

	/**
	 * Find and return the Fee line item for delivery slot.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return WC_Order_Item
	 */
	public static function get_delivery_fees_line_item( $order ) {
		global $iconic_wds;
		$fees_name = $iconic_wds->fee->get_fee_name();

		foreach ( $order->get_fees() as $item_id => $fees_item ) {
			if ( $fees_name === $fees_item->get_name() ) {
				return $fees_item;
			}
		}

		return false;
	}

	/**
	 * Get order meta.
	 *
	 * Also checks the non hidden version (without underscore) of the meta key to
	 * keep compatibility with legacy meta keys.
	 *
	 * @param WC_order $order Order.
	 * @param string   $key   Key.
	 *
	 * @return mixed
	 */
	public static function get_order_meta( $order, $key ) {
		if ( empty( $order ) ) {
			return false;
		}

		$non_hidden_key = false;

		$meta = $order->get_meta( $key );

		if ( ! empty( $meta ) ) {
			return $meta;
		}

		// If underscore doesn't exist in the first position, return.
		if ( 0 !== strpos( $key, '_' ) ) {
			return $meta;
		}

		// Check if non-hidden version of meta (without underscore) exists.
		$key = ltrim( $key, '_' );

		return $order->get_meta( $key );
	}

	/**
	 * Is custom order table enabled?
	 *
	 * @return bool
	 */
	public static function is_cot_enabled() {
		if ( version_compare( WC_VERSION, '7.1', '<' ) ) {
			// Old version.
			return false;
		} else {
			// Newer version.
			return OrderUtil::custom_orders_table_usage_is_enabled();
		}
	}

	/**
	 * Get shipping method from instance ID.
	 * Caches the result so event if same instance ID is requested multiple times,
	 * this function would return the cached result.
	 *
	 * @param int $instance_id Instance ID.
	 *
	 * @return WC_Shipping_Method
	 */
	public static function get_shipping_method_from_instance_id( $instance_id ) {
		static $methods = array();

		if ( isset( $methods[ $instance_id ] ) ) {
			return $methods[ $instance_id ];
		}

		$methods[ $instance_id ] = \WC_Shipping_Zones::get_shipping_method( $instance_id );

		return $methods[ $instance_id ];
	}

	/**
	 * Returns the count of items in the WooCommerce cart.
	 */
	public static function get_cart_count() {
		$result            = 0;
		$excluded_products = self::get_max_order_excluded_products();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( ! in_array( $cart_item['product_id'], $excluded_products ) ) {
				$result += $cart_item['quantity'];
			}
		}

		/**
		 * Allows other code to modify or manipulate the value of cart content before
		 * it is returned.
		 *
		 * @since 1.25.0
		 */
		return apply_filters( 'iconic_wds_get_cart_count', $result );
	}

	/**
	 * Get IDs of the products which will not be counted for max order calculation limit.
	 *
	 * @return array
	 */
	public static function get_max_order_excluded_products() {
		global $iconic_wds;

		$excluded_products = isset( $iconic_wds->settings['general_setup_max_orders_exclude_products'] ) ? $iconic_wds->settings['general_setup_max_orders_exclude_products'] : array( -1 );

		// We need atleast one value to maintain SQL query.
		if ( empty( $excluded_products ) ) {
			$excluded_products = array( -1 );
		}

		/**
		 * Allow other code to modify the list of product IDs that will be excluded from the maximum order calculation.
		 *
		 * For example, a store can bake 5 pizzas per timeslot, but there is no limit to the number of cold drinks per
		 * timeslot. By passing the ID of the cold drink to this array, the maximum order calculation will skip counting
		 * the cold drink, allowing for any number of cold drinks to be added to the cart.
		 *
		 * @since 1.25.0
		 */
		return apply_filters( 'iconic_wds_get_max_order_excluded_products', $excluded_products );
	}

	/**
	 * Get allowed tags for kses.
	 *
	 * @return array
	 */
	public static function get_kses_allowed_tags() {
		$allowed_tags = array(
			'br'     => array(),
			'a'      => array(
				'id'    => true,
				'href'  => true,
				'title' => true,
			),
			'strong' => array(),
			'p'      => array(),
		);

		/**
		 * Kses allowed tags.
		 *
		 * @since 1.25.0
		 */
		return apply_filters( 'iconic_wds_kses_allowed_tags', $allowed_tags );
	}

	/**
	 * Returns false of the ID of the order if it is the Thank you page.
	 *
	 * @return bool|int False or Order ID.
	 */
	public static function is_thankyou_page() {
		if ( is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
			global $wp;
			if ( isset( $wp->query_vars['order-received'] ) ) {
				$order_id = absint( $wp->query_vars['order-received'] );
				return $order_id;
			}

			return false;
		}

		return false;
	}

	/**
	 * Is View order page.
	 *
	 * @return int Order ID.
	 */
	public static function is_my_account_order_page() {
		global $wp;

		return ( is_view_order_page() && isset( $wp->query_vars['view-order'] ) ) ? $wp->query_vars['view-order'] : false;
	}

	/**
	 * Get timezone offset for the given timestamp.
	 *
	 * This function is inspired by wc_timezone_offset().
	 * wc_timezone_offset() always returns the offset for today's date,
	 * which is inacurate during daylight savings.
	 * Hence this function, which returns offset for specified date.
	 *
	 * @param int $timestamp Timestamp.
	 *
	 * @return int
	 */
	public static function get_timezone_offset( $timestamp ) {
		$timezone = get_option( 'timezone_string' );

		if ( $timezone ) {
			$timezone_object = new DateTimeZone( $timezone );
			$datetime        = new DateTime();
			$datetime->setTimestamp( $timestamp );

			return $timezone_object->getOffset( $datetime );
		} else {
			return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
		}
	}

	/**
	 * Difference between 2 timestamps in days.
	 *
	 * @param int      $then UTC timestamp.
	 * @param int|bool $now  If false, uses today.
	 *
	 * @return float|int
	 */
	public static function get_difference_in_days( $then, $now = false ) {
		$now = $now ? $now : time();

		return absint( floor( abs( $now - $then ) / 60 / 60 / 24 ) );
	}

	/**
	 * Get date format based on settings
	 *
	 * @param string $js_format JS formatted date to convert to PHP format.
	 *
	 * @return string
	 */
	public static function date_format( $js_format = '' ) {
		global $jckwds;

		$js_format = empty( $js_format ) ? $jckwds->settings['datesettings_datesettings_dateformat'] : $js_format;

		$trans = array(
			// Days.
			'dd' => 'd',
			'd'  => 'j',
			'DD' => 'l',
			'o'  => 'z',

			// Months.
			'MM' => 'F',
			'M'  => 'M',
			'mm' => 'm',
			'm'  => 'n',

			// Years.
			'yy' => 'Y',
			'y'  => 'y',
		);

		return strtr( $js_format, $trans );
	}

	/**
	 * Date format as compatible with date-fns library.
	 * Reference:
	 * 1. https://date-fns.org/docs/format
	 * 2. https://date-fns-interactive.netlify.app/
	 *
	 * @param string $js_format JS formatted date to convert to date-fns format.
	 *
	 * @return string
	 */
	public static function date_format_fns( $js_format = '' ) {
		global $iconic_wds;

		$js_format = empty( $js_format ) ? $iconic_wds->settings['datesettings_datesettings_dateformat'] : $js_format;

		$trans = array(
			// Days.
			'dd' => 'dd',
			'd'  => 'd',
			'DD' => 'EEEE',
			'o'  => 'D',

			// Months.
			'MM' => 'LLLL',
			'M'  => 'LLL',
			'mm' => 'LL',
			'm'  => 'L',

			// Years.
			'yy' => 'yyyy',
			'y'  => 'yy',
		);

		return strtr( $js_format, $trans );
	}

	/**
	 * Convert date to database format (Y-m-d)
	 *
	 * @param string $date   Date.
	 * @param string $format Date Format.
	 *
	 * @return string
	 */
	public static function convert_date_for_database( $date, $format = 'Ymd' ) {
		$dformat = DateTime::createFromFormat( $format, $date, wp_timezone() );

		return $dformat->format( 'Y-m-d' );
	}

	/**
	 * Get current timestamp.
	 *
	 * @return float|int
	 */
	public static function get_current_timestamp() {
		return time() + wc_timezone_offset();
	}

	/**
	 * Compare timestamp to current day.
	 *
	 * @param int $timestamp UTC timestamp of date to check.
	 *
	 * @return bool
	 */
	public static function is_same_day( $timestamp ) {
		$today   = current_time( 'Ymd', 1 );
		$compare = gmdate( 'Ymd', $timestamp );

		return $today === $compare;
	}

	/**
	 * Get the error message from conflict.
	 *
	 * @param array $conflict Conflict Data.
	 *
	 * @return bool|string
	 */
	public static function get_conflict_error_message( $conflict ) {
		if ( empty( $conflict ) || ! empty( $conflict['common_days'] ) ) {
			return false;
		}

		// Convert product Ids into product names.
		$product_names = array();
		foreach ( $conflict['overrides'] as $override ) {
			$product_names[] = get_the_title( $override['product_id'] );
		}
		$product_names = implode( ', ', $product_names );

		// Translators: Comma seperated product names.
		$message = sprintf( esc_html__( 'These products are not available for delivery/pickup on the same date: %s. Please remove one from the cart and place a separate order.', 'jckwds' ), esc_html( $product_names ) );

		return $message;
	}

	/**
	 * Get localized date strings.
	 *
	 * @return array
	 */
	public static function get_localized_date_strings() {
		$string = array(
			'days'         => array(
				__( 'Sunday', 'jckwds' ),
				__( 'Monday', 'jckwds' ),
				__( 'Tuesday', 'jckwds' ),
				__( 'Wednesday', 'jckwds' ),
				__( 'Thursday', 'jckwds' ),
				__( 'Friday', 'jckwds' ),
				__( 'Saturday', 'jckwds' ),
			),
			'days_short'   => array(
				__( 'Su', 'jckwds' ),
				__( 'Mo', 'jckwds' ),
				__( 'Tu', 'jckwds' ),
				__( 'We', 'jckwds' ),
				__( 'Th', 'jckwds' ),
				__( 'Fr', 'jckwds' ),
				__( 'Sa', 'jckwds' ),
			),
			'months'       => array(
				__( 'January', 'jckwds' ),
				__( 'February', 'jckwds' ),
				__( 'March', 'jckwds' ),
				__( 'April', 'jckwds' ),
				__( 'May', 'jckwds' ),
				__( 'June', 'jckwds' ),
				__( 'July', 'jckwds' ),
				__( 'August', 'jckwds' ),
				__( 'September', 'jckwds' ),
				__( 'October', 'jckwds' ),
				__( 'November', 'jckwds' ),
				__( 'December', 'jckwds' ),
			),
			'months_short' => array(
				__( 'Jan', 'jckwds' ),
				__( 'Feb', 'jckwds' ),
				__( 'Mar', 'jckwds' ),
				__( 'Apr', 'jckwds' ),
				__( 'May', 'jckwds' ),
				__( 'Jun', 'jckwds' ),
				__( 'Jul', 'jckwds' ),
				__( 'Aug', 'jckwds' ),
				__( 'Sep', 'jckwds' ),
				__( 'Oct', 'jckwds' ),
				__( 'Nov', 'jckwds' ),
				__( 'Dec', 'jckwds' ),
			),
		);

		return $string;
	}

	/**
	 * Get localized strings.
	 *
	 * @return array
	 */
	public static function get_localized_strings() {
		$date_strings = self::get_localized_date_strings();

		return array(
			'selectslot'         => self::get_label( 'select_time_slot' ),
			'selectdate_first'   => self::get_label( 'select_date_first' ),
			'selectdate'         => self::get_label( 'select_date' ),
			'noslots'            => self::get_label( 'no_time_slots' ),
			'loading'            => apply_filters( 'iconic_wds_loading_text', __( 'Loading...', 'jckwds' ) ),
			'available'          => __( 'Available', 'jckwds' ),
			'unavailable'        => __( 'Unavailable', 'jckwds' ),
			'timeslot_saved'     => __( 'Success! Your time slot has been successfully updated.', 'jckwds' ),
			'cant_save_timeslot' => __( 'Failed to save timeslot information. Please try again later.', 'jckwds' ),
			'reservation_table'  => array(
				'none_selected'      => _x( 'None selected', 'Reservation table', 'jckwds' ),
				'cancel'             => _x( 'Cancel', 'Reservation table', 'jckwds' ),
				'add'                => _x( 'Add', 'Reservation table', 'jckwds' ),
				'edit'               => _x( 'Edit', 'Reservation table', 'jckwds' ),
				'continue'           => _x( 'Continue', 'Reservation table', 'jckwds' ),
				'select_state'       => _x( 'Select State', 'Reservation table', 'jckwds' ),
				'select_country'     => _x( 'Select Country', 'Reservation table', 'jckwds' ),
				'country'            => __( 'Country/Region', 'woocommerce' ),
				'cant_book_slot'     => _x( 'Could not book the slot. Please try again.', 'Reservation table', 'jckwds' ),
				'reserve'            => _x( 'Reserve', 'Reservation table', 'jckwds' ),
				'free'               => _x( 'Free', 'Reservation table', 'jckwds' ),
				'no_shiping_methods' => _x( 'No shipping methods are available for your address', 'Reservation table', 'jckwds' ),
				'delivery'           => _x( 'Delivery', 'Reservation table', 'jckwds' ),
			),
			'days'               => $date_strings['days'],
			'days_short'         => $date_strings['days_short'],
			'months'             => $date_strings['months'],
			'months_short'       => $date_strings['months_short'],
		);
	}

	/**
	 * Get choosen shipping method from the cart extension REST API request.
	 *
	 * @return string|bool
	 */
	public static function get_chosen_shipping_method_cart_extension() {
		$postdata = file_get_contents( 'php://input' );
		$request  = json_decode( $postdata );

		if ( is_object( $request ) && ! empty( $request->requests[0]->data->data->shipping_method ) ) {
			return $request->requests[0]->data->data->shipping_method;
		}

		// Fallback to session if shipping method is not available in the cart extension request.
		$chosen_method = Iconic_WDS::get_chosen_shipping_method_for_session( true );
		if ( $chosen_method ) {
			return $chosen_method;
		}

		return false;
	}

	/**
	 * If checkout page is using checkout block.
	 *
	 * @return bool
	 */
	public static function is_checkout_page_using_block( $page_id = false ) {
		$checkout_page = get_post( $page_id ? $page_id : wc_get_page_id( 'checkout' ) );

		if ( empty( $checkout_page ) ) {
			return false;
		}

		$checkout_page_content = $checkout_page->post_content;

		return has_block( 'woocommerce/checkout', $checkout_page_content );
	}

	/**
	 * Check if checkout page has the WDS block.
	 *
	 * @return bool
	 */
	public static function checkout_page_has_wds_block() {
		if ( ! self::is_checkout_page_using_block() ) {
			return false;
		}

		$checkout_page_post = get_post( wc_get_page_id( 'checkout' ) );
		if ( empty( $checkout_page_post ) ) {
			return false;
		}

		return has_block( 'iconic-wds/iconic-wds', $checkout_page_post->post_content );
	}

	/**
	 * Check if current page is block checkout.
	 */
	public static function is_block_checkout() {
		return has_block( 'woocommerce/checkout' );;
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function is_wc_active() {
		return function_exists( 'WC' );
	}

	/**
	 * Get accounting script handle.
	 *
	 * @return string
	 */
	public static function get_accounting_script_handle() {
		return version_compare( WC_VERSION, '10.3', '<' ) ? 'accounting' : 'wc-accounting';
	}
}
