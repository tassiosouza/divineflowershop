<?php

	add_action( 'woocommerce_review_order_after_cart_contents', 'wpslash_tipping_woocommerce_checkout_order_review_form', 10, 0 );


function wpslash_tipping_woocommerce_checkout_order_review_form() {
		$tipping_enabled =  get_option( 'wc_settings_tab_wpslash_tipping_enabled', false );
		$tipping_enabled_filter  = apply_filters('wpslash_tipping_enabled_filter', true );
		$buttons_background_color  = get_option( 'wc_settings_tab_wpslash_tipping_button_background_color', '#28a745' );
		$buttons_text_color  = get_option( 'wc_settings_tab_wpslash_tipping_button_text_color', '#ffffff' );
		$body_background_color  = get_option( 'wc_settings_tab_wpslash_tipping_body_background_color', 'transparent' );
		$title_background_color  = get_option( 'wc_settings_tab_wpslash_tipping_title_background_color', '#ffffff' );
		$title_text_color  = get_option( 'wc_settings_tab_wpslash_tipping_title_text_color', '#ffffff' );


	if ('yes' == $tipping_enabled && $tipping_enabled_filter) {

		$tipping_title =  !empty(get_option( 'wc_settings_tab_wpslash_tipping_title', true )) ? get_option( 'wc_settings_tab_wpslash_tipping_title', true ) : '' ;
		$tipping_title_enabled =  !empty(get_option( 'wc_settings_tab_wpslash_tipping_title_enabled', true )) ? get_option( 'wc_settings_tab_wpslash_tipping_title_enabled', true ) : 'no' ;
		$tipping_percentage=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_percentage', true )) ? get_option( 'wc_settings_tab_wpslash_tipping_percentage', true )  : '';
		$tipping_percentage_enabled=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_percentage_enabled', true )) ? get_option( 'wc_settings_tab_wpslash_tipping_percentage_enabled', true ) :'no'  ;
		$tipping_buttons=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_buttons', 'no' )) ? get_option( 'wc_settings_tab_wpslash_tipping_buttons', 'no' )  : 'no';
		$tipping_buttons_enabled=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_buttons_enabled', 'no' )) ? get_option( 'wc_settings_tab_wpslash_tipping_buttons_enabled', 'no' ) :'no'  ;
		$tipping_percentage_display=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_percentage_display', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_percentage_display', true ) : 'percentage';
		$tipping_buttons_display=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_buttons_display', 'amount' )) ?  get_option( 'wc_settings_tab_wpslash_tipping_buttons_display', 'amount' ) : 'amount';
		$move_custom_tip=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_custom_tip_inside_buttons', 'no' )) ?  get_option( 'wc_settings_tab_wpslash_tipping_custom_tip_inside_buttons', 'no' ) : 'no';

		$tipping_default_amount=  !empty(get_option( 'wc_settings_tab_wpslash_tipping_default_amount', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_default_amount', true ) : 0;
		$tipping_taxable=  ( !empty(get_option( 'wc_settings_tab_wpslash_tipping_taxable', true )) ) && ( get_option( 'wc_settings_tab_wpslash_tipping_taxable', true ) =='yes' )  ?  true : false;
		$tax_class =  !empty(get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true ) : '';
		$styling_disabled  = get_option('wc_settings_tab_wpslash_tipping_styling_disabled', 'no');
		$default_class_button='';
		$default_class_input='';
		if (!is_ajax()) {
			?>
			
	<div class="wpslash-tip-wrapper">
			<?php if (!empty($tipping_title) && ( 'yes' == $tipping_title_enabled )) : ?>		
	<div class="wpslash-tip-title">
				<?php echo esc_html($tipping_title); ?>
	</div>	
<?php endif; ?>
		<?php 
			if ('yes' == $styling_disabled) {
			$default_class_button = 'button';
			$default_class_input = 'input-text';
			}
			?>
	<?php if ('no' == $move_custom_tip) : ?>			
	<div class="wpslash-tipping-form-wrapper">
	<input type="number" value="<?php echo esc_html($tipping_default_amount); ?>" class="wpslash-tip-input <?php echo esc_html($default_class_input); ?>"/>
	<a class="wpslash-tip-submit <?php echo esc_html($default_class_button); ?>"><?php esc_html_e('Add', 'wpslash-tipping'); ?></a>
	</div>
	<?php endif; ?>			

			<?php 
			if ('yes' == $tipping_percentage_enabled) :
				$tipping_percentages = explode(',', str_replace(' ', '', $tipping_percentage));

				?>
	<div class="wpslash-percentage-tip-buttons">
				<?php foreach ($tipping_percentages as $percentage) : ?>
					<?php 
					$subtotal =WC()->cart->get_subtotal();
					$subtotal_tax =WC()->cart->get_subtotal_tax();

					$amount =  ( ( $subtotal+$subtotal_tax ) * ( $percentage/100 ) );
					?>
	<a class="wpslash-tip-percentage-btn tip <?php echo esc_html($default_class_button); ?>" percentage="<?php echo floatval($percentage); ?>"><?php echo esc_html(wpslash_tipping_percentage_display_format($percentage, $amount, $tipping_percentage_display)); ?></a>
	<?php endforeach; ?>
	<?php if ('yes' == $move_custom_tip) : ?>
	<a class="wpslash-tip-percentage-btn custom <?php echo esc_html($default_class_button); ?>"> <?php esc_html_e('Custom Tip', 'wpslash-tipping'); ?> </a>
	<?php endif; ?>

	</div>
		<?php endif; ?>
		<?php 
			if ('yes' == $tipping_buttons_enabled) :
				$tipping_buttons = explode(',', str_replace(' ', '', $tipping_buttons));

				?>
	<div class="wpslash-percentage-tip-buttons">
				<?php foreach ($tipping_buttons as $button) : ?>
					<?php 
					$subtotal =WC()->cart->get_subtotal();
					$subtotal_tax =WC()->cart->get_subtotal_tax();

					$total_cart =   ( $subtotal+$subtotal_tax ) ;

		

					$percentage = $button*100/$total_cart;


					?>
	<a class="wpslash-tip-percentage-btn tip <?php echo esc_html($default_class_button); ?>" amount="<?php echo floatval($button); ?>"><?php echo esc_html(wpslash_tipping_button_display_format($button, $percentage, $tipping_buttons_display)); ?></a>
	<?php endforeach; ?>
	<?php if ('yes' == $move_custom_tip) : ?>
	<a class="wpslash-tip-percentage-btn custom <?php echo esc_html($default_class_button); ?>"> <?php esc_html_e('Custom Tip', 'wpslash-tipping'); ?> </a>
	<?php endif; ?>


	</div>
	
		<?php endif; ?>
		<?php if ('yes' == $move_custom_tip) : ?>			
	<div class="wpslash-tipping-form-wrapper hidden">
	<input type="number" value="<?php echo esc_html($tipping_default_amount); ?>" class="wpslash-tip-input <?php echo esc_html($default_class_input); ?>"/>
	<a class="wpslash-tip-submit <?php echo esc_html($default_class_button); ?>"><?php esc_html_e('Add', 'wpslash-tipping'); ?></a>
	</div>
	<?php endif; ?>
		<style>
				a.wpslash-tip-percentage-btn, a.wpslash-tip-submit
				{
					background-color:<?php echo esc_html($buttons_background_color); ?>;
					color:<?php echo esc_html($buttons_text_color); ?>;
					-webkit-text-fill-color:<?php echo esc_html($buttons_text_color); ?>;
				}
				.wpslash-tip-wrapper
				{
					background-color:<?php echo esc_html($body_background_color); ?>;

				}
				.wpslash-tip-title
				{

					 background-color:<?php echo esc_html($title_background_color); ?>;
					color:<?php echo esc_html($title_text_color); ?>;
					-webkit-text-fill-color:<?php echo esc_html($title_text_color); ?>;

				}
			</style>
	</div>
			<?php
		}
	}
}

add_filter('woocommerce_cart_totals_fee_html', 'wpslash_tipping_add_remove_btn', 10, 2);
function wpslash_tipping_add_remove_btn( $cart_total_fees, $fee ) {
	$tip_name = WC()->session->get( 'wpslash_tip_name' );

	if ($fee->name === $tip_name ) {
		$cart_total_fees .='<a class="wpslash_tip_remove_btn">' . esc_html__('x', 'wpslash-tipping') . '</a>';

	}
	return $cart_total_fees;
}

function wpslash_tipping_percentage_display_format( $percentage, $amount, $format ) {
	switch ($format) {
		case 'percentage':
			/* translators: %s: Percentage of Tip */
			return sprintf(esc_html__('Add %s%%  Tip', 'wpslash-tipping'), $percentage);
			break;

		case 'amount':
			/* translators: %s: Tip Amount */
			return sprintf(esc_html__('Add %s Tip', 'wpslash-tipping'), strip_tags(wc_price($amount)));
			break;

		case 'percentage-amount':
			/* translators: 1:Tip Percentage 2:Tip amount */
			return sprintf( esc_html__('Add %1$s %% (%2$s) Tip', 'wpslash-tipping'), $percentage, strip_tags(wc_price($amount)) );
			break;

	}
}

function wpslash_tipping_button_display_format( $amount, $percentage, $format ) {
	switch ($format) {
		case 'percentage':
			/* translators: %s: Percentage of Tip */
			return sprintf(esc_html__('Add %s%%  Tip', 'wpslash-tipping'), round($percentage, 0));
			break;

		case 'amount':
			/* translators: %s: Tip Amount */
			return sprintf(esc_html__('Add %s Tip', 'wpslash-tipping'), strip_tags(wc_price($amount)));
			break;

		case 'percentage-amount':
			/* translators: 1:Tip Percentage 2:Tip amount */
			return sprintf( esc_html__('Add %1$s %% (%2$s) Tip', 'wpslash-tipping'), round($percentage, 0), strip_tags(wc_price($amount)) );
			break;

	}
}
