<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <support@powerfulwp.com>
 */


if ( ! class_exists( 'GMFW' ) ) {


	class GMFW {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      GMFW_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			/**
			 * Currently plugin version.
			 * Start at version 1.0.0 and use SemVer - https://semver.org
			 * Rename this for your plugin and update it as you release new versions.
			 */
			if ( ! defined( 'GMFW_VERSION' ) ) {
				define( 'GMFW_VERSION', '1.7.9' );
			}

			if ( defined( 'GMFW_VERSION' ) ) {
				$this->version = GMFW_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'gmfw';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - GMFW_Loader. Orchestrates the hooks of the plugin.
		 * - GMFW_I18n. Defines internationalization functionality.
		 * - GMFW_Admin. Defines all hooks for the admin area.
		 * - GMFW_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gmfw-loader.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gmfw-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gmfw-public.php';

			/**
			 * The file responsible for defining all the metaboxes in admin panel
			 */
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gmfw-metaboxes.php';

			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {
					/**
					 * The file responsible for cart functions
					 */
					include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gmfw-cart.php';
				}
			}

			/**
			 * The file responsible for global functions
			 */
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

			$this->loader = new GMFW_Loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the GMFW_I18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			// $plugin_i18n = new GMFW_I18n();

			// $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			$plugin_admin = new GMFW_Admin( $this->get_plugin_name(), $this->get_version() );

			register_activation_hook( __FILE__, array( $this, 'gmfw_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'gmfw_deactivate' ) );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

			// Set default values.
			$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'update_default_options' );

			/**
			 * Add menu
			*/
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'gmfw_admin_menu', 99 );

			/**
			 * Settings
			*/
			$this->loader->add_action( 'admin_init', $plugin_admin, 'gmfw_settings_init' );

			/**
			 * Plugin review
			*/
			$this->loader->add_action( 'admin_init', $plugin_admin, 'plugin_review' );

			/**
			 * Order custom fields
			 */
			$this->loader->add_filter( 'is_protected_meta', $plugin_admin, 'gmfw_exclude_custom_fields', 10, 3 );

			/**
			 * Ajax calls
			 */
			$this->loader->add_action( 'wp_ajax_gmfw_ajax', $plugin_admin, 'gmfw_ajax' );
			$this->loader->add_action( 'wp_ajax_nopriv_gmfw_ajax', $plugin_admin, 'gmfw_ajax' );

			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {

					/**
					 * Order columns
					 */
					if ( gmfw_is_hpos_enabled() ) {
						$this->loader->add_action( 'woocommerce_shop_order_list_table_custom_column', $plugin_admin, 'orders_list_columns__premium_only', 20, 2 );
						$this->loader->add_filter( 'woocommerce_shop_order_list_table_columns', $plugin_admin, 'orders_list_columns_order__premium_only', 20 );
					} else {
						$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'orders_list_columns__premium_only', 20, 2 );
						$this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'orders_list_columns_order__premium_only', 20 );
					}

					/**
					 * Import data
					*/
					$this->loader->add_action( 'update_option_gmfw_import_data', $plugin_admin, 'gmfw_start_import_data__premium_only', 10, 2 );

					/**
					 * Settings
					*/
					$this->loader->add_action( 'init', $plugin_admin, 'gmfw_gift_message_posttype__premium_only' );

					/**
					 * Hide parent dropdown.
					*/
					$this->loader->add_filter( 'post_edit_category_parent_dropdown_args', $plugin_admin, 'hide_parent_dropdown_select__premium_only' );
				}
			}

		}



		/**
		 * The code that runs during plugin activation.
		 * Deactive free plugin version.
		 */
		public function gmfw_deactivate_lite_version__premium_only() {
			deactivate_plugins( 'gift-message-for-woocommerce/gift-message-for-woocommerce.php' );
		}

		/**
		 * The code that runs during plugin activation.
		 *
		 * @since    1.0.0
		 */
		public function gmfw_activate() {
			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {
					if ( is_plugin_active( 'gift-message-for-woocommerce/gift-message-for-woocommerce.php' ) ) {
						add_action( 'update_option_active_plugins', array( $this, 'gmfw_deactivate_lite_version__premium_only' ) );
					}
				}
			}

			require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmfw-activator.php';
			GMFW_Activator::activate();
		}

		/**
		 * The code that runs during plugin deactivation.
		 *
		 * @since    1.0.0
		 */
		public function gmfw_deactivate() {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmfw-deactivator.php';
			GMFW_Deactivator::deactivate();
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {
			if ( '1' === get_option( 'gmfw_enable_gift_message', '' ) ) {

				$plugin_public = new GMFW_Public( $this->get_plugin_name(), $this->get_version() );
				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

				$gmfw_woocommerce_checkout_section = get_option( 'gmfw_woocommerce_checkout_section', '' );
				if ( '' === $gmfw_woocommerce_checkout_section ) {
					$gmfw_woocommerce_checkout_section = 'woocommerce_after_order_notes';
				}

				$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_gift_header' );

				if ( gmfw_fs()->is__premium_only() ) {
					if ( gmfw_fs()->can_use_premium_code() ) {
						if ( '1' === get_option( 'gmfw_enable_gift_message_suggestions', '' ) ) {
							$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_occasions_field__premium_only' );
							$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_suggestions__premium_only' );
						}
					}
				}

				$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_message_field' );
				$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_gift_footer' );

				if ( gmfw_fs()->is__premium_only() ) {
					if ( gmfw_fs()->can_use_premium_code() ) {
						$this->loader->add_action( $gmfw_woocommerce_checkout_section, $plugin_public, 'gmfw_checkout_gift_cards__premium_only' );

						if ( false !== gmfw_get_gift_message_fee() ) {
							// Add gift message fee to checkout.
							$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'set_gift_message_cart_fee' );

							// Recalculate the order total when updating the order review.
							$this->loader->add_action( 'woocommerce_review_order_after_cart_contents', $plugin_public, 'update_cart_fee' );
						}
					}
				}

				$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'gmfw_validate_checkout_fields' );

				$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'gmfw_update_checkout_fields' );

				$this->loader->add_action( 'woocommerce_email_after_order_table', $plugin_public, 'gmfw_checkout_fields_email', 20, 4 );

				$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'gmfw_thankyou', 10, 1 );

				$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'gmfw_details_after_order_table', 10, 4 );
			}
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    GMFW_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}



	}
}


