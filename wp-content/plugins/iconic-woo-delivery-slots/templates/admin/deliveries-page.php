<?php
/**
 * Admin - deliveries page.
 *
 * @package Iconic_WDS
 */

global $iconic_wds;

if ( empty( $reservations['results'] ) ) {
	if ( $reservations['processed'] ) {
		echo '<p>' . esc_html__( 'There are currently no upcoming deliveries.', 'jckwds' ) . '</p>';
	} else {
		echo '<p>' . esc_html__( 'There are currently no active reservations.', 'jckwds' ) . '</p>';
	}

	return;
}
?>

<style>
	.iconic-wds-key__dot,
	.iconic-wds-delivery__date:before {
		content: "";
		display: inline-block;
		width: 8px;
		height: 8px;
		border-radius: 4px;
		background: #D2E4E6;
		margin: 0 5px 0 0;
	}

	.iconic-wds-key__dot--same-day,
	.iconic-wds-delivery--same-day .iconic-wds-delivery__date:before {
		background-color: #F65536;
	}

	.iconic-wds-key__dot--next-day,
	.iconic-wds-delivery--next-day .iconic-wds-delivery__date:before {
		background-color: #3185FC;
	}

	.iconic-wds-key {
		list-style: none none outside;
		padding: 0;
		margin: 0 0 20px;
	}

	.iconic-wds-key li {
		display: inline-block;
		margin: 0 10px 0 0;
	}

	.iconic-wds-delivery__mobile_time {
		display: none;
		margin-left: 10px;
	}

	@media (max-width: 782px) {
		.iconic-wds-delivery__mobile_time {
			display: inline-block;
		}
	}
</style>

<ul class="iconic-wds-key">
	<li><strong><?php esc_html_e( 'Key:', 'jckwds' ); ?></strong></li>
	<li>
		<span class="iconic-wds-key__dot iconic-wds-key__dot--same-day"></span><?php esc_html_e( 'Today', 'jckwds' ); ?>
	</li>
	<li>
		<span class="iconic-wds-key__dot iconic-wds-key__dot--next-day"></span><?php esc_html_e( 'Tomorrow', 'jckwds' ); ?>
	</li>
	<li><span class="iconic-wds-key__dot"></span><?php esc_html_e( 'Upcoming', 'jckwds' ); ?></li>
</ul>

<table class="wp-list-table widefat fixed striped" cellspacing="0">
	<thead>
	<tr>
		<th class="column-primary" scope="col"><?php esc_html_e( 'Date', 'jckwds' ); ?></th>
		<?php if ( $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) { ?>
			<th scope="col"><?php esc_html_e( 'Time Slot', 'jckwds' ); ?></th>
		<?php } ?>
		<?php if ( $reservations['processed'] ) { ?>
			<th scope="col"><?php esc_html_e( 'Order', 'jckwds' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Ship to', 'jckwds' ); ?></th>
		<?php } ?>
		<th scope="col"><?php esc_html_e( 'Customer Name', 'jckwds' ); ?></th>
		<th scope="col"><?php esc_html_e( 'Customer Email', 'jckwds' ); ?></th>
		<?php if ( $reservations['processed'] ) { ?>
			<th id="order_status" class="manage-column column-order_status" scope="col">
				<?php esc_html_e( 'Status', 'jckwds' ); ?>
			</th>
		<?php } ?>
		<?php
		/**
		 * Admin - deliveries table heading ends.
		 *
		 * @since 1.0.0.
		 */
		do_action( 'iconic_wds_admin_deliveries_table_heading' );
		?>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $reservations['results'] as $reservation ) { ?>
		<?php
		$classes = array(
			'iconic-wds-delivery',
		);

		if ( $reservation->iconic_wds['same_day'] ) {
			$classes[] = 'iconic-wds-delivery--same-day';
		}

		if ( $reservation->iconic_wds['next_day'] ) {
			$classes[] = 'iconic-wds-delivery--next-day';
		}
		?>
		<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<td class='column-primary'>
				<strong class="iconic-wds-delivery__date"><?php echo esc_html( $reservation->iconic_wds['date_formatted'] ); ?></strong>
				<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'jckwds' ); ?></span></button>
				<?php if ( $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) { ?>
					<span class='iconic-wds-delivery__mobile_time'>
						<?php if ( $reservation->asap ) { ?>
							<?php esc_html_e( 'ASAP', 'jckwds' ); ?>
						<?php } else { ?>
							<?php echo esc_html( $reservation->iconic_wds['time_slot_formatted'] ); ?>
						<?php } ?>
					</span>
				<?php } ?>
			</td>
			<?php if ( $iconic_wds->settings['timesettings_timesettings_setup_enable'] ) { ?>
				<td data-colname="<?php esc_html_e( 'Timeslot', 'jckwds' ); ?>">
					<?php if ( $reservation->asap ) { ?>
						<?php esc_html_e( 'ASAP', 'jckwds' ); ?>
					<?php } else { ?>
						<?php echo esc_html( empty( $reservation->starttime ) ? '&mdash;' : $reservation->iconic_wds['time_slot_formatted'] ); ?>
					<?php } ?>
				</td>
			<?php } ?>
			<?php if ( $reservations['processed'] ) { ?>
				<td data-colname="<?php esc_html_e( 'Order', 'jckwds' ); ?>">
					<div><?php echo wp_kses_post( $reservation->order_edit ); ?></div>
					<div><span class="description"><?php echo wp_kses_post( $reservation->order_items ); ?></span></div>
				</td>
				<td data-colname="<?php esc_html_e( 'Ship to', 'jckwds' ); ?>">
					<div><strong><?php echo esc_html( $reservation->method_label ); ?></strong></div>
					<div><?php echo wp_kses_post( $reservation->address_link ); ?></div>
					<div>
						<span class="description"><?php printf( '%s %s', esc_html__( 'via', 'jckwds' ), esc_html( $reservation->shipping_method ) ); ?></span>
					</div>
				</td>
			<?php } ?>
			<td data-colname="<?php esc_html_e( 'Customer Name', 'jckwds' ); ?>"><?php echo esc_html( $reservation->billing_name ); ?></td>
			<td data-colname="<?php esc_html_e( 'Customer Email', 'jckwds' ); ?>"><?php echo wp_kses_post( $reservation->billing_email ); ?></td>
			<?php if ( $reservations['processed'] ) { ?>
				<td data-colname="<?php esc_html_e( 'Status', 'jckwds' ); ?>" class="order_status column-order_status">
					<?php echo wp_kses_post( $reservation->order_status_badge ); ?>
				</td>
			<?php } ?>
			<?php
			/**
			 * Admin - deliveries table body.
			 *
			 * @since 1.1.0.
			 */
			do_action( 'iconic_wds_admin_deliveries_table_body_cell', $reservation );
			?>
		</tr>
	<?php } ?>
	</tbody>
</table>
