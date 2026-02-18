<?php defined( 'ABSPATH' ) || exit;
/**
 * @var Iconic_WSB_Order_Bump_Checkout $bump
 * @var string                         $step_id
 * @var array                          $step
 */

?>
<div class="iconic-wsb-edit-step">
	<h2 class="iconic-wsb-edit-step__header">
		<?php esc_html_e( 'Select Product(s)', 'iconic-wsb' ); ?>
	</h2>
	<p class="iconic-wsb-edit-step__description">
		<?php esc_html_e( 'This offer will be enabled when the following criteria is met.', 'iconic-wsb' ); ?>
	</p>
	<div class="iconic-wsb-edit-step__body iconic-wsb-form" data-iconic-wsb-display-for--scope>
		<div class="iconic-wsb-form__row">
			<label>
				<?php esc_html_e( 'Display for:', 'iconic-wsb' ); ?>
				<select class="iconic-wsb-form__control" name="iconic_wsb_display_type" data-iconic-wsb-display-for--select required>
					<option value="all" <?php selected( 'all', $bump->get_display_type() ); ?> >
						<?php esc_html_e( 'All Products', 'iconic-wsb' ); ?>
					</option>
					<option value="specific" <?php selected( 'specific', $bump->get_display_type() ); ?> >
						<?php esc_html_e( 'Specific Products', 'iconic-wsb' ); ?>
					</option>
					<option value="categories" <?php selected( 'categories', $bump->get_display_type() ); ?> >
						<?php esc_html_e( 'Categories', 'iconic-wsb' ); ?>
					</option>
				</select>
			</label>
		</div>

		<div class="iconic-wsb-form__row <?php echo esc_attr( $bump->get_display_type( 'all' ) === 'all' ? 'iconic-wsb-hidden' : '' ); ?>"
			data-iconic-wsb-display-for--spoiler data-iconic-wsb-display-for--categories>
			<div class="iconic-wsb-form__row">
				<label>
					<span class="iconic-wsb-form__pre-label"><?php esc_html_e( 'Display bump when the cart contains', 'iconic-wsb' ); ?></span>
					<select class="iconic-wsb-form__control" name="iconic_wsb_apply_when_specific" <?php echo $bump->get_display_type() === 'all' ? '' : 'required'; ?> data-iconic-wsb-display-for--control>
						<option value="any" <?php selected( 'any', $bump->get_apply_when_specific() ); ?> >
							<?php esc_html_e( 'Any', 'iconic-wsb' ); ?>
						</option>
						<option value="all" <?php selected( 'all', $bump->get_apply_when_specific() ); ?> >
							<?php esc_html_e( 'All', 'iconic-wsb' ); ?>
						</option>
						<option value="only" <?php selected( 'only', $bump->get_apply_when_specific() ); ?> >
							<?php esc_html_e( 'Only', 'iconic-wsb' ); ?>
						</option>
						<option value="none" <?php selected( 'none', $bump->get_apply_when_specific() ); ?> >
							<?php esc_html_e( 'No', 'iconic-wsb' ); ?>
						</option>
					</select>
					<span class="iconic-wsb-form__post-label <?php echo esc_attr( $bump->get_display_type() !== 'specific' ? 'iconic-wsb-hidden' : '' ); ?>" data-iconic-wsb-display-for--spoiler><?php esc_html_e( 'products listed below', 'iconic-wsb' ); ?>:</span>
					<span class="iconic-wsb-form__post-label <?php echo esc_attr( $bump->get_display_type() !== 'categories' ? 'iconic-wsb-hidden' : '' ); ?>" data-iconic-wsb-display-for--categories><?php esc_html_e( 'categories listed below', 'iconic-wsb' ); ?>:</span>
				</label>
			</div>
			<div class="iconic-wsb-form__row <?php echo esc_attr( $bump->get_display_type() !== 'specific' ? 'iconic-wsb-hidden' : '' ); ?>" data-iconic-wsb-display-for--spoiler>
				<label>
					<select class="iconic-wsb-form__product-search wc-product-search"
						<?php echo $bump->get_display_type() !== 'specific' ? '' : 'required'; ?>
							data-iconic-wsb-display-for--control
							data-iconic-wsb-specific-products
							multiple="multiple"
							data-minimum_input_length="1"
							name="iconic_wsb_specific_product[]"
							data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
							data-action="woocommerce_json_search_products_and_variations">

						<?php if ( $bump->get_specific_products() ) : ?>
							<?php
							foreach ( $bump->get_specific_products() as $product_id ) :
								$product = wc_get_product( $product_id );
								?>
								<?php if ( $product ) : ?>
									<option selected value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( Iconic_WSB_Helpers::get_formatted_name( $product ) ); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif ?>
					</select>
				</label>
			</div>
			<div class="iconic-wsb-form__row <?php echo esc_attr( $bump->get_display_type() !== 'categories' ? 'iconic-wsb-hidden' : '' ); ?>" data-iconic-wsb-display-for--categories>
				<label>
					<select class="iconic-wsb-form__product-search wc-category-search"
						<?php echo $bump->get_display_type() !== 'categories' ? '' : 'required'; ?>
							data-iconic-wsb-display-for--category-control
							data-iconic-wsb-categories
							multiple="multiple"
							data-minimum_input_length="1"
							name="iconic_wsb_specific_categories[]"
							data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'woocommerce' ); ?>"
							data-action="woocommerce_json_search_products_and_variations">

						<?php if ( $bump->get_specific_categories() ) : ?>
							<?php
							foreach ( $bump->get_specific_categories() as $category_slug ) :
								$category = get_term_by( 'slug', $category_slug, 'product_cat' );
								?>
								<?php if ( $category ) : ?>
									<option selected value="<?php echo esc_attr( $category_slug ); ?>"><?php echo esc_html( wp_strip_all_tags( $category->name ) ); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif ?>
					</select>
				</label>
			</div>
		</div>

		<div class="iconic-wsb-form__row">
			<label>
				<input
					type="checkbox"
					class='iconic-wsb-form__control iconic-wsb-form__control--checkbox' 
					name='iconic_wsb_enable_bump_for_same_product'
					<?php checked( $bump->get_enable_bump_for_same_product(), true ); ?>
				/>
				<?php esc_html_e( 'Show Order Bump even if the offer product is already in the cart.', 'iconic-wsb' ); ?>
			</label>
		</div>
	</div>
</div>
