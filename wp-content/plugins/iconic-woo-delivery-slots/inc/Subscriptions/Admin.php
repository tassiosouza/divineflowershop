<?php
/**
 * Admin class.
 *
 * @package Iconic_WDS\Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Subscriptions\Dto\SubscriptionOrderMetaData;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionOrderMetaKey;

/**
 * Admin class.
 */
class Admin {

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'woocommerce_admin_order_items_after_line_items', array( __CLASS__, 'display_timeslot_line_item' ), 15, 1 );

		// Add subscription delivery column to orders table.
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_column' ) );
		add_filter( 'woocommerce_shop_order_list_table_columns', array( __CLASS__, 'add_order_column' ) );

		// Make the column sortable.
		add_filter( 'manage_edit-shop_order_sortable_columns', array( __CLASS__, 'make_column_sortable' ) );
		add_filter( 'woocommerce_shop_order_list_table_sortable_columns', array( __CLASS__, 'make_column_sortable' ) );

		// Render column content.
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_order_column' ), 20, 2 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( __CLASS__, 'render_order_column' ), 20, 2 );

		// Handle sorting.
		add_filter( 'request', array( __CLASS__, 'handle_column_sorting' ) );
		add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( __CLASS__, 'handle_column_sorting' ) );
	}

	/**
	 * Add subscription delivery column to orders table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public static function add_order_column( $columns ) {
		$columns['iconic_wds_subscription_delivery'] = __( 'Subscription Delivery', 'iconic-wds' );
		return $columns;
	}

	/**
	 * Make the subscription delivery column sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array Modified sortable columns.
	 */
	public static function make_column_sortable( $columns ) {
		$columns['iconic_wds_subscription_delivery'] = 'iconic_wds_subscription_delivery';
		return $columns;
	}

	/**
	 * Render subscription delivery column content.
	 *
	 * @param string $column Column name.
	 * @param int    $order_id Order ID.
	 */
	public static function render_order_column( $column, $order_id ) {
		if ( 'iconic_wds_subscription_delivery' !== $column ) {
			return;
		}

		$meta_data = SubscriptionOrderMetaData::from_order_id( $order_id );

		if ( ! $meta_data || empty( $meta_data->date ) ) {
			return;
		}

		$label_type = $meta_data->get_shipping_label_type();

		$date_text = 'delivery' === $label_type ? __( 'Delivery Date', 'iconic-wds' ) : __( 'Collection Date', 'iconic-wds' );
		$time_text = __( 'Time Slot', 'jckwds' );

		if ( $meta_data->date ) {
			printf( '<p><strong>%s</strong> <br>%s</p>', esc_html( $date_text ), esc_html( $meta_data->get_formatted_date() ) );
		}

		if ( ! empty( $meta_data->timeslot ) ) {
			printf( '<p><strong>%s</strong> <br>%s</p>', esc_html( $time_text ), esc_html( $meta_data->get_formatted_timeslot() ) );
		}
	}

	/**
	 * Handle column sorting.
	 *
	 * @param array $query_vars Query variables.
	 * @return array Modified query variables.
	 */
	public static function handle_column_sorting( $query_vars ) {
		if ( ! isset( $query_vars['orderby'] ) || 'iconic_wds_subscription_delivery' !== $query_vars['orderby'] ) {
			return $query_vars;
		}

		$query_vars = array_merge(
			$query_vars,
			array(
				'meta_key'         => SubscriptionOrderMetaKey::TIMESTAMP_META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_compare_key' => 'REGEXP',
				'orderby'          => 'meta_value_num',
			)
		);

		return $query_vars;
	}

	/**
	 * Display timeslot line item.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function display_timeslot_line_item( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$meta_data = SubscriptionOrderMetaData::from_order( $order );

		if ( ! $meta_data || empty( $meta_data->date ) ) {
			return;
		}

		global $iconic_wds;

		$date                 = $meta_data->get_formatted_date();
		$time                 = $meta_data->get_formatted_timeslot();
		$shipping_method      = $meta_data->get_shipping_method();
		$date_time            = $time ? $date . ' @ ' . $time : $date;
		$shipping_methods     = $iconic_wds->get_shipping_method_options();
		$shipping_method_name = isset( $shipping_methods[ $shipping_method ] ) ? $shipping_methods[ $shipping_method ] : '';
		$order_taxes_count    = count( $order->get_taxes() );
		$col_span             = 5 + $order_taxes_count;
		$updated_class        = '';

		require_once ICONIC_WDS_PATH . 'templates/admin/subscription/timeslot-line-item.php';
	}
}
