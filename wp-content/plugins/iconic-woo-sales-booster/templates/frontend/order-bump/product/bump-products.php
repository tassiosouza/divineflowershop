<?php defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Product   $bump_product
 * @var WC_Product[] $bump_products
 * @var array        $settings
 * @var WC_Product   $product
 * @var string       $total_price
 * @var string       $discount_message
 */
?>
<?php if ( $should_wrap_with_form ) : ?>
	<form
		class="cart <?php echo $product->is_type( 'variable' ) ? esc_attr( 'variations_form' ) : ''; ?>"
		action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
		method="post"
		enctype='multipart/form-data'
	>
<?php endif; ?>
<div class="iconic-wsb-product-bumps <?php echo $sales_pitch ? 'iconic-wsb-product-bumps--has-sales-pitch' : ''; ?>">
	<?php if ( ! empty( $title ) ) { ?>
		<div class="iconic-wsb-product-bumps__header">
			<h3 class="iconic-wsb-product-bumps__title">
				<?php echo wp_kses_post( $title ); ?>
			</h3>
		</div>
	<?php } ?>

	<div class="iconic-wsb-product-bumps__body">
		<?php if ( $settings['show_product_thumbnail'] == 1 ) : ?>
			<ul class="iconic-wsb-product-bumps__images">
				<?php
				foreach ( $bump_products as $bump_product ) {
					$checked = in_array( $bump_product->get_id(), $checked_products, true );
					?>
					<li
						class="iconic-wsb-product-bumps__image"
						data-product-id="<?php echo esc_attr( $bump_product->get_ID() ); ?>"
						style="<?php echo $checked ? '' : 'display: none'; ?>"
					>
						<?php
						// width x height
						$size = [ 60, 60 ];
						$attr = [];

						if ( method_exists( 'Automattic\WooCommerce\Utilities\NumberUtil', 'round' ) && 'custom' === get_option( 'woocommerce_thumbnail_cropping', '1:1' ) ) {
							$width  = max( 1, (float) get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
							$height = max( 1, (float) get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );

							// Mimics the function wc_get_image_size to calculate the height
							$size[1]       = absint( Automattic\WooCommerce\Utilities\NumberUtil::round( ( $size[0] / $width ) * $height ) );
							$attr['class'] = 'iconic-wsb-product-bumps__image--cropped';
						}

						echo wp_kses_post(
							$bump_product->get_image(
								/**
								 * Filter the size of the thumbnail shown in the
								 * Frequently Bought Together section.
								 *
								 * To change the size of the thumbnail in the
								 * frontend is necessary to add a CSS rule
								 * to the selector `.iconic-wsb-product-bumps__image img`.
								 *
								 * @since 1.13.0
								 * @hook iconic_wsb_fbt_thumbnail_size
								 * @param string|array $size         Default: [60 x 60] when thumbnail cropping is not set to custom.
								 * @param  WC_Product  $bump_product The product shown in the FBT section.
								 * @param  WC_Product  $product      The product shown on the page.
								 * @return string|array New value
								 */
								apply_filters( 'iconic_wsb_fbt_thumbnail_size', $size, $bump_product, $product ),
								$attr
							)
						);
						?>
					</li>
				<?php } ?>
			</ul>
		<?php endif; ?>

		<?php if ( $sales_pitch ) { ?>
			<div class="iconic-wsb-product-bumps__sales_pitch">
				<p><?php echo esc_html( $sales_pitch ); ?></p>
			</div>
		<?php } ?>

		<ul class="iconic-wsb-product-bumps__list">
			<?php
			foreach ( $bump_products as $bump_product ) {
				$in_cart              = ! in_array( $bump_product->get_id(), $removed_product_ids, true );
				$variation_attributes = array();
				if ( $bump_product->is_type( 'variation' ) ) {
					$variation_attributes = $bump_product->get_variation_attributes();
				}
				$this_item = $bump_product->get_id() === $product->get_id();
				$checked   = in_array( $bump_product->get_id(), $checked_products );
				$classes   = array( 'iconic-wsb-product-bumps__list-item' );

				if ( ! $checked ) {
					$classes[] = 'iconic-wsb-product-bumps__list-item--faded';
				}

				$classes = join( ' ', $classes );
				?>
				<li
					class="<?php echo esc_attr( $classes ); ?>"
					data-product_id="<?php echo esc_attr( $bump_product->get_ID() ); ?>"
					data-product_type="<?php echo esc_attr( $bump_product->get_type() ); ?>"
				>
					<div class="iconic-wsb-bump-product">
						<div class="iconic-wsb-bump-product__body">
							<label>
								<input
									type="checkbox"
									class="iconic-wsb-bump-product__checkbox"
									name="iconic-wsb-products-add-to-cart[<?php echo esc_attr( $bump_product->get_id() ); ?>]"
									value="<?php echo esc_attr( $bump_product->get_id() ); ?>"
									<?php checked( $checked ); ?>
									<?php disabled( $hide_already_in_cart && $in_cart ); ?>
								/>

								<?php if ( $this_item && $is_original_product ) { ?>
									<strong class="iconic-wsb-bump-product__title iconic-wsb-bump-product__title--this-item"><?php esc_html_e( 'This item', 'iconic-wsb' ); ?>: <?php echo esc_html( $bump_product->get_title() ); ?></strong>
								<?php } elseif ( '1' === $settings['link_product_titles'] ) { ?>
									<a class="iconic-wsb-bump-product__title iconic-wsb-bump-product__title--link"
										href="<?php echo esc_url( $bump_product->get_permalink() ); ?>">
										<?php echo esc_html( $bump_product->get_title() ); ?>
									</a>
									<?php
								} elseif ( ! empty( $variation_attributes ) ) {
									$product_summary = $bump_product->get_attribute_summary();
									$product_summary = ! empty( $product_summary ) ? sprintf( esc_html__( '(%s)', 'iconic-wsb' ), $product_summary ) : '';
									?>
									<span class="iconic-wsb-bump-product__title"><?php echo $bump_product->get_title(); ?> <?php echo esc_html( $product_summary ); ?></span>
								<?php } else { ?>
									<span class="iconic-wsb-bump-product__title"><?php echo $bump_product->get_title(); ?></span>
								<?php } ?>

								<span class="iconic-wsb-bump-product__price"><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_price_html( $bump_product ); ?></span> <?php echo $in_cart ? esc_html__( '(Already in cart)', 'iconic-wsb' ) : ''; ?>

								<?php
								if ( $bump_product->is_type( 'variable' ) ) {
									$variations = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variations( $bump_product );
									?>
									<select
										class='iconic-wsb-bump-product__select iconic-wsb-bump-product__select--<?php echo esc_attr( $bump_product->get_id() ); ?>'
										name='iconic-wsb-products-add-to-cart-variation-<?php echo esc_attr( $bump_product->get_id() ); ?>'
										data-product_id="<?php echo esc_attr( $bump_product->get_id() ); ?>">

										<option disabled selected value><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_variable_dropdown_placeholder( $bump_product ); ?></option>

										<?php

										foreach ( $variations as $variation ) {
											$option_attributes = array();
											foreach ( $variation['attributes'] as $attribute_key => $attribute_value ) {
												$option_attributes[] = $attribute_value['label'];
											}
											$option_string = implode( ' - ', $option_attributes );
											?>
											<option
												value='<?php echo $variation['variation_id']; ?>'
												data-attributes="<?php echo esc_attr( json_encode( $variation['attributes'] ) ); ?>">
												<?php echo $option_string; ?>
											</option>
											<?php
										}
										?>
									</select>
									<input type="hidden" name="iconic-wsb-bump-product_attributes-<?php echo esc_attr( $bump_product->get_id() ); ?>" value="">
									<?php
								} elseif ( $bump_product->is_type( 'variation' ) ) {
									$attributes = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_any_attributes( $bump_product );
									if ( ! empty( $variation_attributes ) && ! empty( $attributes ) ) {
										?>
										<select
											class='iconic-wsb-bump-product__select iconic-wsb-bump-product__select--<?php echo esc_attr( $bump_product->get_id() ); ?>'
											name='iconic-wsb-products-add-to-cart-variation-<?php echo esc_attr( $bump_product->get_id() ); ?>'
											data-product_id="<?php echo esc_attr( $bump_product->get_id() ); ?>">

											<option disabled selected value><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_dropdown_placeholder( $bump_product ); ?></option>
											<?php
											foreach ( $attributes as $attribute ) {
												$option_strings = array();
												foreach ( $attribute as $attribute_key => $attribute_value ) {
													$option_strings[] = $attribute_value['label'];
												}
												$option_string     = implode( ' - ', $option_strings );
												$option_attributes = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_dropdown_option_attributes( $bump_product, $attribute );
												?>
												<option
													value='<?php echo esc_attr( $bump_product->get_id() ); ?>'
													data-attributes="<?php echo esc_attr( json_encode( $option_attributes ) ); ?>">
													<?php echo esc_html( $option_string ); ?>
												</option>
												<?php
											}
											?>
										</select>
									<?php } else { ?>
										<input type="hidden" name="iconic-wsb-products-add-to-cart-variation-<?php echo esc_attr( $bump_product->get_id() ); ?>" value="<?php echo esc_attr( $bump_product->get_id() ); ?>">
									<?php } ?>
									<input type="hidden" name="iconic-wsb-bump-product_attributes-<?php echo esc_attr( $bump_product->get_id() ); ?>" value="">
								<?php } ?>
							</label>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul>
		<div class="iconic-wsb-product-bumps__actions">
			<div class="iconic-wsb-product-bumps__total-price">
				<span class="iconic-wsb-product-bumps__total-price-label">
					<?php esc_html_e( 'Total Price:', 'iconic-wsb' ); ?>
				</span>
				<span class="iconic-wsb-product-bumps__total-price-amount">
					<?php echo wp_kses_post( $total_price ); ?>
				</span>
			</div>
			<?php if ( $discount_message ) { ?>
				<div class="iconic-wsb-product-bumps__discount-message">
					<?php echo wp_kses_post( $discount_message ); ?>
				</div>
			<?php } ?>
			<div class="iconic-wsb-product-bumps__button-wrap">
				<button
					type="submit"
					class="button iconic-wsb-product-bumps__button"
					name="iconic-wsb-add-selected"
					data-bump-product-form-submit
					data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
				>
					<?php esc_html_e( 'Add Selected to Cart', 'iconic-wsb' ); ?>
				</button>
			</div>
			<input type="hidden" name="iconic-wsb-fbt-this-product" value="<?php echo esc_attr( $product->get_ID() ); ?>">
			<?php
				/**
				 * Fires after `Add Selected to Cart` button in the
				 * Frequently Bought Together section.
				 *
				 * @since 1.14.0
				 * @hook iconic_wsb_fbt_after_add_selected_button
				 * @param  WC_Product   $product       The product shown on the page.
				 * @param  WC_Product[] $bump_products The array of bump products.
				 */
				do_action( 'iconic_wsb_fbt_after_add_selected_button', $product, $bump_products )
			?>
		</div>
	</div>
</div>
<?php if ( $should_wrap_with_form ) : ?>
	</form>
<?php endif; ?>
