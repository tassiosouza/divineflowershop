<?php

/**
 * Review order table
 * 
 * @author  NasaTheme
 * @package Elessi-theme/WooCommerce
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

global $nasa_opt;
?>

<table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
        <tr>
            <th class="product-name" ><?php esc_html_e('Product', 'elessi-theme'); ?></th>
            <th class="product-total"><?php esc_html_e('Subtotal', 'elessi-theme'); ?></th>
        </tr>
    </thead>
    
    <tbody>
        <?php
        do_action('woocommerce_review_order_before_cart_contents');
        
        $cart = WC()->cart->get_cart();
        
        foreach ($cart as $cart_item_key => $cart_item) :
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) :
                
                $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); 
                ?>
                <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                    <td class="product-name" colspan="2">
                        <div class="co-wrap-item">
                            <?php if (!isset($nasa_opt['mini_checkout_img']) || $nasa_opt['mini_checkout_img']) : ?>
                                <div class="co-wrap-img margin-right-15 rtl-margin-right-0 rtl-margin-left-15 nasa-relative">
                                    <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail'), $cart_item, $cart_item_key); ?>

                                    <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', '<span class="product-quantity quantity-review-item">' . sprintf('%s', $cart_item['quantity']) . '</span>', $cart_item, $cart_item_key); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="co-wrap-info nasa-flex flex-wrap jbw align-start">
                                <span class="co-product-name">
                                    <?php
                                    echo $product_name;
                                    
                                    if (isset($nasa_opt['mini_checkout_img']) && !$nasa_opt['mini_checkout_img'] && (!isset($nasa_opt['mini_checkout_qty']) || !$nasa_opt['mini_checkout_qty'])) :
                                        echo apply_filters('woocommerce_checkout_cart_item_quantity', '<strong class="product-quantity">' . sprintf('&nbsp;&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key );
                                    endif;
                                    ?>
                                </span>
                                
                                <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                
                                <?php
                                if (isset($nasa_opt['mini_checkout_qty']) && $nasa_opt['mini_checkout_qty']) :
                                    if ($_product->is_sold_individually()) :
                                        $min_quantity = 1;
                                        $max_quantity = 1;
                                    else :
                                        $min_quantity = 0;
                                        $max_quantity = $_product->get_max_purchase_quantity();
                                    endif;
                                    
                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => 'cart[' . $cart_item_key . '][qty]',
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $product_name
                                        ),
                                        $_product,
                                        false
                                    );

                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                                endif;
                                ?>

                                <div class="product-total">
                                    <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <?php
                
            endif;
        endforeach;

        do_action('woocommerce_review_order_after_cart_contents');
        ?>
    </tbody>
    
    <tfoot>
        <?php do_action('nasa_review_order_before_cart_subtotal');?>

        <tr class="cart-subtotal">
            <th><?php esc_html_e('Subtotal', 'elessi-theme'); ?></th>
            <td ><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <?php $coupons = WC()->cart->get_coupons(); ?>
        <?php if ($coupons): ?>
            <?php foreach ($coupons as $code => $coupon) : ?>
                <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                    <td ><?php wc_cart_totals_coupon_html($coupon); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
            <?php do_action('woocommerce_review_order_before_shipping'); ?>
            
            <?php if (!defined('NASA_CHECKOUT_LAYOUT') || NASA_CHECKOUT_LAYOUT != 'modern') : ?>
                <?php wc_cart_totals_shipping_html(); ?>
            <?php else : ?>
                <tr class="order-shipping-modern hidden-tag"><th></th><td ></td></tr>
            <?php endif; ?>
            
            <?php do_action('woocommerce_review_order_after_shipping'); ?>
        <?php endif; ?>

        <?php $fees = WC()->cart->get_fees(); ?>
        <?php if ($fees) : ?>
            <?php foreach ($fees as $fee) : ?>
                <tr class="fee">
                    <th><?php echo esc_html($fee->name); ?></th>
                    <td ><?php wc_cart_totals_fee_html($fee); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                <?php
                $taxs = WC()->cart->get_tax_totals();
                foreach ($taxs as $code => $tax) : ?>
                    <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                        <th><?php echo esc_html($tax->label); ?></th>
                        <td ><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="tax-total">
                    <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                    <td ><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action('woocommerce_review_order_before_order_total'); ?>

        <tr class="order-total">
            <th><?php esc_html_e('Total', 'elessi-theme'); ?></th>
            <td ><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action('woocommerce_review_order_after_order_total'); ?>

    </tfoot>
</table>
