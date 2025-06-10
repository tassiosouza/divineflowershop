<?php
/**
 * Front Page Template showing Bloom static homepage with dynamic featured products.
 */
$theme_dir  = get_stylesheet_directory();
$theme_uri  = get_stylesheet_directory_uri();
$html = file_get_contents($theme_dir . '/bloom/index.html');
// Prefix asset paths
$html = str_replace('href="assets/', 'href="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('src="assets/', 'src="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace("url('assets/", "url('" . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('url("assets/', 'url("' . $theme_uri . '/bloom/assets/', $html);

function bloom_featured_products_html() {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 6,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'featured',
            ),
        ),
    );
    $query = new WP_Query( $args );
    ob_start();
    ?>
    <section class="py-80">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-48">
                <div class="heading">
                    <h2>Featured <span>Flowers</span></h2>
                </div>
            </div>
            <div class="products-slider wow fadeInUp" data-wow-delay="0.4s">
                <button class="arrow prev-btn" data-slide="featured-product-slider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                        <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                    </svg>
                </button>
                <button class="arrow next-btn" data-slide="featured-product-slider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                    </svg>
                </button>
                <div class="row featured-product-slider">
                <?php while ( $query->have_posts() ) : $query->the_post(); global $product; ?>
                    <div class="col-12">
                        <div class="product-card">
                            <figure>
                                <a href="<?php the_permalink(); ?>">
                                    <?php echo $product->get_image(); ?>
                                </a>
                                <ul class="unstyled action-list">
                                    <li><a href="javascript:;" class="icon wishlist-icon"><i class="fa-light fa-heart"></i></a></li>
                                    <li><a href="<?php the_permalink(); ?>" class="icon"><i class="fa-light fa-eye"></i></a></li>
                                </ul>
                            </figure>
                            <div class="text-block">
                                <a href="<?php the_permalink(); ?>" class="h4 mb-16"><?php the_title(); ?></a>
                                <div class="price mb-32">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                                <div class="action-block">
                                    <?php woocommerce_template_loop_add_to_cart(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            </div>
            <div class="slider-arrows d-lg-none mt-48">
                <button class="arrow prev-btn" data-slide="featured-product-slider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                        <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                    </svg>
                </button>
                <button class="arrow next-btn" data-slide="featured-product-slider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

$featured = bloom_featured_products_html();
$html = preg_replace('/<!-- Featured Products Section Start -->.*<!-- Featured Products Section End -->/s', $featured, $html);

echo $html;
