<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

?>

<form id="order_review" method="post" class='flux-order-pay'>
	<div class="flux-common-wrap">
		<div class="flux-order-pay-header flux-order-pay-header--mobile">
			<?php
			if ( Iconic_Flux_Helpers::is_modern_theme() ) {
				Iconic_Flux_Steps::render_header( false );
			}
			?>
		</div>
		<div class="flux-common-wrap__wrapper">
			<div class="flux-common-wrap__content-left">
				<div class="flux-step">
					<div class="flux-order-pay-header flux-order-pay-header--desktop">
						<?php
						if ( Iconic_Flux_Helpers::is_modern_theme() ) {
							Iconic_Flux_Steps::render_header( false );
						}
						?>
					</div>
					<h2 class="flux-heading flux-heading--order-pay"><?php esc_html_e( 'Pay for Order', 'flux-checkout' ); ?></h2>
					<div id="order_review">
						<div id="payment">
							<?php if ( $order->needs_payment() ) : ?>
								<ul class="wc_payment_methods payment_methods methods">
									<?php
									if ( ! empty( $available_gateways ) ) {
										foreach ( $available_gateways as $gateway ) {
											wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
										}
									} else {
										echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) ) . '</li>';
									}
									?>
								</ul>
							<?php endif; ?>
							<div class="form-row">
								<input type="hidden" name="woocommerce_pay" value="1" />

								<?php wc_get_template( 'checkout/terms.php' ); ?>

								<?php
								/**
								 * Order pay page - before submit button.
								 *
								 * @since 2.3.0
								 */
								do_action( 'woocommerce_pay_order_before_submit' );
								?>

								<footer class="flux-footer flux-footer--order-pay">
									<?php
									if ( Iconic_Flux_Helpers::is_modern_theme() ) {
										?>
										<a class='flux-step__back' href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Back to Account', 'flux-checkout' ); ?></a>
										<?php
									}
									?>
									<?php echo '<button type="submit" class="button alt" id="place_order" data-text="' . esc_attr( Iconic_Flux_Helpers::get_order_pay_btn_text( $order ) ) . '" value="' . esc_html__( 'Pay for order' ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . wp_kses_post( $order_button_text ) . '</button>'; ?>
								</footer>

								<?php
								/**
								 * Order pay page - after submit button.
								 *
								 * @since 2.3.0
								 */
								do_action( 'woocommerce_pay_order_after_submit' );
								?>

								<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="flux-common-wrap__content-right">
				<section class="flux-order-pay-order-review">
					<?php Iconic_Flux_Thankyou::render_product_details( $order ); ?>
				</section>
			</div>
		</div>
	</div>
</form>
