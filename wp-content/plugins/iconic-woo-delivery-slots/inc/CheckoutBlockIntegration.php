<?php
/**
 * Checkout blocks integration interface.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for integrating with WooCommerce Blocks
 */
class CheckoutBlockIntegration implements IntegrationInterface {
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'iconic-wds';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$path = ICONIC_WDS_PATH . 'blocks/build/checkout-fields-block';
		register_block_type( $path );

		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
		$this->register_main_integration();
	}

	/**
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	private function register_main_integration() {
		$load_script = is_admin() || Helpers::is_checkout_page_using_block();
		/**
		 * Filters the load script flag for the checkout block integration.
		 *
		 * @param bool $load_script The load script flag.
		 *
		 * @since 2.11.0
		 */
		$load_script = apply_filters( 'iconic_wds_load_block_checkout_integration_script', $load_script );

		$script_path = '/blocks/build/checkout-fields-block-plugin.js';
		$style_path  = '/blocks/build/checkout-fields-block-frontend.css';

		$script_url = plugins_url( $script_path, ICONIC_WDS_FILE );
		$style_url  = plugins_url( $style_path, ICONIC_WDS_FILE );

		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/block/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( ICONIC_WDS_PATH . $script_path ),
			);

		// Only add the dependency if not in admin.
		if ( ! is_admin() ) {
			$dependencies = array_merge( $script_asset['dependencies'], array( Iconic_WDS::$slug . '-script' ) );
		} else {
			$dependencies = $script_asset['dependencies'];
		}

		wp_enqueue_style(
			'iconic-wds-blocks-integration',
			$style_url,
			array(),
			$this->get_file_version( ICONIC_WDS_PATH . $style_path )
		);

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style(
			Iconic_WDS::$slug . '-style',
			ICONIC_WDS_URL . '/assets/frontend/css/main' . $min . '.css',
			array(),
			Iconic_WDS::$version
		);

		wp_register_script(
			'iconic-wds-blocks-integration',
			$script_url,
			$dependencies,
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'iconic-wds-blocks-integration',
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
		global $iconic_wds;

		if ( $iconic_wds->has_subscription_product_in_cart() ) {
			return array();
		}

		return array( 'iconic-wds-blocks-integration', 'iconic-checkout-fields-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'iconic-wds-blocks-integration', 'iconic-wds-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		global $iconic_wds_dates, $iconic_wds;

		$data = array(
			'bookable_dates'         => $iconic_wds_dates->get_upcoming_bookable_dates(),
			'settings'               => $iconic_wds->settings,
			'date_fns_format'        => Helpers::date_format_fns(),
			'locale'                 => get_locale(),
			'labels'                 => Helpers::get_label(),
			'i18n'                   => Helpers::get_localized_date_strings(),
			'shipping_method_labels' => Settings::get_shipping_method_labels(),
			'strings'                => Helpers::get_localized_strings(),
		);

		return $data;
	}

	/**
	 * Registers the block editor scripts.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {
		$script_path       = '/blocks/build/checkout-fields-block.js';
		$script_url        = plugins_url( $script_path, ICONIC_WDS_FILE );
		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/build/checkout-fields-block.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'iconic-wds-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'iconic-wds-blocks-integration',
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
		$script_path       = '/blocks/build/checkout-fields-block-frontend.js';
		$script_url        = plugins_url( $script_path, ICONIC_WDS_FILE );
		$script_asset_path = dirname( ICONIC_WDS_FILE ) . '/build/checkout-fields-block-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'iconic-checkout-fields-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'iconic-checkout-fields-block-frontend',
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
