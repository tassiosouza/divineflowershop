<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPIO_Ajax {

    public static function init() {
        add_action( 'wp_ajax_wpio_get_unoptimized', [__CLASS__, 'get_unoptimized'] );
        add_action( 'wp_ajax_wpio_optimize_next', [__CLASS__, 'optimize_next'] );
    }

    // Get all unoptimized images
    public static function get_unoptimized() {
        global $wpdb;

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID 
                 FROM {$wpdb->posts} p
                 LEFT JOIN {$wpdb->postmeta} pm 
                    ON p.ID = pm.post_id 
                    AND pm.meta_key = %s
                 WHERE p.post_type = %s 
                 AND p.post_mime_type IN (%s,%s)
                 AND pm.post_id IS NULL", // only those not optimized
                '_wpio_optimized',
                'attachment',
                'image/jpeg',
                'image/png'
            )
        );

        wp_send_json_success(['total' => count($ids)]);
    }

    // Optimize next image
    public static function optimize_next() {
        global $wpdb;

        $id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT p.ID 
                 FROM {$wpdb->posts} p
                 LEFT JOIN {$wpdb->postmeta} pm 
                    ON p.ID = pm.post_id 
                    AND pm.meta_key = %s
                 WHERE p.post_type = %s 
                 AND p.post_mime_type IN (%s,%s)
                 AND pm.post_id IS NULL
                 ORDER BY p.ID ASC
                 LIMIT 1",
                '_wpio_optimized',
                'attachment',
                'image/jpeg',
                'image/png'
            )
        );

        if ( $id ) {
            $file = get_attached_file($id);
            $opts = get_option('wpio_settings');
            $info = getimagesize($file);

            if ( $info && isset($info['mime']) ) {
                if($info['mime'] === 'image/jpeg') {
                    WPIO_Optimizer::compress_jpeg($file, $opts['jpeg_quality']);
                } elseif($info['mime'] === 'image/png') {
                    WPIO_Optimizer::compress_png($file, $opts['png_level']);
                }
            }

            // Mark as optimized ✅
            update_post_meta($id, '_wpio_optimized', 1);

            wp_send_json_success(['optimized_id' => $id]);
        }

        wp_send_json_error(['message' => 'No more images to optimize']);
    }
}

WPIO_Ajax::init();
