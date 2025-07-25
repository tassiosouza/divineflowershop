<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optimize html
 */
$tmpl = isset($nasa_opt['tmpl_html']) && $nasa_opt['tmpl_html'] ? 'template' : 'div';

$max_qty = isset($bulk_discount['max']) ? $bulk_discount['max'] : 0;
$rules = isset($bulk_discount['rules']) ? $bulk_discount['rules'] : array();

if ($max_qty && !empty($rules)) :
    $suffix = $product->get_price_suffix();
    
    $price_org = floatval(wc_get_price_to_display($product));
    $price_org_html = wc_price($price_org) . $suffix;
    
    $allowed_html = array(
        'i' => array()
    );

    $shows = array();

    foreach ($rules as $rule) :
        $qty = floatval($rule['qty']);
        $dsct = floatval($rule['dsct']);
        
        if ($discount_type == 'flat') :
            /**
             * Compatible - WPML - WooCommerce Multilingual
             */
            if (isset($wpml_multi_currencies) && $wpml_multi_currencies) {
                $dsct = $wpml_multi_currencies->convert_price_amount($dsct);
            }
            
            $dsct = $dsct_obj->convert_price($dsct);
            $new_price = $price_org - $dsct;
            $new_price_html = wc_price($new_price) . $suffix;
            $save_per = floor($dsct / $price_org * 100);

            $dsct_txt = '<span class="nasa-bold">' . sprintf(_n('Buy&nbsp;%s&nbsp;item&nbsp;get&nbsp;', 'Buy&nbsp;%s&nbsp;items&nbsp;get&nbsp;', $qty, 'nasa-core'), $qty) . '<span class="primary-color">' . esc_html__($save_per.'&#37;&nbsp;OFF', 'nasa-core') . '</span></span>';  

            $dsct_txt .= '<br /><span class="ev-dsc-qty-note">' . esc_html__('on&nbsp;each&nbsp;product', 'nasa-core') . '</span>';
            
            if ($qty > 1) :
                
                $tmp = '<' . $tmpl . ' class="tmp-content hidden-tag">';
                
                $tmp .= '<span class="bulk-price">';
                
                $tmp .= $new_price_html . '<span class="bulk-after-price">' . esc_html__('&nbsp;each', 'nasa-core') . '</span>&nbsp;<del class="old-price-note">' . $price_org_html . '</del>';
                
                $tmp .= '<span class="bulk-desc">';
                
                $tmp .= '<span class="save-note">' .
                    sprintf(
                        wp_kses(__('Save&nbsp;%s&nbsp;<i>(%s&#37;&nbsp;OFF)</i>', 'nasa-core'), $allowed_html),
                        wc_price($dsct),
                        $save_per
                    ) .
                '</span>';
                
                $tmp .= '</span>';
                $tmp .= '</span>';
                
                $tmp .= '</' . $tmpl . '>';

                $dsct_txt .= $tmp;
            endif;
            
        else :
            
            $dsct_txt = '<span class="nasa-bold">' . sprintf(_n('Buy&nbsp;%s&nbsp;item&nbsp;get&nbsp;', 'Buy&nbsp;%s&nbsp;items&nbsp;get&nbsp;', $qty, 'nasa-core'), $qty) . '<span class="primary-color">' . esc_html__($dsct.'&#37;&nbsp;OFF', 'nasa-core') . '</span></span>';  

            $dsct_txt .= '<br /><span class="ev-dsc-qty-note">' . esc_html__('on&nbsp;each&nbsp;product', 'nasa-core') . '</span>';
            
            if ($qty > 1) :
                $new_price = $price_org - ($price_org * $dsct / 100);
                $new_price_html = wc_price($new_price) . $suffix;
                
                $tmp = '<' . $tmpl . ' class="tmp-content hidden-tag">';

                $tmp .= '<span class="bulk-price">';
                
                $tmp .= $new_price_html . '<span class="bulk-after-price">' . esc_html__('&nbsp;each', 'nasa-core') . '</span>&nbsp;<del class="old-price-note">' . $price_org_html . '</del>';

                $save_per = $dsct;
                
                $tmp .= '<span class="bulk-desc">';
                
                $tmp .= '<span class="save-note">' .
                    sprintf(
                        wp_kses(__('Save&nbsp;%s&nbsp;<i>(%s&#37;&nbsp;OFF)</i>', 'nasa-core'), $allowed_html),
                        wc_price($price_org - $new_price),
                        $save_per
                    ) .
                '</span>';
                
                $tmp .= '</span>';
                $tmp .= '</span>';
                
                $tmp .= '</' . $tmpl . '>';
                
                $dsct_txt .= $tmp;
            endif;
        endif;

        $shows[] = array(
            'qty' => $qty,
            'dsct' => $dsct_txt
        );
        
    endforeach;

    if (!empty($shows)) :
        $allowed_html = array(
            'strong' => array()
        );
        ?>
        <div class="nasa-dsc-type-2 nasa-dsc-wrap nasa-not-in-sticky">
            <div class="dsc-label">
                <svg width="20" height="25" viewBox="0 0 30 32" fill="currentColor"><path d="M27.787 14.933h-0.587c-0.8-2.080-2.4-3.84-4.533-5.12 0-2.827 0.267-3.467 0.853-5.28-2.187 0.32-4.373 1.653-5.493 3.573-0.747-0.16-1.493-0.213-2.24-0.267 0.107-0.427 0.213-0.907 0.213-1.44 0-2.667-2.133-4.8-4.8-4.8s-4.8 2.133-4.8 4.8c0 1.333 0.533 2.507 1.44 3.413-2.080 1.227-3.68 2.933-4.533 4.96-1.44-0.533-2.24-1.333-2.24-2.347 0-1.12 0.907-2.133 1.707-2.4l-0.32-1.013c-1.333 0.373-2.453 1.867-2.453 3.307 0 0.853 0.373 2.453 2.933 3.413-0.213 0.693-0.267 1.387-0.267 2.133 0 2.667 1.333 5.12 3.467 6.933l-0.64 1.867c-0.427 1.28 0.213 2.613 1.493 3.040l1.493 0.533c0.267 0.107 0.533 0.16 0.8 0.16 1.013 0 1.92-0.64 2.293-1.6l0.427-1.227c1.067 0.213 2.133 0.373 3.307 0.373 0.747 0 1.493-0.053 2.187-0.16l0.64 1.333c0.427 0.853 1.28 1.333 2.187 1.333 0.373 0 0.693-0.053 1.067-0.267l1.387-0.693c1.173-0.587 1.707-2.027 1.12-3.2l-0.373-0.8c1.44-0.96 2.56-2.187 3.36-3.627h1.067c1.44 0 2.613-1.173 2.613-2.613v-1.707c-0.16-1.44-1.333-2.613-2.773-2.613zM7.467 6.4c0-2.080 1.653-3.733 3.733-3.733s3.733 1.653 3.733 3.733c0 0.533-0.107 1.013-0.32 1.493-2.133 0.053-4.107 0.587-5.813 1.387-0.8-0.693-1.333-1.707-1.333-2.88zM29.333 19.253c0 0.853-0.693 1.547-1.547 1.547h-1.707l-0.32 0.533c-0.693 1.227-1.707 2.347-2.987 3.253l-0.747 0.533 0.427 0.8 0.373 0.8c0.32 0.64 0.053 1.493-0.64 1.813l-1.387 0.64c-0.16 0.107-0.373 0.16-0.587 0.16-0.533 0-0.96-0.267-1.227-0.747l-0.64-1.333-0.32-0.693-0.8 0.107c-0.693 0.107-1.387 0.16-2.027 0.16-1.013 0-2.080-0.107-3.093-0.32l-0.907-0.213-0.267 0.907-0.427 1.227c-0.213 0.533-0.693 0.907-1.28 0.907-0.16 0-0.32 0-0.427-0.053l-1.493-0.533c-0.693-0.267-1.067-1.013-0.8-1.707l0.64-1.92 0.267-0.693-0.587-0.48c-2.027-1.653-3.093-3.787-3.093-6.080 0-4.907 5.173-8.96 11.467-8.96 0.853 0 1.76 0.107 2.613 0.213l0.747 0.16 0.373-0.64c0.64-1.12 1.76-2.027 2.987-2.56-0.213 0.907-0.32 1.92-0.373 3.733v0.587l0.533 0.32c1.92 1.173 3.413 2.773 4.107 4.587l0.267 0.693h1.333c0.853 0 1.547 0.693 1.547 1.547v1.707z"/><path d="M23.467 15.733c0 0.736-0.597 1.333-1.333 1.333s-1.333-0.597-1.333-1.333c0-0.736 0.597-1.333 1.333-1.333s1.333 0.597 1.333 1.333z"/></svg>
                <span><?php echo wp_kses(__('<strong>Bulk Savings</strong>&nbsp;(Buy more save more)', 'nasa-core'), $allowed_html); ?></span>
            </div>
            
            <div class="dsc-flex-column nasa-crazy-box">
                <?php foreach ($shows as $show) :?>
                    <div class="dsc-flex-column ev-dsc-qty-wrap nasa-flex">
                        <a href="javascript:void(0);" data-qty="<?php echo esc_attr($show['qty']); ?>" class="ev-dsc-qty ev-dsc-qty-type-2" rel="nofollow">
                            <?php echo $show['dsct']; ?>
                        </a>

                        <a href="javascript:void(0);" class="ev-dsc-quick-add button" rel="nofollow">
                            <?php echo esc_html__('Add', 'nasa-core') ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    endif;
endif;
