<?php
/**
 * Checkout blocks integration interface.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS\Subscriptions\CheckoutBlock;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use Iconic_WDS\Helpers;
use Iconic_WDS\Iconic_WDS;
use Iconic_WDS\Subscriptions\Boot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for integrating with WooCommerce Blocks
 */
class SubscriptionsCheckoutBlockIntegration implements IntegrationInterface {
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'iconic-wds-subscriptions';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$path = ICONIC_WDS_PATH . 'blocks/build/subscriptions-block';
		register_block_type( $path );

		if ( empty( Boot::get_active_integration() ) ) {
			return;
		}

		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
		$this->register_main_integration();
	}

	/**
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	private function register_main_integration() {
		if ( ! is_admin() && Helpers::is_block_checkout() ) {
			return;
		}

		$script_path = '/blocks/build/subscriptions-block-plugin.js';
		$style_path  = '/blocks/build/subscriptions-frontend.css';

		$script_url = plugins_url( $script_path, ICONIC_WDS_FILE );
		$style_url  = plugins_url( $style_path, ICONIC_WDS_FILE );

		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/block/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( ICONIC_WDS_PATH . $script_path ),
			);

		wp_enqueue_style(
			'iconic-wds-subscriptions-blocks',
			$style_url,
			array(),
			$this->get_file_version( ICONIC_WDS_PATH . $style_path )
		);

		wp_register_script(
			'iconic-wds-subscriptions-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'iconic-wds-subscriptions-blocks-integration',
			'jckwds',
			dirname( ICONIC_WDS_FILE ) . '/languages'
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		if ( empty( Boot::get_active_integration() ) ) {
			return array();
		}

		return array( 'iconic-wds-subscriptions-blocks-integration', 'iconic-wds-subscriptions-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		if ( empty( Boot::get_active_integration() ) ) {
			return array();
		}

		return array( 'iconic-wds-subscriptions-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		global $iconic_wds_dates, $iconic_wds;

		$data = array(
			'bookable_dates'  => $iconic_wds_dates->get_upcoming_bookable_dates(),
			'settings'        => $iconic_wds->settings,
			'date_fns_format' => Helpers::date_format_fns(),
			'locale'          => get_locale(),
			'labels'          => Helpers::get_label(),
			'i18n'            => Helpers::get_localized_date_strings(),
			'strings'         => Helpers::get_localized_strings(),
		);

		return $data;
	}

	/**
	 * Registers the block editor scripts.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {
		$script_path       = '/blocks/build/subscriptions-block.js';
		$script_url        = plugins_url( $script_path, ICONIC_WDS_FILE );
		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/build/subscriptions-block.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'iconic-wds-subscriptions-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'iconic-wds-subscriptions-block-editor',
			'jckwds',
			dirname( ICONIC_WDS_FILE ) . '/languages'
		);
	}

	/**
	 * Registers the block frontend scripts.
	 *
	 * @return void
	 */
	public function register_block_frontend_scripts() {
		$script_path       = '/blocks/build/subscriptions-block-frontend.js';
		$script_url        = plugins_url( $script_path, ICONIC_WDS_FILE );
		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/build/subscriptions-block-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'iconic-wds-subscriptions-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'iconic-wds-subscriptions-block-frontend',
			'jckwds',
			ICONIC_WDS_PATH . '/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return Iconic_WDS::$version;
	}
}
