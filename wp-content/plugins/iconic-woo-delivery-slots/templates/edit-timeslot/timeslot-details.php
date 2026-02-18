<?php
/**
 * Edit timeslot post checkout.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Subscriptions\Boot;

$add_btn_text       = $has_timeslot_data ? __( 'Change', 'jckwds' ) : __( '+ Add Time Slot', 'jckwds' );
$delivery_date_text = $has_timeslot_data ? __( 'Delivery Date & Time', 'jckwds' ) : __( 'Delivery Date', 'jckwds' );
$slot               = empty( $has_timeslot_data ) ? __( 'Not Selected', 'jckwds' ) : sprintf( '%s %s', $has_timeslot_data['date'], $has_timeslot_data['time'] );
$classes            = array();

if ( is_wc_endpoint_url( 'order-received' ) ) {
	$classes[] = 'wds-edit-slot--thankyou';
}

if ( empty( $has_timeslot_data ) ) {
	$classes[] = 'wds-edit-slot--empty';
}

if ( ! Boot::does_order_have_regular_product( $order ) ) {
	return;
}

?>
<div class="wds-edit-slot <?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="iconic_wds-edit-timeslot">
	<h2 class="wds-edit-slot__heading"><?php esc_html_e( 'Delivery details', 'jckwds' ); ?></h2>

	<div class="wds-edit-slot__wrap">
		<div class="wds-edit-slot__details">
			<strong class='wds-edit-slot__details-heading'>
				<?php
					echo '1' === $iconic_wds->settings['timesettings_timesettings_setup_enable'] ? esc_html__( 'Delivery Date & Time', 'jckwds' ) : esc_html__( 'Delivery Date', 'jckwds' );
				?>
			</strong>
			<span class='wds-edit-slot__details-timeslot'><?php echo esc_html( $slot ); ?></span>
		</div>
		<div class="wds-edit-slot__action">
			<?php
			if ( $show_update_btn ) {
				?>
				<a class='wds-edit-slot__action-btn' href="#"><?php echo esc_html( $add_btn_text ); ?></a>
				<?php
			}
			?>
		</div>
		<input type="hidden" name='wds_order_id' value='<?php echo esc_attr( $order_id ); ?>'>
	</div>

	<div class="wds-edit-slot-popup wds-edit-slot-popup--hidden">
		<div class="wds-edit-slot-popup__inner">
			<div class="wds-edit-slot-popup__header">
				<img src="<?php echo esc_url( ICONIC_WDS_URL . 'assets/img/admin-popup-noshipping.svg' ); ?>" alt="">
				<?php esc_html_e( 'Change your delivery slot', 'jckwds' ); ?>
			</div>
			<div class="wds-edit-slot-popup__fields">
				<?php require ICONIC_WDS_PATH . 'templates/checkout-fields.php'; ?>
			</div>

			<div class="wds-edit-slot-popup__footer">
				<a class="wds-edit-slot-popup__cancel"><?php esc_html_e( 'Cancel', 'jckwds' ); ?></a>
			</div>
		</div>
	</div>
</div>
