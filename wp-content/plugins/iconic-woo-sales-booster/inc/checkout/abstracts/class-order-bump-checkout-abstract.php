<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Order_Bump_Checkout_Abstract.
 *
 * @class    Iconic_WSB_Order_Bump_Checkout_Abstract
 * @version  1.0.0
 * @category Abstract Class
 * @author   Iconic
 */
abstract class Iconic_WSB_Order_Bump_Checkout_Abstract {
	/**
	 * @var int bump id
	 */
	private $id;

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * Iconic_WSB_Order_Bump_Checkout constructor.
	 *
	 * @param int    $bump_id
	 *
	 * @param string $post_type
	 *
	 * @throws Exception
	 */
	public function __construct( $bump_id, $post_type ) {
		$this->post_type = $post_type;

		/**
		 * Filter the order bump ID.
		 *
		 * @since 1.23.0
		 * @hook iconic_wsb_order_bump_id
		 * @param  int    $bump_id   The bump ID.
		 * @param  string $post_type The bump post type.
		 * @return int New value
		 */
		$bump_id = apply_filters( 'iconic_wsb_order_bump_id', $bump_id, $post_type );

		$post = get_post( $bump_id );

		if ( $post instanceof WP_Post && $post->post_type === $post_type ) {
			$this->id   = $bump_id;
			$this->post = $post;
		} else {
			throw new Exception( 'Not valid bump id' );
		}
	}

	/**
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Return order bump WP Post
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Get order bump meta data
	 *
	 * @param string $name
	 * @param bool   $default
	 *
	 * @return mixed
	 */
	protected function get_meta( $name, $default = false ) {
		$value = get_post_meta( $this->get_id(), '_' . $name, true );

		$value = apply_filters( 'iconic_wsb_get_checkout_bump_meta', $value, $this, $name, $default );

		if ( ! $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Check if bump in cart
	 *
	 * @return bool|array
	 */
	public function in_cart_as_bump() {
		$cart_item = Iconic_WSB_Cart::get_cart_item_by_product_id( $this->get_product_offer()->get_id() );

		if ( isset( $cart_item['bump_price'] ) ) {
			return $cart_item;
		}

		return false;
	}

	/**
	 * Check if the bump condition is fulfilled
	 *
	 * @param bool $check_for_cart
	 *
	 * @return bool
	 */
	abstract public function is_suitable( $check_for_cart = true );

	/**
	 * Get all metadata associated to the Order Bump.
	 *
	 * @return void
	 */
	abstract public function get_all_metadata();

	/**
	 * Check suitability.
	 *
	 * @return bool
	 */
	public function check_suitability() {
		$display_for = $this->get_display_type();

		if ( 'specific' === $display_for ) {
			$needle_products    = array_map( 'intval', $this->get_specific_products() );
			$condition          = $this->get_apply_when_specific();
			$cart_product_count = count( WC()->cart->get_cart() );

			if ( 'all' === $condition ) {
				foreach ( $needle_products as $needle_product ) {
					if ( ! Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return false;
					}
				}

				return true;
			} elseif ( 'any' === $condition ) {
				foreach ( $needle_products as $needle_product ) {
					if ( Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return true;
					}
				}

				return false;
			} elseif ( 'only' === $condition ) {
				// Adjust the count if the offer was added to the cart.
				if ( $this->in_cart_as_bump() ) {
					$cart_product_count = --$cart_product_count;
				}

				if ( count( $needle_products ) !== $cart_product_count ) {
					return false;
				}

				foreach ( $needle_products as $needle_product ) {
					if ( ! Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return false;
					}
				}

				return true;
			} elseif ( 'none' === $condition ) {
				foreach ( $needle_products as $needle_product ) {
					if ( Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return false;
					}
				}

				return true;
			}
		} elseif ( 'categories' === $display_for ) {
			$needle_categories     = array_map( 'sanitize_title', $this->get_specific_categories() );
			$condition             = $this->get_apply_when_specific();
			$cart_categories_count = count( $this->get_cart_category_ids() );

			if ( 'all' === $condition ) {
				foreach ( $needle_categories as $needle_category ) {
					if ( ! Iconic_WSB_Cart::is_category_in_cart( $needle_category ) ) {
						return false;
					}
				}

				return true;
			} elseif ( 'any' === $condition ) {
				foreach ( $needle_categories as $needle_category ) {
					if ( Iconic_WSB_Cart::is_category_in_cart( $needle_category ) ) {
						return true;
					}
				}

				return false;
			} elseif ( 'only' === $condition ) {
				// Check if there is any category in cart that is not in the list of categories to apply the bump.
				$products_not_in_category = Iconic_WSB_Cart::get_cart_products_not_in_category( $needle_categories );

				// If there is even one product not in the category, return false.
				if ( ! empty( $products_not_in_category ) ) {
					return false;
				}

				foreach ( $needle_categories as $needle_category ) {
					if ( ! Iconic_WSB_Cart::is_category_in_cart( $needle_category ) ) {
						return false;
					}
				}

				return true;
			} elseif ( 'none' === $condition ) {
				foreach ( $needle_categories as $needle_category ) {
					if ( Iconic_WSB_Cart::is_category_in_cart( $needle_category ) ) {
						return false;
					}
				}

				return true;
			}
		}

		return true;
	}

	/**
	 * Get offer product price
	 *
	 * @return bool|float|int
	 */
	public function get_discount_price( $product_id = false ) {
		$product_id    = $product_id ? $product_id : $this->get_product_offer();
		$offer_product = wc_get_product( $product_id );
		$discount_type = $this->get_discount_type();
		$discount      = $this->get_discount();
		$initial_price = apply_filters( 'iconic_wsb_discounted_price_before_discount', $offer_product->get_price(), $product_id );
		if ( $offer_product && $discount_type && $discount ) {
			$discount_value = $discount_type == 'percentage' ? ( $initial_price / 100 ) * $discount : $discount;

			return $initial_price - $discount_value;
		}
		return $initial_price;
	}

	/**
	 * Get price HTML.
	 *
	 * @return string
	 */
	public function get_price_html( $product_id = false ) {
		$product_id     = $product_id ? $product_id : $this->get_product_offer();
		$html           = '';
		$offer_product  = wc_get_product( $product_id );
		$initial_price  = $offer_product->get_price( 'view' );
		$initial_price  = apply_filters( 'iconic_wsb_inital_price', $initial_price, $offer_product );
		$initial_price  = wc_get_price_to_display( $offer_product, array( 'price' => $initial_price ) );
		$discount_price = wc_get_price_to_display( $offer_product, array( 'price' => $this->get_discount_price( $product_id ) ) );

		if ( $initial_price > $discount_price ) {
			$html .= '<del>' . wc_price( $initial_price ) . '</del>';
		}

		$html .= wc_price( $discount_price );

		return apply_filters( 'iconic_wsb_price_html', $html, $offer_product, $initial_price, $discount_price );
	}

	/**
	 * Check if offered product is in cart
	 *
	 * @return bool
	 */
	public function is_in_cart() {
		$offer = $this->get_product_offer();
		if ( $offer ) {
			return Iconic_WSB_Cart::is_product_in_cart( $offer->get_id() );
		}
	}

	/**
	 * Generate priority for new bump
	 */
	public function generate_priority() {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT MAX(meta_value) AS last_priority FROM ' . $wpdb->postmeta . ' AS pm
		INNER JOIN ' . $wpdb->posts . ' AS p ON (pm.post_id = p.ID) WHERE meta_key = %s AND p.post_type = %s',
			'_priority',
			$this->post_type
		);

		$last_priority = $wpdb->get_row( $sql )->last_priority;

		if ( $last_priority ) {
			$this->set_priority( $last_priority + 1 );
		} else {
			$this->set_priority( 1 );
		}
	}

	/**
	 * Checks if this order bump offer is for a valid product.
	 *
	 * @return bool
	 */
	public function is_valid() {
		$product = $this->get_product_offer();

		if ( $product ) {
			return $product->is_in_stock() && $product->is_purchasable();
		}

		return false;
	}

	/**
	 * Set bump post as draft
	 */
	public function set_draft() {
		wp_update_post(
			array(
				'ID'          => $this->get_id(),
				'post_status' => 'draft',
			)
		);
	}

	/**
	 * Update bump meta
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return bool|int
	 */
	protected function update_meta( $name, $value ) {
		return update_post_meta( $this->get_id(), '_' . $name, $value );
	}

	/**
	 * @param $default
	 *
	 * @return int
	 */
	public function get_priority( $default = false ) {
		return (int) $this->get_meta( 'priority', $default );
	}

	/**
	 * @param int $priority
	 *
	 * @return bool|int
	 */
	public function set_priority( $priority ) {
		return $this->update_meta( 'priority', $priority );
	}

	/**
	 * @param $default
	 *
	 * @return string
	 */
	public function get_display_type( $default = false ) {
		return $this->get_meta( 'display_type', $default );
	}

	/**
	 * Update display type for checkout bump
	 *
	 * @param string $display_type
	 *
	 * @return bool|int
	 */
	public function set_display_type( $display_type ) {
		if ( ! in_array( $display_type, array( 'all', 'specific', 'categories' ) ) ) {
			return false;
		}

		return $this->update_meta( 'display_type', $display_type );
	}

	/**
	 * @param $default
	 *
	 * @return string
	 */
	public function get_apply_when_specific( $default = false ) {
		return $this->get_meta( 'apply_when_specific', $default );
	}

	/**
	 * Update applying type when display type is specific
	 *
	 * @param string $apply_when_specific
	 *
	 * @return bool|int
	 */
	public function set_apply_when_specific( $apply_when_specific ) {
		if ( ! in_array( $apply_when_specific, array( 'any', 'all', 'only', 'none' ) ) ) {
			return false;
		}

		return $this->update_meta( 'apply_when_specific', $apply_when_specific );
	}

	/**
	 * Get 'enable bump for same product' meta.
	 *
	 * If true then the order bump could be visible even
	 * if the same offer product is already in cart.
	 *
	 * @return bool enable?
	 */
	public function get_enable_bump_for_same_product() {
		return (bool) $this->get_meta( 'enable_bump_for_same_product', false );
	}

	/**
	 * Set 'enable bump for same product' meta.
	 *
	 * @param boolean $enable Enable or not.
	 *
	 * @return mixed
	 */
	public function set_enable_bump_for_same_product( $enable ) {
		return $this->update_meta( 'enable_bump_for_same_product', $enable );
	}

	/**
	 * @param $default
	 *
	 * @return int[]
	 */
	public function get_specific_products( $default = false ) {
		return $this->get_meta( 'specific_products', $default );
	}

	/**
	 * @param $default
	 *
	 * @return int[]
	 */
	public function get_specific_categories( $default = false ) {
		return $this->get_meta( 'specific_categories', $default );
	}

	/**
	 * @param array $specific_products
	 *
	 * @return mixed
	 */
	public function set_specific_products( $specific_products ) {
		if ( ! is_array( $specific_products ) ) {
			return false;
		}

		$specific_products = array_map( 'intval', $specific_products );

		return $this->update_meta( 'specific_products', $specific_products );
	}

	/**
	 * @param array $specific_products
	 *
	 * @return mixed
	 */
	public function set_specific_categories( $specific_categories ) {
		if ( ! is_array( $specific_categories ) ) {
			return false;
		}

		$specific_categories = array_map( 'sanitize_title', $specific_categories );

		return $this->update_meta( 'specific_categories', $specific_categories );
	}

	/**
	 * @param $default
	 *
	 * @return WC_Product
	 */
	public function get_product_offer( $default = false ) {
		return wc_get_product( $this->get_meta( 'product_offer', $default ) );
	}

	/**
	 * @param int $product_offer
	 *
	 * @return bool|int
	 */
	public function set_product_offer( $product_offer ) {
		return $this->update_meta( 'product_offer', intval( $product_offer ) );
	}

	/**
	 * @param $default
	 *
	 * @return int
	 */
	public function get_discount( $default = false ) {
		return $this->get_meta( 'discount', $default );
	}

	/**
	 * @param int $discount
	 *
	 * @return bool|int
	 */
	public function set_discount( $discount ) {
		return $this->update_meta( 'discount', floatval( $discount ) );
	}

	/**
	 * @param $default
	 *
	 * @return string
	 */
	public function get_discount_type( $default = false ) {
		return $this->get_meta( 'discount_type', $default );
	}

	/**
	 * @param string $discount_type
	 *
	 * @return bool|int
	 */
	public function set_discount_type( $discount_type ) {
		if ( ! in_array( $discount_type, array( 'simple', 'percentage' ) ) ) {
			return false;
		}

		return $this->update_meta( 'discount_type', $discount_type );
	}

	/**
	 * @param $default
	 *
	 * @return int
	 */
	public function get_impression_count( $default = 0 ) {
		return $this->get_meta( 'impression_count', $default );
	}

	/**
	 * @param int $impression
	 *
	 * @return bool|int
	 */
	public function set_impression_count( $impression ) {
		return $this->update_meta( 'impression_count', intval( $impression ) );
	}

	/**
	 * Increase impression
	 *
	 * @param int $count
	 */
	public function increase_impression_count( $count = 1 ) {
		$impression  = $this->get_impression_count();
		$impression += $count;

		$this->set_impression_count( $impression );
	}

	/**
	 * @param $default
	 *
	 * @return int
	 */
	public function get_purchases_count( $default = 0 ) {
		return (int) $this->get_meta( 'purchases_count', $default );
	}

	/**
	 * @param int $impression
	 *
	 * @return bool|int
	 */
	public function set_purchases_count( $impression ) {
		return $this->update_meta( 'purchases_count', intval( $impression ) );
	}

	/**
	 * Increase impression
	 *
	 * @param int $count
	 */
	public function increase_purchases_count( $count = 1 ) {
		$impression  = $this->get_purchases_count();
		$impression += $count;

		$this->set_purchases_count( $impression );
	}

	/**
	 * Get Added Revenue.
	 *
	 * @param float|int $default The default value.
	 *
	 * @return float
	 */
	public function get_added_revenue( $default = 0 ) {
		return (float) $this->get_meta( 'added_revenue', $default );
	}

	/**
	 * Set Added Revenue value.
	 *
	 * @param float $added_revenue The added revenue value.
	 *
	 * @return bool|int
	 */
	public function set_added_revenue( $added_revenue ) {
		return $this->update_meta( 'added_revenue', floatval( $added_revenue ) );
	}

	/**
	 * Increase Added Revenue
	 *
	 * @param float $bump_price The bump price.
	 */
	public function increase_added_revenue( $bump_price ) {
		if ( empty( $bump_price ) ) {
			return;
		}

		if ( ! is_numeric( $bump_price ) ) {
			return;
		}

		$added_revenue  = $this->get_added_revenue();
		$added_revenue += $bump_price;

		$this->set_added_revenue( $added_revenue );
	}

	/**
	 * @return float|int
	 */
	public function get_conversion_rate() {
		if ( $this->get_purchases_count( 0 ) === 0 or $this->get_impression_count( 0 ) === 0 ) {
			return 0;
		}

		return $this->get_purchases_count( 0 ) / $this->get_impression_count( 1 );
	}

	/**
	 * Get the target rate value.
	 *
	 * @param number $default The default target rate.
	 *
	 * @return float|false
	 */
	public function get_target_rate( $default = 0 ) {
		return $this->get_meta( 'target_rate', $default );
	}

	/**
	 * Set the target rate.
	 *
	 * @param number $target_rate The target rate value.
	 *
	 * @return bool|int
	 */
	public function set_target_rate( $target_rate ) {
		$target_rate = $target_rate > 0 ? $target_rate : 0;

		return $this->update_meta( 'target_rate', floatval( $target_rate ) );
	}

	/**
	 * Get the `allow_product_offer_quantity_change` value.
	 *
	 * @param bool $default The default value.
	 *
	 * @return bool
	 */
	public function get_allow_product_offer_quantity_change( $default = false ) {
		return (bool) $this->get_meta( 'allow_product_offer_quantity_change', $default );
	}

	/**
	 * Set the `allow_product_offer_quantity_change`.
	 *
	 * @param bool $allow_product_offer_quantity_change The `allow_product_offer_quantity_change` value.
	 *
	 * @return bool|int
	 */
	public function set_allow_product_offer_quantity_change( $allow_product_offer_quantity_change ) {
		return $this->update_meta( 'allow_product_offer_quantity_change', (bool) $allow_product_offer_quantity_change );
	}

	/**
	 * Get Cart Category IDs.
	 *
	 * Get all of the IDs of all of the categories that the products within the cart are in.
	 *
	 * @return array Cart Category IDs.
	 */
	public function get_cart_category_ids() {
		$cart_categories_ids = array();
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_categories_ids = array_merge(
				$cart_categories_ids,
				$cart_item['data']->get_category_ids()
			);
		}
		return $cart_categories_ids;
	}
}
