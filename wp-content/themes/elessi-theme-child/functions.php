<?php
/**
 * Recommended way to include parent theme styles.
 * Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme
 */

add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 998);
function theme_enqueue_styles() {
	$prefix = function_exists('elessi_prefix_theme') ? elessi_prefix_theme() : 'elessi';
    wp_enqueue_style($prefix . '-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style($prefix . '-child-style', get_stylesheet_uri());
}
/**
 * Your code goes below
 */

/**
 * Reorder product variations by price (Standard, Deluxe, Premium)
 */
function reorder_variations_by_price($variations, $product) {
    if (!$product || !$product->is_type('variable')) {
        return $variations;
    }
    
    // Sort variations by price
    usort($variations, function($a, $b) {
        return $a['display_price'] <=> $b['display_price'];
    });
    
    return $variations;
}
add_filter('woocommerce_available_variations', 'reorder_variations_by_price', 10, 2);

/**
 * Also reorder variations in the admin and frontend display
 */
function reorder_variations_display($variations, $product) {
    if (!$product || !$product->is_type('variable')) {
        return $variations;
    }
    
    // Sort variations by price
    usort($variations, function($a, $b) {
        return $a['display_price'] <=> $b['display_price'];
    });
    
    return $variations;
}
add_filter('woocommerce_product_get_children', 'reorder_variations_display', 10, 2);