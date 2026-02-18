<?php
/**
 * Iconic_Flux_Checkout_Patterns.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Checkout_Patterns.
 *
 * @class    Iconic_Flux_Checkout_Patterns
 * @version  2.1.0
 */
class Iconic_Flux_Checkout_Patterns {

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'on_init' ) );
	}

	/**
	 * On init.
	 */
	public static function on_init() {

		$images_path = ICONIC_FLUX_URL . 'images/blocks/';

		/**
		 * Register block pattern category.
		 */
		register_block_pattern_category(
			'flux-checkout',
			array( 'label' => __( 'Flux Checkout', 'flux-checkout' ) )
		);

		/**
		 * Trust badge 1.
		 */
		register_block_pattern(
			'flux-checkout/trust-badge-1',
			array(
				'title'      => __( 'Flux Element - Trust badge 1', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"style":{"color":{"background":"#e7f0fa"}},"className":"flux-elements-block flux-elements-block\u002d\u002dtype1","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"center","orientation":"horizontal"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--type1 has-background" style="background-color:#e7f0fa"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large flux-elements-block__icon"><img src="' . $images_path . 'delivery.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p><strong>Free shipping on orders over $50</strong><br>Place your order with peace of mind.</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->',
			)
		);

		/**
		 * Trust badge 2.
		 */
		register_block_pattern(
			'flux-checkout/trust-badge-2',
			array(
				'title'      => __( 'Flux Element - Trust badge 2', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"style":{"color":{"background":"#e9f5ec"}},"className":"flux-elements-block flux-elements-block\u002d\u002dtype1","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"center","orientation":"horizontal"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--type1 has-background" style="background-color:#e9f5ec"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'badge.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p><strong>100% satisfaction guarantee!  With 90-Day exchanges & returns<br></strong>Place your order with peace of mind.</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->',
			)
		);

		/**
		 * Reward points.
		 */
		register_block_pattern(
			'flux-checkout/sale-banner',
			array(
				'title'      => __( 'Flux Element - Sale Banner', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"style":{"color":{"background":"#e9f5ec"}},"className":"flux-elements-block flux-elements-block\u002d\u002dtype1","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"center","orientation":"horizontal"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--type1 has-background" style="background-color:#e9f5ec"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'star-circle.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p>Flash sale! Use code <b>DISCOUNTCODE</b> today only!</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->',
			)
		);

		/**
		 * Testimonial block.
		 */
		register_block_pattern(
			'flux-checkout/testimonial',
			array(
				'title'      => __( 'Flux Element - Testimonial', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"backgroundColor":"white","className":"flux-elements-block flux-elements-block\u002d\u002dtestimonial","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"top","orientation":"horizontal"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--testimonial has-white-background-color has-background"><!-- wp:image {"width":16,"height":16,"scale":"cover","sizeSlug":"large"} -->
				<figure class="wp-block-image size-large is-resized"><img src="' . $images_path . 'quote.svg" alt="" style="object-fit:cover;width:16px;height:16px" width="16" height="16"/></figure>
				<!-- /wp:image -->
				
				<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group"><!-- wp:paragraph -->
				<p class="flux-elements-block__testimonial-para">ShoeFly is the best website in the world to buy shoes. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna.</p>
				<!-- /wp:paragraph -->
				
				<!-- wp:image {"sizeSlug":"large","className":"flux-elements-block__star"} -->
				<figure class="wp-block-image size-large flux-elements-block__star"><img src=" ' . $images_path . '5stars.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p><strong>Adam Smith</strong></p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group --></div>
				<!-- /wp:group -->',
			)
		);

		/**
		 * Feature point.
		 */
		register_block_pattern(
			'flux-checkout/feature-point',
			array(
				'title'      => __( 'Flux Element - Feature Points', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"metadata":{"name":"Feature Points"},"className":"flux-elements-block__feature-point-wrap","layout":{"type":"constrained"}} -->
				<div class="wp-block-group flux-elements-block__feature-point-wrap"><!-- wp:group {"className":"flux-elements-block flux-elements-block\u002d\u002dfeature","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--feature"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'check.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p>First class support</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {"className":"flux-elements-block flux-elements-block\u002d\u002dfeature","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--feature"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'check.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p>Free delivery over $50</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {"className":"flux-elements-block flux-elements-block\u002d\u002dfeature","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--feature"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'check.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p>30-day returns</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {"className":"flux-elements-block flux-elements-block\u002d\u002dfeature","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--feature"><!-- wp:image {"sizeSlug":"large"} -->
				<figure class="wp-block-image size-large"><img src="' . $images_path . 'check.svg" alt=""/></figure>
				<!-- /wp:image -->
				
				<!-- wp:paragraph -->
				<p>Trusted by over 10,000 customers</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group --></div>
				<!-- /wp:group -->',
			)
		);

		/**
		 * FAQ
		 */
		register_block_pattern(
			'flux-checkout/faq',
			array(
				'title'      => __( 'Flux Element - Frequently Asked Questions', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => "<!-- wp:group {\"metadata\":{\"name\":\"FAQs\"},\"className\":\"flux-elements-block flux-elements-block\u002d\u002dfaq\",\"layout\":{\"type\":\"constrained\"}} -->
				<div class=\"wp-block-group flux-elements-block flux-elements-block--faq\"><!-- wp:heading {\"level\":3} -->
				<h3 class=\"wp-block-heading\" id=\"frequently-asked-questions\">Frequently Asked Questions</h3>
				<!-- /wp:heading -->
				
				<!-- wp:group {\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\"}} -->
				<div class=\"wp-block-group\"><!-- wp:paragraph {\"className\":\"flux-elements-block-faq__q\"} -->
				<p class=\"flux-elements-block-faq__q\"><strong>What payment methods do you accept?</strong></p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {\"className\":\"flux-elements-block-faq__a\"} -->
				<p class=\"flux-elements-block-faq__a\">We accept a variety of payment methods, including credit/debit cards (Visa, MasterCard, American Express), PayPal, and other digital wallets. The available options may vary based on your location. </p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\"}} -->
				<div class=\"wp-block-group\"><!-- wp:paragraph {\"className\":\"flux-elements-block-faq__q\"} -->
				<p class=\"flux-elements-block-faq__q\"><strong>How can I track my order?</strong></p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {\"className\":\"flux-elements-block-faq__a\"} -->
				<p class=\"flux-elements-block-faq__a\">After your order is shipped, you'll receive an email with a tracking number and a link to track your order. You can also check the status by logging into your account on our site.</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\"}} -->
				<div class=\"wp-block-group\"><!-- wp:paragraph {\"className\":\"flux-elements-block-faq__q\"} -->
				<p class=\"flux-elements-block-faq__q\"><strong>Do you offer international shipping?</strong></p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {\"className\":\"flux-elements-block-faq__a\"} -->
				<p class=\"flux-elements-block-faq__a\">Yes, we ship to various countries worldwide. Shipping costs and delivery times vary depending on the destination.</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->
				
				<!-- wp:group {\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\"}} -->
				<div class=\"wp-block-group\"><!-- wp:paragraph {\"className\":\"flux-elements-block-faq__q\"} -->
				<p class=\"flux-elements-block-faq__q\"><strong>Can I apply multiple discount codes to my order?</strong></p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {\"className\":\"flux-elements-block-faq__a\"} -->
				<p class=\"flux-elements-block-faq__a\">Only one discount code can be used per order. Discount codes cannot be combined with other promotional offers.</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group --></div>
				<!-- /wp:group -->",
			)
		);

		/**
		 * FAQ
		 */
		register_block_pattern(
			'flux-checkout/sale-banner',
			array(
				'title'      => __( 'Flux Element - Sale banner', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => "<!-- wp:group {\"metadata\":{\"name\":\"Sale Banner\"},\"style\":{\"elements\":{\"link\":{\"color\":{\"text\":\"var:preset|color|white\"}}}},\"backgroundColor\":\"black\",\"textColor\":\"white\",\"className\":\"flux-elements-block flux-elements-block\u002d\u002dbanner\",\"layout\":{\"type\":\"flex\",\"flexWrap\":\"nowrap\",\"justifyContent\":\"center\"}} -->
				<div class=\"wp-block-group flux-elements-block flux-elements-block--banner has-white-color has-black-background-color has-text-color has-background has-link-color\"><!-- wp:paragraph {\"align\":\"center\",\"style\":{\"elements\":{\"link\":{\"color\":{\"text\":\"var:preset|color|white\"}}}},\"textColor\":\"white\"} -->
				<p class=\"has-text-align-center has-white-color has-text-color has-link-color\">Flash sale! Use code <strong><span style=\"text-decoration: underline;\">DISCOUNTCODE</span></strong> today only!</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:group -->",
			)
		);

		/**
		 * Cards.
		 */
		register_block_pattern(
			'flux-checkout/cards',
			array(
				'title'      => __( 'Flux Element - Accepted credit cards', 'flux-checkout' ),
				'categories' => array( 'flux-checkout' ),
				'content'    => '<!-- wp:group {"className":"flux-elements-block flux-elements-block\u002d\u002dcards"} -->
				<div class="wp-block-group flux-elements-block flux-elements-block--cards"><!-- wp:paragraph {"className":"flux-elements-cards__heading"} -->
				<p class="flux-elements-cards__heading">We accept these credit/debit cards:</p>
				<!-- /wp:paragraph -->
				
				<!-- wp:group {"className":"","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group"><!-- wp:image {"id":10538,"sizeSlug":"full","linkDestination":"none"} -->
				<figure class="wp-block-image size-full"><img src="' . $images_path . 'visa.svg" alt="" class="wp-image-10538"/></figure>
				<!-- /wp:image -->
				
				<!-- wp:image {"id":10537,"sizeSlug":"full","linkDestination":"none"} -->
				<figure class="wp-block-image size-full"><img src="' . $images_path . 'mc.svg" alt="" class="wp-image-10537"/></figure>
				<!-- /wp:image -->
				
				<!-- wp:image {"id":10536,"sizeSlug":"full","linkDestination":"none"} -->
				<figure class="wp-block-image size-full"><img src="' . $images_path . 'discover.svg" alt="" class="wp-image-10536"/></figure>
				<!-- /wp:image -->
				
				<!-- wp:image {"id":10535,"sizeSlug":"full","linkDestination":"none"} -->
				<figure class="wp-block-image size-full"><img src="' . $images_path . 'amex.svg" alt="" class="wp-image-10535"/></figure>
				<!-- /wp:image --></div>
				<!-- /wp:group --></div>
				<!-- /wp:group -->',
			)
		);
	}
}
