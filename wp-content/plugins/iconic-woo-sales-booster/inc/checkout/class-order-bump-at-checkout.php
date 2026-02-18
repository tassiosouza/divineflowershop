<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'abstracts/class-order-bump-checkout-abstract.php';

/**
 * Iconic_WSB_Order_Bump_Checkout.
 *
 * @class    Iconic_WSB_Order_Bump_Checkout
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_At_Checkout extends Iconic_WSB_Order_Bump_Checkout_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_Checkout constructor.
	 *
	 * @param int $bump_id Bump ID.
	 *
	 * @throws Exception
	 */
	public function __construct( $bump_id ) {
		parent::__construct( $bump_id, Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->get_post_type() );
	}

	/**
	 * Get bump image
	 *
	 * @param array $size Size.
	 *
	 * @return array|bool|false|string
	 */
	public function get_offer_image_src( $size = array( 100, 100 ) ) {
		$image = false;

		$size = apply_filters( 'iconic_wsb_order_bump_image_size', $size, $this, $this->get_product_offer() );

		if ( $this->get_custom_image_id( 0 ) > 0 ) {
			$image = wp_get_attachment_image_url( $this->get_custom_image_id(), $size );
		}

		if ( ! $image ) {
			$offer_product = wc_get_product( $this->get_product_offer() );

			if ( $offer_product ) {
				$image = wp_get_attachment_image_url( $offer_product->get_image_id(), $size );
			}
		}

		return $image ? $image : wc_placeholder_img_src( $size );
	}

	/**
	 * Get Custom Image ID.
	 *
	 * @param mixed $default ID.
	 *
	 * @return int
	 */
	public function get_custom_image_id( $default = false ) {
		return $this->get_meta( 'custom_image_id', $default );
	}

	/**
	 * Set Custom Image ID.
	 *
	 * @param int $attachment_id ID.
	 *
	 * @return bool|int
	 */
	public function set_custom_image_id( $attachment_id ) {
		return $this->update_meta( 'custom_image_id', intval( $attachment_id ) );
	}

	/**
	 * Get Checkbox Text.
	 *
	 * @param string $default Default Text.
	 *
	 * @return string
	 */
	public function get_checkbox_text( $default = false ) {
		return $this->get_meta( 'checkbox_text', $default );
	}

	/**
	 * Set Checkbox Text.
	 *
	 * @param string $checkbox_text Checkbox Text.
	 *
	 * @return bool|int
	 */
	public function set_checkbox_text( $checkbox_text ) {
		return $this->update_meta( 'checkbox_text', $checkbox_text );
	}

	/**
	 * Get Bump Description.
	 *
	 * @param mixed $default Description.
	 *
	 * @return string
	 */
	public function get_bump_description( $default = false ) {
		return $this->get_meta( 'bump_description', $default );
	}

	/**
	 * Set Bump Description.
	 *
	 * @param string $bump_description Description.
	 *
	 * @return bool|int
	 */
	public function set_bump_description( $bump_description ) {
		return $this->update_meta( 'bump_description', $bump_description );
	}

	/**
	 * Is Suitable.
	 *
	 * @inheritDoc
	 */
	public function is_suitable( $check_for_cart = true ) {
		if ( WC()->cart->is_empty() ) {
			return false;
		}

		if ( $check_for_cart && $this->is_in_cart() && ! self::in_cart_as_bump() ) {
			return false;
		}

		if ( ! $this->is_valid() ) {
			return false;
		}

		/**
		 * Check suitability at checkout.
		 *
		 * Checks the suitability of the order bump at the checkout.
		 *
		 * @param bool $suitability Suitability from `check_suitability` function.
		 *
		 * @return bool.
		 */
		return apply_filters( 'iconic_wsb_check_suitability_at_checkout', $this->check_suitability() );
	}

	/**
	 * Get Render Settings.
	 *
	 * @param $default Use default if set.
	 *
	 * @return array
	 */
	public function get_render_settings( $default = false ) {
		$default = $default ? $default : array(
			'highlight_color' => '#333333',
			'border_color'    => '#E2E2E2',
			'border_style'    => 'solid',
			'show_image'      => 'yes',
			'show_shadow'     => 'yes',
			'show_price'      => 'yes',
			'position'        => 'woocommerce_review_order_before_submit',
		);

		return $this->get_meta( 'render_settings', $default );
	}

	/**
	 * Set Render Settings.
	 *
	 * @param array $render_settings Render settings array.
	 *
	 * @return bool|int
	 */
	public function set_render_settings( $render_settings ) {
		$defaults = array(
			'highlight_color' => '#333333',
			'border_color'    => '#E2E2E2',
			'border_style'    => 'solid',
			'show_image'      => 'yes',
			'show_shadow'     => 'yes',
			'show_price'      => 'yes',
			'position'        => 'woocommerce_review_order_before_submit',
		);

		$render_settings = wp_parse_args( $render_settings, $defaults );

		return $this->update_meta( 'render_settings', $render_settings );
	}

	/**
	 * Get count of click on order bump
	 *
	 * @param int $default Default value.
	 *
	 * @return int
	 */
	public function get_clicks_count( $default = 0 ) {
		return (int) $this->get_meta( 'clicks_count', $default );
	}

	/**
	 * Increase clicks count on $on value
	 *
	 * @param int $on Amount to increase.
	 *
	 * @return bool|int
	 */
	public function increase_click_count( $on = 1 ) {
		return $this->set_clicks_count( $this->get_clicks_count() + $on );
	}

	/**
	 * Set clicks count
	 *
	 * @param int $count Count.
	 *
	 * @return bool|int
	 */
	public function set_clicks_count( $count ) {
		return $this->update_meta( 'clicks_count', $count );
	}

	/**
	 * Get all metadata associated to the order bump at checkout.
	 *
	 * @return array
	 */
	public function get_all_metadata() {
		return array(
			'_display_type'                 => $this->get_display_type(),
			'_apply_when_specific'          => $this->get_apply_when_specific(),
			'_specific_products'            => $this->get_specific_products(),
			'_specific_categories'          => $this->get_specific_categories(),
			'_enable_bump_for_same_product' => $this->get_enable_bump_for_same_product(),
			'_product_offer'                => $this->get_product_offer(),
			'_discount'                     => $this->get_discount(),
			'_discount_type'                => $this->get_discount_type(),
			'_checkbox_text'                => $this->get_checkbox_text(),
			'_custom_image_id'              => $this->get_custom_image_id(),
			'_bump_description'             => $this->get_bump_description(),
			'_render_settings'              => $this->get_render_settings(),
		);
	}
}
