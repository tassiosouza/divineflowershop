<?php
/**
 * Plugin Name: Iconic Sales Booster for WooCommerce
 * Plugin URI: https://iconicwp.com/products/sales-booster-for-woocommerce/
 * Description: Increase your average order value with strategic cross-sells.
 * Version: 1.26.0
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Text Domain: iconic-wsb
 * WC requires at least: 3.6.0
 * WC tested up to: 10.3.5
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Iconic_WSB_NS\StellarWP\ContainerContract\ContainerInterface;

class Iconic_Woo_Sales_Booster {
	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public static $name = 'Iconic Sales Booster for WooCommerce';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.26.0';

	/**
	 * Variable to hold default/saved settings.
	 *
	 * @var array|null
	 */
	public $settings = null;

	/**
	 * @var Iconic_WSB_Template
	 */
	public $template;

	/**
	 * The singleton instance of the plugin.
	 *
	 * @var Iconic_Woo_Sales_Booster
	 */
	private static $instance;

	/**
	 * The DI container.
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Construct the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_text_domain' ) );

		$this->define_constants();
		$this->load_classes();

		$this->container = new Iconic_WSB_Core_Container();

		// Declare compatibility with High-Performance Order Storage (HPOS).
		add_action(
			'before_woocommerce_init',
			function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			}
		);
	}

	/**
	 * Instantiate a single instance of our plugin.
	 *
	 * @return Iconic_Woo_Sales_Booster
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the DI container.
	 *
	 * @return ContainerInterface
	 */
	public function container() {
		return $this->container;
	}

	/**
	 * Load text domain.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'iconic-wsb', false, ICONIC_WSB_DIRNAME . '/languages/' );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_WSB_FILE', __FILE__ );
		$this->define( 'ICONIC_WSB_PATH', plugin_dir_path( ICONIC_WSB_FILE ) );
		$this->define( 'ICONIC_WSB_URL', plugin_dir_url( ICONIC_WSB_FILE ) );
		$this->define( 'ICONIC_WSB_INC_PATH', ICONIC_WSB_PATH . 'inc/' );
		$this->define( 'ICONIC_WSB_VENDOR_PATH', ICONIC_WSB_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_WSB_TPL_PATH', ICONIC_WSB_PATH . 'templates/' );
		$this->define( 'ICONIC_WSB_BASENAME', plugin_basename( ICONIC_WSB_FILE ) );
		$this->define( 'ICONIC_WSB_DIRNAME', dirname( ICONIC_WSB_BASENAME ) );
		$this->define( 'ICONIC_WSB_VERSION', self::$version );
		$this->define( 'ICONIC_WSB_PLUGIN_PATH_FILE', str_replace( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ), '', wp_normalize_path( ICONIC_WSB_FILE ) ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name
	 * @param string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load classes
	 */
	private function load_classes() {
		require_once ICONIC_WSB_PATH . 'vendor-prefixed/autoload.php';
		$this->init_autoloader();

		if ( ! Iconic_WSB_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return;
		}

		$this->init_settings();
		$this->init_license();
		$this->init_telemetry();

		$this->template = new Iconic_WSB_Template();

		$this->init_services();

		add_action( 'plugins_loaded', array( 'Iconic_WSB_Core_Onboard', 'run' ), 10 );
	}

	/**
	 * Init licence class.
	 */
	public function init_license() {
		// Allows us to transfer Freemius license.
		if ( file_exists( ICONIC_WSB_PATH . 'class-core-freemius-sdk.php' ) ) {
			require_once ICONIC_WSB_PATH . 'class-core-freemius-sdk.php';

			new Iconic_WSB_Core_Freemius_SDK(
				array(
					'plugin_path'        => ICONIC_WSB_PATH,
					'plugin_file'        => ICONIC_WSB_FILE,
					'uplink_plugin_slug' => 'iconic-wsb',
					'freemius'           => array(
						'id'         => '3212',
						'slug'       => 'iconic-woo-sales-booster-lite',
						'public_key' => 'pk_3ff1f2e5cb38f67915e2b154565d6',
					),
				)
			);
		}

		Iconic_WSB_Core_License_Uplink::run(
			array(
				'basename'        => ICONIC_WSB_BASENAME,
				'plugin_slug'     => 'iconic-wsb',
				'plugin_name'     => self::$name,
				'plugin_version'  => self::$version,
				'plugin_path'     => ICONIC_WSB_PLUGIN_PATH_FILE,
				'plugin_class'    => self::class,
				'option_group'    => 'iconic-wsb',
				'urls'            => array(
					'product' => 'https://iconicwp.com/products/sales-booster-for-woocommerce/',
				),
				'container_class' => self::class,
				'license_class' => Iconic_WSB_Core_Uplink_Helper::class,
			)
		);
	}

	/**
	 * Init telemetry class.
	 *
	 * @return void
	 */
	public function init_telemetry() {
		Iconic_WSB_Core_Telemetry::run(
			array(
				'file'                  => __FILE__,
				'plugin_slug'           => 'iconic-wsb',
				'option_group'          => 'iconic-wsb',
				'plugin_name'           => self::$name,
				'plugin_url'            => ICONIC_WSB_URL,
				'opt_out_settings_path' => 'sections/license/fields',
				'container_class'       => self::class,
			)
		);
	}

	/**
	 * Init settings framework
	 */
	private function init_settings() {
		Iconic_WSB_Core_Settings::run(
			array(
				'parent_slug'   => 'iconic_wsb_order_bumps',
				'vendor_path'   => ICONIC_WSB_VENDOR_PATH,
				'title'         => __( 'Sales Booster for WooCommerce', 'iconic-wsb' ), // Plugin title.
				'version'       => self::$version,
				'menu_title'    => __( 'Settings', 'iconic-wsb' ), // Menu title. Defaults to under the `WooCommerce` menu.
				'settings_path' => ICONIC_WSB_INC_PATH . 'admin/settings.php',
				'option_group'  => 'iconic-wsb',
				'docs'          => array(
					'collection'      => 'iconic-sales-booster-for-woocommerce/',
					'troubleshooting' => 'iconic-sales-booster-for-woocommerce/isb-troubleshooting/',
					'getting-started' => 'iconic-sales-booster-for-woocommerce/isb-getting-started/',
				),
				'cross_sells'   => array(
					'iconic-woo-show-single-variations',
					'iconic-woothumbs',
				),
			)
		);
	}

	/**
	 *  Init plugin autoloader
	 */
	private function init_autoloader() {
		require_once ICONIC_WSB_INC_PATH . 'class-core-autoloader.php';

		Iconic_WSB_Core_Autoloader::run(
			array(
				'prefix'   => 'Iconic_WSB_',
				'inc_path' => ICONIC_WSB_INC_PATH,
			)
		);
	}

	/**
	 * Set settings.
	 */
	public function set_settings() {
		$this->settings = Iconic_WSB_Core_Settings::$settings;
	}

	/**
	 * Init plugin services
	 */
	private function init_services() {
		Iconic_WSB_Notifier::run();
		Iconic_WSB_Settings::run();
		Iconic_WSB_Assets::run();
		Iconic_WSB_Cart::run();
		Iconic_WSB_Ajax::run();
		Iconic_WSB_Order_Bump::run();
		Iconic_WSB_Admin_Orders::run();
		Iconic_WSB_Admin_Product_Tab::run();
		Iconic_WSB_Compat_Woo_Attributes_Swatches::run();
		Iconic_WSB_Shortcodes::run();
		Iconic_WSB_Blocks::run();
		Iconic_WSB_Compat_Variation_Swatches_For_WooCommerce::run();
		Iconic_WSB_Compat_Divi::run();
		Iconic_WSB_Compat_WooCommerce_Subscriptions::run();
		Iconic_WSB_Compat_WooCommerce_Ecurring_Gateway::run();
		Iconic_WSB_Compat_WooCommerce_Multilingual::run();
		Iconic_WSB_Bulk_Import_Export::run();
		Iconic_WSB_Compat_WPML::run();
		Iconic_WSB_Compat_YITH_WooCommerce_Added_To_Cart_Popup::run();
		Iconic_WSB_Compat_WooCommerce_Booking::run();
		Iconic_WSB_Product_Block_Editor::run();
		Iconic_WSB_Compat_WooCommerce_Shipping_Tax::run();
	}

	/**
	 * Activation hook.
	 */
	public static function activation_hook() {
		update_option( 'iconic_wsb_activated', true );
	}
}

$iconic_wsb_class = Iconic_Woo_Sales_Booster::instance();

register_activation_hook( __FILE__, array( 'Iconic_Woo_Sales_Booster', 'activation_hook' ) );
