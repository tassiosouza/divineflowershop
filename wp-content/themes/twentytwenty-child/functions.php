<?php
function twentytwenty_child_enqueue_styles() {
    wp_enqueue_style( 'twentytwenty-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bloom-style', get_theme_root_uri() . '/bloom-theme/assets/css/app.css', array(), null );
    wp_enqueue_style( 'twentytwenty-child-style', get_stylesheet_uri(), array( 'twentytwenty-style', 'bloom-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_child_enqueue_styles' );

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
