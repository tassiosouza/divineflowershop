<?php
/**
 * Timeslot line item in the Admin's Orders page.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Subscriptions\Boot;

$badge_text = Boot::does_order_have_subscription_product( $order ) ? __( 'One Time Delivery Slot', 'jckwds' ) : __( 'Delivery Slot', 'jckwds' );

?>
<tbody>
	<tr class='item wds-timeslot-lineitem <?php echo esc_attr( $updated_class ); ?>'>
		<td class='thumb'>
			<?php require trailingslashit( ICONIC_WDS_PATH ) . 'assets/img/admin-popup-delivery.svg'; ?>
		</td>
		<td class='name' colspan='<?php echo esc_attr( $col_span ); ?>'>
			<div class="wds-timeslot-lineitem__row">
				<div class="wds-timeslot-lineitem__badge"><?php echo esc_html( $badge_text ); ?></div>
				<div class="wds-timeslot-lineitem__timeslot">
					<div class="wds-timeslot-lineitem__timeslot-datetime"><?php echo esc_html( $date_time ); ?></div>
					<?php
					if ( $shipping_method_name ) {
						?>
						<div class="wds-timeslot-lineitem__timeslot-shipping"><?php echo esc_html( $shipping_method_name ); ?></div>
						<?php
					}
					?>
				</div>
			</div>
		</td>
		<td class="wc-order-edit-line-item" width="1%">
			<div class="wc-order-edit-line-item-actions">
				<a class="wds-edit-order-item" href="#" aria-label="<?php echo esc_attr( __( 'Edit Timeslot', 'jckws' ) ); ?>"></a>
				<a class="wds-delete-order-item" href="#" aria-label="<?php echo esc_attr( __( 'Delete Timeslot', 'jckws' ) ); ?>"></a>
			</div>
		</td>
	</tr>
</tbody>
