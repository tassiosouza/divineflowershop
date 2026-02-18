<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="flux-common-wrap">
	<div class="flux-common-wrap__wrapper">
		<div class="flux-common-wrap__content-left">
			<?php Iconic_Flux_Thankyou::left_column( $order ); ?>
		</div>
		<div class="flux-common-wrap__content-right">
			<section class="flux-ty-order-review">
			<?php Iconic_Flux_Thankyou::render_product_details( $order ); ?>
			</section>
		</div>
	</div>
</div>
