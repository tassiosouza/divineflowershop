<?php
/**
 * Display the Dropdown with Subscriptions plans (merged: new plugin logic + custom button layout)
 *
 * @package Buy Once or Subscribe for WooCommerce Subscriptions
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render only the amount HTML for a given number in the context of a product,
 * respecting tax display and currency, but avoiding extra filters that may
 * append additional prices (e.g., Wholesale Prices).
 */
if ( ! function_exists( 'bos4w_only_amount_html' ) ) {
	function bos4w_only_amount_html( $amount, WC_Product $context_product ) : string {
		$raw     = (float) wc_format_decimal( (float) $amount, wc_get_price_decimals() );
		$display = wc_get_price_to_display( $context_product, array( 'price' => $raw ) );
		$symbol  = get_woocommerce_currency_symbol();
		$number  = wc_format_localized_price( $display );
		return '<span class="woocommerce-Price-amount amount"><bdi>' . $symbol . $number . '</bdi></span>';
	}
}

// Compute prices for custom button display (one-time + subscribe)
$one_time_price_raw = isset( $product ) ? (float) wc_format_decimal( $product->get_price(), wc_get_price_decimals() ) : 0;
$one_time_price_html = isset( $product ) ? bos4w_only_amount_html( $one_time_price_raw, $product ) : '';
$subscribe_price_raw = 0;
$subscribe_price_html = '';
$subscription_discount_percentage = 0;
$subscription_fixed_discount = 0;
$use_fixed_price = false;

if ( isset( $plan_options ) && is_array( $plan_options ) && count( $plan_options ) > 0 ) {
	// First plan: for variations, plan_options is keyed by variation_id; for simple, numeric array of plans
	$first_plan = null;
	if ( isset( $plan_type ) && 'variation' === $plan_type ) {
		$first_variation_plans = reset( $plan_options );
		$first_plan = is_array( $first_variation_plans ) ? reset( $first_variation_plans ) : $first_variation_plans;
	} else {
		$first_plan = isset( $plan_options[0] ) ? $plan_options[0] : reset( $plan_options );
	}
	if ( is_array( $first_plan ) ) {
		$product_price = BOS4W_Front_End::bos4w_get_product_price( $product );
		$use_fixed_price = $product->get_meta( '_bos4w_use_fixed_price' );
		if ( $use_fixed_price ) {
			$subscription_fixed_discount = ! empty( $first_plan['subscription_price'] ) ? floatval( $first_plan['subscription_price'] ) : 0;
			$subscribe_price_raw = (float) wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_fixed_discount;
		} else {
			$subscription_discount_percentage = ! empty( $first_plan['subscription_discount'] ) ? floatval( $first_plan['subscription_discount'] ) : 0;
			$subscribe_price_raw = (float) wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( (float) $product_price * ( $subscription_discount_percentage / 100 ) );
		}
		$subscribe_price_html = bos4w_only_amount_html( $subscribe_price_raw, $product );
	}
}
?>
<div class="bos4w-display-wrap bos4w-custom-subscribe-wrap" style="margin-bottom: 2em;"
     data-one-time-price="<?php echo esc_attr( $one_time_price_raw ); ?>"
     data-subscribe-price="<?php echo esc_attr( $subscribe_price_raw ); ?>"
     data-subscription-discount-percentage="<?php echo esc_attr( $subscription_discount_percentage ); ?>"
     data-subscription-fixed-discount="<?php echo esc_attr( $subscription_fixed_discount ); ?>"
     data-use-fixed-price="<?php echo esc_attr( $use_fixed_price ? '1' : '0' ); ?>">
	<div class="bos4w-custom-plan-buttons">
		<label for="bos4w-one-time" class="bos4w-custom-btn bos4w-custom-btn-onetime">
			<input id="bos4w-one-time" name="bos4w-purchase-type" value="0" type="radio" class="bos4w-buy-type" checked style="display:none;"/>
			<div class="bos4w-btn-title"><?php echo esc_html( $one_time ); ?></div>
			<div class="bos4w-btn-price"><?php echo $one_time_price_html; ?></div>
		</label>
		<label for="bos4w-subscribe-to" class="bos4w-custom-btn bos4w-custom-btn-subscribe">
			<input id="bos4w-subscribe-to" name="bos4w-purchase-type" value="1" type="radio" class="bos4w-buy-type" style="display:none;"/>
			<div class="bos4w-btn-title"><?php echo esc_html( $subscribe_and_save ); ?></div>
			<div class="bos4w-btn-price"><?php echo $subscribe_price_html; ?></div>
		</label>
	</div>
	<div id="bos4w-delivery-select-wrap" class="bos4w-display-dropdown bos4w-custom-delivery-select" style="max-width:350px; display:none;">
		<?php if ( $plan_options ) { ?>
			<?php
			if ( 1 === count( $plan_options ) && 'variation' !== $plan_type ) {
				$plan = $plan_options[0];
				$product_price   = BOS4W_Front_End::bos4w_get_product_price( $product );
				$use_fixed_price = $product->get_meta( '_bos4w_use_fixed_price' );
				if ( $use_fixed_price ) {
					$bos_type           = 'fixed_price';
					$subscription_price = ! empty( $plan['subscription_price'] ) ? floatval( $plan['subscription_price'] ) : 0;
					$discounted_price   = wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_price;
				} else {
					$bos_type              = 'percentage_price';
					$subscription_discount = ! empty( $plan['subscription_discount'] ) ? floatval( $plan['subscription_discount'] ) : 0;
					$discounted_price     = wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( wc_format_decimal( $product_price, wc_get_price_decimals() ) * ( (float) $subscription_discount / 100 ) );
				}
				$selling_price   = bos4w_only_amount_html( $discounted_price, $product );
				$display_discount = '';
				if ( isset( $plan['subscription_discount'] ) && $plan['subscription_discount'] > 0 ) {
					$display_discount = sprintf( ' (%s&#37; %s)', esc_attr( $plan['subscription_discount'] ), esc_html__( 'off', 'bos4w' ) );
				}
				if ( isset( $plan['subscription_price'] ) && $plan['subscription_price'] > 0 ) {
					$display_discount = sprintf( ' (%s %s)', esc_attr( strip_tags( wc_price( $plan['subscription_price'] ) ) ), esc_html__( 'off', 'bos4w' ) );
				}
				$period_interval = isset( $plan['subscription_period_interval'] ) && isset( $plan['subscription_period'] ) ? wcs_get_subscription_period_strings( $plan['subscription_period_interval'], $plan['subscription_period'] ) : '';
				$discount_plan = $use_fixed_price && isset( $plan['subscription_price'] ) ? esc_attr( $plan['subscription_price'] ) : esc_attr( $plan['subscription_discount'] );
				$discount_plan = $discount_plan ?? 0;
				$display_plans = sprintf( esc_html__( 'Every %1$s for %2$s %3$s', 'bos4w' ), wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
				$output_plan = apply_filters( 'ssd_subscription_plan_display', wp_kses_post( $display_plans ), wp_kses_post( $period_interval ), wp_kses_post( $selling_price ), wp_kses_post( $display_discount ) );
				$display_text_settings = get_option( 'ssd_subscription_plan_display' );
				if ( $display_text_settings ) {
					$output_plan = sprintf( $display_text_settings, wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
				}
				?>
				<label for="bos4w-dropdown-plan" class="bos4w-delivery-label"><?php echo esc_html( $dropdown_label ); ?></label>
				<span class="bos4w-one-plan-only"><?php echo wp_kses_post( $output_plan ); ?></span>
				<?php
				echo sprintf(
					'<input type="hidden" name="convert_to_sub_plan_%d" id="bos4w-dropdown-plan" data-discount="%s" data-price="%s" data-type="%s" value="%s"/>',
					absint( $product_id ),
					esc_attr( $discount_plan ),
					esc_attr( wc_format_decimal( $discounted_price, wc_get_price_decimals() ) ),
					esc_attr( $bos_type ),
					esc_attr( $plan['subscription_period_interval'] . '_' . $plan['subscription_period'] . '_' . $discount_plan )
				);
			} else {
				?>
				<label for="bos4w-dropdown-plan" class="bos4w-delivery-label"><?php echo esc_html( $dropdown_label ); ?></label>
				<select id="bos4w-dropdown-plan" name="convert_to_sub_plan_<?php echo absint( $product_id ); ?>" class="bos4w-delivery-select">
					<?php
					if ( 'variation' === $plan_type ) {
						foreach ( $plan_options as $variation_id => $plans ) {
							$variation = wc_get_product( $variation_id );
							if ( ! $variation ) {
								continue;
							}
							$product_price   = BOS4W_Front_End::bos4w_get_product_price( $variation );
							$use_fixed_price = $variation->get_meta( '_bos4w_use_fixed_price' );
							foreach ( $plans as $plan ) {
								if ( $use_fixed_price ) {
									$bos_type           = 'fixed_price';
									$subscription_price = ! empty( $plan['subscription_price'] ) ? floatval( $plan['subscription_price'] ) : 0;
									$discounted_price   = wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_price;
								} else {
									$bos_type              = 'percentage_price';
									$subscription_discount = ! empty( $plan['subscription_discount'] ) ? floatval( $plan['subscription_discount'] ) : 0;
									$discounted_price      = wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( wc_format_decimal( $product_price, wc_get_price_decimals() ) * ( (float) $subscription_discount / 100 ) );
								}
								$context_product = $variation instanceof WC_Product ? $variation : $product;
								$selling_price   = bos4w_only_amount_html( $discounted_price, $context_product );
								$display_discount = '';
								if ( isset( $plan['subscription_discount'] ) && $plan['subscription_discount'] > 0 ) {
									$display_discount = sprintf( ' (%s&#37; %s)', esc_attr( $plan['subscription_discount'] ), esc_html__( 'off', 'bos4w' ) );
								}
								if ( isset( $plan['subscription_price'] ) && $plan['subscription_price'] > 0 ) {
									$display_discount = sprintf( ' (%s %s)', esc_attr( strip_tags( wc_price( $plan['subscription_price'] ) ) ), esc_html__( 'off', 'bos4w' ) );
								}
								$period_interval = isset( $plan['subscription_period_interval'] ) && isset( $plan['subscription_period'] ) ? wcs_get_subscription_period_strings( $plan['subscription_period_interval'], $plan['subscription_period'] ) : '';
								$discount_plan = $use_fixed_price ? esc_attr( $plan['subscription_price'] ) : esc_attr( $plan['subscription_discount'] );
								$discount_plan = $discount_plan ?? 0;
								$display_plans = sprintf( esc_html__( 'Every %1$s for %2$s %3$s', 'bos4w' ), wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
								$output_plan = apply_filters( 'ssd_subscription_plan_display', wp_kses_post( $display_plans ), wp_kses_post( $period_interval ), wp_kses_post( $selling_price ), wp_kses_post( $display_discount ) );
								$display_text_settings = get_option( 'ssd_subscription_plan_display' );
								if ( $display_text_settings ) {
									$output_plan = sprintf( $display_text_settings, wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
								}
								?>
								<option data-discount="<?php echo (float) $discount_plan; ?>" data-price="<?php echo (float) wc_format_decimal( $discounted_price, wc_get_price_decimals() ); ?>" data-type="<?php echo esc_attr( $bos_type ); ?>" value="<?php echo esc_attr( $plan['subscription_period_interval'] ) . '_' . esc_attr( $plan['subscription_period'] ) . '_' . (float) $discount_plan; ?>"><?php echo wp_kses_post( $output_plan ); ?></option>
								<?php
							}
						}
					} else {
						foreach ( $plan_options as $plan ) {
							$product_price   = BOS4W_Front_End::bos4w_get_product_price( $product );
							$use_fixed_price = $product->get_meta( '_bos4w_use_fixed_price' );
							if ( $use_fixed_price ) {
								$bos_type           = 'fixed_price';
								$subscription_price = ! empty( $plan['subscription_price'] ) ? floatval( $plan['subscription_price'] ) : 0;
								$discounted_price   = wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_price;
							} else {
								$bos_type              = 'percentage_price';
								$subscription_discount = ! empty( $plan['subscription_discount'] ) ? floatval( $plan['subscription_discount'] ) : 0;
								$discounted_price      = wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( wc_format_decimal( $product_price, wc_get_price_decimals() ) * ( (float) $subscription_discount / 100 ) );
							}
							$selling_price = bos4w_only_amount_html( $discounted_price, $product );
							$display_discount = '';
							if ( isset( $plan['subscription_discount'] ) && $plan['subscription_discount'] > 0 ) {
								$display_discount = sprintf( ' (%s&#37; %s)', esc_attr( $plan['subscription_discount'] ), esc_html__( 'off', 'bos4w' ) );
							}
							if ( isset( $plan['subscription_price'] ) && $plan['subscription_price'] > 0 ) {
								$display_discount = sprintf( ' (%s %s)', esc_attr( strip_tags( wc_price( $plan['subscription_price'] ) ) ), esc_html__( 'off', 'bos4w' ) );
							}
							$period_interval = isset( $plan['subscription_period_interval'] ) && isset( $plan['subscription_period'] ) ? wcs_get_subscription_period_strings( $plan['subscription_period_interval'], $plan['subscription_period'] ) : '';
							$discount_plan = $use_fixed_price && isset( $plan['subscription_price'] ) ? esc_attr( $plan['subscription_price'] ) : esc_attr( $plan['subscription_discount'] );
							$discount_plan = $discount_plan ?? 0;
							$display_plans = sprintf( esc_html__( 'Every %1$s for %2$s %3$s', 'bos4w' ), wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
							$output_plan = apply_filters( 'ssd_subscription_plan_display', wp_kses_post( $display_plans ), wp_kses_post( $period_interval ), wp_kses_post( $selling_price ), wp_kses_post( $display_discount ) );
							$display_text_settings = get_option( 'ssd_subscription_plan_display' );
							if ( $display_text_settings ) {
								$output_plan = sprintf( $display_text_settings, wp_kses_data( $period_interval ), wp_kses_data( $selling_price ), wp_kses_data( $display_discount ) );
							}
							?>
							<option data-discount="<?php echo (float) $discount_plan; ?>" data-price="<?php echo (float) wc_format_decimal( $discounted_price, wc_get_price_decimals() ); ?>" data-type="<?php echo esc_attr( $bos_type ); ?>" value="<?php echo esc_attr( $plan['subscription_period_interval'] ) . '_' . esc_attr( $plan['subscription_period'] ) . '_' . (float) $discount_plan; ?>"><?php echo wp_kses_post( $output_plan ); ?></option>
							<?php
						}
					}
					?>
				</select>
			<?php } ?>
			<input type="hidden" name="bos4w-selected-price" id="bos4w-selected-price"/>
		<?php } ?>
	</div>
</div>
<style>
/* Force subscription box onto its own row; quantity + button stay below */
.bos4w-display-wrap.bos4w-custom-subscribe-wrap {
	width: 100%;
	flex-basis: 100%;
	display: block;
	margin-bottom: 1em;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-plan-buttons {
	display: flex;
	gap: 0;
	margin-bottom: 1.5em;
	border-radius: 14px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0,0,0,.06);
	max-width: 500px;
	align-items: stretch;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: flex-start;
	text-align: center;
	padding: 0.4em 0.6em 0.45em;
	cursor: pointer;
	font-weight: 500;
	border: 2px solid #918978;
	font-size: 1.1em;
	transition: background 0.2s, color 0.2s;
	border-radius: 0;
	background: #fff;
	color: #918978;
	user-select: none;
	min-width: 0;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn-onetime {
	border-right: none;
	border-radius: 14px 0 0 14px;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe {
	background: #fff;
	color: #918978;
	border-radius: 0 14px 14px 0;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:has(input[type="radio"]:checked) {
	background: #918978;
	color: #fff;
	border-color: #918978;
}
/* Fixed-height label area so both prices align on the same baseline */
.bos4w-custom-subscribe-wrap .bos4w-btn-title {
	font-size: 0.95em;
	line-height: 1.3;
	height: 2.6em;
	max-height: 2.6em;
	margin: 0;
	padding: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
}
.bos4w-custom-subscribe-wrap .bos4w-btn-price {
	font-size: 22px;
	font-weight: bold;
	margin: 0;
	padding: 0.15em 0 0;
	line-height: 1.2;
	flex-shrink: 0;
	height: 1.35em;
	min-height: 1.35em;
	display: flex;
	align-items: center;
	justify-content: center;
	box-sizing: border-box;
	overflow: hidden;
}
/* Remove any extra spacing from price content (especially subscribe side) */
.bos4w-custom-subscribe-wrap .bos4w-btn-price .woocommerce-Price-amount,
.bos4w-custom-subscribe-wrap .bos4w-btn-price .amount,
.bos4w-custom-subscribe-wrap .bos4w-btn-price bdi {
	margin: 0 !important;
	padding: 0 !important;
	line-height: inherit !important;
	display: inline !important;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe .bos4w-btn-price {
	padding-bottom: 0 !important;
	margin-bottom: 0 !important;
}
/* Definitive: same pixel size for BOTH prices (beats theme/WC) */
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-onetime .bos4w-btn-price,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-onetime .bos4w-btn-price .woocommerce-Price-amount,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-onetime .bos4w-btn-price .amount,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-onetime .bos4w-btn-price bdi,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe .bos4w-btn-price,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe .bos4w-btn-price .woocommerce-Price-amount,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe .bos4w-btn-price .amount,
body .bos4w-custom-subscribe-wrap .bos4w-custom-btn-subscribe .bos4w-btn-price bdi {
	font-size: 22px !important;
	font-weight: bold !important;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:has(input[type="radio"]:checked) .bos4w-btn-price,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:has(input[type="radio"]:checked) .bos4w-btn-price .woocommerce-Price-amount,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:has(input[type="radio"]:checked) .bos4w-btn-price .amount,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:has(input[type="radio"]:checked) .bos4w-btn-price bdi {
	color: #fff !important;
}
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:not(:has(input[type="radio"]:checked)) .bos4w-btn-price,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:not(:has(input[type="radio"]:checked)) .bos4w-btn-price .woocommerce-Price-amount,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:not(:has(input[type="radio"]:checked)) .bos4w-btn-price .amount,
.bos4w-custom-subscribe-wrap .bos4w-custom-btn:not(:has(input[type="radio"]:checked)) .bos4w-btn-price bdi {
	color: #918978 !important;
}
.bos4w-custom-subscribe-wrap .bos4w-delivery-label {
	font-weight: bold;
	font-size: 1.1em;
	margin-bottom: 0.5em;
	display: block;
}
.bos4w-custom-subscribe-wrap .bos4w-delivery-select {
	width: 100%;
	padding: 0.7em;
	font-size: 1.1em;
	border-radius: 8px;
	border: 1px solid #ccc;
	box-shadow: 0 2px 8px rgba(0,0,0,.06);
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
		document.querySelectorAll('input[name="bos4w-purchase-type"]').forEach(function(radio) {
		var label = radio.closest('label');
		if (label) label.classList.toggle('selected', radio.checked);
		});
	}
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('input[name="bos4w-purchase-type"]').forEach(function(radio) {
			radio.addEventListener('change', function() {
				updateDeliveryVisibility();
				updateButtonSelection();
			});
		});
		updateDeliveryVisibility();
		updateButtonSelection();

		// Same font-size for both prices (definitive; overrides theme/WC after inject)
		var BOS4W_PRICE_FONT_SIZE = '22px';
		function bos4wApplyPriceSize() {
			var wrap = document.querySelector('.bos4w-custom-subscribe-wrap');
			if (!wrap) return;
			var one = wrap.querySelector('.bos4w-custom-btn-onetime .bos4w-btn-price');
			var sub = wrap.querySelector('.bos4w-custom-btn-subscribe .bos4w-btn-price');
			if (one) one.style.setProperty('font-size', BOS4W_PRICE_FONT_SIZE, 'important');
			if (sub) sub.style.setProperty('font-size', BOS4W_PRICE_FONT_SIZE, 'important');
			[one, sub].forEach(function(el) {
				if (!el) return;
				el.querySelectorAll('.woocommerce-Price-amount, .amount, bdi').forEach(function(node) {
					node.style.setProperty('font-size', BOS4W_PRICE_FONT_SIZE, 'important');
				});
			});
		}
		// Update One-time / Subscribe button prices when package (variation) changes
		if (typeof jQuery !== 'undefined' && jQuery('.variations_form').length) {
			var wrap = document.querySelector('.bos4w-custom-subscribe-wrap');
			var oneTimePriceEl = wrap ? wrap.querySelector('.bos4w-custom-btn-onetime .bos4w-btn-price') : null;
			var subscribePriceEl = wrap ? wrap.querySelector('.bos4w-custom-btn-subscribe .bos4w-btn-price') : null;
			jQuery('.single_variation_wrap').on('show_variation', function(event, variation) {
				if (!oneTimePriceEl || !subscribePriceEl) return;
				if (variation.price_html) {
					oneTimePriceEl.innerHTML = variation.price_html;
				}
				if (variation.bos4w_discounted_price && variation.bos4w_discounted_price.length > 0) {
					var firstPlan = variation.bos4w_discounted_price[0];
					subscribePriceEl.innerHTML = firstPlan.discounted_price || '';
				}
				bos4wApplyPriceSize();
			});
		}
		bos4wApplyPriceSize();
	});
})();
</script>
