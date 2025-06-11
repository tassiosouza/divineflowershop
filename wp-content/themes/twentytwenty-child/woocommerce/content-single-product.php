<?php
defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form();
    return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
    <section class="py-80">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-xl-10 col-lg-11">
                    <div class="product-detail pb-80">
                        <div class="row row-gap-4">
                            <div class="col-md-6">
                                <div class="row align-items-center row-gap-3">
                                    <div class="list">
                                        <button class="slider-btn prev-btn" data-slide="preview-slider-nav">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17 16" fill="none">
                                                <path d="M0.857543 12.1506C1.14152 12.4346 1.60203 12.4347 1.88605 12.1506L8.64436 5.39213L15.403 12.1506C15.687 12.4346 16.1475 12.4347 16.4315 12.1506C16.7155 11.8666 16.7155 11.4061 16.4315 11.1221L9.15859 3.84935C9.0222 3.71296 8.83723 3.63635 8.64436 3.63635C8.45148 3.63635 8.26647 3.71301 8.13013 3.84939L0.857592 11.1221C0.573519 11.4061 0.573519 11.8666 0.857543 12.1506Z"/>
                                            </svg>
                                        </button>
                                        <div class="preview-slider-nav mt-3">
                                            <?php
                                            $attachment_ids = $product->get_gallery_image_ids();
                                            $main_id        = $product->get_image_id();
                                            if ( $main_id ) {
                                                array_unshift( $attachment_ids, $main_id );
                                            }
                                            foreach ( $attachment_ids as $id ) :
                                                $img = wp_get_attachment_image( $id, 'woocommerce_thumbnail' );
                                                ?>
                                                <div class="detail-img-block">
                                                    <?php echo $img; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="slider-btn next-btn" data-slide="preview-slider-nav">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17 16" fill="none">
                                                <path d="M16.4315 3.84935C16.1475 3.56537 15.687 3.56532 15.403 3.84939L8.6447 10.6078L1.88606 3.84935C1.60208 3.56537 1.14157 3.56532 0.857549 3.84939C0.573525 4.13342 0.573525 4.59388 0.857549 4.8779L8.13047 12.1506C8.26686 12.287 8.45183 12.3636 8.6447 12.3636C8.83757 12.3636 9.02259 12.287 9.15893 12.1506L16.4315 4.87786C16.7155 4.59388 16.7155 4.13337 16.4315 3.84935Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="preview">
                                        <div class="preview-slider">
                                            <?php
                                            foreach ( $attachment_ids as $id ) :
                                                $img = wp_get_attachment_image( $id, 'large' );
                                                ?>
                                                <div class="detail-img-block">
                                                    <?php echo $img; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="product-detail-content">
                                    <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-16">
                                        <h4><?php the_title(); ?></h4>
                                        <?php if ( $product->is_in_stock() ) : ?>
                                            <p class="green-tag"><?php esc_html_e( 'In Stock', 'twentytwenty-child' ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <ul class="unstyled mb-24 pro-rel">
                                        <li class="d-flex align-items-center">
                                            <span class="rating-stars me-1"><?php echo wc_get_rating_html( $product->get_average_rating() ); ?></span>
                                            <span class="text-decoration-underline"><?php echo esc_html( $product->get_review_count() ); ?> <?php esc_html_e( 'Reviews', 'twentytwenty-child' ); ?></span>
                                        </li>
                                        <?php if ( wc_product_sku_enabled() && $product->get_sku() ) : ?>
                                            <li>
                                                <span class="bold-text accent-dark me-1"><?php esc_html_e( 'SKU:', 'twentytwenty-child' ); ?></span><span><?php echo esc_html( $product->get_sku() ); ?></span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-24">
                                        <div class="price">
                                            <?php if ( $product->is_on_sale() ) : ?>
                                                <del class="h6 dark-gray"><?php echo wc_price( $product->get_regular_price() ); ?></del>
                                            <?php endif; ?>
                                            <h3><?php echo wc_price( $product->get_price() ); ?></h3>
                                        </div>
                                        <?php
                                        if ( $product->is_on_sale() ) {
                                            $regular = (float) $product->get_regular_price();
                                            $sale    = (float) $product->get_sale_price();
                                            if ( $regular > 0 && $sale < $regular ) {
                                                $discount = round( ( ( $regular - $sale ) / $regular ) * 100 );
                                                echo '<p class="red-tag">' . esc_html( $discount ) . '% off</p>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="mb-24"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
                                    <hr class="dash-line mb-16">
                                    <div class="action-block mb-16">
                                        <?php woocommerce_quantity_input(); ?>
                                        <?php woocommerce_template_single_add_to_cart(); ?>
                                        <a href="javascript:;" class="icon wishlist-icon"><i class="fa-light fa-heart"></i></a>
                                    </div>
                                    <hr class="dash-line mb-24">
                                    <?php if ( wc_product_sku_enabled() && $product->get_sku() ) : ?>
                                        <div class="mb-16">
                                            <span class="bold-text accent-dark me-1"><?php esc_html_e( 'SKU:', 'twentytwenty-child' ); ?></span><span><?php echo esc_html( $product->get_sku() ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="tags mb-24">
                                        <span class="bold-text accent-dark me-1"><?php esc_html_e( 'Tags:', 'twentytwenty-child' ); ?></span>
                                        <?php echo wc_get_product_tag_list( $product->get_id(), ', ' ); ?>
                                    </div>
                                    <hr class="dash-line mb-16">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-16">
                                        <span class="bold-text accent-dark"><?php esc_html_e( 'Share:', 'twentytwenty-child' ); ?></span>
                                        <ul class="unstyled social-icons">
                                            <li><a href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
                                            <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                                            <li><a href="#"><i class="fa-brands fa-pinterest"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php do_action( 'woocommerce_after_single_product_summary' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>
