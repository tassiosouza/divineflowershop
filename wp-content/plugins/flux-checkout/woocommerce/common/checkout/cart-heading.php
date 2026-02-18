<?php
/**
 * Checkout Cart heading
 * This template can be overridden by copying it to yourtheme/woocommerce/common/checkout/cart-heading.php.
 *
 * @package WooCommerce\Templates
 */

?>
<h2 class="flux-heading flux-heading--order-review flux-heading--order-review-cart-count" id="order_review_heading">
	<?php
	if ( Iconic_Flux_Helpers::is_modern_theme() ) {
		esc_html_e( 'Cart', 'woocommerce' );
	} else {
		esc_html_e( 'Your Order', 'woocommerce' );
	}
	?>
	<span class="flux-heading__count"><?php echo esc_html( WC()->cart->cart_contents_count ); ?></span>
</h2>
