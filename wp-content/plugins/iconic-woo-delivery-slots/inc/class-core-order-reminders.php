<?php
/**
 * Setting related functions.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WDS_Core_Order_Reminders' ) ) {
	return;
}


/**
 * Iconic_WDS_Core_Order_Reminders.
 *
 * @class    Iconic_WDS_Core_Order_Reminders
 * @version  1.0.6
 */
class Iconic_WDS_Core_Order_Reminders {
	/**
	 * Single instance of the Iconic_WDS_Core_Order_Reminders object.
	 *
	 * @var Iconic_WDS_Core_Order_Reminders
	 */
	public static $single_instance = null;

	/**
	 * Meta where we keep the number of sent reminder mail count.
	 *
	 * @var int
	 */
	public static $meta_key_sent_reminder_count;

	/**
	 * Meta key where we store the flag to check if the order has a pending timeslot.
	 *
	 * @var string
	 */
	public static $meta_key_pending_timeslot;

	/**
	 * Meta key flag to mark the reminders as completed.
	 *
	 * @var [type]
	 */
	public static $meta_key_reminders_completed;

	/**
	 * Cron action key.
	 *
	 * @var string
	 */
	public static $cron_action;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Creates/returns the single instance Iconic_WDS_Core_Order_Reminders object.
	 *
	 * @param array $args Arguments. Required data is given below.
	 * - enabled:           Boolean.
	 * - reminder_duration: array( 'number' => 1, 'unit' => minutes|hours|days )
	 * - email_body:        Email body.
	 * - max_reminder:      Maximum number of reminders.
	 * - plugin_slug:       Plugin slug.
	 * - order_meta:        This class will check for the presence of this metadata in the order,
	 *                      will send reminders only if this meta is not present.
	 *
	 * @return Iconic_WDS_Core_Order_Reminders
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args = $args;

			self::$single_instance = new Iconic_WDS_Core_Order_Reminders();
			return self::$single_instance;
		}

		return self::$single_instance;
	}

	/**
	 * Construct.
	 */
	private function __construct() {
		self::$meta_key_sent_reminder_count = sprintf( '%s_reminder_count', self::$args['plugin_slug'] );
		self::$meta_key_pending_timeslot    = sprintf( '%s_pending_timeslot', self::$args['plugin_slug'] );
		self::$meta_key_reminders_completed = sprintf( '%s_reminders_completed', self::$args['plugin_slug'] );
		self::$cron_action                  = sprintf( '%s_send_reminders', self::$args['plugin_slug'] );

		$this->init();
	}

	/**
	 * Init.
	 */
	public function init() {
		if ( ! self::$args['enabled'] ) {
			return;
		}

		add_action( 'woocommerce_payment_complete', array( $this, 'after_order_created' ), 100 );
		add_action( 'woocommerce_thankyou', array( $this, 'after_order_created' ), 100 );
		add_action( self::$cron_action, array( $this, 'send_reminders' ) );

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'add_meta_args_to_wc_query' ), 10, 2 );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_my_account_order_actions' ), 10, 2 );
		add_action( self::$args['plugin_slug'] . '_reminder_opt_out', array( $this, 'opt_out' ) );

		if ( false === as_next_scheduled_action( self::$cron_action ) ) {
			$duration = $this->get_duration();
			if ( ! empty( $duration ) ) {
				as_schedule_single_action( $duration, self::$cron_action, array() );
			}
		}
	}

	/**
	 * Add Order action to My account > Orders page.
	 *
	 * @param array    $actions Actions.
	 * @param WC_Order $order   Order.
	 *
	 * @return array.
	 */
	public function add_my_account_order_actions( $actions, $order ) {
		if ( ! $this->show_edit_timeslot_link( $order ) ) {
			return $actions;
		}

		$delivery_type = $this->get_shipping_type( $order );
		$delivery      = 'collection' === $delivery_type ? esc_html__( 'Collection', 'jckwds' ) : esc_html__( 'Delivery', 'jckwds' );

		$actions['select_timeslot'] = array(
			// Translators: Delivery type i.e. 'delivery' or 'collection'.
			'name' => sprintf( esc_html__( 'Select %s Date', 'jckwds' ), esc_html( $delivery ) ),
			'url'  => $order->get_view_order_url() . '#' . sprintf( '%s-edit-timeslot', self::$args['plugin_slug'] ),
		);

		return $actions;
	}

	/**
	 * Opt out of reminder for this order.
	 *
	 * @param WC_Order|int $order Order.
	 *
	 * @return bool
	 */
	public function opt_out( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( empty( $order ) ) {
			return false;
		}

		$order->delete_meta_data( self::$meta_key_pending_timeslot );
		$order->save();

		return true;
	}


	/**
	 * Get duration.
	 *
	 * @param bool $return_diff Return difference.
	 *
	 * @return float
	 */
	public function get_duration( $return_diff = false ) {
		if (
			empty( self::$args['reminder_duration'] ) ||
			empty( self::$args['reminder_duration']['number'] ) ||
			empty( self::$args['reminder_duration']['unit'] )
			) {
			return false;
		}

		$duration = floatval( self::$args['reminder_duration']['number'] );
		$unit     = self::$args['reminder_duration']['unit'];

		// unit is minutes, hours, days.
		if ( 'minutes' === $unit ) {
			$duration = $duration * 60;
		} elseif ( 'hours' === $unit ) {
			$duration = $duration * 60 * 60;
		} elseif ( 'days' === $unit ) {
			$duration = $duration * 60 * 60 * 24;
		}

		if ( $return_diff ) {
			return $duration;
		}

		return time() + $duration;
	}

	/**
	 * Add meta_query argument to WC_Query.
	 * WC_Query doesn't directly accepts 'meta_query' argument.
	 *
	 * @param Object $query      Query.
	 * @param array  $query_vars Array.
	 *
	 * @return Object.
	 */
	public function add_meta_args_to_wc_query( $query, $query_vars ) {
		if ( empty( $query_vars[ self::$args['plugin_slug'] . '_wcquery' ] ) ) {
			return $query;
		}

		$query['meta_query'] = $query_vars['meta_query'];

		return $query;
	}

	/**
	 * Send reminders.
	 */
	public function send_reminders() {
		if ( ! self::$args['enabled'] ) {
			return;
		}

		$duration = $this->get_duration( true );
		if ( false === $duration ) {
			$duration = 0;
		}

		$max_reminder = intval( self::$args['max_reminder'] ) ? intval( self::$args['max_reminder'] ) : 3;

		$args = array(
			'status'                                => 'on-hold',
			'date_created'                          => '<' . ( time() - $duration ),
			self::$args['plugin_slug'] . '_wcquery' => true,
			'meta_query'                            => array(
				'relation'       => 'AND',
				'pending_order'  => array(
					'key'     => self::$meta_key_pending_timeslot,
					'value'   => 'true',
					'compare' => '=',
				),
				'reminder_count' => array(
					'key'     => self::$meta_key_sent_reminder_count,
					'value'   => $max_reminder,
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
			),
		);

		$orders = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			$to = $order->get_billing_email();
			// Translators: Order number.
			$subject = sprintf( esc_html__( 'Delivery Slot Reminder (Order #%d)', 'jckwds' ), $order->get_order_number() );
			$body    = self::$args['email_body'];

			$title = sprintf( 'Reminder to enter a timeslot for your order #%d', $order->get_id() );

			$title = $this->replace_placeholders( $title, $order );
			$body  = $this->replace_placeholders( $body, $order );
			$this->send_email( $to, $subject, $title, $body );

			// Increment the reminder count.
			$reminder_count = intval( $order->get_meta( self::$meta_key_sent_reminder_count ) );
			$order->update_meta_data( self::$meta_key_sent_reminder_count, ++$reminder_count );

			if ( $max_reminder <= $reminder_count ) {
				$order->update_meta_data( self::$meta_key_sent_reminder_count, 'complete' );
				$order->delete_meta_data( self::$meta_key_pending_timeslot );
			}

			$order->save();
		}
	}


	/**
	 * Check if data/time data exists.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public function after_order_created( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $this->is_delivery_date_pending( $order ) ) {
			return;
		}

		$order->update_status( 'wc-on-hold' );
		$order->update_meta_data( self::$meta_key_pending_timeslot, 'true' );
		$order->update_meta_data( self::$meta_key_sent_reminder_count, 0 );
		$order->save();
	}

	/**
	 * Is it a wc_stripe_create_order request?
	 *
	 * @return bool
	 */
	public static function is_stripe_creating_order() {
		$wc_ajax = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return 'wc_stripe_create_order' === $wc_ajax;
	}

	/**
	 * Send email using the WooCommerce template.
	 *
	 * @param string $to            Receiver's email.
	 * @param string $email_subject Email subject.
	 * @param string $body_heading  Title which appears at the top in large fonts.
	 * @param string $body_message  Email text.
	 *
	 * @return bool
	 */
	public function send_email( $to, $email_subject, $body_heading, $body_message ) {
		$mailer  = WC()->mailer();
		$message = $mailer->wrap_message( $body_heading, $body_message );
		return $mailer->send( $to, wp_strip_all_tags( $email_subject ), $message );
	}

	/**
	 * Replace placeholders.
	 *
	 * @param string   $string  The subject in which we want to replace placeholders.
	 * @param WC_Order $order   Order.
	 *
	 * @return string
	 */
	public function replace_placeholders( $string, $order ) {
		$string = str_replace( '{ORDER_ID}', $order->get_id(), $string );
		$string = str_replace( '{SITE_NAME}', get_bloginfo( 'name' ), $string );
		$string = str_replace( '{ORDER_NUMBER}', $order->get_order_number(), $string );
		$string = str_replace( '{ORDER_DATE_TIME}', $order->get_date_created()->format( 'Y-m-d H:i:s' ), $string );
		$string = str_replace( '{CUSTOMER_NAME}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $string );
		$string = str_replace( '{CUSTOMER_EMAIL}', $order->get_billing_email(), $string );
		$string = str_replace( '{CUSTOMER_ADDRESS}', wp_strip_all_tags( str_replace( '<br/>', "\n", $order->get_formatted_billing_address() ) ), $string );
		$string = str_replace( '{CUSTOMER_PHONE}', $order->get_billing_phone(), $string );
		$string = str_replace( '{NOTE}', $order->get_customer_note(), $string );
		$string = str_replace( '{THANKYOU_URL}', $this->get_order_received_url( $order ), $string );

		if ( false !== strpos( $string, '{CART_ITEMS}' ) ) {
			$cart_items = '';

			foreach ( $order->get_items() as $item ) {
				$cart_items .= $item['name'] . ' x ' . $item['qty'] . ', ';
			}

			$cart_items = trim( $cart_items, ', ' );

			$string = str_replace( '{CART_ITEMS}', $cart_items, $string );
		}

		return $string;
	}

	/**
	 * Get order received URL.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public function get_order_received_url( $order ) {
		$url = $order->get_checkout_order_received_url();
		$url = add_query_arg( 'reminder', 1, $url );

		return $url;
	}

	/**
	 * Is delivery date pending.
	 *
	 * @param WC_Order|int $order WC_Order|Order ID.
	 *
	 * @return bool
	 */
	public function is_delivery_date_pending( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$pending = empty( $order->get_meta( self::$args['order_meta'] ) );

		/**
		 * Filter to update pending status for an order when it's created.
		 *
		 * @param bool $pending Pending status.
		 *
		 * @since 2.3.0
		 */
		return apply_filters( self::$args['plugin_slug'] . '_is_delivery_slot_pending', $pending, $order );
	}

	/**
	 * Get shipping type.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $default Default.
	 *
	 * @return string delivery|collection.
	 */
	public static function get_shipping_type( $order, $default = 'delivery' ) {
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) || ! is_array( $shipping_methods ) ) {
			return $default;
		}

		$method = reset( $shipping_methods );

		if ( empty( $method ) || ! is_a( $method, 'WC_Order_Item_Shipping' ) ) {
			return $default;
		}

		return 'local_pickup' === $method->get_method_id() ? 'collection' : 'delivery';
	}

	/**
	 * Show edit timeslot link.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function show_edit_timeslot_link( $order ) {
		/**
		 * Filter to show/hide the edit timeslot link on the my account page.
		 *
		 * @since 1.25.0
		 */
		return apply_filters( self::$args['plugin_slug'] . '_my_account_show_edit_timeslot_button', $this->is_delivery_date_pending( $order ), $order );
	}
}
