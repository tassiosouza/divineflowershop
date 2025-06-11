<?php
function twentytwentyfive_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bloom-style', get_template_directory_uri() . '/bloom/assets/css/app.css', array( 'parent-style' ), '1.0' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ), '1.0' );
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles' );
