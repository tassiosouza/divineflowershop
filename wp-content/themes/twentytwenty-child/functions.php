<?php
function twentytwenty_child_enqueue_styles() {
    wp_enqueue_style( 'twentytwenty-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bloom-style', get_theme_root_uri() . '/bloom-theme/assets/css/app.css', array(), null );
    wp_enqueue_style( 'twentytwenty-child-style', get_stylesheet_uri(), array( 'twentytwenty-style', 'bloom-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_child_enqueue_styles' );

function twentytwenty_child_enqueue_scripts() {
    $base = get_theme_root_uri() . '/bloom-theme/assets/js/';
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
?>
