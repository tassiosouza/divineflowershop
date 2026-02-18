<?php
/**
 * Allows us to transfer Freemius license.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class_Prefix_Core_Freemius_SDK.
 *
 * @class    Class_Prefix_Core_Freemius_SDK
 * @version  1.0.0
 */
class Class_Prefix_Core_Freemius_SDK {
	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;
	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public $plugin_file;
	/**
	 * Uplink plugin slug.
	 *
	 * @var string
	 */
	public $uplink_plugin_slug;
	/**
	 * Freemius plugin args.
	 *
	 * @var array
	 */
	public $freemius;

	/**
	 * Construct.
	 */
	public function __construct( $args = array() ) {
		$this->plugin_path = $args['plugin_path'];

		if ( ! file_exists( $this->plugin_path . 'inc/vendor/freemius/start.php' ) ) {
			return;
		}

		$this->plugin_file        = $args['plugin_file'];
		$this->uplink_plugin_slug = $args['uplink_plugin_slug'];
		$this->freemius           = (array) $args['freemius'];

		$this->transfer_license();
	}

	/**
	 * Transfer license.
	 *
	 * @return void
	 */
	public function transfer_license() {
		$uplink_option = 'stellarwp_uplink_license_key_' . $this->uplink_plugin_slug;
		$uplink_key    = get_option( $uplink_option, '' );

		// Key is already set.
		if ( ! empty( $uplink_key ) ) {
			return;
		}

		require_once $this->plugin_path . 'inc/vendor/freemius/start.php';

		$freemius = fs_dynamic_init(
			array(
				'id'                  => $this->freemius['id'],
				'slug'                => $this->freemius['slug'],
				'public_key'          => $this->freemius['public_key'],
				'type'                => 'plugin',
				'is_premium'          => true,
				'is_premium_only'     => true,
				'has_premium_version' => true,
				'has_paid_plans'      => true,
				'has_addons'          => false,
				'is_org_compliant'    => false,
				'menu'                => array(),
			)
		);

		if ( ! $freemius ) {
			return;
		}

		$freemius->set_basename( true, $this->plugin_file );

		$license = $freemius->_get_license();

		if ( empty( $license ) || empty( $license->secret_key ) ) {
			return;
		}

		update_option( $uplink_option, $license->secret_key );
	}
}
