<?php
/**
 * Cross sell variable product template.
 *
 * @var WC_Product $product Product object.
 *
 * @package Iconic_Flux
 */

if ( ! $product->is_type( 'variable' ) ) {
	return;
}

$selected_attributes = Iconic_Flux_Cross_Sell::get_selected_attributes( $product->get_id() );
$variation_id        = Iconic_Flux_Cross_Sell::find_variation_id( $product, $selected_attributes );
?>
	<div class="flux-crosssell__variable">
		<table class="variations flux-crosssell__variation-table" cellspacing="0">
			<tbody>
				<?php
				$variable_product = $product->is_type( 'variable' ) ? $product : wc_get_product( $product->get_parent_id() );
				foreach ( $variable_product->get_variation_attributes() as $attribute_name => $options ) :
					?>
					<?php $attribute_name_sanitized = 'attribute_' . sanitize_title( $attribute_name ); ?>
					<tr>
						<td class="label">
							<label for="<?php echo esc_attr( $attribute_name_sanitized ); ?>">
								<?php echo wp_kses_post( wc_attribute_label( $attribute_name ) ); // WPCS: XSS ok. ?>
							</label>
						</td>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options(
									array(
										'options'   => $options,
										'attribute' => $attribute_name,
										'product'   => $product,
										'class'     => 'flux-crosssell__variation-select',
										'selected'  => $selected_attributes[ $attribute_name_sanitized ] ?? false,
									)
								);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="flux-crosssell__variable-overlay"></div>
		<input type="hidden" class="flux-cross-sell__variation-id" value="<?php echo esc_attr( $variation_id ); ?>">
		<input type="hidden" class="flux-cross-sell__variation-data" name="flux-cross-sell-variation-data[<?php echo esc_attr( $product->get_id() ); ?>]" value="<?php echo esc_attr( wp_json_encode( $selected_attributes ) ); ?>">
	</div>
	<p class="wc-no-matching-variations woocommerce-info flux-crosssell__unavailable-msg" style="display:none;"><?php esc_html_e( 'Sorry, this variation is unavailable. Please choose a different combination.', 'flux-checkout' ); ?></p>
