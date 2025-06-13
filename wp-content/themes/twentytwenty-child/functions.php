<?php
function twentytwenty_child_enqueue_styles() {
    wp_enqueue_style( 'twentytwenty-style', get_template_directory_uri() . '/style.css' );
    $bloom_base = get_stylesheet_directory_uri() . '/bloom/assets/css/';
    wp_enqueue_style( 'bloom-fontawesome', $bloom_base . 'vendor/fontawsome.css', array(), null );
    wp_enqueue_style( 'bloom-bootstrap', $bloom_base . 'vendor/bootstrap.min.css', array(), null );
    wp_enqueue_style( 'bloom-magnific', $bloom_base . 'vendor/jquery.magnific-popup.css', array(), null );
    wp_enqueue_style( 'bloom-animate', $bloom_base . 'vendor/animate.min.css', array(), null );
    wp_enqueue_style( 'bloom-slick', $bloom_base . 'vendor/slick.css', array(), null );
    wp_enqueue_style( 'bloom-style', $bloom_base . 'app.css', array( 'bloom-fontawesome', 'bloom-bootstrap', 'bloom-magnific', 'bloom-animate', 'bloom-slick' ), null );
    wp_enqueue_style( 'twentytwenty-child-style', get_stylesheet_uri(), array( 'twentytwenty-style', 'bloom-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_child_enqueue_styles' );

function twentytwenty_child_enqueue_scripts() {
    $base = get_stylesheet_directory_uri() . '/bloom/assets/js/';
    wp_enqueue_script( 'bloom-bootstrap', $base . 'vendor/bootstrap.min.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-appear', $base . 'vendor/jquery-appear.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-nice-select', $base . 'vendor/jquery.nice-select.min.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-slick', $base . 'vendor/slick.min.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-wow', $base . 'vendor/wow.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-range', $base . 'vendor/ion.rangeSlider.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-magnific', $base . 'vendor/jquery.magnific-popup.min.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'bloom-app', $base . 'app.js', array( 'jquery', 'bloom-slick' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_child_enqueue_scripts' );

/**
 * Disable WooCommerce block templates for the single product page.
 *
 * Returning false for the "single-product" template name forces
 * WooCommerce to load the PHP template from the theme instead of the
 * block template provided by the plugin.
 *
 * @param bool   $has_template  Whether a block template exists.
 * @param string $template_name Name of the template being checked.
 * @return bool Modified result.
 */
function twentytwenty_child_disable_single_product_block_template( $has_template, $template_name ) {
    if ( 'single-product' === $template_name ) {
        $theme_template  = get_stylesheet_directory() . '/templates/single-product.html';
        $legacy_template = get_stylesheet_directory() . '/block-templates/single-product.html';

        if ( file_exists( $theme_template ) || file_exists( $legacy_template ) ) {
            return $has_template;
        }

        return false;
    }

    return $has_template;
}
add_filter( 'woocommerce_has_block_template', 'twentytwenty_child_disable_single_product_block_template', 10, 2 );

function bloom_enqueue_product_styles_scripts() {
    if (is_product()) {
        // CSS
        wp_enqueue_style('bloom-bootstrap', get_stylesheet_directory_uri() . '/bloom-assets/css/bootstrap.min.css');
        wp_enqueue_style('bloom-fontawesome', get_stylesheet_directory_uri() . '/bloom-assets/css/fontawsome.css');
        wp_enqueue_style('bloom-slick', get_stylesheet_directory_uri() . '/bloom-assets/css/slick.css');
        wp_enqueue_style('bloom-animate', get_stylesheet_directory_uri() . '/bloom-assets/css/animate.min.css');
        wp_enqueue_style('bloom-popup', get_stylesheet_directory_uri() . '/bloom-assets/css/jquery.magnific-popup.css');
        wp_enqueue_style('bloom-range', get_stylesheet_directory_uri() . '/bloom-assets/css/ion.rangeSlider.css');
        wp_enqueue_style('bloom-app', get_stylesheet_directory_uri() . '/bloom-assets/css/app.css');

        // JS (jQuery incluso automaticamente no WP)
        wp_enqueue_script('bloom-bootstrap', get_stylesheet_directory_uri() . '/bloom-assets/js/bootstrap.min.js', ['jquery'], null, true);
        wp_enqueue_script('bloom-appear', get_stylesheet_directory_uri() . '/bloom-assets/js/jquery-appear.js', ['jquery'], null, true);
        wp_enqueue_script('bloom-nice-select', get_stylesheet_directory_uri() . '/bloom-assets/js/jquery.nice-select.min.js', ['jquery'], null, true);
        wp_enqueue_script('bloom-slick', get_stylesheet_directory_uri() . '/bloom-assets/js/slick.min.js', ['jquery'], null, true);
        wp_enqueue_script('bloom-wow', get_stylesheet_directory_uri() . '/bloom-assets/js/wow.js', ['jquery'], null, true);
        wp_enqueue_script('bloom-range', get_stylesheet_directory_uri() . '/bloom-assets/js/ion.rangeSlider.js', ['jquery'], null, true);

        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('storefront-style');
        wp_dequeue_style('twentytwenty-style');
        wp_dequeue_style('twenty-twenty');
        wp_dequeue_style('wp-block-library'); // Gutenberg base
        wp_dequeue_style('woocommerce-general'); // WooCommerce default
    }
}
add_action('wp_enqueue_scripts', 'bloom_enqueue_product_styles_scripts');

?>

