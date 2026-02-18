<?php
/**
 * Cart related function.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

use Iconic_WDS\Subscriptions\Boot;

use function WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS dates class.
 */
class Cart {

	/**
	 * Products.
	 *
	 * @var array
	 */
	public $products;

	/**
	 * Constructor.
	 *
	 * @param array $products Associative array of products in this format array( product_id_1 => qty_1, product_id_2 => qty_2, ... ).
	 */
	public function __construct( $products = false ) {
		$this->products = $this->prepare_products( $products );
	}

	/**
	 * Create cart from order.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return Cart
	 */
	public static function from_order( $order ) {
		$cart     = new self();
		$products = $order->get_items();

		if ( empty( $products ) ) {
			return $cart;
		}

		foreach ( $products as $product ) {
			$cart->products[ $product->get_id() ] = self::prepare_product( $product->get_product(), $product->get_quantity() );
		}

		return $cart;
	}

	/**
	 * Prepare products from the given array.
	 *
	 * @param array $products Associative array of products=>qty.
	 *
	 * @return array
	 */
	public function prepare_products( $products ) {
		if ( empty( $products ) ) {
			$products = array();

			if ( ! empty( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $item ) {
					$products [ $item['data']->get_id() ] = $item['quantity'];
				}
			}
		}

		$prepared_products = array();

		foreach ( $products as $product_id => $qty ) {
			$prepared = $this->prepare_product( $product_id, $qty );

			if ( $prepared ) {
				$prepared_products[] = $prepared;
			}
		}

		return $prepared_products;
	}

	/**
	 * Product IDs.
	 *
	 * @param WC_Product $product Product object.
	 * @param int        $qty     Quantity.
	 *
	 * @return array
	 */
	public static function prepare_product( $product, $qty ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( empty( $product ) ) {
			return false;
		}

		return array(
			'id'             => $product->get_id(),
			'parent_id'      => $product->get_parent_id() ? $product->get_parent_id() : $product->get_id(),
			'name'           => $product->get_name(),
			'type'           => $product->get_type(),
			'needs_shipping' => $product->needs_shipping(),
			'quantity'       => $qty,
		);
	}

	/**
	 * Refresh cart products.
	 *
	 * @return void
	 */
	public function refresh_cart() {
		$this->products = $this->prepare_products( false );
	}

	/**
	 * Get product IDs.
	 *
	 * @param bool $return_parent_id_if_available Return the parent ID if the product is a variation.
	 *
	 * @return array.
	 */
	public function get_products_ids( $return_parent_id_if_available = true ) {
		$product_ids = array();

		foreach ( $this->products as $product ) {
			$product_ids[] = absint( $product[ $return_parent_id_if_available ? 'parent_id' : 'id' ] );
		}

		return $product_ids;
	}

	/**
	 * Needs shipping.
	 *
	 * @return bool
	 */
	public function needs_shipping() {
		if ( ! wc_shipping_enabled() || 0 === wc_get_shipping_method_count( true ) ) {
			return false;
		}

		if ( empty( $this->products ) ) {
			return true;
		}

		$needs_shipping = false;

		foreach ( $this->products as $product ) {
			if ( $product['needs_shipping'] ) {
				$needs_shipping = true;
				break;
			}
		}

		return $needs_shipping;
	}


	/**
	 * Get total quantity.
	 *
	 * @return int
	 */
	public function get_total_qty() {
		$total_qty = 0;

		foreach ( $this->products as $product ) {
			$total_qty += $product['quantity'];
		}

		return $total_qty;
	}

	/**
	 * Check if date/time should be active based on
	 * categories of products in the cart.
	 *
	 * @return bool
	 */
	public function is_delivery_slots_allowed_for_category() {
		global $iconic_wds;

		$exclude_categories           = $iconic_wds->settings['general_setup_exclude_categories'];
		$exclude_categories_condition = isset( $iconic_wds->settings['general_setup_exclude_categories_condition'] ) ? $iconic_wds->settings['general_setup_exclude_categories_condition'] : 'any';

		if ( empty( $exclude_categories ) ) {
			return true;
		}

		if ( 'any' === $exclude_categories_condition ) {
			return $this->is_delivery_slots_allowed_for_category_any( $exclude_categories );
		} else {
			return $this->is_delivery_slots_allowed_for_category_all( $exclude_categories );
		}
	}

	/**
	 * Checks if date/time should be enabled based on categories
	 * in the cart, and considering that the settings for Exclude
	 * Product Condition is set to "Any".
	 *
	 * @param array $exclude_categories List of categories for which datepicker will be disabled.
	 *
	 * @return bool
	 */
	public function is_delivery_slots_allowed_for_category_any( $exclude_categories ) {
		global $iconic_wds;

		$product_ids = $this->get_products_ids();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( empty( $product ) ) {
				continue;
			}

			$product_parent_id = $product->get_parent_id();

			if ( $product_parent_id ) {
				$product = wc_get_product( $product_parent_id );
			}

			$category_ids = $product->get_category_ids();

			if ( empty( $category_ids ) ) {
				continue;
			}

			$compare = array_intersect( $exclude_categories, $category_ids );

			if ( empty( $compare ) ) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * Checks if date/time should be enabled based on categories
	 * in the cart, and considering that the settings for Exclude
	 * Product Condition is set to "All".
	 *
	 * @param array $exclude_categories List of categories for which datepicker will be disabled.
	 *
	 * @return bool
	 */
	public function is_delivery_slots_allowed_for_category_all( $exclude_categories ) {
		global $iconic_wds;

		$product_ids = $this->get_products_ids();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( empty( $product ) ) {
				continue;
			}

			$product_parent_id = $product->get_parent_id();

			if ( $product_parent_id ) {
				$product = wc_get_product( $product_parent_id );
			}

			$product_categories = $product->get_category_ids();

			// Show the date/time fields because this product doesn't belong to any category.
			if ( empty( $product_categories ) ) {
				return true;
			}

			$common = array_intersect( $exclude_categories, $product_categories );

			/*
			If we get one product which doesn't belong to the exclude categories,
			we need to show date/time fields for it.
			*/
			if ( empty( $common ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if date/time should be active based on
	 * products in the cart.
	 *
	 * @return bool
	 */
	public function is_delivery_slots_allowed_for_product() {
		global $iconic_wds;
		$exclude_condition = isset( $iconic_wds->settings['general_setup_exclude_products_condition'] ) ? $iconic_wds->settings['general_setup_exclude_products_condition'] : 'any';
		$exclude_products  = $iconic_wds->settings['general_setup_exclude_products'];
		$hide_timeslot     = false;

		if ( ! is_array( $exclude_products ) || empty( $exclude_products ) ) {
			return true;
		}

		$exclude_products = array_map( 'absint', $exclude_products );
		$cart_product_ids = $this->get_products_ids();

		if ( 'all' === $exclude_condition ) {
			// Hide timeslots when all products from exclusion list are in the cart.
			$diff = array_diff( $exclude_products, $cart_product_ids );

			if ( 0 === count( $diff ) ) {
				$hide_timeslot = true;
			}
		} else {
			// Hide timeslots even if there is one common product between cart and exclsion list.
			$common = array_intersect( $exclude_products, $cart_product_ids );
			if ( count( $common ) > 0 ) {
				$hide_timeslot = true;
			}
		}

		return ! $hide_timeslot;
	}

	/**
	 * Get count of total products.
	 *
	 * @return int
	 */
	public function get_cart_contents_count() {
		if ( empty( $this->products ) ) {
			return 0;
		}

		$qty = 0;
		foreach ( $this->products as $product ) {
			$qty += absint( $product['quantity'] );
		}

		return $qty;
	}

	/**
	 * Check if the cart has any non-subscription product.
	 *
	 * @return bool
	 */
	public function has_non_subscription_product() {
		$subscription_integration = Boot::get_active_integration();

		if ( ! $subscription_integration ) {
			return false;
		}

		foreach ( $this->products as $product ) {
			if ( ! $subscription_integration->is_subscription_product( wc_get_product( $product['id'] ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get regular shipping method ID.
	 *
	 * @return string|bool
	 */
	public function get_regular_shipping_method_id() {
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen_shipping_methods ) ) {
			return false;
		}

		return $chosen_shipping_methods[0];
	}

	/**
	 * Get subscription shipping method ID.
	 *
	 * @return string|bool
	 */
	public function get_subscription_shipping_method_id() {
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen_shipping_methods ) ) {
			return false;
		}

		// The other shipping method is the next one after index 0, which may have an unknown key.
		foreach ( $chosen_shipping_methods as $key => $method ) {
			if ( $key !== 0 && $key !== '0' ) {
				return $method;
			}
		}

		// If shipping method not found, it means that the subscription shipping method is the only one.
		return $this->get_regular_shipping_method_id();
	}

	/**
	 * Check if all products in the cart are virtual.
	 *
	 * @return bool
	 */
	public function are_all_products_virtual() {
		if ( empty( $this->products ) ) {
			return false;
		}

		foreach ( $this->products as $product ) {
			if ( $product['needs_shipping'] ) {
				return false;
			}
		}

		return true;
	}
}
