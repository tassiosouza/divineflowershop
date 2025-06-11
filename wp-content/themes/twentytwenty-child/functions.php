<?php
function twentytwenty_child_enqueue_styles() {
    wp_enqueue_style( 'twentytwenty-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bloom-style', get_theme_root_uri() . '/bloom-theme/assets/css/app.css', array(), null );
    wp_enqueue_style( 'twentytwenty-child-style', get_stylesheet_uri(), array( 'twentytwenty-style', 'bloom-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_child_enqueue_styles' );
?>
