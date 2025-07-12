<?php
/**
 * Custom override: Display the Dropdown with Subscriptions plans in a button layout
 *
 * @package Buy Once or Subscribe for WooCommerce Subscriptions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Calculate prices and discount for display
$one_time_price = isset($product) ? wc_price($product->get_price()) : '';
$subscribe_price = '';
$subscribe_discount = '';
if (isset($plan_options) && is_array($plan_options) && count($plan_options) > 0) {
    $plan = $plan_options[0];
    $product_price = BOS4W_Front_End::bos4w_get_product_price($product);
    $use_fixed_price = $product->get_meta('_bos4w_use_fixed_price');
    if ($use_fixed_price) {
        $subscription_price = !empty($plan['subscription_price']) ? floatval($plan['subscription_price']) : 0;
        $subscribe_price = wc_price(wc_format_decimal($product_price, wc_get_price_decimals()) - $subscription_price);
        $subscribe_discount = $subscription_price > 0 && $product_price > 0 ? round(($subscription_price / $product_price) * 100) : 0;
    } else {
        $subscription_discount = !empty($plan['subscription_discount']) ? floatval($plan['subscription_discount']) : 0;
        $subscribe_discount = $subscription_discount;
        $subscribe_price = wc_price(wc_format_decimal($product_price, wc_get_price_decimals()) - ($product_price * ($subscription_discount / 100)));
    }
}
?>
<div class="bos4w-custom-subscribe-wrap" style="margin-bottom: 2em;">
    <!-- Quantity selector above purchase options -->
    <!-- Removed duplicate quantity selector -->
    <div class="bos4w-custom-plan-buttons">
        <label for="bos4w-one-time" class="bos4w-custom-btn bos4w-custom-btn-onetime">
            <input id="bos4w-one-time" name="bos4w-purchase-type" value="0" type="radio" class="bos4w-buy-type" checked style="display:none;"/>
            <div class="bos4w-btn-title">One-time purchase</div>
            <div class="bos4w-btn-price"><?php echo $one_time_price; ?></div>
        </label>
        <label for="bos4w-subscribe-to" class="bos4w-custom-btn bos4w-custom-btn-subscribe">
            <input id="bos4w-subscribe-to" name="bos4w-purchase-type" value="1" type="radio" class="bos4w-buy-type" style="display:none;"/>
            <div class="bos4w-btn-title">Subscribe &amp; save <?php echo $subscribe_discount; ?>%</div>
            <div class="bos4w-btn-price"><?php echo $subscribe_price; ?></div>
        </label>
    </div>
    <div id="bos4w-delivery-select-wrap" class="bos4w-custom-delivery-select" style="max-width:350px; display:none;">
        <label for="bos4w-dropdown-plan" class="bos4w-delivery-label">Delivery</label>
        <select id="bos4w-dropdown-plan" name="convert_to_sub_plan_<?php echo absint( $product_id ); ?>" class="bos4w-delivery-select">
            <?php
            $first = true;
            if ( $plan_options ) {
                foreach ( $plan_options as $plan ) {
                    $period_interval = isset( $plan['subscription_period_interval'] ) && isset( $plan['subscription_period'] ) ? wcs_get_subscription_period_strings( $plan['subscription_period_interval'], $plan['subscription_period'] ) : '';
                    $selected = $first ? 'selected' : '';
                    echo '<option value="' . esc_attr( $plan['subscription_period_interval'] ) . '_' . esc_attr( $plan['subscription_period'] ) . '" ' . $selected . '>Delivery every ' . esc_html( $period_interval ) . '</option>';
                    $first = false;
                }
            }
            ?>
        </select>
    </div>
</div>
<style>
/* Quantity Selector */
.bos4w-custom-qty {
    margin-bottom: 1.5em;
    max-width: 180px;
}
.woocommerce .quantity.bos4w-qty-input {
    display: flex;
    align-items: center;
    border: 1px solid #bdb3b3;
    border-radius: 8px;
    overflow: hidden;
    width: 100%;
    background: #fff;
}
.woocommerce .quantity.bos4w-qty-input input.qty {
    border: none;
    text-align: center;
    width: 50px;
    font-size: 1.3em;
    background: #fff;
    box-shadow: none;
}
.woocommerce .quantity.bos4w-qty-input button, .woocommerce .quantity.bos4w-qty-input input[type="button"] {
    background: none;
    border: none;
    color: #7a6f6f;
    font-size: 1.5em;
    width: 40px;
    height: 40px;
    cursor: pointer;
    transition: background 0.2s;
}
.woocommerce .quantity.bos4w-qty-input button:hover {
    background: #f7f7f7;
}

/* Plan Buttons */
.bos4w-custom-plan-buttons {
    display: flex;
    gap: 0;
    margin-bottom: 1.5em;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 8px #0001;
}
.bos4w-custom-btn {
    flex: 1;
    padding: 1.2em 0.5em;
    text-align: center;
    cursor: pointer;
    font-weight: 500;
    border: 2px solid #f47c7c;
    font-size: 1.1em;
    transition: background 0.2s, color 0.2s;
    border-radius: 0;
    background: #fff;
    color: #f47c7c;
    position: relative;
    user-select: none;
}
.bos4w-custom-btn-onetime {
    border-right: none;
    border-radius: 14px 0 0 14px;
}
.bos4w-custom-btn-subscribe {
    background: #f47c7c;
    color: #fff;
    border-radius: 0 14px 14px 0;
}
.bos4w-custom-btn input[type="radio"]:checked + .bos4w-btn-title,
.bos4w-custom-btn input[type="radio"]:checked + .bos4w-btn-price {
    font-weight: bold;
}
.bos4w-custom-btn.selected, .bos4w-custom-btn:has(input[type="radio"]:checked) {
    background: #f47c7c;
    color: #fff;
}
.bos4w-custom-btn.selected.bos4w-custom-btn-onetime, .bos4w-custom-btn-onetime:has(input[type="radio"]:checked) {
    background: #fff;
    color: #f47c7c;
    border: 2px solid #f47c7c;
}
.bos4w-btn-title {
    font-size: 1.1em;
    margin-bottom: 0.2em;
}
.bos4w-btn-price {
    font-size: 2em;
    font-weight: bold;
}

/* Delivery Dropdown */
.bos4w-custom-delivery-select {
    margin-top: 1.2em;
}
.bos4w-delivery-label {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 0.5em;
    display: block;
}
.bos4w-delivery-select {
    width: 100%;
    padding: 0.7em;
    font-size: 1.1em;
    border-radius: 8px;
    border: 1px solid #ccc;
    box-shadow: 0 2px 8px #0001;
}
</style>
<script>
(function() {
    function updateDeliveryVisibility() {
        var subscribeRadio = document.getElementById('bos4w-subscribe-to');
        var deliveryWrap = document.getElementById('bos4w-delivery-select-wrap');
        if (subscribeRadio && deliveryWrap) {
            deliveryWrap.style.display = subscribeRadio.checked ? 'block' : 'none';
        }
    }
    function updateButtonSelection() {
        var radios = document.querySelectorAll('input[name="bos4w-purchase-type"]');
        radios.forEach(function(radio) {
            var label = radio.closest('label');
            if (label) {
                if (radio.checked) {
                    label.classList.add('selected');
                } else {
                    label.classList.remove('selected');
                }
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var radios = document.querySelectorAll('input[name="bos4w-purchase-type"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                updateDeliveryVisibility();
                updateButtonSelection();
            });
        });
        updateDeliveryVisibility();
        updateButtonSelection();
    });
})();
</script>
