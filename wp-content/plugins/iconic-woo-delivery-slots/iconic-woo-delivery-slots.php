<?php
/**
 * Plugin Name: WooCommerce Delivery Slots by Iconic
 * Plugin URI: https://iconicwp.com/products/woocommerce-delivery-slots/
 * Description: Allow your customers to select a delivery slot for their order
 * Version: 2.12.0
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Author Email: support@iconicwp.com
 * Text Domain: jckwds
 * WC requires at least: 2.6.14
 * WC tested up to: 10.4.3
 * Requires PHP: 7.4
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;
use Iconic_WDS_NS\StellarWP\ContainerContract\ContainerInterface;
use Iconic_WDS_Core_Autoloader, Iconic_WDS_Core_Order_Reminders, Iconic_WDS_Core_Uplink_Helper;
use DateTime;
use WC_Shipping_Zones, WC_Shipping_Zone;
use Iconic_WDS\Fee;
use Iconic_WDS\FeeManager;
use Iconic_WDS\Subscriptions\CheckoutBlock\SubscriptionCheckoutBlock;
use Iconic_WDS\Subscriptions\SubscriptionField;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionOrderMetaKey;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;

defined( 'ABSPATH' ) || exit;

/**
 * Main class.
 *
 * @class Iconic_WDS
 */
class Iconic_WDS {
	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public static $name = 'WooCommerce Delivery Slots';

	/**
	 * Plugin shortname.
	 *
	 * @var string
	 */
	public static $shortname = 'Delivery Slots';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public static $slug = 'jckwds';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '2.12.0';

	/**
	 * The singleton instance of the plugin.
	 *
	 * @var Iconic_WDS
	 */
	private static $instance;

	/**
	 * The DI container.
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Database version.
	 *
	 * @var string
	 */
	public $db_version = '1.6';

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Guest user_id cookie name.
	 *
	 * @var string
	 */
	public $guest_user_id_cookie_name = 'jckwds-guest-user-id';

	/**
	 * WP Settings framework option group.
	 *
	 * @var string
	 */
	public $option_group;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $user_id;

	/**
	 * Timeslot meta key.
	 *
	 * @var string
	 */
	public $timeslot_meta_key = 'jckwds_timeslot';

	/**
	 * Date meta key.
	 *
	 * @var string
	 */
	public $date_meta_key = 'jckwds_date';

	/**
	 * Timestamp meta key.
	 *
	 * @var string
	 */
	public $timestamp_meta_key = 'jckwds_timestamp';

	/**
	 * Shipping method meta key.
	 *
	 * @var string
	 */
	public $shipping_method_meta_key = 'jckwds_shipping_method';

	/**
	 * Resevation table name.
	 *
	 * @var string
	 */
	public $reservations_db_table_name;

	/**
	 * Timeslot date transient name.
	 *
	 * @var string
	 */
	public $timeslot_data_transient_name;

	/**
	 * Current day number.
	 *
	 * @var int
	 */
	public $current_day_number;

	/**
	 * Current day in ymd format.
	 *
	 * @var string
	 */
	public $current_ymd;

	/**
	 * Available shipping methods.
	 *
	 * @var array
	 */
	public $shipping_methods = array();

	/**
	 * Days to add, min.
	 *
	 * @var boolean
	 */
	public $days_to_add_min = false;

	/**
	 * Days to add, max.
	 *
	 * @var boolean
	 */
	public $days_to_add_max = false;


	/**
	 * Object of reminders class.
	 *
	 * @var Reminders
	 */
	public $reminders;

	/**
	 * Bookable dates.
	 *
	 * @var array
	 */
	public $bookable_dates = array();

	/**
	 * Fee handler.
	 *
	 * @var ?Fee
	 */
	public $fee;

	/**
	 * Instantiate a single instance of our plugin.
	 *
	 * @return Iconic_WDS
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Check PHP version.
		if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 ) {
			add_action( 'admin_notices', array( $this, 'php_version_error' ) );

			return false;
		}

		$this->define_constants();
		$this->setup_autoloader();
		$this->setup_constants();
		$this->init_license();
		$this->init_telemetry();

		$this->container = new \Iconic_WDS_Core_Container();
		$this->fee       = new Fee();
		Subscriptions\Boot::run();

		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'on_blocks_loaded' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		add_action( 'wp_loaded', array( $this, 'on_wp_loaded' ), 10 );
	}

	/**
	 * Runs on wp_loaded hook.
	 */
	public function on_wp_loaded() {
		// Need to run this before Settings::run() to ensure the Advanced Shipping methods are added to the shipping method options.
		Compatibility\WoocommerceAdvancedShipping::run();
		Settings::run();

		if ( ! Helpers::is_wc_active() ) {
			return;
		}

		$this->load_classes();
		$this->add_compatibility();

		$this->initiate();
		FeeManager::run();
		WooCommerceLocalPickup::run();
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
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_WDS_FILE', __FILE__ );
		$this->define( 'ICONIC_WDS_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_WDS_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_WDS_INC_PATH', ICONIC_WDS_PATH . 'inc/' );
		$this->define( 'ICONIC_WDS_VENDOR_PATH', ICONIC_WDS_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_WDS_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'ICONIC_WDS_PLUGIN_PATH_FILE', str_replace( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ), '', wp_normalize_path( ICONIC_WDS_FILE ) ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Definition name.
	 * @param string|bool $value Definition value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Setup Constants
	 */
	public function setup_constants() {
		global $wpdb;

		$this->reservations_db_table_name   = $wpdb->prefix . self::$slug;
		$this->timeslot_data_transient_name = sprintf( '%s-timeslot-data', self::$slug );
		$this->current_day_number           = absint( current_time( 'w' ) );
		$this->current_ymd                  = current_time( 'Ymd' );
	}

	/**
	 * Setup autoloader.
	 */
	private function setup_autoloader() {
		require_once ICONIC_WDS_PATH . 'vendor-prefixed/autoload.php';
		require_once ICONIC_WDS_INC_PATH . 'class-core-autoloader.php';
		require_once ICONIC_WDS_PATH . 'vendor/autoload.php';

		Iconic_WDS_Core_Autoloader::run(
			array(
				'prefix'   => 'Iconic_WDS_',
				'inc_path' => ICONIC_WDS_INC_PATH,
			)
		);
	}

	/**
	 * Load classes.
	 */
	private function load_classes() {
		Reservations::run();
		Api::init();
		Ajax::init();
		Order::run();
		Helpers::run();
		Shortcodes::run();
		Checkout::run();
		OverrideSettings::run();
		Admin::run();
		Migrate::run();
		OrderMetaKeyMigrate::run();
		EditTimeslots::run();
		ExpressCheckout::run();

		\Iconic_WDS_Core_Onboard::run();
	}

	/**
	 * Init license class.
	 */
	public function init_license() {
		// Allows us to transfer Freemius license.
		if ( file_exists( ICONIC_WDS_PATH . 'class-core-freemius-sdk.php' ) ) {
			require_once ICONIC_WDS_PATH . 'class-core-freemius-sdk.php';

			new \Iconic_WDS_Core_Freemius_SDK(
				array(
					'plugin_path'          => ICONIC_WDS_PATH,
					'plugin_file'          => ICONIC_WDS_FILE,
					'uplink_plugin_slug'   => 'iconic-wds',
					'freemius'             => array(
						'id'         => '1038',
						'slug'       => 'iconic-woo-delivery-slots',
						'public_key' => 'pk_ae98776906ff416522057aab876c0',
					),
				)
			);
		}

		\Iconic_WDS_Core_License_Uplink::run( array(
			'basename'        => ICONIC_WDS_BASENAME,
			'plugin_slug'     => 'iconic-wds',
			'plugin_name'     => self::$name,
			'plugin_version'   => self::$version,
			'plugin_path'     => ICONIC_WDS_PLUGIN_PATH_FILE,
			'plugin_class'    => self::class,
			'option_group'    => 'jckwds',
			'urls'            => array(
				'product' => 'https://iconicwp.com/products/woocommerce-delivery-slots/',
			),
			'container_class' => self::class,
			'license_class' => Iconic_WDS_Core_Uplink_Helper::class,
		) );
	}

	/**
	 * Init telemetry class.
	 *
	 * @return void
	 */
	public function init_telemetry() {
		\Iconic_WDS_Core_Telemetry::run(
			array(
				'file'                  => __FILE__,
				'plugin_slug'           => 'iconic-wds',
				'option_group'          => 'jckwds',
				'plugin_name'           => self::$name,
				'plugin_url'            => ICONIC_WDS_URL,
				'opt_out_settings_path' => 'sections/license/fields',
				'container_class'       => self::class,
			)
		);
	}

	/**
	 * Set settings.
	 *
	 * @param array $settings Settings array.
	 */
	public function set_settings( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Add third party compatibility.
	 */
	public function add_compatibility() {
		Compatibility\FlexibleShipping::run();
		Compatibility\TableRateShipping::run();
		Compatibility\BootstrapDate::run();
		Compatibility\PdfInvoicesPackingSlips::run();
		Compatibility\LeadTime::run();
		Compatibility\MultistepCheckout::run();
		Compatibility\WooPaypalPayments::run();

		require_once ICONIC_WDS_INC_PATH . 'Compatibility.php';
	}

	/**
	 * PHP Version Error Message
	 */
	public function php_version_error() {
		// Translators: PHP version.
		$message = sprintf( __( "You need to be running PHP 5.3+ for Delivery Slots to work. You're on %s.", 'jckwds' ), PHP_VERSION );

		echo '<div class="error"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Runs when the plugin is initialized
	 */
	public function initiate() {
		// Setup localization.
		load_plugin_textdomain( 'jckwds', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		$GLOBALS['iconic_wds_dates'] = new Dates();

		// Set user ID.
		$this->set_user_id();

		if ( is_admin() ) {
			add_filter( 'option_page_capability_' . self::$slug, array( $this, 'option_page_capability' ) );

			add_action( 'set_transient', array( __CLASS__, 'on_update_shipping' ), 10, 3 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_action( 'wp_head', array( $this, 'dynamic_css' ) );
		}

		$this->reminders = Iconic_WDS_Core_Order_Reminders::run(
			array(
				'enabled'           => $this->settings['general_email_reminders_enable_reminders'],
				'reminder_duration' => $this->settings['general_email_reminders_duration'],
				'email_body'        => $this->settings['general_email_reminders_email_text'],
				'max_reminder'      => $this->settings['general_email_reminders_max_emails'],
				'plugin_slug'       => 'iconic_wds',
				'order_meta'        => $this->date_meta_key,
			)
		);

		// WooCommerce Actions and Hooks.

		// Less than 2.3.0.
		if ( version_compare( $this->get_woo_version_number(), '2.3.0', '<' ) ) {
			add_filter( 'woocommerce_email_order_meta_keys', array( $this, 'email_order_meta_keys' ) );
		}

		add_filter(
			'woocommerce_update_order_review_fragments',
			array(
				$this,
				'update_order_review_fragments',
			),
			10,
			1
		);

		add_filter( 'iconic_wds_is_delivery_slot_pending', array( __CLASS__, 'modify_is_delivery_slot_pending' ), 10, 2 );

		$this->position_checkout_fields();
	}

	/**
	 * Transition settings
	 */
	public function transition_settings() {
		$new_settings = get_option( 'jckwds_settings' );
		$old_settings = get_option( 'jckdeliveryslots_settings' );

		if ( ! $new_settings && $old_settings ) {
			$old_settings_formatted = array();

			foreach ( $old_settings as $setting_name => $value ) {
				$old_settings_formatted[ $setting_name ] = $value;

				if ( 'timesettings_timesettings_timeslots' === $setting_name ) {
					if ( ! empty( $value ) ) {
						foreach ( $value as $index => $timeslot ) {
							$old_settings_formatted[ $setting_name ][ $index ]['timefrom'] = $timeslot['timefrom']['time'];
							$old_settings_formatted[ $setting_name ][ $index ]['timeto']   = $timeslot['timeto']['time'];
						}
					}
				}

				if ( 'holidays_holidays_holidays' === $setting_name ) {
					if ( ! empty( $value ) ) {
						foreach ( $value as $index => $holiday ) {
							$old_settings_formatted[ $setting_name ][ $index ]['date'] = $holiday['date']['date'];
						}
					}
				}

				if ( 'datesettings_datesettings_sameday_cutoff' === $setting_name || 'datesettings_datesettings_nextday_cutoff' === $setting_name ) {
					if ( ! empty( $value ) ) {
						$old_settings_formatted[ $setting_name ] = $value['time'];
					}
				}
			}

			update_option( 'jckwds_settings', $old_settings_formatted );
		}
	}

	/**
	 * Admin: Allow shop managers to save options.
	 *
	 * @param string $capability User capability.
	 *
	 * @return string
	 */
	public function option_page_capability( $capability ) {
		return 'manage_woocommerce';
	}

	/**
	 * Admin: Display Deliveries page
	 */
	public function deliveries_page_display() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jckwds' ) );
		}

		require_once 'inc/admin/deliveries.php';
	}

	/**
	 * Frontend: Position the checkout fields
	 */
	public function position_checkout_fields() {
		if ( 'add_manually' === $this->settings['general_setup_position'] ) {
			return;
		}

		// If subscription integration is active, don't display the checkout fields.
		// Subscription integration will display its own checkout fields.
		if ( $this->has_subscription_product_in_cart() ) {
			return;
		}

		global $iconic_wds_dates;

		add_action(
			$this->settings['general_setup_position'],
			array(
				$iconic_wds_dates,
				'display_checkout_fields',
			),
			$this->settings['general_setup_position_priority']
		);
	}

	/**
	 * Get subscription product in cart.
	 *
	 * @return WC_Product|null
	 */
	public function has_subscription_product_in_cart() {
		$subscription_integration = Subscriptions\Boot::get_active_integration();

		if ( ! $subscription_integration ) {
			return null;
		}

		$cart               = new Cart();
		$subscription_field = new SubscriptionField( $cart );

		return $subscription_field->find_subscription_product_in_cart();
	}

	/**
	 * Helper: Get timeslot select value
	 * Format a timeslot for use in a select field.
	 *
	 * @param array $timeslot Timeslot.
	 *
	 * @return str
	 */
	public function get_timeslot_value( $timeslot ) {
		return sprintf( '%s|%01.2f', $timeslot['id'], '' === $timeslot['fee']['value'] ? 0 : $timeslot['fee']['value'] );
	}

	/**
	 * Helper: Add timestamp order meta.
	 *
	 * @param string $date         Ymd.
	 * @param array  $timeslot     get_timeslot_data().
	 * @param int    $order_id     Order ID.
	 * @param string $product_type Product type.
	 *
	 * @return bool
	 */
	public function add_timestamp_order_meta( $date, $timeslot, $order, $product_type = 'regular' ) {
		if ( empty( $date ) ) {
			return false;
		}

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$time = '10:00';

		if ( ! empty( $timeslot ) ) {
			$time = $timeslot['timefrom']['time'];
		}

		// Add meta to order for "ordering".
		$datetime = \DateTime::createFromFormat( 'Ymd H:i', sprintf( '%s %s', $date, $time ), wp_timezone() );

		if ( ! $datetime ) {
			return false;
		}

		$timestamp = $datetime->getTimestamp();

		$key = SubscriptionProductType::SUBSCRIPTION === $product_type ? SubscriptionOrderMetaKey::TIMESTAMP_META_KEY : $this->timestamp_meta_key;

		$order->update_meta_data( $key, $timestamp );

		$order->save();

		return true;
	}

	/**
	 * Helper: Display Date and Timeslot
	 *
	 * @param WC_Order $order      Order.
	 * @param bool     $show_title Show title.
	 * @param bool     $plain_text Plain text.
	 */
	public function display_date_and_timeslot( $order, $show_title = false, $plain_text = false, $date_format = false ) {
		$date_time = Order::get_order_date_time( $order );

		if ( ! $date_time ) {
			return;
		}

		$delivery_details_text = Helpers::get_label( 'details', $order );
		$delivery_date_text    = Helpers::get_label( 'date', $order );
		$time_slot_text        = Helpers::get_label( 'time_slot', $order );
		$timestamp             = $date_time['timestamp'];
		$date                  = empty( $date_time['date'] ) ? false : $date_time['date'];
		$date                  = $date_format ? wp_date( $date_format, $timestamp ) : $date;
		$time                  = empty( $date_time['time'] ) ? false : apply_filters( 'iconic_wds_time_display', $date_time['time'], $date_time );

		/**
		 * Filter the date display.
		 *
		 * @param string   $date       Date.
		 * @param array    $date_time  Date and time information for the order.
		 * @param WC_Order $order      Order.
		 * @param bool     $plain_text Plain text.
		 *
		 * @since 1.17.0
		 */
		$date = apply_filters( 'iconic_wds_date_display', $date, $date_time, $order, $plain_text );

		if ( $plain_text ) {
			echo "\n\n==========\n\n";

			if ( $show_title ) {
				printf( "%s \n", esc_html( strtoupper( $delivery_details_text ) ) );
			}

			if ( $date_time['date'] ) {
				printf( "\n%s: %s", esc_html( $delivery_date_text ), esc_html( $date ) );
			}

			if ( $date_time['time'] ) {
				printf( "\n%s: %s", esc_html( $time_slot_text ), esc_html( $time ) );
			}

			echo "\n\n==========\n\n";
		} else {
			if ( $show_title ) {
				printf( '<h2>%s</h2>', esc_html( $delivery_details_text ) );
			}

			if ( $date ) {
				printf( '<p><strong>%s</strong> <br>%s</p>', esc_html( $delivery_date_text ), esc_html( $date ) );
			}

			if ( $date_time['time'] ) {
				printf( '<p><strong>%s</strong> <br>%s</p>', esc_html( $time_slot_text ), esc_html( $time ) );
			}
		}
	}

	/**
	 * Frontend: Add date and timeslot to order email.
	 *
	 * @param array $keys Meta Keys.
	 *
	 * @return array
	 */
	public function email_order_meta_keys( $keys ) {
		$date_label               = Helpers::get_label( 'date' );
		$time_slot_label          = Helpers::get_label( 'time_slot' );
		$keys[ $date_label ]      = $this->date_meta_key;
		$keys[ $time_slot_label ] = $this->timeslot_meta_key;

		return $keys;
	}

	/**
	 * Helper: Get timeslot data
	 *
	 * @param int $timeslot_id If an Id is passed, get a single timeslot, else get all.
	 *
	 * @return array|bool Returns timeslots with some additional data, like formatted times and values
	 */
	public function get_timeslot_data( $timeslot_id = false ) {
		if ( ! $this->settings['timesettings_timesettings_setup_enable'] ) {
			return false;
		}

		if ( false !== strpos( $timeslot_id, '|' ) ) {
			$timeslot_id = $this->extract_timeslot_id_from_option_value( $timeslot_id );
		}

		$timeslot_data = get_transient( $this->timeslot_data_transient_name );

		if ( false === $timeslot_data || defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			$timeslot_data = array();

			if ( $this->settings['timesettings_timesettings_asap_enable'] ) {
				$timeslot_data['asap'] = $this->get_asap_slot_data();
			}

			$timeslots           = $this->settings['timesettings_timesettings_timeslots'];
			$formatted_timeslots = array();

			if ( ! empty( $timeslots ) ) {
				foreach ( $timeslots as $slot_id => $timeslot ) {
					$slot_id = empty( $timeslot['row_id'] ) ? $slot_id : $timeslot['row_id'];

					if ( empty( $timeslot['frequency'] ) ) {
						$formatted_timeslots[ $slot_id ] = $timeslot;
						continue;
					}

					$looping         = true;
					$i               = 0;
					$start_timestamp = strtotime( '1970-01-01 ' . $timeslot['timefrom'] . ':00 UTC' );
					$end_timestamp   = strtotime( '1970-01-01 ' . $timeslot['timeto'] . ':00 UTC' );
					$frequency       = floatval( $timeslot['frequency'] );
					$duration        = '' !== $timeslot['duration'] ? floatval( $timeslot['duration'] ) : $frequency;

					while ( $looping ) {
						$difference_in_minutes = ( $end_timestamp - $start_timestamp ) / 60;

						// Exit if the start time is after the end time.
						if ( $difference_in_minutes < $frequency || $start_timestamp >= $end_timestamp ) {
							$looping = false;
							break;
						}

						$dynamic_slot_id                                     = $slot_id . '/' . $i;
						$timeto_timestamp                                    = $start_timestamp + ( 60 * $duration );
						$formatted_timeslots[ $dynamic_slot_id ]             = $timeslot;
						$formatted_timeslots[ $dynamic_slot_id ]['timefrom'] = gmdate( 'H:i', $start_timestamp );
						$formatted_timeslots[ $dynamic_slot_id ]['timeto']   = gmdate( 'H:i', $timeto_timestamp );
						$start_timestamp                                     = $start_timestamp + ( 60 * $frequency );

						$i ++;
					}
				}

				foreach ( $formatted_timeslots as $slot_id => $timeslot ) {
					$timeslot_data[ $slot_id ] = $timeslot;

					$start_time_formatted = $this->format_time( $timeslot['timefrom'], 'H:i' );
					$end_time_formatted   = $this->format_time( $timeslot['timeto'], 'H:i' );

					$timeslot_data[ $slot_id ]['id']                 = $slot_id;
					$timeslot_data[ $slot_id ]['timefrom']           = array(
						'time'     => $timeslot_data[ $slot_id ]['timefrom'],
						'stripped' => str_replace( ':', '', $timeslot['timefrom'] ),
					);
					$timeslot_data[ $slot_id ]['timeto']             = array(
						'time'     => $timeslot_data[ $slot_id ]['timeto'],
						'stripped' => str_replace( ':', '', $timeslot['timeto'] ),
					);
					$timeslot_data[ $slot_id ]['time_id']            = $timeslot_data[ $slot_id ]['timefrom']['stripped'] . $timeslot_data[ $slot_id ]['timeto']['stripped'];
					$timeslot_data[ $slot_id ]['fee']                = array(
						'value'     => $timeslot['fee'],
						'formatted' => wc_price( $timeslot['fee'] ),
					);
					$timeslot_data[ $slot_id ]['formatted']          = $start_time_formatted === $end_time_formatted ? $start_time_formatted : sprintf( '%s - %s', $start_time_formatted, $end_time_formatted );
					$timeslot_data[ $slot_id ]['formatted_with_fee'] = $timeslot_data[ $slot_id ]['fee']['value'] > 0 ? sprintf( '%s (+%s)', $timeslot_data[ $slot_id ]['formatted'], wp_strip_all_tags( $timeslot_data[ $slot_id ]['fee']['formatted'] ) ) : $timeslot_data[ $slot_id ]['formatted'];
					$timeslot_data[ $slot_id ]['value']              = $this->get_timeslot_value( $timeslot_data[ $slot_id ] );
				}
			}

			// Sort timeslots array based on the start time.
			uasort( $timeslot_data, array( __CLASS__, 'sort_timeslot_by_from_value' ) );

			set_transient( $this->timeslot_data_transient_name, $timeslot_data, 24 * HOUR_IN_SECONDS );
		}

		// If a specific timeslot IS being grabbed,
		// add dynamic data for that slot only.

		if ( false !== $timeslot_id ) {
			if ( isset( $timeslot_data[ $timeslot_id ] ) ) {
				return apply_filters( 'iconic_wds_timeslot', $timeslot_data[ $timeslot_id ] );
			} else {
				return false;
			}
		}

		// Otherwise, return all timeslots.

		return apply_filters( 'iconic_wds_timeslots', $timeslot_data );
	}

	/**
	 * The callback function to be used by usort.
	 *
	 * @param array $a Single timeslot.
	 * @param array $b Single timeslot.
	 *
	 * @return int difference.
	 */
	public static function sort_timeslot_by_from_value( $a, $b ) {
		return strcmp( $a['timefrom']['stripped'], $b['timefrom']['stripped'] );
	}

	/**
	 * Helper: Implode classes
	 *
	 * @param array $classes Classes.
	 *
	 * @return string
	 */
	public function implode_classes( $classes ) {
		if ( empty( $classes ) ) {
			return '';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Helper: Is timeslot in past?
	 *
	 * Checks whether the satrt time of the timeslot has already passed for the current day
	 *
	 * @param array  $timeslot Timeslot.
	 * @param string $date     Ymd.
	 *
	 * @return bool
	 */
	public function is_timeslot_in_past( $timeslot, $date = false ) {
		$date = $date ? $date : $this->current_ymd;

		$cutoff = $this->get_cutoff( $timeslot );

		if ( 'asap' === $timeslot['id'] ) {
			$asap_cutoff    = ! empty( $this->settings['timesettings_timesettings_asap_cutoff'] ) ? $this->settings['timesettings_timesettings_asap_cutoff'] : '23:59';
			$timeslot_ymdgi = $date . str_replace( ':', '', $asap_cutoff );
		} else {
			$time           = ( isset( $timeslot['cutoff_based_on'] ) && 'to' === $timeslot['cutoff_based_on'] ) ? $timeslot['timeto']['stripped'] : $timeslot['timefrom']['stripped'];
			$timeslot_ymdgi = $date . $time;
		}

		// Deduct cutoff from timeslot date/time.
		$timeslot_date_time = DateTime::createFromFormat( 'YmdGi', $timeslot_ymdgi, wp_timezone() );
		$timeslot_timestamp = $timeslot_date_time->getTimestamp() - ( $cutoff * 60 );

		$in_past = time() >= $timeslot_timestamp;

		return apply_filters( 'iconic_wds_is_timeslot_in_past', $in_past, $timeslot, $date );
	}

	/**
	 * Get cutoff.
	 *
	 * @param bool|array $timeslot Timeslot.
	 *
	 * @return string
	 */
	public function get_cutoff( $timeslot = false ) {
		$cutoff = ! empty( $timeslot['cutoff'] ) ? $timeslot['cutoff'] : $this->settings['timesettings_timesettings_cutoff'];

		return apply_filters( 'iconic_wds_get_cutoff', $cutoff, $timeslot, $this );
	}

	/**
	 * Check if a timeslot is allowed on a specific day of the week.
	 * Timestamp is converted to current timezone.
	 *
	 * @param int $timestamp GMT Timestamp.
	 * @param array  $timeslot  Timeslot.
	 *
	 * @return bool
	 */
	public function is_timeslot_available_on_day( $timestamp, $timeslot ) {
		$allowed       = false;
		$ymd           = date_i18n( 'Ymd', $timestamp );
		$specific_date = Settings::is_specific_date( $ymd );

		// If this is a specific date, check if there's a timeslot for it.
		if ( $specific_date ) {
			$specific_date_row_id = Settings::get_row_id( $specific_date );
			$allowed              = in_array( $specific_date_row_id, $timeslot['days'], true );
		} elseif ( isset( $timeslot['days'] ) && is_array( $timeslot['days'] ) ) {
			$timeslot['days'] = array_map( 'strval', $timeslot['days'] );
			$day_number       = (int) date_i18n( 'w', $timestamp );
			$allowed          = in_array( strval( $day_number ), $timeslot['days'], true );
		}

		return apply_filters( 'iconic_wds_is_timeslot_available_on_day', $allowed, $timestamp, $timeslot );
	}

	/**
	 * Frontend scripts.
	 */
	public function frontend_scripts() {
		$min                           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$load_reservation_table_assets = self::load_reservation_table_assets();
		$order_id                      = self::get_order_id();
		$dates_manager                 = new Dates( array( 'order_id' => $order_id ) );

		$script_vars = array(
			'settings'               => $this->settings,
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'             => wp_create_nonce( self::$slug ),
			'is_mobile'              => wp_is_mobile(),
			'date_fns_format'        => Helpers::date_format_fns(),
			'locale'                 => get_locale(),
			'shipping_method_labels' => Settings::get_shipping_method_labels(),
			'all_products_virtual'   => $dates_manager->cart->are_all_products_virtual(),
			'currency'               => array(
				'precision' => 2,
				'symbol'    => get_woocommerce_currency_symbol(),
				'decimal'   => esc_attr( wc_get_price_decimal_separator() ),
				'thousand'  => esc_attr( wc_get_price_thousand_separator() ),
				'format'    => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			),
			'dates'                  => array(
				'same_day' => $dates_manager->get_same_day_date( Helpers::date_format() ),
				'next_day' => $dates_manager->get_next_day_date( Helpers::date_format() ),
			),
			'strings'                => Helpers::get_localized_strings(),
			'i18n'                   => Helpers::get_localized_date_strings(),
		);

		if ( is_checkout() || $order_id ) {
			$script_vars['bookable_dates'] = $this->get_upcoming_bookable_dates( Helpers::date_format() );
			$script_vars['needs_shipping'] = $dates_manager->needs_shipping();
			$script_vars['day_fees']       = Settings::get_day_fees();
		}

		if ( is_checkout() || $load_reservation_table_assets || $order_id ) {
			$this->load_file( self::$slug . '-script', '/assets/frontend/js/main' . $min . '.js', true, array( 'jquery-ui-datepicker', Helpers::get_accounting_script_handle(), 'wp-html-entities', 'wp-hooks' ), true );
			$this->load_file( self::$slug . '-style', '/assets/frontend/css/main' . $min . '.css' );

			$script_vars['reserved_slot'] = $this->get_reserved_slot( $order_id );
		}

		if ( $load_reservation_table_assets ) {
			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'wc-address-i18n' );
			wp_enqueue_script( 'accounting' );
			$this->load_file( 'vuejs', '/assets/vendor/vue' . $min . '.js', true );
			$this->load_file( 'vue-concise-slider', '/assets/vendor/vue-concise-slider.min.js', true );

			$script_vars['available_countries'] = WC()->countries->get_shipping_countries();
			$script_vars['locale_default']      = WC()->countries->get_default_address_fields();
			$script_vars['address']             = array(
				'country'  => WC()->customer->get_shipping_country(),
				'state'    => WC()->customer->get_shipping_state(),
				'city'     => WC()->customer->get_shipping_city(),
				'postcode' => WC()->customer->get_shipping_postcode(),
			);

			$shipping_info = Helpers::get_allowed_shipping_methods_for_current_session();
			if ( $shipping_info ) {
				$script_vars['shipping'] = array(
					'shipping_methods'      => isset( $shipping_info['shipping_methods'] ) ? $shipping_info['shipping_methods'] : array(),
					'formatted_destination' => isset( $shipping_info['formatted_destination'] ) ? $shipping_info['formatted_destination'] : '',
				);

				$selected_methods = WC()->session->get( 'chosen_shipping_methods' );
				if ( ! empty( $selected_methods ) ) {
					$script_vars['shipping']['selected_shipping_method'] = $selected_methods[0];
				}
			}
		}

		wp_localize_script( self::$slug . '-script', self::$slug . '_vars', $script_vars );
	}

	/**
	 * Automatically checks if we are on the thank you page or the view order page and
	 * return the order ID.
	 *
	 * @return int|bool Order ID or false.
	 */
	public static function get_order_id() {
		$is_thankyou   = Helpers::is_thankyou_page();
		$is_view_order = Helpers::is_my_account_order_page();

		return $is_thankyou ? $is_thankyou : $is_view_order;
	}

	/**
	 * This function checks if the reservation table assets should be loaded based on whether the
	 * "iconic-wds-reservation-table" or "jckwds" shortcode is present in the post content.
	 *
	 * @return bool
	 */
	public static function load_reservation_table_assets() {
		global $post;

		/**
		 * Force load Reservation table assets.
		 *
		 * @since 1.18.0
		 */
		return apply_filters( 'iconic_wds_force_load_reservation_calendar_assets', false )
		||
		( is_object( $post ) && ( has_shortcode( $post->post_content, 'iconic-wds-reservation-table' ) || has_shortcode( $post->post_content, 'jckwds' ) ) );
	}

	/**
	 * Wrapper for get upcoming bookable dates.
	 *
	 * @param string $format       Format of results.
	 * @param bool   $ignore_slots Ignore whether there are slots available.
	 * @param bool   $no_cache     Dont use caching.
	 *
	 * @return array
	 */
	public function get_upcoming_bookable_dates( $format = 'array', $ignore_slots = false, $no_cache = false ) {
		$order_id      = $this->get_order_id();
		$dates_manager = new Dates( array( 'order_id' => $order_id ) );
		return $dates_manager->get_upcoming_bookable_dates( $format, $ignore_slots, $no_cache );
	}

	/**
	 * Frontend: Add dynamic styles to head tag
	 */
	public function dynamic_css() {
		include_once ICONIC_WDS_PATH . 'assets/frontend/css/user.css.php';
	}

	/**
	 * Helper: Add reservation to database
	 *
	 * @param array $data Data.
	 *
	 * @return bool
	 */
	public function add_reservation( $data ) {
		global $wpdb;

		$insert = false;

		$defaults = array(
			'datetimeid' => false,
			'processed'  => 0,
			'date'       => false,
			'starttime'  => '',
			'endtime'    => '',
			'order_id'   => '',
			'asap'       => false,
		);

		$data = wp_parse_args( $data, $defaults );

		if ( $data['date'] ) {
			$expire = ( $data['processed'] ) ? null : strtotime( '+' . $this->settings['reservations_reservations_expires'] . ' minutes', time() );

			$this->remove_existing_reservation( $data['order_id'] );

			$insert = $wpdb->insert(
				$this->reservations_db_table_name,
				array(
					'datetimeid' => $data['datetimeid'],
					'processed'  => $data['processed'],
					'user_id'    => $this->user_id,
					'expires'    => $expire,
					'date'       => $data['date'],
					'starttime'  => $data['starttime'],
					'endtime'    => $data['endtime'],
					'order_id'   => $data['order_id'],
					'asap'       => $data['asap'],
				),
				array(
					'%s',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
				)
			);
		}

		return $insert;
	}

	/**
	 * Remove existing reservation for order ID.
	 *
	 * @param int|bool $order_id Order ID.
	 */
	public function remove_existing_reservation( $order_id = false ) {
		global $wpdb;

		$has_reservation = $this->has_reservation();

		if ( ! $has_reservation && empty( $order_id ) ) {
			return;
		}

		$reservation_id = $has_reservation ? $has_reservation->id : false;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}jckwds WHERE order_id = %d OR id = %d",
				$order_id,
				$reservation_id
			)
		);
	}

	/**
	 * Helper: Set User ID
	 *
	 * If cookie is set, use that, otherwise use logged in user id,
	 * otherwise set cookie and use it.
	 */
	public function set_user_id() {
		/**
		 * Allow third party plugins and snippets to skip the cookie.
		 * This is useful when making plugin complain with GDPR/Cookie Consent plugins.
		 *
		 * @since 2.2.0.
		 */
		if ( apply_filters( 'iconic_wds_skip_cookie', false ) ) {
			return;
		}

		// If the cookie is set.
		if ( isset( $_COOKIE[ $this->guest_user_id_cookie_name ] ) ) {
			// Set the cookie as the user id.
			$this->user_id = sanitize_key( $_COOKIE[ $this->guest_user_id_cookie_name ] );

			// If the user already has a reservation, we'll leave it there
			// this means if a user sets a reservation, then logs in
			// their reservation will be maintained.

			if ( $this->has_reservation() ) {
				return;
			}
		}

		// If they didn't have a reservation, we'll proceed here.
		if ( is_user_logged_in() ) {
			$this->user_id = get_current_user_id();
		} else {
			if ( isset( $_COOKIE[ $this->guest_user_id_cookie_name ] ) ) {
				$this->user_id = sanitize_key( $_COOKIE[ $this->guest_user_id_cookie_name ] );
			} else {
				if ( headers_sent() ) {
					return;
				}

				$this->user_id = uniqid( self::$slug );
				setcookie( $this->guest_user_id_cookie_name, $this->user_id, 0, '/', COOKIE_DOMAIN );
			}
		}
	}

	/**
	 * Helper: Update a reserved slot.
	 *
	 * @param string $slot_id  Slot id e.g: Ymd_0.
	 * @param int    $order_id Order ID.
	 *
	 * @return array.
	 */
	public function update_reservation( $slot_id, $order_id ) {
		global $wpdb;

		if ( ! $order_id ) {
			return;
		}

		$order_reservation          = Reservations::get_reservation_for_order( $order_id );
		$reservation_already_exists = ! is_null( $order_reservation );

		// If the reservation already exists for this order, update it.
		$where_order_id = $reservation_already_exists ? $order_id : 0;
		$slot           = $this->get_slot_data_from_id( $slot_id );

		$update = $wpdb->update(
			$this->reservations_db_table_name,
			array(
				'processed'  => 1,
				'order_id'   => $order_id,
				'datetimeid' => $slot_id,
				'date'       => $slot['date']['database'],
				'starttime'  => $slot['time']['timefrom']['stripped'],
				'endtime'    => $slot['time']['timeto']['stripped'],
				'expires'    => null,
			),
			array(
				'user_id'  => $this->user_id,
				'order_id' => $where_order_id,
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			),
			array(
				'%s',
				'%d',
			)
		);

		return $update;
	}

	/**
	 * Helper: Check if current user has a reservation
	 *
	 * @return [arr/bool]
	 */
	public function has_reservation() {
		global $wpdb;

		$reservation = $wpdb->get_row(
			$wpdb->prepare(
				"
                SELECT *
                FROM {$wpdb->prefix}jckwds
                WHERE processed = 0
                AND user_id = %s
                ",
				$this->user_id
			)
		);

		return ( $reservation ) ? $reservation : false;
	}

	/**
	 * Helper: Register and enqueue scripts and styles
	 *
	 * @param string $name      Script/style Name.
	 * @param string $file_path File path.
	 * @param bool   $is_script Is script or style.
	 * @param array  $deps      Dependencies.
	 * @param bool   $in_footer In footer.
	 */
	public function load_file( $name, $file_path, $is_script = false, $deps = array( 'jquery' ), $in_footer = true ) {
		$url  = plugins_url( $file_path, __FILE__ );
		$file = plugin_dir_path( __FILE__ ) . $file_path;

		if ( file_exists( $file ) ) {
			if ( $is_script ) {
				wp_register_script( $name, $url, $deps, self::$version, $in_footer ); // Depends on jQuery.
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url, array(), self::$version );
				wp_enqueue_style( $name );
			}
		}
	}

	/**
	 * Helper: Get number of of slots available for a specific date/time
	 *
	 * @param array  $timeslots Array of timeslots.
	 * @param string $ymd       Ymd string of date.
	 * @param string $max_order_calculation_method Weather the max order calculation is based on number of 'orders' or 'products'.
	 *
	 * @return array
	 */
	public function get_slots_available_count( $timeslots, $ymd, $max_order_calculation_method = false ) {
		static $counts = array();

		if ( ! empty( $counts[ $ymd ] ) && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			return $counts[ $ymd ];
		}

		if ( false === $max_order_calculation_method ) {
			$max_order_calculation_method = $this->settings['general_setup_max_order_calculation_method'];
		}

		global $iconic_wds_dates;

		$orders_remaining_for_day = $iconic_wds_dates->get_orders_remaining_for_day( $ymd );

		if ( ! $orders_remaining_for_day ) {
			$counts[ $ymd ] = 0;

			return $counts[ $ymd ];
		}

		global $wpdb;

		// No need to run query if lockout/max orders are not set.
		if ( ! $this->timeslots_have_max_orders_limit( $timeslots ) ) {
			$counts[ $ymd ] = array_fill_keys( wp_list_pluck( $timeslots, 'id' ), true );
			/**
			 * Filter slots available count.
			 *
			 * @since 1.25.0
			 */
			return apply_filters( 'iconic_wds_slots_available_count', $counts[ $ymd ], $ymd, $timeslots );
		}

		$timeslot_ids   = wp_list_pluck( $timeslots, 'id' );
		$counts[ $ymd ] = array_fill_keys( $timeslot_ids, 0 );

		$reserved_slots = array();

		$orders_table_name     = Helpers::is_cot_enabled() ? OrdersTableDataStore::get_orders_table_name() : $wpdb->posts;
		$status_column_name    = Helpers::is_cot_enabled() ? 'status' : 'post_status';
		$excluded_order_status = self::get_excluded_order_statuses();

		/*
		If max order calculation method is 'orders' then we count orders per slot,
		else if method is 'products' then we count number of products in orders, per slot.
		*/
		if ( 'orders' === $max_order_calculation_method ) {
			$reserved_slots = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT datetimeid, COUNT(datetimeid) as count
					FROM {$wpdb->prefix}jckwds wds, " . esc_sql( $orders_table_name ) . ' o
					WHERE
					wds.order_id = o.id 
					AND o.' . esc_sql( $status_column_name ) . ' NOT IN ( '
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped in get_excluded_order_statuses() function.
					. $excluded_order_status .
					' )
					AND datetimeid LIKE %s
					AND NOT (user_id = %s AND processed = 0)
					GROUP BY datetimeid',
					$ymd . '_%',
					$this->user_id
				),
				ARRAY_A
			);
		} else {
			$excluded_products = (array) Helpers::get_max_order_excluded_products();
			$excluded_products = implode( ', ', $excluded_products );

			$reserved_slots = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
						datetimeid, SUM(qty.meta_value) as count
					FROM 
						{$wpdb->prefix}jckwds wds, " . esc_sql( $orders_table_name ) . " o,
						{$wpdb->prefix}woocommerce_order_items oi, {$wpdb->prefix}woocommerce_order_itemmeta product, 
						{$wpdb->prefix}woocommerce_order_itemmeta qty 
					WHERE 
						wds.order_id = o.id 
						AND o.id = oi.order_id
						AND oi.order_item_id = product.order_item_id
						AND oi.order_item_id = qty.order_item_id
						AND qty.meta_key = '_qty'
						AND product.meta_key = '_product_id'
						AND datetimeid like %s
						AND NOT (wds.user_id = %s AND wds.processed = 0) 
						AND o." . esc_sql( $status_column_name ) . ' NOT IN ( '
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- already escaped in get_excluded_order_statuses() function.
						. $excluded_order_status . ' )
						AND product.meta_value NOT IN ( ' . esc_sql( $excluded_products ) . ' )
					GROUP BY datetimeid',
					$ymd . '_%',
					$this->user_id
				),
				ARRAY_A
			);

		}

		foreach ( $counts[ $ymd ] as $slot_id => $count ) {
			$timeslot = Helpers::search_array_by_key_value( 'id', $slot_id, $timeslots );

			if ( ! $timeslot ) {
				continue;
			}

			$counts[ $ymd ][ $slot_id ] = '' === trim( $timeslot['lockout'] ) ? true : absint( $timeslot['lockout'] );
		}

		if ( empty( $reserved_slots ) || is_wp_error( $reserved_slots ) ) {
			$counts[ $ymd ] = apply_filters( 'iconic_wds_slots_available_count', $counts[ $ymd ], $ymd, $timeslots );

			return $counts[ $ymd ];
		}

		foreach ( $reserved_slots as $reserved_slot ) {
			$slot_id = str_replace( $ymd . '_', '', $reserved_slot['datetimeid'] );

			if ( ! isset( $counts[ $ymd ][ $slot_id ] ) || true === $counts[ $ymd ][ $slot_id ] ) {
				continue;
			}

			$counts[ $ymd ][ $slot_id ] -= absint( $reserved_slot['count'] );
		}

		$counts[ $ymd ] = apply_filters( 'iconic_wds_slots_available_count', $counts[ $ymd ], $ymd, $timeslots );

		return $counts[ $ymd ];
	}

	/**
	 * Get order statuses which will not be considered as booked when calculating remining orders.
	 *
	 * @return array
	 */
	public static function get_excluded_order_statuses() {
		global $wpdb;

		/**
		 * List of excluded order statuses. The orders belonging to these status will not be counted
		 * when calculating remining orders.
		 *
		 * @since 1.25.0
		 */
		$statuses = apply_filters( 'iconic_wds_get_excluded_order_statuses', array( 'wc-refunded', 'wc-cancelled', 'wc-failed', 'wc-checkout-draft' ) );

		foreach ( $statuses as &$status ) {
			$status = $wpdb->prepare( '%s', $status );
		}

		return implode( ', ', $statuses );
	}

	/**
	 * Check if timeslots have a max orders limit.
	 *
	 * @param array $timeslots Timeslots.
	 *
	 * @return bool True If at least one timeslot has a max orders limit, false otherwise.
	 */
	public function timeslots_have_max_orders_limit( $timeslots ) {
		foreach ( $timeslots as $timeslot ) {
			if ( '' !== trim( $timeslot['lockout'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * ASAP slot data.
	 *
	 * @return array
	 */
	public function get_asap_slot_data() {
		$fee           = ! empty( $this->settings['timesettings_timesettings_asap_fee'] ) ? $this->settings['timesettings_timesettings_asap_fee'] : '0.00';
		$lockout       = isset( $this->settings['timesettings_timesettings_asap_lockout'] ) ? $this->settings['timesettings_timesettings_asap_lockout'] : '';
		$formatted_fee = wc_price( $fee );
		$label         = apply_filters( 'iconic_wds_asap_label', __( 'ASAP', 'jckwds' ) );

		return apply_filters(
			'iconic_wds_asap_slot_data',
			array(
				'id'                 => 'asap',
				'value'              => sprintf( 'asap|%01.2f', $fee ),
				'time_id'            => '00000000',
				'fee'                => array(
					'value'     => $fee,
					'formatted' => $formatted_fee,
				),
				'lockout'            => $lockout,
				'formatted'          => $label,
				'formatted_with_fee' => $fee > 0 ? sprintf( '%s (+%s)', $label, wp_strip_all_tags( $formatted_fee ) ) : $label,
				'days'               => array( 0, 1, 2, 3, 4, 5, 6 ),
				'timefrom'           => array(
					'time'     => '00:00',
					'stripped' => '0000',
				),
				'timeto'             => array(
					'time'     => '00:00',
					'stripped' => '0000',
				),
				'asap'               => true,
				'shipping_methods'   => array( 'any' ),
			)
		);
	}

	/**
	 * Helper: Format time
	 *
	 * Give a time id, format it according to the admin settings
	 *
	 * @param string $time_id      "Hi" format e.g. "0100" or "1430".
	 * @param string $start_format "Hi" by default - PHP time format.
	 * @param string $end_format   Defined in the admin settings - probably something like "H:i".
	 *
	 * @return string End formatted time.
	 */
	public function format_time( $time_id, $start_format = 'Hi', $end_format = false ) {
		$end_format = ( $end_format ) ? $end_format : $this->settings['timesettings_timesettings_setup_timeformat'];
		$time       = false;

		if ( $end_format ) {
			if ( 'Hi' === $start_format ) {
				$time_id = str_pad( $time_id, 4, '0', STR_PAD_LEFT );
			}

			$time = DateTime::createFromFormat( $start_format, $time_id, wp_timezone() );

			return wp_date( $end_format, $time->getTimestamp() );
		}

		return $time;
	}

	/**
	 * Helper: Get reserved slot data.
	 *
	 * @param int|false $order_id (optional) Order ID, if skipped it will fetch the reserved slot which isn't processed yet.
	 *
	 * @return bool|array
	 */
	public function get_reserved_slot( $order_id = false ) {
		global $wpdb;

		$this->remove_outdated_reservations();
		$slot_id = false;

		if ( $order_id ) {
			$slot_id = $wpdb->get_var(
				$wpdb->prepare( "SELECT datetimeid FROM {$wpdb->prefix}jckwds WHERE order_id = %d", $order_id )
			);
		} else {
			$slot_id = $wpdb->get_var(
				$wpdb->prepare( "SELECT datetimeid FROM {$wpdb->prefix}jckwds WHERE user_id = %s AND processed = '0'", $this->user_id )
			);
		}

		if ( null !== $slot_id ) {
			return $this->get_slot_data_from_id( $slot_id );
		} else {
			return false;
		}
	}

	/**
	 * Get slot data from ID.
	 *
	 * @param string $slot_id Slot ID e.g. Ymd_0.
	 *
	 * @return array
	 */
	public function get_slot_data_from_id( $slot_id ) {
		$slot_id_exploded = explode( '_', $slot_id );
		$date             = DateTime::createFromFormat( 'Ymd', $slot_id_exploded[0], wp_timezone() );

		$slot = array(
			'id'        => $slot_id,
			'date'      => array(
				'database'  => Helpers::convert_date_for_database( $slot_id_exploded[0] ),
				'formatted' => wp_date( Helpers::date_format(), $date->getTimestamp() ),
				'id'        => $date->format( 'Ymd' ),
				'ymd'       => $date->format( 'Ymd' ),
			),
			'time'      => isset( $slot_id_exploded[1] ) ? $this->get_timeslot_data( $slot_id_exploded[1] ) : false,
			'formatted' => ReservationTable::get_reserved_slot_formatted(),
		);

		return apply_filters( 'iconic_wds_slot_data', $slot );
	}

	/**
	 * Helper: Remove outdated pending slots
	 */
	public function remove_outdated_reservations() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}jckwds WHERE expires <= %d AND processed = 0", time() ) );
	}

	/**
	 * When shipping is updated, delete shipping method transient.
	 *
	 * @param string $transient  Transient.
	 * @param mixed  $value      Value.
	 * @param int    $expiration Expiration time.
	 */
	public static function on_update_shipping( $transient, $value, $expiration ) {
		if ( 'shipping-transient-version' !== $transient ) {
			return;
		}

		delete_transient( 'iconic-wds-shipping-methods' );
	}

	/**
	 * Helper: get shipping method options
	 *
	 * Also checks whether zones exist, as per the latest WooCommerce (2.6.0)
	 *
	 * @return array
	 */
	public function get_shipping_method_options() {
		if ( ! empty( $this->shipping_methods ) ) {
			return apply_filters( 'iconic_wds_shipping_method_options', $this->shipping_methods );
		}

		$transient_name         = 'iconic-wds-shipping-methods';
		$this->shipping_methods = get_transient( $transient_name );

		if ( false !== $this->shipping_methods ) {
			return apply_filters( 'iconic_wds_shipping_method_options', $this->shipping_methods );
		}

		$shipping_method_options = array(
			'any'     => __( 'Any shipping', 'jckwds' ),
			'any_virtual' => __( 'Virtual Product (No Shipping)', 'jckwds' ),
		);

		if ( class_exists( '\WC_Shipping_Zones' ) ) {
			$shipping_zones = $this->get_shipping_zones();

			if ( ! empty( $shipping_zones ) ) {
				foreach ( $shipping_zones as $shipping_zone ) {
					$methods = $shipping_zone->get_shipping_methods( true );

					if ( ! $methods ) {
						continue;
					}

					foreach ( $methods as $method ) {
						$zone_based_shipping_method = apply_filters( 'iconic_wds_zone_based_shipping_method', array(), $method, $shipping_zone );

						if ( ! empty( $zone_based_shipping_method ) ) {
							$shipping_method_options = $shipping_method_options + $zone_based_shipping_method;
							continue;
						}

						$title = empty( $method->title ) ? ucfirst( $method->id ) : $method->title;
						$class = str_replace( 'wc_shipping_', '', strtolower( get_class( $method ) ) );

						if ( 'table_rate' === $class ) {
							$trs_methods = $this->get_trs_methods_zones( $method, $class, $shipping_zone );

							$shipping_method_options = $shipping_method_options + $trs_methods;
						} elseif ( 'be_cart_based_shipping' === $class ) {
							$value = sprintf( 'cart_based_rate%d', $method->instance_id );

							$shipping_method_options[ $value ] = esc_html( sprintf( '%s: %s', $shipping_zone->get_zone_name(), $title ) );
						} else {
							$value = sprintf( '%s:%d', $class, $method->instance_id );

							$shipping_method_options[ $value ] = esc_html( sprintf( '%s: %s', $shipping_zone->get_zone_name(), $title ) );
						}
					}
				}
			}
		}

		$shipping_methods = WC()->shipping->load_shipping_methods();

		foreach ( $shipping_methods as $method ) {
			if ( ! $method->has_settings() ) {
				continue;
			}

			$standard_shipping_method = apply_filters( 'iconic_wds_standard_shipping_method', array(), $method );

			if ( ! empty( $standard_shipping_method ) ) {
				$shipping_method_options = $shipping_method_options + $standard_shipping_method;
				continue;
			}

			$title = empty( $method->method_title ) ? ucfirst( $method->id ) : $method->method_title;
			$class = get_class( $method );

			if ( 'Wafs_Free_Shipping_Method' === $class ) {
				$wafs_methods = $this->get_wafs_methods();

				$shipping_method_options = $shipping_method_options + $wafs_methods;
			} elseif ( 'BE_Table_Rate_Shipping' === $class ) {
				$trs_methods = $this->get_trs_methods();

				$shipping_method_options = $shipping_method_options + $trs_methods;
			} elseif ( 'WC_Shipping_WooShip' === $class ) {
				$wooship_methods = $this->get_wooship_methods();

				$shipping_method_options = $shipping_method_options + $wooship_methods;
			} elseif ( 'MH_Table_Rate_Plus_Shipping_Method' === $class ) {
				$table_rate_plus_methods = $this->get_table_rate_plus_methods( $method );

				$shipping_method_options = $shipping_method_options + $table_rate_plus_methods;
			} elseif ( 'WC_Distance_Rate_Shipping' === $class || 'WC_Collection_Delivery_Rate' === $class || 'WC_Special_Delivery_Rate' === $class ) {
				$distance_rate_shipping_methods = $this->get_distance_rate_shipping_methods( $method );

				$shipping_method_options = $shipping_method_options + $distance_rate_shipping_methods;
			} else {
				$shipping_method_options[ strtolower( $class ) ] = esc_html( $title );
			}
		}

		
		$shipping_method_options = WooCommerceLocalPickup::modify_shipping_method_options( $shipping_method_options );

		$this->shipping_methods = apply_filters( 'iconic_wds_shipping_method_options', $shipping_method_options );

		set_transient( $transient_name, $this->shipping_methods, 30 * DAY_IN_SECONDS );

		return $this->shipping_methods;
	}

	/**
	 * Helper: Get all shipping zones
	 *
	 * @return array
	 */
	public function get_shipping_zones() {
		$shipping_zones = WC_Shipping_Zones::get_zones();

		if ( $shipping_zones ) {
			foreach ( $shipping_zones as $index => $shipping_zone ) {
				$shipping_zones[ $index ] = new WC_Shipping_Zone( $shipping_zone['zone_id'] );
			}
		}

		$shipping_zones[] = new WC_Shipping_Zone( 0 );

		return $shipping_zones;
	}

	/**
	 * Helper: Get "WooCommerce Advanced Free Shipping" methods
	 *
	 * @return array
	 */
	public function get_wafs_methods() {
		$methods_array = array();
		$methods       = wafs_get_rates();

		if ( empty( $methods ) ) {
			return array();
		}

		foreach ( $methods as $method ) {
			$key = sprintf( '%d_advanced_free_shipping', $method->ID );

			$methods_array[ $key ] = sprintf( 'Advanced Free Shipping: %s', ! empty( $method->post_title ) ? $method->post_title : $method->ID );
		}

		return $methods_array;
	}

	/**
	 * Helper: Get "WooCommerce Table Rate Shipping" methods
	 *
	 * @return array
	 */
	public function get_trs_methods() {
		$methods_array = array();
		$table_rates   = array_filter( (array) get_option( 'woocommerce_table_rates' ) );

		if ( $table_rates && ! empty( $table_rates ) ) {
			foreach ( $table_rates as $table_rate ) {
				$methods_array[ sprintf( 'table_rate_shipping_%s', $table_rate['identifier'] ) ] = esc_html( $table_rate['title'] );
			}
		}

		return $methods_array;
	}

	/**
	 * Helper: Get "WooCommerce Table Rate Shipping" methods for Zone based shipping.
	 *
	 * @param Object $method        Shipping method.
	 * @param string $class         Name of the method's class.
	 * @param Object $shipping_zone Shipping zone.
	 *
	 * @retrun arr
	 * @since  1.7.1
	 */
	public function get_trs_methods_zones( $method, $class, $shipping_zone ) {
		$methods_array = array();
		$rates         = Compatibility\TableRateShipping::get_shipping_rates( $method );

		if ( ! $rates || empty( $rates ) ) {
			return $methods_array;
		}

		$title = ! empty( $method->title ) ? $method->title : ucfirst( $method->id );

		foreach ( $rates as $rate ) {
			$value = sprintf( '%s:%d', $class, $method->instance_id );

			if ( isset( $methods_array[ $value ] ) ) {
				continue;
			}

			$methods_array[ $value ] = esc_html( sprintf( '%s: %s', $shipping_zone->get_zone_name(), $title ) );
		}

		return $methods_array;
	}

	/**
	 * Helper: Get "WooShip" methods
	 *
	 * @return arr
	 */
	public function get_wooship_methods() {
		$methods_array = array();
		$wooship       = \WooShip::get_instance();

		if ( $wooship && ( ! empty( $wooship->config['shipping_methods'] ) && is_array( $wooship->config['shipping_methods'] ) ) ) {
			foreach ( $wooship->config['shipping_methods'] as $method_key => $method ) {
				$methods_array[ sprintf( 'wooship_%d', $method_key ) ] = esc_html( $method['title'] );
			}
		}

		return $methods_array;
	}

	/**
	 * Helper: Get "Table Rate Plus" methods
	 *
	 * @param MH_Table_Rate_Plus_Shipping_Method $method Shipping Method.
	 *
	 * @return arr
	 */
	public function get_table_rate_plus_methods( $method ) {
		$methods_array = array();
		$zones         = $method->zones;
		$services      = $method->services;
		$rates         = $method->table_rates;

		if ( $rates && ! empty( $rates ) ) {
			foreach ( $rates as $rate ) {
				$zone    = isset( $zones[ $rate['zone'] - 1 ]['name'] ) ? $zones[ $rate['zone'] - 1 ]['name'] : __( 'Everywhere Else', 'jckwds' );
				$service = $services[ $rate['service'] - 1 ]['name'];

				$title = sprintf( '%s: %s', $zone, $service );

				$methods_array[ sprintf( 'mh_wc_table_rate_plus_%d', $rate['id'] ) ] = esc_html( $title );
			}
		}

		return $methods_array;
	}

	/**
	 * Helper: Get Distance rate Shipping" methods
	 *
	 * @param WC_Distance_Rate_Shipping $method Shipping method.
	 *
	 * @return array
	 */
	public function get_distance_rate_shipping_methods( $method ) {
		$methods_array = array();

		if ( empty( $method->distance_rate_shipping_rates ) ) {
			return $methods_array;
		}

		$i = 1;
		foreach ( $method->distance_rate_shipping_rates as $rate ) {
			$value = sprintf( '%s:%d', $method->id, $i );

			if ( isset( $methods_array[ $value ] ) ) {
				continue;
			}

			$title = ! empty( $rate['title'] ) ? $rate['title'] : sprintf( '%s %d', __( 'Rule', 'jckwds' ), $i );

			$methods_array[ $value ] = esc_html( sprintf( '%s: %s', $method->method_title, $title ) );

			$i ++;
		}

		return $methods_array;
	}

	/**
	 * Helper: Extract timeslot id from option value
	 *
	 * In order to add fees, timeslot options at checkout have a |fee added to their values
	 * This functions let's us extract the timeslot id from that string
	 *
	 * @param string|bool $option_value Option value.
	 *
	 * @return bool|string|int
	 */
	public function extract_timeslot_id_from_option_value( $option_value = false ) {
		if ( ! $option_value ) {
			return false;
		}

		$option_value_exploded = explode( '|', $option_value );

		return $option_value_exploded[0];
	}

	/**
	 * Helper: Extract fee from option value
	 *
	 * As above, but for the fee
	 *
	 * @param string|bool $option_value Option value.
	 *
	 * @return string
	 */
	public function extract_fee_from_option_value( $option_value = false ) {
		if ( ! $option_value ) {
			return false;
		}

		$option_value_exploded = explode( '|', $option_value );
		$fee                   = ( isset( $option_value_exploded[1] ) ) ? (float) $option_value_exploded[1] : 0;

		return $fee;
	}

	/**
	 * Get Woo Version Number
	 *
	 * @return mixed bool/string NULL or Woo version number
	 */
	public function get_woo_version_number() {
		// If get_plugins() isn't available, require it.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Create the plugins folder and file variables.
		$plugin_folder = get_plugins( '/woocommerce' );
		$plugin_file   = 'woocommerce.php';

		// If the plugin version number is set, return it.
		if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			// Otherwise return null.
			return null;
		}
	}

	/**
	 * Get selected shipping method.
	 *
	 * @return string|null
	 */
	public static function get_chosen_shipping_method() {
		static $chosen_method = null;

		$chosen_method = filter_input(
			INPUT_POST,
			'selected_shipping_method',
			FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			array(
				'default' => null,
			)
		);

		if ( ! is_null( $chosen_method ) && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			/**
			 * Chosen shipping method.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'iconic_wds_chosen_method', strval( $chosen_method ) );
		}

		if ( wp_is_json_request() ) {
			$shipping_method = Helpers::get_chosen_shipping_method_cart_extension();
			if ( $shipping_method ) {
				// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
				return apply_filters( 'iconic_wds_chosen_method', $shipping_method );
			}
		}

		$order_type = self::get_current_order_type();

		if ( ! $order_type ) {
			return apply_filters( 'iconic_wds_chosen_method', $chosen_method );
		}

		$method        = sprintf( 'get_chosen_shipping_method_for_%s', $order_type );
		$chosen_method = call_user_func( array( __CLASS__, $method ) );

		if ( ( 'distance_rate_shipping' === $chosen_method || 'collection_delivery_shipping' === $chosen_method || 'special_delivery_shipping' === $chosen_method ) && function_exists( 'woocommerce_distance_rate_shipping_get_rule_number' ) ) {
			$rule_id       = woocommerce_distance_rate_shipping_get_rule_number( $chosen_method );
			$chosen_method = sprintf( '%s:%s', $chosen_method, $rule_id );
		}

		return apply_filters( 'iconic_wds_chosen_method', strval( $chosen_method ) );
	}

	/**
	 * Get current order type.
	 *
	 * @return bool|string
	 */
	public static function get_current_order_type() {
		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'update_order_review' === $wc_ajax ) {
			return 'update_order_review';
		}

		$wc_session = WC()->session;
		if ( ! empty( $wc_session ) ) {
			return 'session';
		}

		if ( ! is_admin() ) {
			return false;
		}

		// Gutenberg tries to render shortcode in the backend.
		// If it is admin but not shop_order post type then return false.
		global $pagenow;
		$post = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'post.php' === $pagenow && ! empty( $post ) && 'shop_order' !== get_post_type( $post ) ) {
			return false;
		}

		$selected_shipping_method = filter_input( INPUT_POST, 'selected_shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( $selected_shipping_method ) {
			return 'save_order';
		}

		return 'edit_order';
	}

	/**
	 * Get chosen shipping method for session.
	 *
	 * @return bool|string
	 */
	public static function get_chosen_shipping_method_for_session( $load_cart = false ) {
		if ( empty( WC()->session ) && $load_cart ) {
			wc_load_cart();
		}

		// If session is still empty, return null.
		if ( empty( WC()->session ) ) {
			return null;
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen_methods ) ) {
			return false;
		}

		return strval( $chosen_methods[0] );
	}

	/**
	 * Get chosen shipping method when editing order (admin).
	 *
	 * @return bool|string
	 */
	public static function get_chosen_shipping_method_for_edit_order() {
		$admin_order_id = (int) filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( $admin_order_id <= 0 ) {
			return false;
		}

		$order = wc_get_order( $admin_order_id );

		if ( empty( $order ) ) {
			return false;
		}

		return strval( Order::get_shipping_method_id( $order ) );
	}

	/**
	 * Get chosen shipping method when saving order (admin).
	 *
	 * @return bool|string
	 */
	public static function get_chosen_shipping_method_for_save_order() {
		$selected_shipping_method = filter_input( INPUT_POST, 'selected_shipping_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		return $selected_shipping_method ? strval( wc_clean( wp_unslash( $selected_shipping_method ) ) ) : false;
	}

	/**
	 * Get chosen shipping method for update order review.
	 *
	 * @return string|null
	 */
	public static function get_chosen_shipping_method_for_update_order_review() {
		$post_data       = filter_input( INPUT_POST, 'post_data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$post_data       = htmlspecialchars_decode($post_data);
		$post_data_array = array();
		parse_str( $post_data, $post_data_array );

		$allowed_methods = (array) WC()->session->get( 'shipping_for_package_0' );
		if ( ! empty( $allowed_methods['rates'] ) ) {
			$allowed_methods = array_keys( $allowed_methods['rates'] );
		}

		if ( empty( $post_data_array['shipping_method'] ) || ! in_array( $post_data_array['shipping_method'][0], $allowed_methods, true ) ) {
			return self::get_chosen_shipping_method_for_session();
		}

		return $post_data_array['shipping_method'][0];
	}

	/**
	 * Update order review fragments
	 *
	 * @param array $fragments Fragments.
	 *
	 * @return array
	 */
	public function update_order_review_fragments( $fragments ) {
		$dates_manager           = new Dates( array( 'shipping_method' => $this->get_chosen_shipping_method() ) );
		$allowed                 = $dates_manager->is_delivery_slots_allowed();
		$fragments['iconic_wds'] = array(
			'slots_allowed'          => $allowed,
			'chosen_shipping_method' => $this->get_chosen_shipping_method(),
			'labels'                 => Helpers::get_label(),
			'bookable_dates'         => $allowed ? $this->get_upcoming_bookable_dates( Helpers::date_format() ) : false,
		);

		return $fragments;
	}

	/**
	 * Do not mark the orders with excluded products/categories/shipping method as pending.
	 * This will ensure that reminder emails are not sent to those orders.
	 *
	 * @param bool     $pending Whether the order is delivery slot is pending.
	 * @param WC_Order $order   Order.
	 *
	 * @return bool
	 */
	public static function modify_is_delivery_slot_pending( $pending, $order ) {
		$dates_manager = new Dates( array( 'order_id' => $order->get_id() ) );

		if ( ! $dates_manager->is_delivery_slots_allowed() ) {
			return false;
		}

		return $pending;
	}

	/**
	 * On WooCommerce blocks loaded.
	 */
	public static function on_blocks_loaded() {
		CheckoutBlock::on_blocks_loaded();
		SubscriptionCheckoutBlock::on_blocks_loaded();
	}

	/**
	 * Declare HPOS compatiblity.
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * Run database update when plugin is activated.
	 */
	public static function activate() {
		Migrate::update();
	}
}

global $iconic_wds, $jckwds;

$iconic_wds = Iconic_WDS::instance();

// Backwards compatibility.
$jckwds = $iconic_wds;

register_activation_hook( __FILE__, array( $iconic_wds, 'activate' ) );
