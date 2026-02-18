<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPIO_Bulk_Page {

    public static function render() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Bulk Image Optimization', 'wpoptimizers-image-optimizer-lite'); ?></h1>
            <p id="wpio-status"><?php echo esc_html__('Checking images...', 'wpoptimizers-image-optimizer-lite'); ?></p>
            <button id="wpio-start" class="button button-primary" disabled><?php echo esc_html__('Start Optimization', 'wpoptimizers-image-optimizer-lite'); ?></button>
            <div id="wpio-progress" style="margin-top:20px; width:100%; background:#eee; height:25px; border-radius:5px;">
                <div id="wpio-bar" style="width:0%; height:100%; background:#2271b1; color:#fff; text-align:center; line-height:25px; border-radius:5px;">0%</div>
            </div>
        </div>
        <?php
    }
}
