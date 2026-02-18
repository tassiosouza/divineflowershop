<?php
/**
 * Notice to be shown when in the admin when timeslot for an order is updated.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Helpers;

?>
<tbody class='wds-timeslot-changed-notice-body'>
	<tr>
		<td colspan='<?php echo esc_attr( $col_span + 4 ); ?>'>
			<div class="wds-timeslot-changed-notice">
				<div class="wds-timeslot-changed-notice__text">
					<div class="iconic-wds-notice">
						<?php echo esc_html( $message ); ?>
					</div>
				</div>
				<div class="wds-timeslot-changed-notice__view">
					<?php
					// If sub orders are empty then its the parent order.
					if ( empty( $sub_orders ) ) {
						// Translators: order list.
						echo sprintf( esc_html__( 'View Parent Order %s', 'jckwds' ), wp_kses( $orders_list, Helpers::get_kses_allowed_tags() ) );
					} else {
						// Translators: order list.
						echo sprintf( esc_html__( 'View Child Order %s', 'jckwds' ), wp_kses( $orders_list, Helpers::get_kses_allowed_tags() ) );
					}
					?>
				</div>
			</div>
		</td>
	</tr>
</tbody>
