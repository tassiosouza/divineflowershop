<?php
/**
 * Admin Orders
 *
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Admin_Orders.
 *
 * @class    Iconic_WSB_Admin_Orders
 * @version  1.16.2
 */
class Iconic_WSB_Admin_Orders {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'woocommerce_admin_order_totals_after_discount', array( __CLASS__, 'output_bought_together_discount' ), 10 );
	}

	/**
	 * Outputs the bought together discount.
	 *
	 * @param int $order_id The order ID.
	 */
	public static function output_bought_together_discount( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( empty( $order->get_items( 'fee' ) ) ) {
			return;
		}

		$bought_together_discount = 0;
		foreach ( $order->get_items( 'fee' ) as $item_fee ) {
			if ( 'Bought together discount' !== $item_fee->get_name() ) {
				continue;
			}

			if ( 0 < $item_fee->get_total() ) {
				continue;
			}

			$bought_together_discount -= $item_fee->get_total();
			break;
		}

		if ( empty( $bought_together_discount ) ) {
			return;
		}

		?>
			<tr>
				<td class="label">
					<?php echo esc_html( __( 'Bought together discount', 'iconic-wsb' ) ); ?>
				</td>
				<td width="1%"></td>
				<td class="total">
					- <?php echo wp_kses_post( wc_price( $bought_together_discount ) ); ?>
				</td>
			</tr>
		<?php
	}
}
