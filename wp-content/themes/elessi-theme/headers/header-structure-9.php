<?php defined('ABSPATH') or die(); // Exit if accessed directly 
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

            <div class="row">
                <div class="large-12 columns header-container">
                    <div class="nasa-hide-for-mobile nasa-wrap-event-search">
                        <div class="nasa-relative nasa-header-flex nasa-elements-wrap nasa-wrap-width-main-menu jbw">

                            <!-- Group icon header -->
                            <div class="order-3 icons-wrapper">
                                <?php echo $nasa_header_icons; ?>
                            </div>

                            <!-- Logo -->
                            <div class="order-2 logo-wrapper fjct">
                                <?php echo elessi_logo(); ?>
                            </div>

                            <!-- Main menu -->
                            <div class="wide-nav order-1 nasa-bg-wrap<?php echo esc_attr($menu_warp_class); ?>">
                                <div class="nasa-menus-wrapper nasa-menus-wrapper-reponsive nasa-loading" data-padding_x="<?php echo (int) $data_padding_x; ?>">
                                    <?php elessi_get_main_menu(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Search form in header -->
                        <div class="nasa-header-search-wrap nasa-hide-for-mobile">
                            <?php echo elessi_search('icon'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (defined('NASA_TOP_FILTER_CATS') && NASA_TOP_FILTER_CATS) : ?>
                <div class="nasa-top-cat-filter-wrap">
                    <?php echo elessi_get_all_categories(false, true); ?>
                    <a href="javascript:void(0);" title="<?php esc_attr_e('Close', 'elessi-theme'); ?>" class="nasa-close-filter-cat nasa-stclose nasa-transition" rel="nofollow"></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>