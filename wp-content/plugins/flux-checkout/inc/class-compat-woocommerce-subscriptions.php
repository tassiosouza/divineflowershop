<?php
/**
 * Iconic_Flux_Compat_WooCommerce_Subscriptions.
 *
 * Compatibility with WooCommerce Subscriptions.
 * [https://woocommerce.com/products/woocommerce-subscriptions/]
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_WooCommerce_Subscriptions' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_WooCommerce_Subscriptions.
 *
 * @class    Iconic_Flux_Compat_WooCommerce_Subscriptions.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_WooCommerce_Subscriptions {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Run on init hook.
	 */
	public static function init() {
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return;
		}

		remove_filter( 'wcs_cart_totals_order_total_html', 'wcs_add_cart_first_renewal_payment_date', 10 );
		add_filter( 'wcs_cart_totals_order_total_html', array( __CLASS__, 'add_cart_first_renewal_payment_date' ), 10, 2 );

		// If custom thank you apge is enabled.
		if ( '1' === Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] ) {
			remove_action( 'woocommerce_thankyou', array( 'WC_Subscriptions_Order', 'subscription_thank_you' ) );
			add_action( 'flux_thankyou_after_content', array( __CLASS__, 'subscription_thank_you' ) );
			add_action( 'flux_thankyou_after_customer_details', array( __CLASS__, 'related_subscriptions' ) );
		}
	}

	/**
	 * Subscription thankyou.
	 *
	 * @param WC_Order $order order.
	 *
	 * @return void
	 */
	public static function subscription_thank_you( $order ) {
		if ( ! class_exists( 'WC_Subscriptions_Order' ) ) {
			return;
		}

		WC_Subscriptions_Order::subscription_thank_you( $order->get_id() );
	}

	/**
	 * Add related subscriptions to the thankyou page.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function related_subscriptions( $order ) {
		if ( ! class_exists( 'WC_Subscriptions_Order' ) ) {
			return;
		}

		$subscriptions = wcs_get_subscriptions_for_order( $order->get_id(), array( 'order_type' => 'any' ) );

		if ( empty( $subscriptions ) ) {
			return;
		}
		?>
		<div class="flux-ty-content flux-ty-subscriptions flux-ty-box flux-ty-box--subscriptions">
			<?php WC_Subscriptions_Order::add_subscriptions_to_view_order_templates( $order->get_id() ); ?>
		</div>
		<?php
	}

	/**
	 * Append the first renewal payment date to a string (which is the order total HTML string by default).
	 *
	 * @param string $order_total_html Order total HTML.
	 * @param mixed  $cart             Cart.
	 *
	 * @return string
	 */
	public static function add_cart_first_renewal_payment_date( $order_total_html, $cart ) {
		if ( 0 !== $cart->next_payment_date ) {
			$first_renewal_date = date_i18n( wc_date_format(), wcs_date_to_time( get_date_from_gmt( $cart->next_payment_date ) ) );
			// Translators: placeholder is a date.
			$order_total_html .= '<div class="first-payment-date"><small>' . __( 'First renewal', 'flux-checkout' ) . '<br />' . $first_renewal_date . '</small></div>';
		}

		return $order_total_html;
	}
}
