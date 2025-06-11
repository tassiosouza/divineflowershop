<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
    the_post();
    do_action( 'woocommerce_before_single_product' );
    if ( post_password_required() ) {
        echo get_the_password_form();
        return;
    }
    ?>
    <section class="py-80">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-xl-10 col-lg-11">
                    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-detail pb-80' ); ?>>
                        <div class="row row-gap-4">
                            <div class="col-md-6">
                                <?php
                                do_action( 'woocommerce_before_single_product_summary' );
                                ?>
                            </div>
                            <div class="col-md-6">
                                <div class="product-detail-content">
                                    <?php woocommerce_template_single_title(); ?>
                                    <?php woocommerce_template_single_rating(); ?>
                                    <?php woocommerce_template_single_price(); ?>
                                    <?php woocommerce_template_single_excerpt(); ?>
                                    <?php woocommerce_template_single_add_to_cart(); ?>
                                    <?php woocommerce_template_single_meta(); ?>
                                    <?php woocommerce_template_single_sharing(); ?>
                                </div>
                            </div>
                        </div>
                        <?php do_action( 'woocommerce_after_single_product_summary' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
do_action( 'woocommerce_after_single_product' );
endwhile;

do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
