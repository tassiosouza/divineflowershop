<?php
/*
Plugin Name: Bloom Product Shortcode
Description: Display WooCommerce product details using Bloom template layout.
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function bloom_product_shortcode( $atts ) {
    if ( ! function_exists( 'wc_get_product' ) ) {
        return '';
    }

    $atts = shortcode_atts( array(
        'id' => 0,
    ), $atts );

    $product = wc_get_product( $atts['id'] );
    if ( ! $product ) {
        return '';
    }

    ob_start();
    ?>
    <div class="product-detail-content">
        <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-16">
            <h4><?php echo esc_html( $product->get_name() ); ?></h4>
            <?php if ( $product->is_in_stock() ) : ?>
                <p class="green-tag"><?php esc_html_e( 'In Stock', 'bloom' ); ?></p>
            <?php else : ?>
                <p class="red-tag"><?php esc_html_e( 'Out of Stock', 'bloom' ); ?></p>
            <?php endif; ?>
        </div>
        <ul class="unstyled mb-24 pro-rel">
            <li class="d-flex align-items-center">
                <?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
                <span class="text-decoration-underline">
                    <?php printf( _n( '%s Review', '%s Reviews', $product->get_review_count(), 'bloom' ), $product->get_review_count() ); ?>
                </span>
            </li>
            <?php if ( $product->get_sku() ) : ?>
            <li>
                <span class="bold-text accent-dark me-1"><?php esc_html_e( 'SKU:', 'bloom' ); ?></span>
                <span><?php echo esc_html( $product->get_sku() ); ?></span>
            </li>
            <?php endif; ?>
        </ul>
        <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-24">
            <div class="price">
                <?php echo $product->get_price_html(); ?>
            </div>
        </div>
        <div class="mb-24">
            <?php echo wpautop( $product->get_description() ); ?>
        </div>
        <hr class="dash-line mb-16">
        <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
            <div class="action-block mb-16">
                <div class="quantity-wrap">
                    <div class="decrement"><i class="fa-solid fa-dash"></i></div>
                    <?php echo woocommerce_quantity_input( array( 'input_value' => 1 ), $product, false ); ?>
                    <div class="increment"><i class="fa-solid fa-plus-large"></i></div>
                </div>
                <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="cart-btn cart-button">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/media/icons/cart.svg' ); ?>" alt="">
                </button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'bloom_product', 'bloom_product_shortcode' );
