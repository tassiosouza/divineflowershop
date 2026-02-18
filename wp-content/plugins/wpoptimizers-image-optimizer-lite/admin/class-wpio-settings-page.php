<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPIO_Settings_Page {

    public static function render() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('General Settings', 'wpoptimizers-image-optimizer-lite'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpio_settings_group');
                do_settings_sections('wpio-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Register settings
add_action('admin_init', function(){

    register_setting('wpio_settings_group','wpio_settings', [
        'sanitize_callback' => function($input){
            $output = [];
            $output['jpeg_quality'] = isset($input['jpeg_quality']) ? max(0, min(100, intval($input['jpeg_quality']))) : 75;
            $output['png_level']    = isset($input['png_level']) ? max(0, min(9, intval($input['png_level']))) : 6;
            return $output;
        }
    ]);

    add_settings_section(
        'wpio_main',
        __('Image Optimization Settings', 'wpoptimizers-image-optimizer-lite'),
        null,
        'wpio-settings'
    );

    // JPEG Quality Field
    add_settings_field('jpeg_quality', __('JPEG Quality (0-100)', 'wpoptimizers-image-optimizer-lite'), function(){
        $opt = get_option('wpio_settings');
        $val = isset($opt['jpeg_quality']) ? intval($opt['jpeg_quality']) : 75;
        echo "<input type='number' name='wpio_settings[jpeg_quality]' value='" . esc_attr($val) . "' min='0' max='100'>";
        echo "<p class='description'>" . esc_html__('⚡ Set the image quality for JPEGs. Lower value = smaller file size but lower visual quality. Recommended: 70–85.', 'wpoptimizers-image-optimizer-lite') . "</p>";
    }, 'wpio-settings', 'wpio_main');

    // PNG Compression Field
    add_settings_field('png_level', __('PNG Compression (0-9)', 'wpoptimizers-image-optimizer-lite'), function(){
        $opt = get_option('wpio_settings');
        $val = isset($opt['png_level']) ? intval($opt['png_level']) : 6;
        echo "<input type='number' name='wpio_settings[png_level]' value='" . esc_attr($val) . "' min='0' max='9'>";
        echo "<p class='description'>" . esc_html__('📦 Set the compression level for PNG images. 0 = no compression, 9 = maximum compression. Recommended: 5–7.', 'wpoptimizers-image-optimizer-lite') . "</p>";
    }, 'wpio-settings', 'wpio_main');

});
