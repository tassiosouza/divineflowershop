<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://powerfulwp.com
 * @since             1.0.0
 * @package           GMFW
 *
 * @wordpress-plugin
 * Plugin Name:       Gift Message for WooCommerce
 * Plugin URI:        https://powerfulwp.com/gift-message-for-woocommerce-premium/
 * Description:       Let your customers choose an occasion and add a gift message on checkout.
 * Version:           1.7.9
 * Author:            powerfulwp
 * Author URI:        https://powerfulwp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gmfw
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 4.8
 * @fs_premium_only /includes/class-gmfw-cart.php, /public/css/owl.carousel.min.css, /public/js/owl.carousel.min.js *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Declare extension compatible with HPOS.
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

if ( ! function_exists( 'gmfw_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return statment
	 */
	function gmfw_fs() {
		global $gmfw_fs;

		if ( ! isset( $gmfw_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$gmfw_fs = fs_dynamic_init(
				array(
					'id'                  => '6963',
					'slug'                => 'gift-message-for-woocommerce',
					'type'                => 'plugin',
					'public_key'          => 'pk_952ca2cbb4ab27d1837efde31e3a9',
					'is_premium'          => true,
					'premium_suffix'      => 'Premium',
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'trial'               => array(
						'days'               => 14,
						'is_require_payment' => true,
					),
					'menu'                => array(
						'slug'    => 'gmfw-settings',
						'support' => false,
						'network' => true,
					),
				)
			);
		}

		return $gmfw_fs;
	}

	// Init Freemius.
	gmfw_fs();
	// Signal that SDK was initiated.
	do_action( 'gmfw_fs_loaded' );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmfw.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if ( ! function_exists( 'gmfw_run' ) ) {
	function gmfw_run() {
		$plugin = new GMFW();
		$plugin->run();
	}
}

/**
 * Initializes the GMFW plugin.
 * This function checks if WooCommerce is active before running the plugin.
 * If WooCommerce is not active, it displays an admin notice.
 */
if ( ! function_exists( 'initialize_gmfw_run' ) ) {
	function initialize_gmfw_run() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			// Adding action to admin_notices to display a notice if WooCommerce is not active.
			add_action( 'admin_notices', 'gmfw_woocommerce_missing_notice' );
			return; // Stop the initialization as WooCommerce is not active.
		}

		 // WooCommerce is active, so initialize the GMFW plugin.
		gmfw_run();
	}
}

/**
 * Displays an admin notice if WooCommerce is not active.
 * This function is hooked to 'admin_notices' to show a warning message
 * in the WordPress admin area when WooCommerce is not installed or activated.
 */
if ( ! function_exists( 'gmfw_woocommerce_missing_notice' ) ) {
	function gmfw_woocommerce_missing_notice() {
		?>
	<div class="notice notice-error">
		<p><?php echo esc_html__( 'The Gift Message for WooCommerce plugin requires WooCommerce to be installed and active.', 'gmfw' ); ?></p>
	</div>
		<?php
	}
}

 // Include the internationalization class to handle text domain loading.
 require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmfw-i18n.php';

 /**
 * Initializes internationalization (i18n) support for the plugin.
 */
if ( ! function_exists( 'gmfw_initialize_i18n' ) ) {
	function gmfw_initialize_i18n() {
		// Create an instance of the GMFW_I18n class.
		$plugin_i18n = new GMFW_I18n();

		// Hook the 'load_plugin_textdomain' method of the GMFW_I18n class to the 'plugins_loaded' action.
		// This ensures that the plugin's text domain is loaded as soon as all plugins are loaded by WordPress,
		// making translations available.
		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}
}

// Call the function to initialize internationalization support.
gmfw_initialize_i18n();



add_action( 'plugins_loaded', 'initialize_gmfw_run', 20 );
