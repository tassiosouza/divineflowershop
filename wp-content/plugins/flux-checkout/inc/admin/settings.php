<?php
/**
 * Settings.
 *
 * Adds settings to the admin screen.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Run.
add_filter( 'wpsf_register_settings_iconic_flux', 'iconic_flux_settings' );

/**
 * Flux Checkout settings.
 *
 * @param array $settings Settings Array.
 *
 * @return array
 */
function iconic_flux_settings( $settings ) {
	// Tabs.

	$settings['tabs'][] = array(
		'id'    => 'styles',
		'title' => __( 'Styles', 'flux-checkout' ),
	);

	$settings['tabs'][] = array(
		'id'    => 'general',
		'title' => __( 'Checkout Page', 'flux-checkout' ),
	);

	$settings['tabs'][] = array(
		'id'    => 'theme',
		'title' => __( 'Theme', 'flux-checkout' ),
	);

	$settings['tabs'][] = array(
		'id'    => 'thankyou',
		'title' => __( 'Thank You Page', 'flux-checkout' ),
	);

	$settings['tabs'][] = array(
		'id'    => 'integrations',
		'title' => __( 'Integrations', 'flux-checkout' ),
	);

	$general_fields = array(
		array(
			'id'       => 'show_company_field',
			'title'    => __( 'Show Company Field', 'flux-checkout' ),
			'subtitle' => __( 'Enable the Company field within the Billing Details step.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'use_autocomplete',
			'title'    => __( 'Use Address Autocomplete', 'flux-checkout' ),
			'subtitle' => __( 'Enable Address Autocomplete to automatically fill out address details. Requires a valid <a href="#tab-integrations|integrations_integrations_google_api_key" class="wsf-internal-link">Google API key</a>.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'separate_street_number_field',
			'title'    => __( 'Separate Street Number Field', 'flux-checkout' ),
			'subtitle' => __( 'Separate House Number/Name field from Street Address field.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'show_back_to_shop_btn',
			'title'    => __( 'Show "Back to Shop" button.', 'flux-checkout' ),
			'subtitle' => __( 'Add a button to go back to the cart page.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => true,
		),
		array(
			'id'       => 'skip_cart_page',
			'title'    => __( 'Skip Cart Page', 'flux-checkout' ),
			'subtitle' => __( 'When enabled, skips the cart page and directly redirects users to the checkout page, reducing a step in the purchase process.<br/><br/><b>Note:</b> Doesn\'t work for the classic theme if sidebar is disabled.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => '0',
		),
		array(
			'id'       => 'optimize_digital',
			'title'    => __( 'Optimize for Digital Products', 'flux-checkout' ),
			'subtitle' => __( 'Reduce checkout fields when only digital products are in the cart.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'make_cart_images_clickable',
			'title'    => __( 'Make Cart Items images clickable', 'flux-checkout' ),
			'subtitle' => __( 'Allows the images on the Order review cart to be clicked on and linked to the product page.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'show_cross_sell_products',
			'title'    => __( 'Show Cross-sell products on the Checkout page', 'flux-checkout' ),
			// Translators: %s is the URL to the Checkout Elements page.
			'subtitle' => sprintf( __( 'Show customers additional items they might like while they\'re finalizing their purchase.<br><br>You can set cross-sell products on the edit product page in the Product Data section (Linked Products tab > Cross-sells field).<br><br> Use the Flux Cross-sell Products block in <a href="%s">Checkout Elements</a> for more control over position.', 'flux-checkout' ), admin_url( 'edit.php?post_type=checkout_elements' ) ),
			'type'     => 'toggle',
			'default'  => true,
		),
		array(
			'id'       => 'auto_apply_coupon',
			'title'    => __( 'Auto Apply Coupon', 'flux-checkout' ),
			'subtitle' => __( 'Enter a coupon that will be automatically applied at checkout.', 'flux-checkout' ),
			'type'     => 'text',
			'default'  => '',
		),
		array(
			'id'       => 'hide_coupon',
			'title'    => __( 'Hide Coupon Field', 'flux-checkout' ),
			'subtitle' => __( 'When enabled, the coupon field will not be displayed to users and they will not be able to enter a coupon code. However, you can automatically apply a coupon using the "coupon" URL parameter. Example: site.com/checkout?coupon=10PCTOFF', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
		array(
			'id'       => 'international_phone',
			'title'    => __( 'Enable Phone number Country Code Selector', 'flux-checkout' ),
			'subtitle' => __( 'Enable country code selector for the phone number field.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		),
	);

	if ( Iconic_Flux_Helpers::is_a_page_builder_installed() ) {
		$general_fields [] = array(
			'id'       => 'enable_header_footer',
			'title'    => __( 'Custom Header & Footer', 'flux-checkout' ),
			'subtitle' => __( 'Enable custom headers & footers created with page builders such as Elementor, Divi, Breakdance, Bricks and Beaver Builder.', 'flux-checkout' ),
			'type'     => 'toggle',
			'default'  => false,
		);
	}

	// Sections.
	$settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'general',
		'section_title'       => __( 'General Settings', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => $general_fields,
	);

	$settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'user',
		'section_title'       => __( 'User Settings', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'auto_assign_guest_orders',
				'title'    => __( 'Assign Guest Orders', 'flux-checkout' ),
				'subtitle' => __( 'Flux Checkout will automatically link guest orders to existing customer accounts if the email address provided in the order matches an existing account.', 'flux-checkout' ),
				'type'     => 'toggle',
				'default'  => false,
			),
			array(
				'id'       => 'existing_user',
				'title'    => __( 'Existing User', 'flux-checkout' ),
				'subtitle' => __( 'How to handle the familiar user emails on the checkout page.', 'flux-checkout' ),
				'type'     => 'select',
				'choices'  => array(
					'dont_offer'   => __( "Don't offer to login", 'flux-checkout' ),
					'inline_only'  => __( 'Show an inline message', 'flux-checkout' ),
					'inline_popup' => __( 'Show a popup and an inline message', 'flux-checkout' ),
				),
				'default'  => 'inline_only',
			),
		),
	);

	$settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'mobile',
		'section_title'       => __( 'Mobile Settings', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'coupon_position',
				'title'    => __( 'Coupon Field position on mobile', 'flux-checkout' ),
				'subtitle' => __( 'Define the position of the Coupon field for mobile devices.', 'flux-checkout' ),
				'type'     => 'select',
				'choices'  => array(
					'woocommerce_review_order_before_payment' => __( 'On Payment Step', 'flux-checkout' ),
					'woocommerce_review_order_after_cart_contents' => __( 'Within Order Summary', 'flux-checkout' ),
				),
				'default'  => 'woocommerce_review_order_before_payment',
			),
		),
	);

	$settings['sections'][] = array(
		'tab_id'              => 'styles',
		'section_id'          => 'theme',
		'section_title'       => __( 'Theme', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 5,
		'fields'              => array(
			array(
				'id'       => 'choose_theme',
				'title'    => __( 'Choose Theme', 'flux-checkout' ),
				'subtitle' => __( 'Choose the theme you want Flux to use.', 'flux-checkout' ),
				'type'     => 'image_radio',
				'class'    => 'flux-theme-type',
				'choices'  => array(
					'classic' => array(
						'text'  => 'Classic',
						'image' => ICONIC_FLUX_URL . 'images/theme-classic.png',
					),
					'modern'  => array(
						'text'  => 'Modern',
						'image' => ICONIC_FLUX_URL . 'images/theme-modern.png',
					),
				),
				'default'  => 'modern',
			),
			array(
				'id'       => 'show_sidebar',
				'title'    => __( 'Show Order Review', 'flux-checkout' ),
				'subtitle' => __( 'Enable a persistent order review at checkout. This replaces the cart page.', 'flux-checkout' ),
				'type'     => 'toggle',
				'class'    => 'theme-type--classic',
				'default'  => false,
			),
		),
	);

	$web_safe_fonts_key = __( 'Web Safe Fonts', 'flux-checkout' );
	$web_safe_fonts     = Iconic_Flux_Helpers::get_web_safe_fonts();
	$google_fonts_key   = __( 'Google WebFonts', 'flux-checkout' );
	$google_fonts       = Iconic_Flux_Helpers::get_google_fonts();
	$font_size_px_key   = __( 'Pixels', 'flux-checkout' );
	$font_size_px       = Iconic_Flux_Helpers::get_font_size_px();
	$font_size_em_key   = __( 'Emphemeral Units', 'flux-checkout' );
	$font_size_em       = Iconic_Flux_Helpers::get_font_size_em();
	$gradients          = Iconic_Flux_Helpers::get_gradients();

	$settings['sections'][] = array(
		'tab_id'              => 'styles',
		'section_id'          => 'header',
		'section_title'       => __( 'Header Styles', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'branding',
				'title'    => __( 'Header Type', 'flux-checkout' ),
				'subtitle' => __( 'Select whether to use an image or text for the branding.', 'flux-checkout' ),
				'type'     => 'select',
				'choices'  => array(
					'text'  => __( 'Text', 'flux-checkout' ),
					'image' => __( 'Image', 'flux-checkout' ),
				),
				'default'  => 'text',
				'class'    => 'header-type',
			),
			array(
				'id'       => 'logo_image',
				'title'    => __( 'Header Image', 'flux-checkout' ),
				'subtitle' => __( 'Select your logo (Recommended size: 200 x 40px).', 'flux-checkout' ),
				'type'     => 'file',
				'class'    => 'header-type--image',
			),
			array(
				'id'       => 'logo_image_width',
				'title'    => __( 'Image Width', 'flux-checkout' ),
				'subtitle' => __( 'Please enter the width of your logo in px.', 'flux-checkout' ),
				'type'     => 'number',
				'class'    => 'header-type--image',
			),
			array(
				'id'      => 'header_text',
				'title'   => __( 'Header Text', 'flux-checkout' ),
				'type'    => 'text',
				'default' => __( 'Checkout', 'flux-checkout' ),
				'class'   => 'header-type--text',
			),
			array(
				'id'      => 'header_font_family',
				'title'   => __( 'Header Font Family', 'flux-checkout' ),
				'type'    => 'select',
				'class'   => 'header-type--text',
				'choices' => array(
					'inherit'           => __( 'inherit', 'flux-checkout' ),
					$web_safe_fonts_key => $web_safe_fonts,
					$google_fonts_key   => $google_fonts,
				),
				'default' => 'inherit',
			),
			array(
				'id'      => 'header_font_colour',
				'title'   => __( 'Header Font Colour', 'flux-checkout' ),
				'type'    => 'color',
				'class'   => 'header-type--text',
				'default' => '#FFFFFF',
			),
			array(
				'id'      => 'header_font_size',
				'title'   => __( 'Header Font Size', 'flux-checkout' ),
				'type'    => 'select',
				'class'   => 'header-type--text',
				'choices' => array(
					'inherit'         => __( 'inherit', 'flux-checkout' ),
					$font_size_px_key => $font_size_px,
					$font_size_em_key => $font_size_em,
				),
				'default' => '24px',
			),
			array(
				'id'       => 'header_background',
				'title'    => __( 'Header Background', 'flux-checkout' ),
				'subtitle' => __( 'Select whether to use a gradient for the header banner.', 'flux-checkout' ),
				'type'     => 'radio',
				'class'    => 'header-background',
				'choices'  => array(
					'primary-color' => __( 'Use Primary Color', 'flux-checkout' ),
					'custom'        => __( 'Use Custom Color', 'flux-checkout' ),
					'gradient'      => __( 'Use Gradient', 'flux-checkout' ),
				),
				'default'  => 'primary-color',
			),
			array(
				'id'       => 'background',
				'title'    => __( 'Gradient Background', 'flux-checkout' ),
				'subtitle' => __( 'The background gradient to use. Preview all gradients at <a target="_blank" href="http://uigradients.com/"> http://uigradients.com/</a>.', 'flux-checkout' ),
				'type'     => 'select',
				'class'    => 'header-background--gradient',
				'choices'  => $gradients,
			),
			array(
				'id'      => 'custom_header_color',
				'title'   => __( 'Custom Header Color', 'flux-checkout' ),
				'type'    => 'color',
				'class'   => 'header-background--custom',
				'default' => '#333333',
			),
			array(
				'id'      => 'cart_icon_color',
				'title'   => __( '"Back to Cart" Link Color', 'flux-checkout' ),
				'type'    => 'color',
				'default' => '#ffffff',
			),
		),
	);

	$primary_colours = Iconic_Flux_Helpers::get_classic_pallet();
	$accent_colours  = Iconic_Flux_Helpers::get_classic_pallet( true );

	$settings['sections'][] = array(
		'tab_id'              => 'styles',
		'section_id'          => 'checkout',
		'section_title'       => __( 'Checkout Styles', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'use_custom_colors',
				'title'    => __( 'Color Theme', 'flux-checkout' ),
				'subtitle' => __( 'Select whether to use a custom colors.', 'flux-checkout' ),
				'type'     => 'radio',
				'class'    => 'colour-type',
				'choices'  => array(
					'mdl'    => __( 'Select from Design palette', 'flux-checkout' ),
					'custom' => __( 'Choose custom colors', 'flux-checkout' ),
				),
				'default'  => 'mdl',
			),
			array(
				'id'       => 'primary_color',
				'title'    => __( 'Primary Color', 'flux-checkout' ),
				'subtitle' => __( 'The Primary Color will be used for the progress bar, form fields, switches, checkboxes and radio buttons.', 'flux-checkout' ),
				'type'     => 'custom',
				'class'    => 'colour-type--palette',
				'choices'  => $primary_colours,
				'default'  => '#3F51B5',
				'output'   => array( 'Iconic_Flux_Helpers', 'control_radio_classic_palette' ),
			),
			array(
				'id'       => 'accent_color',
				'title'    => __( 'Accent Color', 'flux-checkout' ),
				'subtitle' => __( 'The Accent Color will be used for buttons and links.', 'flux-checkout' ),
				'type'     => 'custom',
				'class'    => 'colour-type--palette',
				'choices'  => $accent_colours,
				'default'  => '#2196F3',
				'output'   => array( 'Iconic_Flux_Helpers', 'control_radio_classic_palette' ),
			),
			array(
				'id'       => 'custom_primary_color',
				'title'    => __( 'Custom Primary Color', 'flux-checkout' ),
				'subtitle' => __( 'The Primary Color will be used for the progress bar, form fields, switches, checkboxes and radio buttons.', 'flux-checkout' ),
				'type'     => 'color',
				'class'    => 'colour-type--primary',
				'default'  => '#2196F3',
			),
			array(
				'id'       => 'custom_accent_color',
				'title'    => __( 'Custom Accent Color', 'flux-checkout' ),
				'subtitle' => __( 'The Accent Color will be used for buttons and links.', 'flux-checkout' ),
				'type'     => 'color',
				'class'    => 'colour-type--primary',
				'default'  => '#03A9F4',
			),
			array(
				'id'       => 'modern_custom_placeholder_color',
				'title'    => __( 'Placeholders', 'flux-checkout' ),
				'subtitle' => __( 'Used for placeholders and form labels', 'flux-checkout' ),
				'type'     => 'color',
				'default'  => '#5F6061',
			),
			array(
				'id'       => 'modern_custom_link_color',
				'title'    => __( 'Link colour', 'flux-checkout' ),
				'subtitle' => __( 'Used for links.', 'flux-checkout' ),
				'type'     => 'color',
				'default'  => '#3d9cd2',
			),
			array(
				'id'       => 'modern_custom_primary_button_color',
				'title'    => __( 'Primary button colour', 'flux-checkout' ),
				'subtitle' => __( 'Used for the primary buttons.', 'flux-checkout' ),
				'type'     => 'color',
				'default'  => '#16110E',
			),
			array(
				'id'       => 'modern_custom_secondary_button_color',
				'title'    => __( 'Secondary button colour', 'flux-checkout' ),
				'subtitle' => __( 'Used for the secondary buttons.', 'flux-checkout' ),
				'type'     => 'color',
				'default'  => '#16110E',
			),
			array(
				'id'       => 'custom_css',
				'title'    => __( 'Custom CSS', 'flux-checkout' ),
				'subtitle' => __( 'Put your custom CSS rules here.', 'flux-checkout' ),
				'type'     => 'custom',
				'output'   => array( 'Iconic_Flux_Helpers', 'control_textarea_custom_css' ),
			),
		),
	);

	$settings['sections'][] = array(
		'tab_id'              => 'thankyou',
		'section_id'          => 'thankyou',
		'section_title'       => __( 'Thank You Page Settings', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'enable_thankyou_page',
				'title'    => __( 'Enable Flux Thank You Page', 'flux-checkout' ),
				'subtitle' => __( 'Enable the Flux custom Thank You page. This will override the default WooCommerce Thank You page.', 'flux-checkout' ) . '<br><br>' . Iconic_Flux_Thankyou::get_thankyou_page_preview_link( true ),
				'type'     => 'toggle',
				'default'  => false,
			),
			array(
				'id'       => 'show_map',
				'title'    => __( 'Show Map', 'flux-checkout' ),
				'subtitle' => __( 'Enable a map showing the customer\'s shipping location. <br><br>Requires a valid <a href="#tab-integrations|integrations_integrations_google_api_key" class="wsf-internal-link">Google API key</a>. ', 'flux-checkout' ),
				'type'     => 'toggle',
				'default'  => true,
			),
			array(
				'id'              => 'content',
				'title'           => __( 'Thank You Page Content', 'flux-checkout' ),
				// Translators: %s: Checkout Elements link.
				'subtitle'        => sprintf( wp_kses( __( 'Display a custom message/content on the Thank You page.<br><br><strong>Tip:</strong> You can use the WordPress block editor to design and place custom elements anywhere on the Thank You page using <a href="%s">Checkout Elements</a>.', 'flux-checkout' ), Iconic_Flux_Helpers::get_kses_allowed_tags() ), admin_url( 'edit.php?post_type=checkout_elements' ) ),
				'type'            => 'editor',
				'default'         => '<h3 class="">Order Updates</h3><p>You will receive order and shipping updates via email.</p>',
				'editor_settings' => array(),
			),
			array(
				'id'       => 'content_position',
				'title'    => __( 'Content placement', 'flux-checkout' ),
				'subtitle' => __( 'Where to display the custom message/content.', 'flux-checkout' ),
				'type'     => 'select',
				'default'  => 'flux_thankyou_after_order_status',
				'choices'  => array(
					'flux_thankyou_after_order_status'      => esc_html__( 'After order status', 'flux-checkout' ),
					'flux_thankyou_before_customer_details' => esc_html__( 'Before Customer details', 'flux-checkout' ),
					'flux_thankyou_after_customer_details'  => esc_html__( 'After Customer details', 'flux-checkout' ),
					'flux_thankyou_before_product_details'  => esc_html__( 'Before Product details', 'flux-checkout' ),
					'flux_thankyou_after_product_details'   => esc_html__( 'After Product details', 'flux-checkout' ),
				),
			),
			array(
				'id'       => 'contact_page',
				'title'    => __( 'Contact Page', 'flux-checkout' ),
				'subtitle' => __( 'Select a contact page to be linked on the Thank You page. Leave empty to hide the contact link.', 'flux-checkout' ),
				'type'     => 'custom',
				'default'  => '',
				'output'   => function( $args ) {
					$value = isset( $args['value'] ) ? intval( $args['value'] ) : 0;
					wp_dropdown_pages(
						array(
							'depth'            => 3,
							'show_option_none' => esc_html__( '-Select Page-', 'flux-checkout' ),
							'name'             => 'iconic_flux_settings[thankyou_thankyou_contact_page]',
							'selected'         => esc_attr( $value ),
						)
					);
				},
			),
		),
	);

	$settings['sections'][] = array(
		'tab_id'              => 'integrations',
		'section_id'          => 'integrations',
		'section_title'       => __( 'Integrations', 'flux-checkout' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'google_api_key',
				'title'    => __( 'Google API Key', 'flux-checkout' ),
				'subtitle' => __( 'Your API key to enable Address Autocomplete. <a href="https://iconicwp.com/docs/flux-checkout-for-woocommerce/how-to-get-your-google-api-key-for-address-autocomplete/" target="_blank">View documentation</a>.', 'flux-checkout' ),
				'type'     => 'text',
			),
		),
	);

	return $settings;
}
