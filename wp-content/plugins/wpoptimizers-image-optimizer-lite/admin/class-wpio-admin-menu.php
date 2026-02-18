<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPIO_Admin_Menu {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );

        // Media Library column + badge
        add_filter( 'manage_upload_columns', array( __CLASS__, 'add_optimization_column' ) );
        add_action( 'manage_media_custom_column', array( __CLASS__, 'render_optimization_column' ), 10, 2 );
    }

    public static function register_menu() {
        add_menu_page(
            'WPOptimizers',
            'WPOptimizers',
            'manage_options',
            'wpio_dashboard', // slug
            array( __CLASS__, 'dashboard_page' ),
            'dashicons-images-alt2',
            60
        );

        add_submenu_page(
            'wpio_dashboard',
            'General Settings',
            'General Settings',
            'manage_options',
            'wpio-settings',
            array( 'WPIO_Settings_Page', 'render' )
        );

        add_submenu_page(
            'wpio_dashboard',
            'Bulk Optimization',
            'Bulk Optimization',
            'manage_options',
            'wpio-bulk-optimization',
            array( 'WPIO_Bulk_Page', 'render' )
        );
    }

    public static function dashboard_page() {
        $logo_url = WPIO_URL . 'assets/images/logo.png';
        echo '<div class="wrap wpio-wrap">';
        echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr__( 'WPOptimizers Logo', 'wpoptimizers-image-optimizer-lite' ) . '" style="max-width:150px; margin-bottom:20px;">';
        echo '<h1>' . esc_html__( 'Welcome to WPOptimizers', 'wpoptimizers-image-optimizer-lite' ) . '</h1>';
        echo '<p>' . esc_html__( 'WPOptimizers - Image Optimizer Lite helps you automatically compress images on upload and bulk optimize your existing images to improve website speed and performance.', 'wpoptimizers-image-optimizer-lite' ) . '</p>';
        echo '<h2>' . esc_html__( 'What you can do:', 'wpoptimizers-image-optimizer-lite' ) . '</h2>';
        echo '<ul>';
        echo '<li>⚡ ' . esc_html__( 'Set JPEG quality and PNG compression in General Settings.', 'wpoptimizers-image-optimizer-lite' ) . '</li>';
        echo '<li>📦 ' . esc_html__( 'Bulk compress all previously uploaded images with progress tracking.', 'wpoptimizers-image-optimizer-lite' ) . '</li>';
        echo '<li>🚀 ' . esc_html__( 'Improve your page load times and overall website performance.', 'wpoptimizers-image-optimizer-lite' ) . '</li>';
        echo '</ul>';
        echo '<p>' . esc_html__( 'Use the tabs on the left sidebar to navigate to General Settings or Bulk Optimization.', 'wpoptimizers-image-optimizer-lite' ) . '</p>';
        echo '</div>';
    }

    /**
     * Add new column in Media Library
     */
    public static function add_optimization_column( $columns ) {
        $columns['wpio_optimization'] = __( 'Optimization', 'wpoptimizers-image-optimizer-lite' );
        return $columns;
    }

    /**
     * Render badge in custom column
     */
    public static function render_optimization_column( $column_name, $attachment_id ) {
        if ( $column_name === 'wpio_optimization' ) {
            $optimized = get_post_meta( $attachment_id, '_wpio_optimized', true );
            $savings   = get_post_meta( $attachment_id, '_wpio_savings', true );

            if ( $optimized ) {
                $label = esc_html__( 'Optimized ✅', 'wpoptimizers-image-optimizer-lite' );
                if ( $savings !== '' && $savings > 0 ) {
                    $label .= ' (' . intval( $savings ) . '% ' . esc_html__( 'smaller', 'wpoptimizers-image-optimizer-lite' ) . ')';
                }
                echo '<span style="color:green;font-weight:bold;">' . $label . '</span>';
            } else {
                echo '<span style="color:red;font-weight:bold;">' . esc_html__( 'Not optimized ❌', 'wpoptimizers-image-optimizer-lite' ) . '</span>';
            }
        }
    }
}

WPIO_Admin_Menu::init();
