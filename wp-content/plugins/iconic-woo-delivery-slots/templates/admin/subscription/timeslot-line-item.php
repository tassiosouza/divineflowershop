<?php
/**
 * Admin: Subscription timeslot line item.
 *
 * @package Iconic_WDS\Templates\Admin\Subscription
 */

?>
<tbody>
	<tr class='item wds-timeslot-lineitem <?php echo esc_attr( $updated_class ); ?>'>
		<td class='thumb'>
			<?php require trailingslashit( ICONIC_WDS_PATH ) . 'assets/img/admin-popup-delivery.svg'; ?>
		</td>
		<td class='name' colspan='<?php echo esc_attr( $col_span ); ?>'>
			<div class="wds-timeslot-lineitem__row">
				<div class="wds-timeslot-lineitem__badge"><?php echo esc_html_x( 'Subscription Delivery Slot', 'admin', 'jckwds' ); ?></div>
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
	</tr>
</tbody>
