<?php
/**
 * WDS Migrate class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrate.
 */
class Migrate {

	/**
	 * DB updates.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.7.0'  => array(
			'iconic_wds_1_7_0_update_db',
		),
		'1.24.0' => array(
			'iconic_wds_1_24_0_update_db',
		),
	);

	/**
	 * Run
	 */
	public static function run() {
		add_action( 'iconic_wds_run_update_callback', array( __CLASS__, 'run_update_callback' ) );
	}

	/**
	 * Run updater.
	 *
	 * @return void
	 */
	public static function update() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		delete_transient( 'iconic-wds-shipping-methods' );

		$current_db_version = get_option( 'jckwds_db_version' );
		$loop               = 0;

		foreach ( self::$db_updates as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					WC()->queue()->schedule_single(
						time() + $loop,
						'iconic_wds_run_update_callback',
						array(
							'update_callback' => $update_callback,
						)
					);
					$loop++;
				}
			}
		}
	}

	/**
	 * Update callback.
	 *
	 * @param string $update_callback Function of this class.
	 *
	 * @return void
	 */
	public static function run_update_callback( $update_callback ) {
		$function = sprintf( '%s::%s', __CLASS__, $update_callback );

		if ( is_callable( $function ) ) {
			call_user_func( $function );
		}
	}

	/**
	 * Update database for version 1.7.0.
	 *
	 * @return void
	 */
	public static function iconic_wds_1_7_0_update_db() {
		global $wpdb;

		$sql = "CREATE TABLE {$wpdb->prefix}jckwds (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`datetimeid` text,
			`processed` tinyint(1) DEFAULT NULL,
			`order_id` bigint DEFAULT NULL,
			`user_id` text,
			`expires` text,
			`date` datetime DEFAULT NULL,
			`starttime` mediumint(4) unsigned zerofill DEFAULT NULL,
			`endtime` mediumint(4) unsigned zerofill DEFAULT NULL,
			`asap` tinyint(1) DEFAULT NULL,
			UNIQUE KEY `id` (`id`)
		);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'jckwds_db_version', '1.7.0' );
	}

	/**
	 * Update database for version 1.24.0.
	 *
	 * @return void
	 */
	public static function iconic_wds_1_24_0_update_db() {
		OrderMetaKeyMigrate::initiate_migration();
	}
}
