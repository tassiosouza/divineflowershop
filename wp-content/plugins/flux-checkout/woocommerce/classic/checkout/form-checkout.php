<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 *
 * @var WC_Checkout $checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires before checkout form.
 *
 * @param WC_Checkout $checkout
 *
 * @since 2.0.0
 */
do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	/**
	 * Message to indicate logging in is required for checkout.
	 *
	 * @since 2.0.0
	 */
	$message = apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'flux-checkout' ) );
	$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ), __( 'Log in', 'iconic-wsb' ), $message );
	?>
	<div class="woocommerce-error"><?php echo wp_kses_post( $message ); ?></div>
	<?php
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<div class="flux-checkout__form-inner">
		<?php if ( Iconic_Flux_Sidebar::is_sidebar_enabled() ) { ?>
			<section class="flux-checkout__order-review">
				<?php wc_get_template( 'checkout/cart-heading.php' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php
					/**
					 * Hook to display the order review.
					 *
					 * @param WC_Checkout $checkout
					 *
					 * @since 2.0.0
					 */
					do_action( 'flux_checkout_order_review', $checkout );
					?>
				</div>
			</section>
		<?php } ?>

		<div class="flux-checkout__steps">
			<?php Iconic_Flux_Steps::render_steps(); ?>
		</div>
	</div>
</form>

<?php
/**
 * Fires after checkout form.
 *
 * @param WC_Checkout $checkout
 *
 * @since 2.0.0
 */
do_action( 'woocommerce_after_checkout_form', $checkout );
