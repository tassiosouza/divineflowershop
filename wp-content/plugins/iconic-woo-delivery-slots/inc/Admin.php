<?php
/**
 * All functions related to the admin side.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use WC_Order_Item_Fee;

/**
 * Admin related functions.
 */
class Admin {

	/**
	 * Run.
	 *
	 * @return void
	 */
	public static function run() {
		add_action( 'woocommerce_order_item_add_line_buttons', array( __CLASS__, 'add_line_button' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'popup' ) );
		add_action( 'woocommerce_admin_order_items_after_line_items', array( __CLASS__, 'display_timeslot_line_item' ) );
		add_action( 'admin_menu_jckwds', array( __CLASS__, 'setup_deliveries_page' ) );

		add_filter( 'is_protected_meta', array( __CLASS__, 'modify_protected_meta_keys' ), 10, 3 );
	}

	/**
	 * Add line button.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function add_line_button( $order ) {
		$data        = Order::get_order_date_time( $order );
		$button_text = $data ? __( 'Edit Delivery Slot', 'jckwds' ) : __( 'Add Delivery Slot', 'jckwds' );
		?>
		<button type="button" id="iconic_wds_add_timeslot_btn" class="button"><?php echo esc_html( $button_text ); ?></button>
		<?php
	}

	/**
	 * Include popup template.
	 */
	public static function popup() {
		global $post;

		$current_screen = get_current_screen();
		$order          = wc_get_order( $post );

		if ( Helpers::is_cot_enabled() ) {
			$id    = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$order = wc_get_order( $id );
		} else {
			$order = wc_get_order( $post );
		}

		if ( 'shop_order' !== $current_screen->id && 'woocommerce_page_wc-orders' !== $current_screen->id ) {
			return;
		}

		include ICONIC_WDS_PATH . '/templates/admin/popup.php';
	}

	/**
	 * Admin scripts.
	 */
	public static function admin_scripts() {
		$screen = get_current_screen();

		if ( empty( $screen->id ) || ! in_array( $screen->id, array( 'shop_order', 'woocommerce_page_jckwds-settings', 'product', 'edit-product_cat', 'woocommerce_page_wc-settings', 'woocommerce_page_wc-orders' ), true ) ) {
			return;
		}

		global $iconic_wds, $iconic_wds_dates;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$iconic_wds->load_file( 'vuejs', '/assets/vendor/vue' . $suffix . '.js', true );
		$iconic_wds->load_file( 'iconic-wds-script', '/assets/admin/js/main' . $suffix . '.js', true, array( 'jquery-ui-datepicker', 'wp-hooks' ), true );

		$iconic_wds->load_file( 'iconic-wds-vcalendar', '/assets/vendor/v-calendar.umd.js', true, array( 'vuejs' ), true );

		if ( ! in_array( $screen->id, array( 'shop_order', 'woocommerce_page_wc-orders', 'woocommerce_page_jckwds-settings' ), true ) ) {
			return;
		}

		$post_id  = (int) filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$id       = (int) filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$order_id = ! empty( $post_id ) ? $post_id : $id;

		$script_vars = array(
			'bookable_dates' => $iconic_wds_dates->get_upcoming_bookable_dates( Helpers::date_format() ),
			'settings'       => $iconic_wds->settings,
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'     => wp_create_nonce( $iconic_wds::$slug ),
			'timeslot'       => $iconic_wds->get_timeslot_data(),
			'order_id'       => $order_id,
			'currency'       => array(
				'precision' => 2,
				'symbol'    => get_woocommerce_currency_symbol(),
				'decimal'   => esc_attr( wc_get_price_decimal_separator() ),
				'thousand'  => esc_attr( wc_get_price_thousand_separator() ),
				'format'    => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			),
			'strings'        => array(
				'selectslot'                    => Helpers::get_label( 'select_time_slot' ),
				'noslots'                       => Helpers::get_label( 'no_time_slots' ),
				'delete_slot'                   => esc_html__( 'Are you sure you want to delete the timeslot?', 'jckwds' ),
				'no_shiping_methods'            => esc_html__( 'No shipping methods are available for your address', 'jckwds' ),
				'select_timeslot_specific_date' => esc_html__( 'Please select a time slot for this specific date from Time Slot Settings', 'jckwds' ),
				'reminder_setting_disabled'     => esc_html__( 'Allow customers to edit timeslots from the Order Received page to enable reminders.', 'jckwds' ),
				'days'                          => array(
					__( 'Sunday', 'jckwds' ),
					__( 'Monday', 'jckwds' ),
					__( 'Tuesday', 'jckwds' ),
					__( 'Wednesday', 'jckwds' ),
					__( 'Thursday', 'jckwds' ),
					__( 'Friday', 'jckwds' ),
					__( 'Saturday', 'jckwds' ),
				),
				'days_short'                    => array(
					__( 'Su', 'jckwds' ),
					__( 'Mo', 'jckwds' ),
					__( 'Tu', 'jckwds' ),
					__( 'We', 'jckwds' ),
					__( 'Th', 'jckwds' ),
					__( 'Fr', 'jckwds' ),
					__( 'Sa', 'jckwds' ),
				),
				'months'                        => array(
					__( 'January', 'jckwds' ),
					__( 'February', 'jckwds' ),
					__( 'March', 'jckwds' ),
					__( 'April', 'jckwds' ),
					__( 'May', 'jckwds' ),
					__( 'June', 'jckwds' ),
					__( 'July', 'jckwds' ),
					__( 'August', 'jckwds' ),
					__( 'September', 'jckwds' ),
					__( 'October', 'jckwds' ),
					__( 'November', 'jckwds' ),
					__( 'December', 'jckwds' ),
				),
				'months_short'                  => array(
					__( 'Jan', 'jckwds' ),
					__( 'Feb', 'jckwds' ),
					__( 'Mar', 'jckwds' ),
					__( 'Apr', 'jckwds' ),
					__( 'May', 'jckwds' ),
					__( 'Jun', 'jckwds' ),
					__( 'Jul', 'jckwds' ),
					__( 'Aug', 'jckwds' ),
					__( 'Sep', 'jckwds' ),
					__( 'Oct', 'jckwds' ),
					__( 'Nov', 'jckwds' ),
					__( 'Dec', 'jckwds' ),
				),
			),
		);

		if ( in_array( $screen->id, array( 'shop_order', 'woocommerce_page_wc-orders' ), true ) && $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order ) {
				$script_vars['order_meta'] = array(
					'date'            => $order->get_meta( $iconic_wds->date_meta_key ),
					'timeslot'        => $order->get_meta( $iconic_wds->timeslot_meta_key ),
					'timestamp'       => $order->get_meta( $iconic_wds->timestamp_meta_key ),
					'shipping_method' => $order->get_meta( $iconic_wds::$slug . '_shipping_method' ),
					'override_rules'  => $order->get_meta( $iconic_wds::$slug . '_override_rules' ),
				);
			}
		}

		wp_localize_script( 'iconic-wds-script', 'iconic_wds_vars', $script_vars );
	}

	/**
	 * Admin styles.
	 */
	public static function admin_styles() {
		global $iconic_wds;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'wc-admin-layout' );
		$iconic_wds->load_file( 'iconic-wds-style', '/assets/admin/css/main' . $suffix . '.css', false, array( 'jquery-ui-datepicker' ), true );
	}

	/**
	 * Display line item in the edit order page.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public static function display_timeslot_line_item( $order_id ) {
		global $iconic_wds;

		$order = wc_get_order( $order_id );

		$data = Order::get_order_date_time( $order );

		if ( empty( $data ) ) {
			return;
		}

		$date                 = $data['date'];
		$time                 = $data['time'];
		$shipping_method      = $data['shipping_method'];
		$date_time            = $time ? $date . ' @ ' . $time : $date;
		$shipping_methods     = $iconic_wds->get_shipping_method_options();
		$shipping_method_name = isset( $shipping_methods[ $shipping_method ] ) ? $shipping_methods[ $shipping_method ] : '';
		$order_taxes_count    = count( $order->get_taxes() );
		$col_span             = 4 + $order_taxes_count;

		if ( $date ) {
			$updated_class = '';
			$sub_orders    = EditTimeslots::get_child_orders( $order );
			$parent_order  = EditTimeslots::is_sub_order( $order );

			if ( ! empty( $sub_orders ) || ! empty( $parent_order ) ) {
				$update_orders = empty( $sub_orders ) ? $parent_order : $sub_orders;
				$orders_list   = self::get_related_orders_list( $order );
				$message       = empty( $sub_orders ) ?
								__( 'Note: Delivery slot was updated. Customer was charged fees for the difference.', 'jckwds' ) :
								__( 'Note: Delivery slot was changed.', 'jckwds' );

				if ( ! empty( $orders_list ) ) {
					$updated_class = 'wds-timeslot-lineitem--timeslot-updated';
					include trailingslashit( ICONIC_WDS_PATH ) . 'templates/admin/timeslot-changed-notice.php';
				}
			}

			include trailingslashit( ICONIC_WDS_PATH ) . 'templates/admin/timeslot-line-item.php';
		}
	}

	/**
	 * Save fees to the given Order.
	 *
	 * @param float    $fees Fees.
	 * @param WC_Order $order Order.
	 *
	 * @return $order
	 */
	public static function save_fee_to_order( $fees, $order ) {
		// Save/update fees.
		global $iconic_wds;

		$existing_item = Helpers::get_delivery_fees_line_item( $order );
		$fee_line_item = false;

		if ( empty( $fees ) || 'NaN' === $fees ) {
			if ( $existing_item ) {
				$item_id = $existing_item->get_id();
				if ( $item_id ) {
					wc_delete_order_item( $item_id );
				}
			}

			// Need to reinitiate the Order object so it doesn't have the cached data.
			// Cached $order would keep displaying the deleted Fees line item.
			return wc_get_order( $order->get_id() );
		}

		if ( ! $existing_item ) {
			$fee_line_item = new WC_Order_Item_Fee();
			$fee_line_item->set_name( $iconic_wds->fee->get_fee_name() );
		} else {
			$fee_line_item = $existing_item;
		}

		$fee_line_item->set_amount( $fees );
		$fee_line_item->set_total( $fees );

		if ( $existing_item ) {
			$fee_line_item->save();
		} else {
			$order->add_item( $fee_line_item );
		}

		return $order;
	}

	/**
	 * Add WDS meta keys to the list of protected meta keys, so they cannot be directly modified by the user.
	 * It is important to note that we cannot add an underscore to the meta keys, as they have been in the non-underscore format
	 * for a long time, and we do not wish to make any changes that may impact existing users.
	 *
	 * @param array  $protected List of protected meta keys.
	 * @param string $meta_key  Meta key.
	 * @param string $meta_type Meta type.
	 *
	 * @return array
	 */
	public static function modify_protected_meta_keys( $protected, $meta_key, $meta_type ) {
		global $current_screen;

		if ( empty( $current_screen ) || ! in_array( $current_screen->id, array( 'shop_order', 'woocommerce_page_wc-orders' ), true ) ) {
			return $protected;
		}

		$wds_meta_keys = array( 'jckwds_timeslot', 'jckwds_date', 'jckwds_date_ymd', 'jckwds_timestamp', 'jckwds_shipping_method', 'jckwds_timeslot_id' );

		if ( in_array( $meta_key, $wds_meta_keys, true ) ) {
			return true;
		}

		return $protected;
	}

	/**
	 * Admin: Setup Deliveries page.
	 */
	public static function setup_deliveries_page() {
		$deliveries_page = add_submenu_page(
			'woocommerce',
			__( 'Deliveries', 'jckwds' ),
			sprintf( '<span class="iconic-wds-submenu">%s</span>', __( 'Deliveries', 'jckwds' ) ),
			'manage_woocommerce',
			Iconic_WDS::$slug . '-deliveries',
			array(
				__CLASS__,
				'deliveries_page_display',
			)
		);

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( $page && Iconic_WDS::$slug . '-deliveries' === $page ) {
			// Woo styles.
			wp_enqueue_style( 'admin_enqueue_styles-' . $deliveries_page, WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

			// Woo scripts register.
			wp_register_script(
				'woocommerce_admin',
				WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.min.js',
				array(
					'jquery',
					'jquery-blockui',
					'jquery-ui-sortable',
					'jquery-ui-widget',
					'jquery-ui-core',
					'jquery-tiptip',
				),
				WC_VERSION,
				true
			);
			wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );

			// Woo scripts enqueue.
			wp_enqueue_script( 'jquery-tiptip' );
			wp_enqueue_script( 'woocommerce_admin' );
		}
	}

	/**
	 * Admin: Display Deliveries page
	 */
	public static function deliveries_page_display() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jckwds' ) );
		}

		require_once ICONIC_WDS_PATH . 'inc/admin/deliveries.php';
	}

	/**
	 * Helper: Display reservations in a table
	 *
	 * @param array $reservations Array of reservations.
	 */
	public static function reservations_layout( $reservations ) {
		include ICONIC_WDS_PATH . 'templates/admin/deliveries-page.php';
	}

	/**
	 * Get list of related orders in HTML format.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string|false
	 */
	public static function get_related_orders_list( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		$sub_orders   = EditTimeslots::get_child_orders( $order );
		$parent_order = EditTimeslots::is_sub_order( $order );
		$orders_list  = '';

		if ( empty( $sub_orders ) && empty( $parent_order ) ) {
			return;
		}

		if ( ! empty( $sub_orders ) ) {
			foreach ( $sub_orders as $_order_id ) {
				$_order = wc_get_order( $_order_id );

				if ( empty( $_order ) ) {
					continue;
				}

				$orders_list .= sprintf( '<a href="%s">#%s</a> ', esc_url( $_order->get_edit_order_url() ), $_order_id );
			}
		} else {
			$_order = wc_get_order( $parent_order );

			if ( ! empty( $_order ) ) {
				$orders_list = sprintf( '<a href="%s">#%s</a> ', esc_url( $_order->get_edit_order_url() ), $_order->get_id() );
			}
		}

		return $orders_list;
	}


	/**
	 * Get order id from param.
	 *
	 * @return int Order ID
	 */
	public static function get_order_id_from_param() {
		$is_hpos = Helpers::is_cot_enabled();
		return $is_hpos ? filter_input( INPUT_GET, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}
}
