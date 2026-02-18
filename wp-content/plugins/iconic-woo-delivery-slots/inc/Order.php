<?php
/**
 * WDS Order class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Automattic\WooCommerce\Utilities\OrderUtil;
use Iconic_WDS\Subscriptions\Boot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order class.
 */
class Order {
	/**
	 * Run.
	 */
	public static function run() {
		self::add_filters();
		self::add_actions();
	}

	/**
	 * Add filters.
	 */
	public static function add_filters() {
		add_filter( 'woocommerce_get_order_item_totals', array( __CLASS__, 'add_to_order_details' ), 10, 3 );
		add_filter( 'woocommerce_shop_order_list_table_columns', array( __CLASS__, 'shop_order_columns' ) );
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'shop_order_columns' ) );
		add_filter( 'manage_edit-shop_order_sortable_columns', array( __CLASS__, 'sortable_shop_order_columns' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_sortable_columns', array( __CLASS__, 'sortable_shop_order_columns' ) );
		add_filter( 'woocommerce_orders_table_query_clauses', array( __CLASS__, 'modify_query_clauses_for_delivery_date_sorting' ), 10, 3 );
		add_filter( 'request', array( __CLASS__, 'orderby_shop_order_columns' ) );

		if ( is_admin() ) {
			add_filter( 'request', array( __CLASS__, 'filter_orders_by_delivery_date' ), 200, 1 );
			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( __CLASS__, 'filter_orders_by_delivery_date' ), 200, 1 );
		}
	}

	/**
	 * Add actions.
	 */
	public static function add_actions() {
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_shop_order_columns' ), 2, 2 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( __CLASS__, 'render_shop_order_columns' ), 2, 2 );
		add_action( 'deleted_post', array( __CLASS__, 'cancel_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'status_changed' ), 10, 3 );
		add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_delivery_details' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'restrict_manage_posts', array( __CLASS__, 'add_delivery_date_filter' ), 20, 1 );
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( __CLASS__, 'add_delivery_date_filter' ), 20, 1 );
		}
	}

	/**
	 * Admin: Add Columns to orders tab
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public static function shop_order_columns( $columns ) {
		$columns['jckwds_delivery'] = Boot::get_active_integration() ? __( 'One Time Delivery', 'jckwds' ) : __( 'Delivery', 'jckwds' );

		return $columns;
	}

	/**
	 * Admin: Output date and timeslot columns on orders tab
	 *
	 * @param string $column Column.
	 */
	public static function render_shop_order_columns( $column, $order ) {
		global $post, $woocommerce, $the_order, $jckwds, $pagenow;

		$current_order = $order;
		$screen        = get_current_screen();

		if ( 'woocommerce_page_wc-orders' !== $screen->id ) {
			if ( empty( $the_order ) || self::get_id( $the_order ) !== $post->ID ) {
				$current_order = wc_get_order( $post->ID );
			} else {
				$current_order = $the_order;
			}
		}

		switch ( $column ) {
			case 'jckwds_delivery':
				$jckwds->display_date_and_timeslot( $current_order, false, false, Helpers::date_format() );
				break;
		}
	}

	/**
	 * Admin: Make delivery column sortable
	 *
	 * @param array $columns Columns.
	 */
	public static function sortable_shop_order_columns( $columns ) {
		$columns['jckwds_delivery'] = 'jckwds_delivery';

		return $columns;
	}

	/**
	 * Modify query clauses for delivery date sorting.
	 *
	 * @param array  $pieces Pieces.
	 * @param string $query Query.
	 * @param array  $args  Args.
	 */
	public static function modify_query_clauses_for_delivery_date_sorting( $pieces, $query, $args ) {
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'jckwds_delivery' !== $orderby ) {
			return $pieces;
		}

		global $wpdb;

		$order             = 'asc' === filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ? 'ASC' : 'DESC';
		$pieces['join']   .= " LEFT JOIN (
							select * from {$wpdb->prefix}wc_orders_meta where meta_key = 'jckwds_timestamp'
							) meta ON {$wpdb->prefix}wc_orders.id = meta.order_id ";
		$pieces['orderby'] = "meta.meta_value {$order}";

		return $pieces;
	}

	/**
	 * Admin: Delivery columns orderby.
	 *
	 * @param array $vars Variables.
	 */
	public static function orderby_shop_order_columns( $vars ) {
		if ( isset( $vars['orderby'] ) && 'jckwds_delivery' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key'         => '^\_?jckwds_timestamp', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_compare_key' => 'REGEXP',
					'orderby'          => 'meta_value_num',
				)
			);
		}

		return $vars;
	}

	/**
	 * Get shipping method ID for order.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool|string
	 */
	public static function get_shipping_method_id( $order ) {
		$shipping_methods = $order->get_shipping_methods();

		if ( empty( $shipping_methods ) ) {
			return false;
		}

		$shipping_method = array_pop( $shipping_methods );
		$instance_id     = $shipping_method->get_instance_id();

		$method = Helpers::get_shipping_method_from_instance_id( $instance_id );

		if ( empty( $method ) ) {
			return false;
		}

		$rate_id = $method->get_rate_id();

		if ( empty( $rate_id ) ) {
			return false;
		}

		return $rate_id;
	}

	/**
	 * Helper: Check if order has date or time
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool|array
	 */
	public static function get_order_date_time( $order ) {
		global $iconic_wds;

		$meta = array(
			'date'            => false,
			'time'            => false,
			'timeslot_id'     => false,
			'timestamp'       => false,
			'ymd'             => false,
			'override_rules'  => false,
			'shipping_method' => false,
		);

		$has_meta        = false;
		$date            = Helpers::get_order_meta( $order, $iconic_wds->date_meta_key );
		$time            = Helpers::get_order_meta( $order, $iconic_wds->timeslot_meta_key );
		$timestamp       = Helpers::get_order_meta( $order, $iconic_wds->timestamp_meta_key );
		$ymd             = Helpers::get_order_meta( $order, $iconic_wds->date_meta_key . '_ymd' );
		$shipping_method = Helpers::get_order_meta( $order, $iconic_wds->shipping_method_meta_key );
		$timeslot_id     = Helpers::get_order_meta( $order, '_jckwds_timeslot_id' );
		$override_rules  = Helpers::get_order_meta( $order, '_jckwds_override_rules' );

		if ( ! empty( $date ) ) {
			$meta['date'] = $date;
			$has_meta     = true;
		}

		if ( ! empty( $time ) ) {
			$meta['time'] = $time;
			$has_meta     = true;
		}

		$meta['ymd']             = $ymd;
		$meta['timestamp']       = $timestamp;
		$meta['timeslot_id']     = $timeslot_id;
		$meta['override_rules']  = $override_rules;
		$meta['shipping_method'] = $shipping_method;

		if ( ! $has_meta ) {
			/**
			 * Delivery slots data for a specific Order.
			 *
			 * @since 1.19.0
			 */
			return apply_filters( 'iconic_wds_delivery_slot_data', false, $order ); // nosemgrep
		}

		/**
		 * Delivery slots data for a specific Order.
		 *
		 * @since 1.19.0
		 */
		return apply_filters( 'iconic_wds_delivery_slot_data', $meta, $order ); // nosemgrep
	}

	/**
	 * Cancel order
	 *
	 * If an order is cancelled, delete the time slot, too
	 *
	 * @param int $order_id Order ID.
	 */
	public static function cancel_order( $order_id ) {
		global $jckwds;

		if ( 'shop_order' !== OrderUtil::get_order_type( $order_id ) ) {
			return;
		}

		global $wpdb;

		$order = wc_get_order( $order_id );

		$delete = $wpdb->delete(
			$jckwds->reservations_db_table_name,
			array(
				'order_id' => $order_id,
			),
			array(
				'%d',
			)
		);

		if ( ! $delete ) {
			return;
		}

		$order->delete_meta_data( $jckwds->date_meta_key );
		$order->delete_meta_data( $jckwds->timeslot_meta_key );
		$order->delete_meta_data( $jckwds->timestamp_meta_key );
		$order->save();
	}

	/**
	 * Delete timeslot when status is changed to chancelled.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $from     From.
	 * @param string $to       To.
	 */
	public static function status_changed( $order_id, $from, $to ) {
		if ( 'cancelled' !== $to ) {
			return;
		}

		self::cancel_order( $order_id );
	}

	/**
	 * Get ID
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_id( $order ) {
		return method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	}

	/**
	 * Get billing first name
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_billing_first_name( $order ) {
		return method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
	}

	/**
	 * Get billing last name
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_billing_last_name( $order ) {
		return method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;
	}

	/**
	 * Get billing email
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_billing_email( $order ) {
		return method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
	}

	/**
	 * Save delivery details when editing an order in admin.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post Object.
	 */
	public static function save_delivery_details( $post_id, $post ) {
		self::update_order_meta( $post_id );
	}

	/**
	 * Get shipping address link.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_shipping_address_link_html( $order ) {
		$address = preg_replace( '#<br\s*/?>#i', ', ', $order->get_formatted_shipping_address() );
		$map_url = $order->get_shipping_address_map_url();

		return sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( $map_url ), $address );
	}

	/**
	 * Get edit order link html.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return string
	 */
	public static function get_edit_order_link_html( $order_id ) {
		$edit_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

		return sprintf( '<a href="%s" target="_blank">#%d</a>', $edit_url, $order_id );
	}

	/**
	 * Get billing full name.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_billing_full_name( $order ) {
		$billing_first_name = self::get_billing_first_name( $order );
		$billing_last_name  = self::get_billing_last_name( $order );

		return sprintf( '%s %s', $billing_first_name, $billing_last_name );
	}

	/**
	 * Get billing email link html.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_billing_email_link_html( $order ) {
		$billing_email = self::get_billing_email( $order );

		return Helpers::get_email_link_html( $billing_email );
	}

	/**
	 * Get status badge.
	 *
	 * @param string $status Status.
	 *
	 * @return string
	 */
	public static function get_status_badge( $status ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$status_label = ucwords( $status );

			return sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', esc_attr( $status ), esc_attr( $status_label ), $status_label );
		} else {
			return sprintf( '<mark class="order-status status-%s"><span>%s</span></mark>', esc_attr( $status ), ucwords( $status ) );
		}
	}

	/**
	 * Get order items.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool|string
	 */
	public static function get_order_items( $order ) {
		$items = $order->get_items();

		if ( empty( $items ) ) {
			return false;
		}

		$items_array = array();

		foreach ( $items as $item ) {
			$quantity      = isset( $item['quantity'] ) ? $item['quantity'] : $item['qty'];
			$items_array[] = sprintf( '%s (x%d)', $item['name'], $quantity );
		}

		return implode( ', ', $items_array );
	}

	/**
	 * Add delivery dates filter to orders screen.
	 *
	 * @param string $screen Screen.
	 */
	public static function add_delivery_date_filter( $screen ) {
		if ( ! in_array( $screen, array( 'shop_order' ), true ) ) {
			return;
		}

		$selected_date = filter_input( INPUT_GET, 'delivery_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$today         = time();
		$today_ymd     = date_i18n( 'Ymd', $today );
		$tomorrow      = strtotime( date_i18n( 'Y-m-d', $today ) . '+1 day' );
		$tomorrow_ymd  = date_i18n( 'Ymd', $tomorrow );
		$options       = array(
			$today_ymd    => __( 'Today', 'jckwds' ),
			$tomorrow_ymd => __( 'Tomorrow', 'jckwds' ),
		);
		$months        = self::get_months_with_deliveries();
		$options       = $options + $months;
		?>
		<select name="delivery_date" class="wc-enhanced-select">
			<option selected="selected" value=""><?php esc_attr_e( 'All delivery dates', 'jckwds' ); ?></option>
			<?php
			foreach ( $options as $date => $label ) {
				?>
				<option value="<?php echo esc_attr( $date ); ?>" <?php selected( $selected_date, $date ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}

	/**
	 * Get list of months with deliveries in them.
	 *
	 * @return array
	 */
	public static function get_months_with_deliveries() {
		global $wpdb;

		static $months = array();

		if ( ! empty( $months ) && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			return $months;
		}

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(date, '%%Y%%m') as month, UNIX_TIMESTAMP(date) as timestamp 
				FROM {$wpdb->prefix}jckwds
				WHERE processed = 1
				AND date >= %s
				GROUP BY month
				ORDER BY month ASC",
				current_time( 'mysql', 1 )
			)
		);

		if ( empty( $result ) ) {
			return $months;
		}

		foreach ( $result as $month ) {
			// Use gmdate() as timestamp is based on timezone already (see query above).
			$months[ $month->month ] = gmdate( 'F Y', $month->timestamp );
		}

		return $months;
	}

	/**
	 * Filter orders by delivery date.
	 *
	 * @param array $query_vars Query variables.
	 *
	 * @return mixed
	 */
	public static function filter_orders_by_delivery_date( $query_vars ) {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ), true ) ) {
			return $query_vars;
		}

		$date = filter_input( INPUT_GET, 'delivery_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $date ) {
			return $query_vars;
		}

		if ( ! isset( $query_vars['meta_query'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$query_vars['meta_query'] = array();
		}

		$query_vars['meta_query'][] = array(
			'key'     => 'jckwds_date_ymd',
			'value'   => '^' . $date,
			'compare' => 'REGEXP',
		);

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$query_vars['meta_key'] = 'jckwds_timestamp';

		return $query_vars;
	}

	/**
	 * Helper: Update order meta on successful checkout submission
	 *
	 * @param int   $order_id Order ID.
	 * @param array $data     Timeslot data.
	 */
	public static function update_order_meta( $order, $data = array() ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		global $iconic_wds, $iconic_wds_dates;

		$timeslot = false;

		$posted_date = Helpers::get_filtered_input( 'jckwds-delivery-date' );
		if ( false === $posted_date && isset( $data['jckwds-delivery-date'] ) ) {
			$posted_date = $data['jckwds-delivery-date'];
		}

		$posted_date_ymd = Helpers::get_filtered_input( 'jckwds-delivery-date-ymd' );
		if ( false === $posted_date_ymd && isset( $data['jckwds-delivery-date-ymd'] ) ) {
			$posted_date_ymd = $data['jckwds-delivery-date-ymd'];
		}

		$posted_time = Helpers::get_filtered_input( 'jckwds-delivery-time' );
		if ( false === $posted_time && isset( $data['jckwds-delivery-time'] ) ) {
			$posted_time = $data['jckwds-delivery-time'];
		}

		$posted_shipping_method = Helpers::get_filtered_input( 'shipping_method', 'array' );
		if ( false === $posted_shipping_method && isset( $data['shipping_method'] ) ) {
			$posted_shipping_method = $data['shipping_method'];
		}

		$date_changed = Helpers::get_filtered_input( 'jckwds-date-changed', 'int' );
		if ( false === $date_changed && isset( $data['jckwds-date-changed'] ) ) {
			$date_changed = $data['jckwds-date-changed'];
		}

		if ( empty( $posted_date ) || empty( $posted_date_ymd ) ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ( is_admin() && 'woocommerce_checkout' !== $action ) && ! $date_changed ) {
			return;
		}

		$is_same_day = $iconic_wds_dates->get_same_day_date( 'Ymd' ) === $posted_date_ymd;
		$is_next_day = $iconic_wds_dates->get_next_day_date( 'Ymd' ) === $posted_date_ymd;

		$order->update_meta_data( '_iconic_wds_is_same_day', $is_same_day );
		$order->update_meta_data( '_iconic_wds_is_next_day', $is_next_day );
		$order->update_meta_data( $iconic_wds->date_meta_key, esc_attr( $posted_date ) );
		$order->update_meta_data( $iconic_wds->date_meta_key . '_ymd', esc_attr( $posted_date_ymd ) );

		if ( is_array( $posted_shipping_method ) && isset( $posted_shipping_method[0] ) ) {
			$order->update_meta_data( $iconic_wds->shipping_method_meta_key, esc_attr( $posted_shipping_method[0] ) );
		}

		if ( ! empty( $posted_time ) ) {
			$timeslot_id = $iconic_wds->extract_timeslot_id_from_option_value( $posted_time );

			// Add time data to the order.
			if ( false !== $timeslot_id ) {
				$timeslot = $iconic_wds->get_timeslot_data( $timeslot_id );
				$order->update_meta_data( $iconic_wds->timeslot_meta_key, esc_attr( $timeslot['formatted'] ) );
				$order->update_meta_data( $iconic_wds->timeslot_meta_key . '_id', esc_attr( $timeslot['value'] ) );
			} else {
				$order->delete_meta_data( $iconic_wds->timeslot_meta_key );
				$order->delete_meta_data( $iconic_wds->timeslot_meta_key . '_id' );
			}
		}

		if ( ! empty( $posted_date_ymd ) ) {
			$slot_id = $timeslot ? sprintf( '%s_%s', $posted_date_ymd, $timeslot['id'] ) : $posted_date_ymd;

			if ( $iconic_wds->has_reservation() && ! is_admin() ) {
				$iconic_wds->update_reservation( $slot_id, $order->get_id() );
			} else {
				$data = array(
					'datetimeid' => $slot_id,
					'date'       => Helpers::convert_date_for_database( $posted_date_ymd ),
					'order_id'   => $order->get_id(),
					'processed'  => 1,
				);

				if ( $timeslot ) {
					$data['starttime'] = $timeslot['timefrom']['stripped'];
					$data['endtime']   = $timeslot['timeto']['stripped'];
					$data['asap']      = ! empty( $timeslot['asap'] );
				}

				$iconic_wds->add_reservation( $data );
			}

			$iconic_wds->add_timestamp_order_meta( $posted_date_ymd, $timeslot, $order );
		}

		if ( is_admin() ) {
			$order->add_order_note( __( 'Delivery date updated.', 'jckwds' ), false );
		}

		$order->save();
	}

	/**
	 * Add date and time to order details.
	 *
	 * @param array    $total_rows  Order total rows.
	 * @param WC_Order $order       The order object.
	 * @param bool     $tax_display Whether to display tax.
	 *
	 * @return array
	 */
	public static function add_to_order_details( $total_rows, $order, $tax_display ) {
		$delivery_slot_data = self::get_order_date_time( $order );

		if ( empty( $delivery_slot_data ) || empty( $delivery_slot_data['date'] ) ) {
			return $total_rows;
		}

		$total_rows['iconic_wds_order_date'] = array(
			'label' => Helpers::get_label( 'date', $order ) . ':',
			'value' => $delivery_slot_data['date'],
		);

		if ( ! empty( $delivery_slot_data['time'] ) ) {
			$total_rows['iconic_wds_order_time'] = array(
				'label' => Helpers::get_label( 'time_slot', $order ) . ':',
				'value' => $delivery_slot_data['time'],
			);
		}

		return $total_rows;
	}

	/**
	 * Get delivery slot data.
	 *
	 * This is an Alias of Order::get_order_date_time. To avoid
	 * fatal error since this function being used in Flux checkout.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return array|false.
	 */
	public static function get_delivery_slot_data( $order ) {
		return self::get_order_date_time( $order );
	}
}
