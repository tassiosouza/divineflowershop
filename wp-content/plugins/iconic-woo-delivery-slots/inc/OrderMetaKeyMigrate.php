<?php
/**
 * Migrate old meta keys to new meta keys.
 * Example _jckwds_date -> jckwds_date.
 *
 * Make use of WooCommerce's action schedular to accomplish that.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

defined( 'ABSPATH' ) || exit;

/**
 * Order_Meta_Key_Migrate.
 */
class OrderMetaKeyMigrate {

	/**
	 * Orders to proccess per step.
	 *
	 * @var int
	 */
	const ORDERS_LIMIT_PER_RUN = 20;

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'iconic_wds_run_order_meta_migration_step', array( __CLASS__, 'run_step' ) );
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( __CLASS__, 'update_order_query' ), 10, 2 );
	}

	/**
	 * Initiate migration.
	 *
	 * @return void
	 */
	public static function initiate_migration() {
		// Dont start duplicate process.
		if ( get_option( 'iconic_wds_meta_key_migration_in_progress', false ) ) {
			return;
		}

		update_option( 'iconic_wds_meta_key_migration_in_progress', true );
		self::run_step();
	}

	/**
	 * Run single step.
	 *
	 * @return void
	 */
	public static function run_step() {
		$orders = self::get_orders_with_old_meta_key();
		if ( ! empty( $orders ) ) {
			self::do_migration( $orders );
			self::enqueue_next_step_run();
		} else {
			self::finalize_regeneration();
		}
	}

	/**
	 * Get orders with the old meta key.
	 */
	public static function get_orders_with_old_meta_key() {
		$orders = array();

		if ( Helpers::is_cot_enabled() ) {
			$orders = wc_get_orders(
				array(
					'iconic_wds_fetch_orders_with_old_metadata' => '1',
					'limit'      => self::ORDERS_LIMIT_PER_RUN,
					'status'     => 'any',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'jckwds_date',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_jckwds_date',
							'compare' => 'EXISTS',
						),
					),
				)
			);

		} else {
			$order_ids = get_posts(
				array(
					'post_type'      => 'shop_order',
					'posts_per_page' => self::ORDERS_LIMIT_PER_RUN,
					'post_status'    => 'any',
					'fields'         => 'ids',
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'   => 'jckwds_date',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'   => '_jckwds_date',
							'compare' => 'EXISTS',
						),
					),
				)
			);

			foreach ( $order_ids as $post_id ) {
				$orders[] = wc_get_order( $post_id );
			}
		}

		return $orders;
	}

	/**
	 * With WooCommerce Order Query, the only way as of now(July, 2023) is to run
	 * use `woocommerce_order_data_store_cpt_get_orders_query` filter and pass
	 * meta query arguments when a custom parameter matches.
	 *
	 * `iconic_wds_fetch_orders_with_old_metadata` is our custom paramater to identify our custom query.
	 *
	 * @param array $query     Query.
	 * @param array $query_vars Query vars.
	 *
	 * @return array
	 */
	public static function update_order_query( $query, $query_vars ) {
		if ( empty( $query_vars['iconic_wds_fetch_orders_with_old_metadata'] ) ) {
			return $query;
		}

		$query['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'     => 'jckwds_date',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_jckwds_date',
				'compare' => 'EXISTS',
			),
		);

		return $query;
	}

	/**
	 * Enqueue next run.
	 *
	 * @return void
	 */
	public static function enqueue_next_step_run() {
		WC()->queue()->schedule_single(
			time() + 1,
			'iconic_wds_run_order_meta_migration_step',
			array()
		);
	}

	/**
	 * Perform meta data migration.
	 *
	 * @param array $orders Array of WC_Order.
	 *
	 * @return void
	 */
	public static function do_migration( $orders ) {
		foreach ( $orders as $order ) {
			$date            = $order->get_meta( '_jckwds_date' );
			$ymd             = $order->get_meta( '_jckwds_date_ymd' );
			$timeslot        = $order->get_meta( '_jckwds_timeslot' );
			$timestamp       = $order->get_meta( '_jckwds_timestamp' );
			$shipping_method = $order->get_meta( '_jckwds_shipping_method' );

			if ( $date ) {
				$order->update_meta_data( 'jckwds_date', $date, true );
				$order->update_meta_data( 'jckwds_date_ymd', $ymd, true );
				$order->update_meta_data( 'jckwds_timeslot', $timeslot, true );
				$order->update_meta_data( 'jckwds_timestamp', $timestamp, true );
				$order->update_meta_data( 'jckwds_shipping_method', $shipping_method, true );
				$order->save();
			}
		}
	}


	/**
	 * Mark regeneration as completed.
	 *
	 * @return void
	 */
	public static function finalize_regeneration() {
		delete_option( 'iconic_wds_meta_key_migration_in_progress' );
		update_option( 'jckwds_db_version', '1.24.0' );
	}
}
