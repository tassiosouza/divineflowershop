<?php
$number_slide = (isset($nasa_opt['number_post_slide']) && (int) $nasa_opt['number_post_slide']) ? (int) $nasa_opt['number_post_slide'] : 1;

$style_bg = (isset($nasa_opt['background_area']) && $nasa_opt['background_area']) ? 'background: url(\'' . $nasa_opt['background_area'] . '\') center center no-repeat; background-size: cover;' : '';

$promo_slide = isset($nasa_opt['enable_promo_slide']) && $nasa_opt['enable_promo_slide'] ? 'nasa-promo-silde slick-slider' : '';

$promo_slide .= isset($nasa_opt['promo_slide_direction']) && $nasa_opt['promo_slide_direction'] != '' ? ' nasa-promo-silde-'.$nasa_opt['promo_slide_direction'] : ' nasa-promo-silde-vertical';

$style_bg = ($style_bg != '') ? ' style="' . esc_attr($style_bg) . '"' : '';

$style_color = (isset($nasa_opt['t_promotion_color']) && $nasa_opt['t_promotion_color']) ? 'color:' . $nasa_opt['t_promotion_color'] : '';

$style_color = ($style_color != '') ? ' style="' . esc_attr($style_color) . '"' : '';
?>

<div class="section-element nasa-promotion-news nasa-hide">
    <div class="nasa-wapper-promotion">
        <div class="nasa-content-promotion-news <?php echo (!isset($nasa_opt['enable_fullwidth']) || $nasa_opt['enable_fullwidth'] == 1) ? 'nasa-row fullwidth' : 'row'; ?>"<?php echo $style_bg; ?>>
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Close', 'elessi-theme'); ?>" class="nasa-promotion-close nasa-stclose bg-white" rel="nofollow"></a>

            <?php if ($content): ?>
                <div class="nasa-content-promotion-custom <?php echo $promo_slide; ?>"<?php echo $style_color; ?>>
                    <?php echo do_shortcode($content); ?>
                </div>
            <?php elseif (!empty($posts)): ?>
                <div class="nasa-post-slider hidden-tag" data-autoplay="true" data-columns="<?php echo esc_attr($number_slide); ?>" data-columns-small="1" data-columns-tablet="1" data-switch-tablet="<?php echo elessi_switch_tablet(); ?>" data-switch-desktop="<?php echo elessi_switch_desktop(); ?>">
                    <?php foreach ($posts as $v): ?>
                        <div class="nasa-post-slider-item">
                            <a href="<?php echo esc_url(get_permalink($v->ID)); ?>" title="<?php echo esc_attr($v->post_title); ?>"<?php echo $style_color; ?>><?php echo $v->post_title; ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="nasa-position-relative nasa-promo-bg"></div>

<a href="javascript:void(0);" title="<?php echo esc_attr__('Show', 'elessi-theme'); ?>" class="nasa-promotion-show" rel="nofollow">
    <svg width="20" height="20" viewBox="0 0 32 32" stroke-width=".5" stroke="currentColor">
        <path d="M15.233 19.175l0.754 0.754 6.035-6.035-0.754-0.754-5.281 5.281-5.256-5.256-0.754 0.754 3.013 3.013z" fill="currentColor" />
    </svg>
</a>
