<?php
/**
 * Single product template based on Bloom product-detail layout.
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $product;
?>

<div class="container my-5">
    <div class="row row-gap-4">
        <div class="col-md-6">
            <?php woocommerce_show_product_images(); ?>
        </div>
        <div class="col-md-6">
            <div class="product-detail-content">
                <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-16">
                    <h4><?php the_title(); ?></h4>
                    <?php if ( $product->is_in_stock() ) : ?>
                        <p class="green-tag"><?php esc_html_e( 'In Stock', 'twentytwentyfive-child' ); ?></p>
                    <?php endif; ?>
                </div>
                <ul class="unstyled mb-24 pro-rel">
                    <li class="d-flex align-items-center">
                        <?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
                        <span class="text-decoration-underline ms-2"><?php echo esc_html( $product->get_review_count() ); ?> <?php esc_html_e( 'Reviews', 'twentytwentyfive-child' ); ?></span>
                    </li>
                    <li>
                        <span class="bold-text accent-dark me-1"><?php esc_html_e( 'SKU:', 'twentytwentyfive-child' ); ?></span><span><?php echo esc_html( $product->get_sku() ); ?></span>
                    </li>
                </ul>
                <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-24">
                    <div class="price">
                        <?php if ( $product->is_on_sale() ) : ?>
                            <del class="h6 dark-gray"><?php echo wc_price( $product->get_regular_price() ); ?></del>
                            <h3><?php echo wc_price( $product->get_sale_price() ); ?></h3>
                        <?php else : ?>
                            <h3><?php echo wc_price( $product->get_price() ); ?></h3>
                        <?php endif; ?>
                    </div>
                    <?php if ( $product->is_on_sale() ) : ?>
                        <?php $regular = (float) $product->get_regular_price();
                              $sale    = (float) $product->get_sale_price();
                              $discount = $regular ? round( 100 - ( $sale / $regular * 100 ) ) : 0; ?>
                        <p class="red-tag"><?php echo esc_html( $discount . '% off' ); ?></p>
                    <?php endif; ?>
                </div>
                <p class="mb-24"><?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?></p>
                <hr class="dash-line mb-16">
                <div class="action-block mb-16">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
                <hr class="dash-line mb-24">
                <div class="mb-16">
                    <span class="bold-text accent-dark me-1"><?php esc_html_e( 'SKU:', 'twentytwentyfive-child' ); ?></span><span><?php echo esc_html( $product->get_sku() ); ?></span>
                </div>
                <div class="tags mb-24">
                    <span class="bold-text accent-dark me-1"><?php esc_html_e( 'Tags:', 'twentytwentyfive-child' ); ?></span><?php the_terms( $product->get_id(), 'product_tag', '', ', ', '' ); ?>
                </div>
                <hr class="dash-line mb-16">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-16">
                    <span class="bold-text accent-dark"><?php esc_html_e( 'Share:', 'twentytwentyfive-child' ); ?></span>
                    <ul class="unstyled social-icons">
                        <li><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-pinterest"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?>
