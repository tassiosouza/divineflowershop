<?php
/**
 * Iconic_Flux_Checkout_Element.
 *
 * Checkout Element.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Checkout_Element' ) ) {
	return;
}

/**
 * Iconic_Flux_Checkout_Element.
 *
 * @class    Iconic_Flux_Checkout_Elements.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Checkout_Element {

	/**
	 * Post object.
	 *
	 * @var WP_Post
	 */
	public $post;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Position
	 *
	 * @var string
	 */
	private $position;

	/**
	 * Constructor.
	 *
	 * @param int $post_id Post ID.
	 */
	public function __construct( $post_id ) {
		$this->post     = get_post( $post_id );
		$this->position = get_post_meta( $post_id, 'fce_position', true );

		$settings       = get_post_meta( $post_id, 'fce_settings', true );
		$this->settings = ! empty( $settings ) ? json_decode( $settings, true ) : array();

		$this->maybe_convert_old_rules_format();
	}

	/**
	 * Save
	 *
	 * @param string $position Postion.
	 * @param string $settings Settings.
	 *
	 * @return void
	 */
	public function save( $position, $settings ) {
		update_post_meta( $this->post->ID, 'fce_position', $position );
		update_post_meta( $this->post->ID, 'fce_settings', $settings );
	}

	/**
	 * Get element data
	 *
	 * @return array
	 */
	public function get_element_data() {
		return array(
			'settings' => $this->settings,
			'position' => array(
				'value' => $this->position,
				'hook'  => $this->get_position_hook(),
				'wrap'  => $this->get_position_wrap(),
			),
		);
	}

	/**
	 * Get position hook.
	 *
	 * @return int
	 */
	public function get_position_hook() {
		$position = explode( ':', $this->position );
		return isset( $position[1] ) ? $position[1] : 10;
	}

	/**
	 * Get position wrap.
	 *
	 * @return string
	 */
	public function get_position_wrap() {
		$position = explode( ':', $this->position );
		return isset( $position[2] ) ? $position[2] : '';
	}

	/**
	 * Do rules match for the given checkout element.
	 *
	 * @return bool
	 */
	public function do_rules_match() {
		$data     = $this->get_element_data();
		$order_id = Iconic_Flux_Thankyou::get_thankyou_page_order_id();

		if ( empty( $data['settings'] ) ) {
			return true;
		}

		$settings = $data['settings'];

		if ( empty( $settings['enable_rules'] ) || empty( $settings['rules'] ) ) {
			return true;
		}

		foreach ( $settings['rules'] as $or_rule ) {
			$match = $this->does_rule_group_match( $or_rule, $order_id );

			if ( $match ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does rule group match.
	 *
	 * @param array $or_rule OR Rule.
	 * @param int   $order_id Order ID.
	 *
	 * @return bool
	 */
	public function does_rule_group_match( $or_rule, $order_id = false ) {
		if ( empty( $or_rule ) ) {
			return false;
		}

		$match = null;

		foreach ( $or_rule as $rule ) {
			if ( empty( $rule ) ) {
				continue;
			}

			if ( 'product_cat' === $rule['object'] ) {
				$category_ids = wp_list_pluck( $rule['value'], 'code' );
				$category_ids = array_map( 'intval', $category_ids );
				$present      = $order_id ? self::are_categories_present_in_order( $category_ids, $order_id ) : self::are_categories_present_in_cart( $category_ids );
				$match        = 'is' === $rule['condition'] ? $present : ! $present;
			} elseif ( 'product' === $rule['object'] ) {
				$product_ids = wp_list_pluck( $rule['value'], 'code' );
				$product_ids = array_map( 'intval', $product_ids );
				$present     = $order_id ? self::are_products_in_order( $product_ids, $order_id ) : self::are_products_in_cart( $product_ids, $order_id );
				$match       = 'is' === $rule['condition'] ? $present : ! $present;
			} elseif ( 'user_role' === $rule['object'] ) {
				$user_roles = self::get_user_roles();
				$present    = in_array( $rule['value'], $user_roles, true );
				$match      = 'is' === $rule['condition'] ? $present : ! $present;
			} elseif ( 'cart_total' === $rule['object'] ) {
				$total = $order_id ? self::get_order_total( $order_id ) : WC()->cart->cart_contents_total;
				$value = floatval( $rule['value'] );

				if ( '<' === $rule['condition'] ) {
					$match = $total < $value;
				} elseif ( '<=' === $rule['condition'] ) {
					$match = $total <= $value;
				} elseif ( '>' === $rule['condition'] ) {
					$match = $total > $value;
				} elseif ( '>=' === $rule['condition'] ) {
					$match = $total >= $value;
				} elseif ( 'is' === $rule['condition'] ) {
					$match = intval( $total ) === intval( $value );
				} elseif ( 'is_not' === $rule['condition'] ) {
					$match = intval( $total ) !== intval( $value );
				}
			}

			if ( ! $match ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Are given products in cart.
	 *
	 * @param int $product_ids Product Ids.
	 *
	 * @return bool
	 */
	public static function are_products_in_cart( $product_ids ) {
		if ( empty( WC()->cart ) ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( $cart_item['data']->is_type( 'variation' ) ) {
				$product_id = $cart_item['data']->get_parent_id();
			}

			if ( in_array( $product_id, $product_ids ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Are categories present in the cart.
	 *
	 * @param array $category_ids array of categories ID, slug or name.
	 *
	 * @return bool
	 */
	public static function are_categories_present_in_cart( $category_ids ) {
		if ( empty( WC()->cart ) ) {
			return false;
		}

		$has_item = false;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product    = $cart_item['data'];
			$product_id = ! empty( $product->get_parent_id() ) ? $product->get_parent_id() : $product->get_id();

			if ( has_term( $category_ids, 'product_cat', $product_id ) ) {
				$has_item = true;

				// Break because we only need one "true" to matter here.
				break;
			}
		}

		return $has_item;
	}

	/**
	 * Get user roles.
	 *
	 * @return array
	 */
	public static function get_user_roles() {
		if ( ! is_user_logged_in() ) {
			return array( 'guest' );
		}

		global $current_user;
		return $current_user->roles;
	}

	/**
	 * Returns true if the products belonging to these category IDs present in this Order.
	 *
	 * @param int $category_ids Category IDS.
	 * @param int $order_id     Order ID.
	 *
	 * @return bool
	 */
	public static function are_categories_present_in_order( $category_ids, $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return false;
		}

		$items = $order->get_items();

		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();

			if ( has_term( $category_ids, 'product_cat', $product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Are given product IDs present in the given order.
	 *
	 * @param array $product_ids Array of product IDs.
	 * @param int   $order_id    Order Id.
	 *
	 * @return bool
	 */
	public static function are_products_in_order( $product_ids, $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return false;
		}

		$items = $order->get_items();

		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();

			if ( in_array( $product_id, $product_ids, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Order total.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return float
	 */
	public static function get_order_total( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return false;
		}

		return $order->get_total();
	}

	/**
	 * Maybe convert old rules format.
	 *
	 * @return void
	 */
	public function maybe_convert_old_rules_format() {
		/**
		 * The outer array contains OR conditions.
		 * The inner array contains AND conditions.
		 *
		 * If in the old format all_rules_must_match is true that means all rules were AND conditions.
		 * Else all rules were OR conditions.
		 *
		 * Based on this, we need to convert the old format to the new format.
		 */
		if ( ! empty( $this->settings['rules'][0] ) && empty( $this->settings['rules'][0][0] ) ) {
			if ( $this->settings['all_rules_must_match'] ) {
				// Create a single OR rule and add all the AND rules to it.
				$this->settings['rules'] = array(
					$this->settings['rules'],
				);
			} else {
				// Create a single OR rule for each AND rule.
				$new_rules = array();

				foreach ( $this->settings['rules'] as $rule ) {
					$new_rules[] = array(
						$rule,
					);
				}

				$this->settings['rules'] = $new_rules;
			}
		}
	}
}
