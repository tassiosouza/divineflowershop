<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Iconic_WSB_Helpers' ) ) {
	return;
}

/**
 * Iconic_WSB_Helpers.
 *
 * @class    Iconic_WSB_Helpers
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Helpers {

	/**
	 * uses wc_dropdown_variation_attribute_options() to show the atributes dropdown
	 * If the product is a variation then adds 'wsb_select_readonly' class for those
	 * attributes which don't have 'any' value. On frontend JS code will make
	 * '.wsb_select_readonly' readonly
	 *
	 * @param array $args Arguments same as wc_dropdown_variation_attribute_options()
	 */
	public static function wc_dropdown_variation_attribute_options( $args = array() ) {

		/**
		 * Fires just before outputting dropdown variation attribute options.
		 *
		 * @hook  iconic_wsb_before_wc_dropdown_variation_attribute_options
		 * @since 1.9.0
		 *
		 * @param array $args The arguments used to render the attribute options.
		 */
		do_action( 'iconic_wsb_before_wc_dropdown_variation_attribute_options', $args );

		if ( $args['product']->is_type( 'variable' ) ) {
			wc_dropdown_variation_attribute_options( $args );
		} elseif ( $args['product']->is_type( 'variation' ) ) {

			$product         = $args['product'];
			$parent          = wc_get_product( $args['product']->get_parent_id() );
			$attribute       = $product->get_attribute( $args['attribute'] );
			$args['product'] = $parent;

			// if attribute is preselected then 1) hide the dropdown 2) only show the label
			if ( $attribute !== '' ) {
				$args['class'] .= ' wsb_select_readonly ';
				echo "<div style='display:none'>";
				wc_dropdown_variation_attribute_options( $args );
				echo '</div>';
				echo "<span class='iconic-wsb-variation__select_replace_label'>$attribute</span>";
			} else {
				wc_dropdown_variation_attribute_options( $args );
			}
		}

		/**
		 * Fires just after outputting dropdown variation attribute options.
		 *
		 * @hook  iconic_wsb_before_wc_dropdown_variation_attribute_options
		 * @since 1.9.0
		 *
		 * @param array $args The arguments used to render the attribute options.
		 */
		do_action( 'iconic_wsb_after_wc_dropdown_variation_attribute_options', $args );
	}

	/**
	 * Get Formatted Name.
	 *
	 * Add a few custom extras into the product name.
	 *
	 * @param object $product Product Object.
	 * @return string Product Name.
	 */
	public static function get_formatted_name( $product ) {
		// Get the formatted name from Woo.
		$product_name = wp_strip_all_tags( $product->get_formatted_name() );

		// Woo doesn't put a space between the product ID and the product name.
		$product_name = str_replace( ')', ') ', $product_name );

		// Prepend the name with the product ID.
		$product_name = '#' . $product->get_id() . ' - ' . $product_name;

		// Add a filter so users can customise the name.
		return apply_filters( 'iconic_wsb_admin_dropdown_product_name', $product_name, $product );
	}

	/**
	 * Append `Out of stock` text.
	 *
	 * @param string     $product_name The product name.
	 * @param WC_Product $product      The product object.
	 * @return string
	 */
	public static function append_out_of_stock_text( $product_name, $product ) {
		if ( ! is_string( $product_name ) ) {
			return $product_name;
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $product_name;
		}

		if ( ! $product->is_in_stock() ) {
			$product_name .= ' &ndash; ' . __( 'Out of stock', 'iconic-wsb' );
		}

		return $product_name;
	}

	/**
	 * Append `Price %d` text when the price is equal or less than zero.
	 *
	 * @param string     $product_name The product name.
	 * @param WC_Product $product      The product object.
	 * @return string
	 */
	public static function append_price_text_if_equal_or_less_than_zero( $product_name, $product ) {
		if ( ! is_string( $product_name ) ) {
			return $product_name;
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $product_name;
		}

		$price = $product->get_price();

		if ( 0 >= $price ) {
			// translators: %s: product price.
			$product_name .= ' &ndash; ' . sprintf( __( 'Price: %s', 'iconic-wsb' ), wc_price( $price ) );
		}

		return $product_name;
	}

}
