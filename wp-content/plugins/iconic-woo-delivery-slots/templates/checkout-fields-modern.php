<?php
/**
 * The template for the checkout fields.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $fields ) ) {
	return;
} 

$wrap_classes = array_filter([
	'jckwds-fields-inactive' => ! $active,
	'iconic-wds-fields--has-error' => empty( $bookable_dates ),
]);

$wrap_classes = implode(' ', array_keys($wrap_classes));
?>

<?php
/**
 * Fires before the checkout fields container.
 *
 * @since 1.0.0
 */
do_action( 'iconic_wds_before_checkout_fields' );
?>

	<div id="jckwds-fields" class="iconic-wds-fields woocommerce-billing-fields wds-fieldbox <?php echo esc_attr($wrap_classes); ?>" style="<?php echo esc_attr( ! $active ? 'display: none;' : '' ); ?>">
		<div class="wds-fieldbox-header">
			<span class="wds-fieldbox-header__title"><?php echo wp_kses_post( Helpers::get_label( 'details' ) ); ?></span>
		</div>

		<?php
		/**
		 * Fires after the "Delivery Details" title.
		 *
		 * @since 1.0.0.
		 */
		do_action( 'iconic_wds_after_delivery_details_title' );

		$message = '';
		if ( $all_products_virtual ) {
			$message = __( 'No delivery slots available for virtual products.', 'jckwds' );
		} elseif ( empty( $available_methods ) ) {
			$message = __( 'Enter your address to view available time slots.', 'jckwds' );
		} else {
			$message = __( 'Sorry, there are no dates available for that shipping method. Please select another method or try again later.', 'jckwds' );
		}

		// There is no point in esc_attr as the values are strings but PHPCS requires that.
		echo sprintf( '<div class="iconic-wds-fields__error">');
		wc_print_notice( $message, 'error' );
		echo '</div>';

		?>
		<div class="iconic-wds-fields__fields">
			<?php foreach ( $fields as $field_name => $field_data ) { ?>
				<?php
				/**
				 * Fires before each checkout field.
				 *
				 * @param array $field_data
				 *
				 * @since 1.0.0.
				 */
				do_action( 'iconic_wds_before_delivery_details_field_wrapper', $field_data );
				?>

				<div id="<?php echo esc_attr( $field_name ); ?>-wrapper">
					<?php
					/**
					 * Checkout field data.
					 *
					 * @since 1.0.0
					 */
					$field_data = apply_filters( 'iconic_wds_checkout_field_data', $field_data );
					?>

					<?php if ( 'hidden' === $field_data['field_args']['type'] ) { ?>
						<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_data['value'] ); ?>">
					<?php } else {
						?>
						<div class="wds-fieldbox-sub-field">
							<?php woocommerce_form_field( $field_name, $field_data['field_args'], $field_data['value'] ); ?>
						</div>
					<?php } ?>
				</div>


				<?php
				/**
				 * Fires after each checkout field.
				 *
				 * @param array $field_data
				 *
				 * @since 1.0.0
				 */
				do_action( 'iconic_wds_after_delivery_details_field_wrapper', $field_data );
				?>
			<?php } ?>
			<?php
			if ( ! empty( $show_save_button ) ) {
				?>
				<div id="jckwds-submit-button-wrapper">
					<button id="jckwds-check-button"><?php esc_html_e( 'Change Delivery Slot', 'jckwds' ); ?></button>
					<button id="jckwds-save-button" style="display:none;"><?php esc_html_e( 'Continue to Payment', 'jckwds' ); ?></button>
					<input type="hidden" name="order_id" id="jckwds-fields-order-id" value="<?php echo esc_attr( $order_id ); ?>"/>
				</div>
				<?php
			}
			?>
		</div>

		<?php
		/**
		 * Fires after the checkout fields, but inside the container.
		 *
		 * @since 1.0.0.
		 */
		do_action( 'iconic_wds_after_delivery_details_fields' );
		?>

		<input type="hidden" name="iconic-wds-fields-hidden" value="<?php echo $active ? 0 : 1; ?>" autocomplete="iconic-wds-fields-hidden-off">
	</div>

<?php
/**
 * Fires after the checkout fields container.
 *
 * @since 1.0.0.
 */
do_action( 'iconic_wds_after_checkout_fields' );
