<?php
$max_compare = isset($nasa_opt['max_compare']) ? (int) $nasa_opt['max_compare'] : 4;
$nasa_compare_list = isset($nasa_compare_list) ? $nasa_compare_list : array();
$count_compare = count($nasa_compare_list);
$image_size = apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail');
?>
<div class="nasa-compare-list">
    <div class="row">
        <div class="large-12 columns">
            <table>
                <tr>
                    <td class="nasa-td-30">
                        <h5 class="clearfix text-center mobile-text-left rtl-mobile-text-right nasa-compare-label">
                            <span class="nasa-block mobile-text-center mobile-margin-top-15 mobile-margin-bottom-5 mobile-fs-23">
                                <?php echo esc_html__('Compare Products', 'elessi-theme'); ?>
                            </span>
                            <span class="color-gray hide-for-mobile">
                                (<?php echo $count_compare . ' ' . ($count_compare == 1 ? esc_html__('Product', 'elessi-theme') : esc_html__('Products', 'elessi-theme')); ?>)
                            </span>
                        </h5>
                    </td>
                    <td class="nasa-td-40 nasa-td-products-compare">
                        <div class="row padding-side-15 nasa-flex align-stretch jc">
                            <?php 
                            $k = 0;
                            $class_item = $max_compare == 4 ? 'large-3 small-3 columns' : 'large-4 small-4 columns';
                            if ($nasa_compare_list) :
                                foreach ($nasa_compare_list as $product) :
                                    if ($k > $max_compare - 1):
                                        break;
                                    endif;
                                    $productId = $product->get_id();
                                    $nasa_title = $product->get_name();
                                    $nasa_href = $product->get_permalink();
                                    ?>
                                    <div class="<?php echo esc_attr($class_item); ?>">
                                        <div class="nasa-compare-wrap-item">
                                            <div class="nasa-compare-item-hover">
                                                <div class="nasa-compare-item-hover-wraper">
                                                    <a href="<?php echo esc_url($nasa_href); ?>" title="<?php echo esc_attr($nasa_title); ?>">
                                                        <?php echo $product->get_image($image_size, array('alt' => esc_attr($nasa_title))); ?>
                                                        <h5 class="margin-top-10"><?php echo esc_html($nasa_title); ?></h5>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="nasa-compare-item">
                                                <a href="javascript:void(0);" class="nasa-remove-compare" data-prod="<?php echo esc_attr($productId); ?>" rel="nofollow"><svg width="19" height="19" viewBox="0 0 32 32" fill="currentColor"><path d="M10.722 9.969l-0.754 0.754 5.278 5.278-5.253 5.253 0.754 0.754 5.253-5.253 5.253 5.253 0.754-0.754-5.253-5.253 5.278-5.278-0.754-0.754-5.278 5.278z"/></svg></a>
                                                <a href="<?php echo esc_url($nasa_href); ?>" class="nasa-img-compare" title="<?php echo esc_attr($nasa_title); ?>">
                                                    <?php echo $product->get_image('thumbnail', array('alt' => esc_attr($nasa_title))); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                $k++;
                                endforeach; ?>
                            <?php endif; ?>

                            <?php if ($k < $max_compare) :
                                $nasa_src_no_image = wc_placeholder_img_src();
                                for ($i=$k; $i<$max_compare; $i++): ?>
                                    <div class="<?php echo esc_attr($class_item); ?>">
                                        <div class="nasa-compare-wrap-item">
                                            <div class="nasa-compare-item">
                                                <span class="nasa-no-image">
                                                    <img src="<?php echo esc_url($nasa_src_no_image); ?>" width="65" height="65" alt="<?php echo esc_attr__("Compare Product", 'elessi-theme'); ?>" />
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="nasa-td-30">
                        <div class="ns-compare-btns nasa-compare-label<?php echo !$count_compare ? ' hidden-tag' : ''; ?>">
                            <a class="nasa-compare-clear-all nasa-hover-underline color-gray" href="javascript:void(0);" title="<?php echo esc_attr__('Clear All', 'elessi-theme'); ?>" rel="nofollow"><?php echo esc_html__('Clear All', 'elessi-theme'); ?></a>
                            <a class="nasa-compare-view btn button" href="<?php echo esc_url($view_href); ?>" title="<?php echo esc_attr__("Let's Compare!", 'elessi-theme'); ?>"><?php echo esc_html__("Let's Compare!", 'elessi-theme'); ?></a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <a class="nasa-close-mini-compare nasa-stclose" href="javascript:void(0)" rel="nofollow"></a>
</div>
