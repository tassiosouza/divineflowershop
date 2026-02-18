<?php
/**
 * Plugin Name: Flux Checkout
 * Description: Optimised multi-step checkout plugin for WooCommerce.
 * Plugin URI: https://iconicwp.com/products/flux-checkout-for-woocommerce/
 * Author: Iconic
 * Version: 2.25.0
 * WC requires at least: undefined
 * WC tested up to: undefined
 * Author URI: https://iconicwp.com/
 * Text Domain: flux-checkout
 * Domain Path: /languages/
 * Requires PHP: 7.4
 *
 * @package Iconic_Flux
 */

use Iconic_Flux_NS\StellarWP\ContainerContract\ContainerInterface;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux
 */
class Iconic_Flux_Checkout {
	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public static $name = 'Flux Checkout';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '2.25.0';

	/**
	 * Settings array.
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Plugin initiated.
	 *
	 * @var bool
	 */
	public $initiated = false;

	/**
	 * The singleton instance of the plugin.
	 *
	 * @var Iconic_Flux_Checkout
	 */
	private static $instance;

	/**
	 * The DI container.
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Instantiate a single instance of our plugin.
	 *
	 * @return YourClass
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
	 * Construct the plugin
	 */
	public function __construct() {
		load_plugin_textdomain( 'iconic-flux', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatiblity' ) );

		/**
		 * Allows code snippets to conditionally disable Flux checkout.
		 *
		 * @since 2.8.0
		 */
		if ( apply_filters( 'flux_checkout_disabled', false ) ) {
			return;
		}

		$this->define_constants();
		$this->setup_autoloader();

		if ( ! Iconic_Flux_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return;
		}

		$this->load_classes();
		$this->container = new Iconic_Flux_Core_Container();

		$this->initiated = true;
	}

	/**
	 * Run on activation.
	 */
	public static function activate() {
		if ( ! get_option( 'iconic_flux_flush_rewrite_rules_flag' ) ) {
			add_option( 'iconic_flux_flush_rewrite_rules_flag', true );
		}

		self::clear_wc_template_cache();
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();

		self::clear_wc_template_cache();
	}

	/**
	 * Clear WooCommerce template cache.
	 *
	 * @return void
	 */
	public static function clear_wc_template_cache() {
		if ( function_exists( 'wc_clear_template_cache' ) ) {
			wc_clear_template_cache();
		}
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_FLUX_FILE', __FILE__ );
		$this->define( 'ICONIC_FLUX_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_FLUX_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_FLUX_INC_PATH', ICONIC_FLUX_PATH . 'inc/' );
		$this->define( 'ICONIC_FLUX_VENDOR_PATH', ICONIC_FLUX_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_FLUX_TPL_PATH', ICONIC_FLUX_PATH . 'templates/' );
		$this->define( 'ICONIC_FLUX_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'ICONIC_FLUX_VERSION', self::$version );
		$this->define( 'FLUX_PLUGIN_VERSION', ICONIC_FLUX_VERSION ); // Needed for third party compatibility (IE Sales Booster).
		$this->define( 'ICONIC_FLUX_PLUGIN_PATH_FILE', str_replace( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ), '', wp_normalize_path( ICONIC_FLUX_FILE ) ) );
	}

	/**
	 * Setup autoloader.
	 */
	private function setup_autoloader() {
		require_once ICONIC_FLUX_INC_PATH . 'class-core-autoloader.php';

		Iconic_Flux_Core_Autoloader::run(
			array(
				'prefix'   => 'Iconic_Flux_',
				'inc_path' => ICONIC_FLUX_INC_PATH,
			)
		);
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name Name.
	 * @param string|bool $value Value.
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
		require_once ICONIC_FLUX_PATH . '/vendor-prefixed/autoload.php';

		$this->init_license();
		$this->init_telemetry();

		// @todo Set correct paths.
		// @todo Set correct cross sells.
		Iconic_Flux_Core_Settings::run(
			array(
				'basename'      => ICONIC_FLUX_BASENAME,
				'vendor_path'   => ICONIC_FLUX_VENDOR_PATH,
				'title'         => 'Flux Checkout for WooCommerce',
				'version'       => self::$version,
				'menu_title'    => 'Flux Checkout',
				'settings_path' => ICONIC_FLUX_INC_PATH . 'admin/settings.php',
				'option_group'  => 'iconic_flux',
				'docs'          => array(
					'collection'      => 'flux-checkout-for-woocommerce/',
					'troubleshooting' => 'flux-checkout-for-woocommerce/flux-user-guides/',
					'getting-started' => 'flux-checkout-for-woocommerce/flux-getting-started/',
				),
			)
		);

		Iconic_Flux_Upgrade::run();
		Iconic_Flux_Core::run();
		Iconic_Flux_Assets::run();
		Iconic_Flux_Cross_Sell::run();

		add_action( 'plugins_loaded', array( 'Iconic_Flux_Core_Onboard', 'run' ), 10 );
	}

	/**
	 * Init licence class.
	 */
	public function init_license() {
		// Allows us to transfer Freemius license.
		if ( file_exists( ICONIC_FLUX_PATH . 'class-core-freemius-sdk.php' ) ) {
			require_once ICONIC_FLUX_PATH . 'class-core-freemius-sdk.php';

			new Iconic_Flux_Core_Freemius_SDK(
				array(
					'plugin_path'          => ICONIC_FLUX_PATH,
					'plugin_file'          => ICONIC_FLUX_FILE,
					'uplink_plugin_slug'   => 'flux-checkout',
					'freemius'             => array(
						'id'         => '5383',
						'slug'       => 'flux-checkout',
						'public_key' => 'pk_62e3953ae5e35b8b1163a29e035a7',
					),
				)
			);
		}

		Iconic_Flux_Core_License_Uplink::run(
			array(
				'basename'        => ICONIC_FLUX_BASENAME,
				'plugin_slug'     => 'flux-checkout',
				'plugin_name'     => self::$name,
				'plugin_version'  => self::$version,
				'plugin_path'     => ICONIC_FLUX_PLUGIN_PATH_FILE,
				'plugin_class'    => 'Iconic_Flux_Checkout',
				'option_group'    => 'iconic_flux',
				'container_class' => self::class,
				'license_class' => Iconic_Flux_Core_Uplink_Helper::class,
				'urls'            => array(
					'product' => 'https://iconicwp.com/products/flux-checkout/',
				),
			)
		);
	}

	/**
	 * Init telemetry class.
	 *
	 * @return void
	 */
	public function init_telemetry() {
		Iconic_Flux_Core_Telemetry::run(
			array(
				'file'                  => __FILE__,
				'plugin_slug'           => 'flux-checkout',
				'option_group'          => 'iconic_flux',
				'plugin_name'           => self::$name,
				'plugin_url'            => ICONIC_FLUX_URL,
				'opt_out_settings_path' => 'sections/license/fields',
				'container_class'       => self::class,
			)
		);
	}

	/**
	 * Declare compatibility with HPOS/Custom order table feature of WooCommerce.
	 *
	 * @return void
	 */
	public static function declare_hpos_compatiblity() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

$iconic_flux_checkout = Iconic_Flux_Checkout::instance();

if ( $iconic_flux_checkout->initiated ) {
	register_activation_hook( __FILE__, array( $iconic_flux_checkout, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $iconic_flux_checkout, 'deactivate' ) );

	require_once ICONIC_FLUX_PATH . 'inc/compatibility.php';
}
