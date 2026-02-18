<?php defined( 'ABSPATH' ) || exit;
/**
 * @var Iconic_WSB_Order_Bump_Checkout_Abstract $bump
 */

// Docs for this filter in templates/admin/order-bump/product/product-data-panel.php.
// phpcs:ignore WooCommerce.Commenting.CommentHooks
$action_to_search_products_and_variations = apply_filters(
	'iconic_wsb_action_to_search_products_and_variations',
	'iconic_wsb_json_search_products_and_variations'
);
?>
<div class="iconic-wsb-edit-step" data-iconic-wsb-offer-scope>
	<h2 class="iconic-wsb-edit-step__header">
		<?php esc_html_e( 'Create Offer', 'iconic-wsb' ); ?>
	</h2>
	<p class="iconic-wsb-edit-step__description">
		<?php esc_html_e( 'Select a product to offer during checkout if the conditions are met.', 'iconic-wsb' ); ?>
	</p>
	<div class="iconic-wsb-edit-step__body">
		<div class="iconic-wsb-form">
			<div class="iconic-wsb-form__row">
				<div class="iconic-wsb-form__inner">
					<select class="iconic-wsb-form__product-search wc-product-search"
							id="data-iconic-wsb-offer-product-search"
							data-iconic-wsb-offer-product
							data-exclude="<?php echo esc_attr( join( ',', $bump->get_specific_products( array() ) ) ); ?>"
							data-minimum_input_length="1"
							name="iconic_wsb_product_offer"
							required
							data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
							data-action=<?php echo esc_attr( $action_to_search_products_and_variations ); ?>>
						<?php
						if ( $bump->get_product_offer() ) :
							$product = $bump->get_product_offer();
							?>
							<option value="<?php echo esc_attr( $product->get_id() ); ?>" selected>
								<?php
									$formatted_name =
										Iconic_WSB_Helpers::append_price_text_if_equal_or_less_than_zero(
											Iconic_WSB_Helpers::append_out_of_stock_text(
												Iconic_WSB_Helpers::get_formatted_name( $product ),
												$product
											),
											$product
										);

									// Docs for this filter in templates/admin/order-bump/product/product-data-panel.php.
									// phpcs:ignore WooCommerce.Commenting.CommentHooks
									$formatted_name = apply_filters( 'iconic_wsb_formatted_bump_product_name', $formatted_name, $product );

									echo esc_html( wp_strip_all_tags( $formatted_name ) );
								?>
							</option>
						<?php endif; ?>
					</select>
				</div>
			</div>
			<div class="iconic-wsb-form__row">
				<label class="iconic-wsb-form__label"
					for="iconic_wsb_discount"
				>
					<?php _e( 'Discount (optional):', 'iconic-wsb' ); ?>
				</label>

				<div class="iconic-wsb-form__inner">
					<?php
					$i18nValidation = array(
						'max'        => __( 'Max quantity: ', 'iconic-wsb' ),
						'min'        => __( 'Min quantity: ', 'iconic-wsb' ),
						'onlyNumber' => __( 'Numbers only', 'iconic-wsb' ),
					);
					?>
					<span class="iconic-wsb-quantity" data-quantity-validation="<?php echo esc_attr( htmlspecialchars( json_encode( $i18nValidation ) ) ); ?>">
						<input class="iconic-wsb-form__control"
								type="number"
								data-quantity-field
								id="iconic_wsb_discount"
								min="0"
							<?php
							if ( $product = $bump->get_product_offer() ) :
								;
								?>
								max="<?php echo esc_attr( $bump->get_discount_type() === 'percentage' ? '100' : $product->get_price() ); ?>"
							<?php endif; ?>
								step="0.01"
								name="iconic_wsb_discount"
								data-iconic-wsb-discount-value
								value="<?php echo esc_attr( $bump->get_discount() ); ?>"
						>
					</span>
					<select class="iconic-wsb-form__control"
							name="iconic_wsb_discount_type"
							required
							data-iconic-wsb-discount-type
							data-percentage-max="100"
						<?php
						if ( $product = $bump->get_product_offer() ) :
							;
							?>
							data-simple-max="<?php echo esc_attr( $product->get_price() ); ?>"
						<?php endif; ?>
					>
						<option value="percentage" <?php selected( 'percentage', $bump->get_discount_type() ); ?> ><?php esc_html_e( 'Percent', 'iconic_wsb' ); ?></option>
						<option value="simple" <?php selected( 'simple', $bump->get_discount_type() ); ?> ><?php echo get_woocommerce_currency_symbol(); ?></option>
					</select>
				</div>
			</div>
			<div class="iconic-wsb-form__row">
				<label
					class="iconic-wsb-form__label"
					for="iconic_wsb_target_rate"
				>
					<?php esc_html_e( 'Target rate (optional):', 'iconic-wsb' ); ?>
				</label>

				<div class="iconic-wsb-form__inner">
					<span class="iconic-wsb-target-rate">
						<input
							class="iconic-wsb-form__control iconic-wsb-target-rate__input"
							type="number"
							id="iconic_wsb_target_rate"
							min="0"
							step="0.01"
							name="iconic_wsb_target_rate"
							value="<?php echo empty( $bump->get_target_rate() ) ? '' : esc_attr( $bump->get_target_rate() ); ?>"
						>
					</span>	
					%	
				</div>
			</div>

			<?php if ( 'at_checkout_ob' === get_post_type( $bump->get_id() ) ) : ?>
				<div class="iconic-wsb-form__row">
					<label>
						<input
							type="checkbox"
							class='iconic-wsb-form__control iconic-wsb-form__control--checkbox' 
							name='iconic_allow_product_offer_quantity_change'
							<?php checked( $bump->get_allow_product_offer_quantity_change(), true ); ?>
						/>
						<?php esc_html_e( 'Allow changing the quantity of the product offered on the cart page.', 'iconic-wsb' ); ?>
					</label>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
