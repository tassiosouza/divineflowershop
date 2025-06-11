<?php
/**
 * Front Page Template showing Bloom static homepage.
 */
$theme_dir  = get_stylesheet_directory();
$theme_uri  = get_stylesheet_directory_uri();
$html = file_get_contents($theme_dir . '/bloom/index.html');
// Prefix asset paths
$html = str_replace('href="assets/', 'href="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('src="assets/', 'src="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace("url('assets/", "url('" . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('url("assets/', 'url("' . $theme_uri . '/bloom/assets/', $html);

$products = wc_get_products( array( 'status' => 'publish', 'limit' => 6 ) );
ob_start();
?>
                    <div class="row featured-product-slider">
<?php foreach ( $products as $product ) : ?>
                        <div class="col-12">
                            <div class="product-card">
                                <figure>
                                    <img src="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'full' ) ); ?>" alt="">
                                    <ul class="unstyled action-list">
                                        <li><a href="javascript:;" class="icon wishlist-icon"><i class="fa-light fa-heart"></i></a></li>
                                        <li><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="icon"><i class="fa-light fa-eye"></i></a></li>
                                    </ul>
                                </figure>
                                <div class="text-block">
                                    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="h4 mb-16"><?php echo esc_html( $product->get_name() ); ?></a>
                                    <p class="mb-24"><?php echo esc_html( wp_trim_words( $product->get_description(), 20 ) ); ?></p>
                                    <div class="price mb-32">
                                        <?php  ?>
                                    </div>
                                    <div class="price mb-32">
                                        <h3>$ <?= $product->get_price(); ?></h3>
                                    </div>
                                    <div class="action-block">
                                        <div class="quantity-wrap">
                                            <div class="decrement"><i class="fa-solid fa-dash"></i></div>
                                            <input type="text" name="quantity" value="1" maxlength="4" size="1" class="number">
                                            <div class="increment"><i class="fa-solid fa-plus-large"></i></div>
                                        </div>
                                        <a href="<?php echo esc_url( wc_get_cart_url() . '?add-to-cart=' . $product->get_id() ); ?>" class="cart-btn cart-button"><img src="<?php echo esc_url( $theme_uri . '/bloom/assets/media/icons/cart.svg' ); ?>" alt=""></a>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php endforeach; ?>
                    </div>
<?php
$slider_html = ob_get_clean();
$html = preg_replace( '/<!-- PRODUCT_SLIDER_START -->.*<!-- PRODUCT_SLIDER_END -->/s', '<!-- PRODUCT_SLIDER_START -->' . $slider_html . '<!-- PRODUCT_SLIDER_END -->', $html );

echo $html;

