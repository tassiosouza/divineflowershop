<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPIO_Optimizer {

    // Run on image upload
    public static function optimize_on_upload( $metadata, $attachment_id ) {
        $file    = get_attached_file( $attachment_id );
        $options = get_option( 'wpio_settings' );

        if ( file_exists( $file ) ) {
            $info = getimagesize( $file );
            if ( $info && isset( $info['mime'] ) ) {
                if ( $info['mime'] === 'image/jpeg' ) {
                    self::compress_jpeg( $file, $options['jpeg_quality'] );
                } elseif ( $info['mime'] === 'image/png' ) {
                    self::compress_png( $file, $options['png_level'] );
                }
            }
        }

        // ⚠️ Yahan meta flag set nahi karna
        // taki bulk tab me wo "needs optimization" dikhaye
        return $metadata;
    }

    // Bulk optimization ke liye use hone wala function
    public static function optimize_attachment( $attachment_id ) {
        $file    = get_attached_file( $attachment_id );
        $options = get_option( 'wpio_settings' );

        if ( file_exists( $file ) ) {
            $info = getimagesize( $file );
            if ( $info && isset( $info['mime'] ) ) {
                if ( $info['mime'] === 'image/jpeg' ) {
                    self::compress_jpeg( $file, $options['jpeg_quality'] );
                } elseif ( $info['mime'] === 'image/png' ) {
                    self::compress_png( $file, $options['png_level'] );
                }
            }

            // ✅ Ab meta flag set karna (sirf bulk process me)
            update_post_meta( $attachment_id, '_wpio_optimized', 1 );
        }
    }

    public static function compress_jpeg( $file, $quality ) {
        $img = @imagecreatefromjpeg( $file );
        if ( $img ) {
            imagejpeg( $img, $file, intval( $quality ) );
            imagedestroy( $img );
        }
    }

    public static function compress_png( $file, $level ) {
        $img = @imagecreatefrompng( $file );
        if ( $img ) {
            imagepng( $img, $file, intval( $level ) );
            imagedestroy( $img );
        }
    }
}

// Hook upload
add_filter( 'wp_generate_attachment_metadata', ['WPIO_Optimizer','optimize_on_upload'], 10, 2 );
