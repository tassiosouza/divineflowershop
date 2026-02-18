<?php
/**
 * Template for empty cart.
 *
 * @package Iconic_Flux
 */

?>

<div class="flux-empty-cart">
	<div class="flux-empty-cart__wrap">
		<div class="flux-empty-cart__icon-border">
			<div class="flux-empty-cart__icon"></div>
		</div>
		<div class="flux-empty-cart__text">
			<p><?php esc_html_e( 'Your cart is empty, taking you back to the shop...', 'flux-checkout' ); ?></p>
		</div>
		<div class="flux-empty-cart__button">
			<a class="flux-button flux-button--reverse flux-button--emptycart" href="<?php echo esc_url( Iconic_Flux_Helpers::get_shop_page_url() ); ?>"><?php esc_html_e( 'Return to shop', 'flux-checkout' ); ?></a>
		</div>
	</div>
</div>

