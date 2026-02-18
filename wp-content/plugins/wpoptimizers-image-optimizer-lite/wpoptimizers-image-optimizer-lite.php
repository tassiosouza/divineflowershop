<?php
/**
 * Plugin Name: WPOptimizers - Image Optimizer Lite
 * Description: Optimize images automatically on upload + bulk optimize existing images using GD library.
 * Version:     1.0.4
 * Author:      WPOptimizers
 * Author URI:  https://wpoptimizers.com/
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License:     GPL v2
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: wpoptimizers-image-optimizer-lite
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin paths
define( 'WPIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPIO_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once WPIO_PATH . 'includes/class-wpio-optimizer.php';
require_once WPIO_PATH . 'includes/class-wpio-ajax.php';
require_once WPIO_PATH . 'admin/class-wpio-admin-menu.php';
require_once WPIO_PATH . 'admin/class-wpio-settings-page.php';
require_once WPIO_PATH . 'admin/class-wpio-bulk-page.php';

// Activation: add default settings
register_activation_hook( __FILE__, function() {
    if ( ! get_option( 'wpio_settings' ) ) {
        add_option( 'wpio_settings', [
            'jpeg_quality' => 75,
            'png_level'    => 6,
        ]);
    }
});

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', function($hook){
    // Only load on WPOptimizers admin pages
    if(strpos($hook, 'wpio') === false) return;

    // Enqueue JS
    wp_enqueue_script(
        'wpio-bulk-js',
        WPIO_URL . 'assets/js/bulk.js',
        ['jquery'],
        '1.0.0',
        true
    );

    // Pass ajax_url and nonce to JS
    wp_localize_script('wpio-bulk-js', 'wpio_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wpio_bulk_nonce')
    ]);

    // Enqueue CSS
    wp_enqueue_style(
        'wpio-admin-style',
        WPIO_URL . 'assets/css/admin-style.css',
        [],
        '1.0.0'
    );
});
