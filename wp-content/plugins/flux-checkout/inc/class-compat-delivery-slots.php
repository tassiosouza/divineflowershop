<?php
/**
 * Iconic_Flux_Compat_Delivery_Slots.
 *
 * Compatibility with Iconic Delivery slots.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Delivery_Slots' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Delivery_Slots.
 *
 * @class    Iconic_Flux_Compat_Delivery_Slots.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Delivery_Slots {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'on_init' ) );
	}

	/**
	 * On init.
	 */
	public static function on_init() {
		if ( ! class_exists( 'Iconic_WDS' ) ) {
			return;
		}

		add_filter( 'iconic_wds_labels_by_type', array( __CLASS__, 'remove_placeholder' ), 101 );

		// Thank you page.
		$force = filter_input( INPUT_GET, 'flux_force_ty', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( '1' !== $force && ! Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] ) {
			return;
		}

		add_action( 'flux_thankyou_after_customer_details_payment_row', array( __CLASS__, 'add_delivery_fields' ) );

		if ( class_exists( 'Iconic_WDS_Edit_Timeslots' ) ) {
			add_action( 'flux_thankyou_before_map', array( 'Iconic_WDS_Edit_Timeslots', 'maybe_display_checkout_fields' ), 10, 1 );
		}
	}

	/**
	 * Remove placeholder.
	 *
	 * @param array $labels Labels.
	 */
	public static function remove_placeholder( $labels ) {
		$labels['delivery']['select_date']   = '';
		$labels['collection']['select_date'] = '';
		return $labels;
	}

	/**
	 * Add delivery date/time fields to the Thank you page.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function add_delivery_fields( $order ) {
		$delivery_slot_data = Iconic_WDS_Order::get_delivery_slot_data( $order );

		if ( empty( $delivery_slot_data ) || empty( $delivery_slot_data['date'] ) ) {
			return;
		}

		$time = ! empty( $delivery_slot_data['time_slot'] ) ? $delivery_slot_data['time_slot'] : ( ! empty( $delivery_slot_data['time'] ) ? $delivery_slot_data['time'] : '' );

		?>
		<div class="flux-review-customer__row">
			<div class='flux-review-customer__label'><label><?php echo esc_html( Iconic_WDS_Helpers::get_label( 'date', $order ) ); ?></label></div>
			<div class='flux-review-customer__content'>
				<p>
				<?php
					echo esc_html( $delivery_slot_data['date'] );
				?>
				</p>
			</div>
		</div>
		<?php

		if ( ! empty( $time ) ) {
			?>
			<div class="flux-review-customer__row">
				<div class='flux-review-customer__label'><label><?php echo esc_html( Iconic_WDS_Helpers::get_label( 'time_slot', $order ) ); ?></label></div>
				<div class='flux-review-customer__content'>
					<p>
					<?php
						echo esc_html( $time );
					?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
