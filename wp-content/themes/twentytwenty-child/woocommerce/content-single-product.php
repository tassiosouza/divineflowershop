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
                                <div class="preview">
                                    <?php
                                    do_action( 'woocommerce_before_single_product_summary' );
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="product-detail-content">
                                    <?php
                                    do_action( 'woocommerce_single_product_summary' );
                                    ?>
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
