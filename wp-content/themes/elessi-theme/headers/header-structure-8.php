<?php
defined('ABSPATH') or die(); // Exit if accessed directly
$beside_mm = false;
$popup_block = false;

if (!isset($nasa_opt['nasa_in_mobile']) || !$nasa_opt['nasa_in_mobile']) :
    $beside_mm = isset($nasa_opt['header-block-type_4']) && $nasa_opt['header-block-type_4'] ? elessi_get_block($nasa_opt['header-block-type_4']) : '';
endif;

if (!isset($nasa_opt['nasa_in_mobile']) || !$nasa_opt['nasa_in_mobile']) :
    $popup_block = isset($nasa_opt['nasa_popup_static_block']) && $nasa_opt['nasa_popup_static_block'] ? elessi_get_block($nasa_opt['nasa_popup_static_block']) : '';
endif;

$block_custom = elessi_block_beside_header();
$beside_mm = $block_custom != '' && $block_custom != false ? $block_custom : $beside_mm;

$popup_block_custom = elessi_static_block_popup();
$popup_block = $popup_block_custom != '' && $popup_block_custom != false ? $popup_block_custom : $popup_block;

$menu_warp_class .= $beside_mm ? ' nasa-flex jbw align-stretch' : '';
?>

<div class="<?php echo esc_attr($header_classes); ?>">
    <?php
    /**
     * Hook - top bar header
     */
    do_action('nasa_topbar_header');
    ?>
    
    <div class="sticky-wrapper">
        <div id="masthead" class="site-header">
            <?php do_action('nasa_mobile_header'); ?>
            
            <div class="row nasa-hide-for-mobile">
                <div class="large-12 columns nasa-wrap-event-search">
                    <div class="nasa-header-flex nasa-elements-wrap jbw">
                        <!-- Logo -->
                        <div class="logo-wrapper">
                            <?php echo elessi_logo(); ?>
                        </div>

                        <!-- Search form in header -->
                        <div class="fgr-2 nasa-header-search-wrap nasa-search-relative margin-right-80 margin-left-80">
                            <?php echo elessi_search('full'); ?>
                        </div>
                        
                        <!-- Group icon header -->
                        <div class="icons-wrapper">
                            <?php echo $nasa_header_icons; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main menu -->
            <div class="nasa-elements-wrap nasa-elements-wrap-main-menu nasa-hide-for-mobile nasa-elements-wrap-bg">
                <div class="row">
                    <div class="large-12 columns">
                        <div class="wide-nav nasa-wrap-width-main-menu nasa-bg-wrap<?php echo esc_attr($menu_warp_class); ?>">
                            <div class="nasa-menus-wrapper nasa-menus-wrapper-reponsive nasa-loading nasa-flex" data-padding_x="<?php echo (int) $data_padding_x; ?>">
                                <?php
                                /**
                                 * Vertical Menu
                                 */
                                elessi_get_vertical_menu();
                                
                                /**
                                 * Main Menu
                                 */
                                elessi_get_main_menu();
                                ?>
                            </div>
                            
                            <?php if ($beside_mm) : ?>
                                <div class="nasa-beside-mm hide-for-small hide-for-medium <?php echo ($popup_block != '' && $popup_block != false) ? esc_attr('ns-toggle-popup-static-block') : '';?>">
                                    <?php echo $beside_mm; ?>
                                    <?php if ($popup_block) : ?>
                                        <div class="hide-for-small hide-for-medium ns-static-block-popup">
                                            <?php echo $popup_block; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
