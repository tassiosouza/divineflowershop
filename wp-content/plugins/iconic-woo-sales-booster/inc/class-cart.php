<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Cart
 *
 * @class    Iconic_WSB_Order_Bump
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Cart {
	/**
	 * Run
	 */
	public static function run() {
		self::hooks();
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		add_action( 'wp_loaded', array( __CLASS__, 'add_to_cart_action' ), 11 );
		add_action( 'iconic_wsb_before_get_cart_fragments_for_ajax_fbt_add_to_cart', array( __CLASS__, 'add_message_to_the_cart_fragments' ), 10, 2 );
		add_filter( 'woocommerce_cart_id', array( __CLASS__, 'exclude_parent_id_from_cart_id' ), 11, 5 );
	}

	/**
	 * Handler for iconic-wsb-products-add-to-cart request (Frequently Bought Together)
	 */
	public static function add_to_cart_action() {
		if ( ! isset( $_REQUEST['iconic-wsb-add-selected'] ) || empty( $_REQUEST['iconic-wsb-products-add-to-cart'] ) || ! is_array( $_REQUEST['iconic-wsb-products-add-to-cart'] ) ) {
			return;
		}

		$single_product_id  = $_REQUEST['iconic-wsb-fbt-this-product'];
		$product_ids        = array_map( 'absint', array_filter( $_REQUEST['iconic-wsb-products-add-to-cart'] ) );
		$redirect_after_add = get_option( 'woocommerce_cart_redirect_after_add' );
		$message            = '';

		add_filter(
			'pre_woocommerce_cart_redirect_after_add',
			function () {
				return 'no';
			}
		);

		$added_all_to_cart          = true;
		$products_not_added_to_cart = array();

		$result                     = wp_parse_args(
			self::add_products_to_cart( $product_ids ),
			array(
				'added_all_to_cart'          => true,
				'products_not_added_to_cart' => array(),
			)
		);
		$added_all_to_cart          = $result['added_all_to_cart'];
		$products_not_added_to_cart = $result['products_not_added_to_cart'];

		if ( $added_all_to_cart ) {
			$message = esc_html__( 'All products were added to your cart.', 'iconic-wsb' );
			$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View cart', 'iconic-wsb' ), $message );
		}

		if ( ! empty( $products_not_added_to_cart ) ) {

			// Only show default woo message if we are not doing ajax.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				return;
			}

			$product_title = $products_not_added_to_cart[0];

			// Translators: Product title.
			$message = sprintf( esc_html__( 'Sorry, the following product could not be added to the cart: "%s"', 'iconic-wsb' ), $product_title );

			if ( count( $products_not_added_to_cart ) > 1 ) {
				$product_title = implode( '", "', $products_not_added_to_cart );

				// Translators: Product title.
				$message = sprintf( esc_html__( 'Sorry, the following products could not be added to the cart: "%s"', 'iconic-wsb' ), $product_title );
			}
		}

		// To prevent the main product from adding to cart twice.
		unset( $_REQUEST['add-to-cart'] );
		unset( $_REQUEST['variation_id'] );

		$redirect = apply_filters( 'iconic_wsb_cart_redirect_after_add', false );

		if ( wp_doing_ajax() ) {
			/**
			 * Fires before the refreshed fragments are returned when a product
			 * is added via AJAX in the product page.
			 *
			 * @since 1.14.0
			 * @hook iconic_wsb_before_get_cart_fragments_for_ajax_fbt_add_to_cart
			 * @param string $message           The message about add to cart action.
			 * @param bool   $added_all_to_cart Whether all products were added to the cart.
			 */
			do_action( 'iconic_wsb_before_get_cart_fragments_for_ajax_fbt_add_to_cart', $message, $added_all_to_cart );

			WC_AJAX::get_refreshed_fragments();
		} else {
			$message_type = $added_all_to_cart ? 'success' : 'error';
			wc_add_notice( $message, $message_type );
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		} elseif ( 'yes' === $redirect_after_add ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}

	/**
	 * Add FBT products to the cart.
	 *
	 * @param int[] $product_ids The array of product IDs to be added to the cart.
	 * @return arary Returns an array with `added_all_to_cart` and `products_not_added_to_cart` keys.
	 */
	public static function add_products_to_cart( $product_ids ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		$single_product_id          = empty( $_REQUEST['iconic-wsb-fbt-this-product'] ) ? 0 : absint( $_REQUEST['iconic-wsb-fbt-this-product'] );
		$added_all_to_cart          = true;
		$products_not_added_to_cart = array();

		foreach ( $product_ids as $product_add_to_cart ) {
			$product                     = wc_get_product( $product_add_to_cart );
			$quantity                    = 1;
			$add_to_cart                 = false;
			$variation_dropdown_name     = 'iconic-wsb-products-add-to-cart-variation-' . $product_add_to_cart;
			$variation_attributes_hidden = 'iconic-wsb-bump-product_attributes-' . $product_add_to_cart;
			$meta_data                   = array(
				'iconic_wsb_fbt' => $single_product_id,
			);

			if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) {
				if ( ! empty( $_REQUEST[ $variation_dropdown_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$variation_id   = absint( filter_input( INPUT_POST, $variation_dropdown_name, FILTER_SANITIZE_NUMBER_INT ) );
					$variation_data = filter_input( INPUT_POST, $variation_attributes_hidden );
					$variation_data = json_decode( $variation_data, true );

					/**
					 * Filter the product metadata to be added to the cart.
					 *
					 * @since 1.14.0
					 * @hook iconic_wsb_fbt_before_cart_metadata
					 * @param  array $meta_data  The metadata.
					 * @param  int   $product_id The product ID.
					 * @return array New value
					 */
					$meta_data = apply_filters( 'iconic_wsb_fbt_before_cart_metadata', $meta_data, $variation_id );
					/**
					 * Filters if an item being added to the cart passed validation checks. Default: true.
					 *
					 * @since 1.14.0
					 * @see `woocommerce_add_to_cart_validation` doc in CartController::validate_add_to_cart in WooCommerce.
					 */
					$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_add_to_cart, $quantity, $variation_id, $meta_data );

					if ( ! $passed_validation ) {
						continue;
					}

					$variation_data_parepared = self::prepare_variation_data_before_add_to_cart( $variation_data );

					$add_to_cart = WC()->cart->add_to_cart( $product_add_to_cart, $quantity, $variation_id, $variation_data_parepared, $meta_data );
				}
			} else {
				/**
				 * Filter the product metadata to be added to the cart.
				 *
				 * @see `iconic_wsb_fbt_before_cart_metadata` hook documentation.
				 * @since 1.14.0
				 */
				$meta_data = apply_filters( 'iconic_wsb_fbt_before_cart_metadata', $meta_data, $product_add_to_cart );
				/**
				 * Filters if an item being added to the cart passed validation checks. Default: true.
				 *
				 * @since 1.14.0
				 * @see `woocommerce_add_to_cart_validation` doc in CartController::validate_add_to_cart function in WooCommerce.
				 */
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_add_to_cart, $quantity, 0, $meta_data );

				if ( ! $passed_validation ) {
					continue;
				}

				$add_to_cart = WC()->cart->add_to_cart( $product_add_to_cart, $quantity, 0, array(), $meta_data );
			}

			if ( false === $add_to_cart ) {
				$added_all_to_cart            = false;
				$products_not_added_to_cart[] = $product->get_title();
			}
		}

		$result = array(
			'added_all_to_cart'          => $added_all_to_cart,
			'products_not_added_to_cart' => $products_not_added_to_cart,
		);

		return $result;
	}

	/**
	 * Refresh the FBT panel.
	 *
	 * @return string FBT Html.
	 */
	public static function get_updated_fbt_panel() {
		global $product;

		if ( ! isset( $_REQUEST['post_id'] ) ) {
			return;
		}

		// Get the post id from the AJAX call.
		$post_id = intval( $_REQUEST['post_id'] );

		// Make sure the product is the current product.
		$product = wc_get_product( $post_id );

		// Get the current FBT panel instance.
		$fbt = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance();

		// Render the panel into buffer.
		ob_start();

		$fbt->frontend_product_page_order_bump();
		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Check if product in cart
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_product_in_cart( $product_id ) {
		return self::get_cart_item_by_product_id( $product_id ) != false;
	}

	/**
	 * Check if any of the items in the cart are in the category.
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public static function is_category_in_cart( $slug ) {

		$needle_category = get_term_by( 'slug', $slug, 'product_cat' );
		$category_ids    = array();
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data']->is_type( 'variation' ) ? wc_get_product( $cart_item['data']->get_parent_id() ) : $cart_item['data'];

			if ( empty( $product ) || ! empty( $cart_item['iconic_wsb_at_checkout'] ) ) {
				continue;
			}

			$category_ids = array_merge(
				$category_ids,
				$product->get_category_ids()
			);
		}

		return in_array( $needle_category->term_id, $category_ids, true );
	}

	/**
	 * Get the cart products which do not belong to the given categories.
	 *
	 * @param array $categories Category slugs.
	 *
	 * @return array
	 */
	public static function get_cart_products_not_in_category( $categories ) {
		$category_ids           = array();
		$products_not_belonging = array();

		// Prepare category IDs.
		foreach ( $categories as $category ) {
			$term = get_term_by( 'slug', $category, 'product_cat' );
			if ( is_a( $term, 'WP_Term' ) ) {
				$category_ids[] = $term->term_id;
			}
		}

		// Loop through all cart items and add the product IDs to an array which do not belong to the given categories.
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data']->is_type( 'variation' ) ? wc_get_product( $cart_item['data']->get_parent_id() ) : $cart_item['data'];

			if ( empty( $product ) ) {
				continue;
			}

			$product_category_ids = $product->get_category_ids();

			if ( ! array_intersect( $category_ids, $product_category_ids ) ) {
				// Do not count products added by us.
				if ( isset( $cart_item['iconic_wsb_at_checkout'] ) || isset( $cart_item['iconic_wsb_after_checkout'] ) ) {
					continue;
				}

				$products_not_belonging[] = $product->get_id();
			}
		}

		return $products_not_belonging;
	}

	/**
	 * Get cart item form WC_Cart by product id
	 *
	 * @param int $needle_product_id Needle Product ID.
	 *
	 * @return bool
	 */
	public static function get_cart_item_by_product_id( $needle_product_id ) {
		if ( empty( $needle_product_id ) ) {
			return false;
		}

		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return false;
		}

		$match = false;

		if ( ! WC()->cart ) {
			return;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_item_product_id   = $cart_item['product_id'];
			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;

			if ( $needle_product->is_type( 'variable' ) && $cart_item_parent_id === $needle_product_id ) {
				$match = true;
			} elseif ( $needle_product->is_type( 'variation' ) && $cart_item_variation_id === $needle_product_id ) {
				$match = true;
			} elseif ( $needle_product->is_type( 'variation' ) && $cart_item_parent_id === $needle_product->get_parent_id() && $cart_item_variation_id !== $needle_product_id ) {
				/**
				 * If you have a variation that is a partial variation, Woo will add that to the cart
				 * instead of the actual variation.
				 *
				 * If this happens we need to loop through all the variations and do a partial match on the attributes.
				 */
				$match = false;

				$needle_attributes    = $needle_product->get_attributes();
				$cart_item_attributes = $cart_item['variation'];

				// Cleanup the keys.
				foreach ( array_keys( $cart_item_attributes ) as $cart_item_attribute_key ) {
					$new_cart_item_key                          = str_replace( 'attribute_', '', $cart_item_attribute_key );
					$cart_item_attributes[ $new_cart_item_key ] = $cart_item_attributes[ $cart_item_attribute_key ];
					unset( $cart_item_attributes[ $cart_item_attribute_key ] );
				}

				if ( $needle_attributes === $cart_item_attributes ) {
					$match = true;
				}
			} elseif ( $cart_item_product_id === $needle_product_id ) {
				$match = true;
			}

			if ( $match ) {
				return $cart_item;
			}
		}

		return false;
	}

	/**
	 * Removes any product from cart which has meta 'iconic_wsb_after_checkout' or 'iconic_wsb_at_checkout' which means
	 * the product was added by Woocoomerce Sales Booster.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 */
	public static function remove_previously_added_item( $meta_key ) {
		global $woocommerce;
		$cart_item = self::get_cart_item( $meta_key );
		if ( is_array( $cart_item ) ) {
			WC()->cart->remove_cart_item( $cart_item['key'] );
		}
	}

	/**
	 * Loops through all the cart items to find the item added by Woocommerce sales booster.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 *
	 * @return $cart_item | false
	 */
	public static function get_cart_item( $meta_key ) {
		global $woocommerce;

		foreach ( $woocommerce->cart->get_cart() as $key => $cart_item ) {
			if ( isset( $cart_item[ $meta_key ] ) ) {
				return $cart_item;
			}
		}

		return false;
	}

	/**
	 * Returns variation data for the product which is in cart, if no product is in cart for that bump_id then returns
	 * false.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 *
	 * @return array variation_data | false
	 */
	public static function get_cart_item_variation_data( $meta_key ) {
		$cart_item = self::get_cart_item( $meta_key );
		if ( isset( $cart_item['variation'] ) ) {
			return $cart_item['variation'];
		} elseif ( is_a( $cart_item['data'], 'WC_Product_Variable' ) || is_a( $cart_item['data'], 'WC_Product_Variation' ) ) {
			$variation_data = $cart_item['data']->get_variation_attributes();

			return $variation_data;
		}

		return false;
	}

	/**
	 * Remove from cart by product id
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function remove_from_cart( $product_id ) {
		if ( self::is_product_in_cart( $product_id ) ) {
			foreach ( WC()->cart->cart_contents as $key => $cart_item ) {
				$product_item_id = empty( $cart_item['variation_id'] ) ? $cart_item['product_id'] : $cart_item['variation_id'];

				if ( $product_item_id == $product_id ) {
					WC()->cart->remove_cart_item( $key );
				}
			}
		}

		return true;
	}

	/**
	 * Add product to WC cart
	 *
	 * @param int|WC_Product $product        Product Object.
	 * @param int            $quantity       Quantity.
	 * @param array          $metadata       Meta Data.
	 * @param array          $variation_data Variation Data.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function add_to_cart( $product, $quantity = 1, $metadata = array(), $variation_data = null ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

		if ( $product ) {
			$variation_id = $product->is_type( 'variable' ) ? $product->get_id() : null;
			$product_id   = $product->is_type( 'variable' ) ? $product->get_parent_id() : $product->get_id();
			$metadata     = empty( $metadata ) ? null : $metadata;

			// If variation_data is not provided, let's fetch it from the variation.
			if ( $product->is_type( 'variation' ) && $variation_data == null ) {
				$variation_data = array();
				if ( $variation_data == null ) {
					foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
						$taxonomy                                = str_replace( 'attribute_', '', $taxonomy );
						$attribute_label_name                    = wc_attribute_label( $taxonomy );
						$variation_data[ $attribute_label_name ] = $term_names;
					}
				}
			}

			if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes 'attribute_' from the array key
	 *
	 * @param arr $variation_data Variation Data.
	 *
	 * @return array
	 */
	public static function remove_variation_key_prefix( $variation_data ) {
		$result_arr = array();
		if ( $variation_data && is_array( $variation_data ) ) {
			foreach ( $variation_data as $attribute_key => $attribute_value ) {
				if ( strpos( $attribute_key, 'attribute_' ) === 0 ) {
					$attribute_key = substr( $attribute_key, 10 ); // remove 'attribute_'
				}
				$result_arr[ $attribute_key ] = $attribute_value;
			}
		}

		return $result_arr;
	}

	/**
	 * Prepare variation data before adding the product to cart.
	 *
	 * @param array $variation_data Attribute data passed to PHP via AJAX.
	 *
	 * @return array
	 */
	public static function prepare_variation_data_before_add_to_cart( $variation_data ) {
		$variation_data_parepared = array();

		if ( ! is_array( $variation_data ) ) {
			return $variation_data_parepared;
		}

		foreach ( $variation_data as $attribute => $term ) {
			$variation_data_parepared[ $attribute ] = $term['slug'];
		}

		return $variation_data_parepared;
	}

	/**
	 * Exclude Parent ID from Cart ID.
	 *
	 * FBT passes the meta of the parent ID with the key `iconic_wsb_fbt`, doing
	 * this gives the item id a different cart_id than if it were added using
	 * a default method.
	 *
	 * We do not want to fully override the cart_id as other meta may be set, but
	 * we can discount this key, to make sure the cart ID remains the same no
	 * matter how a product is added.
	 *
	 * @param string $cart_id        Cart item key.
	 * @param int    $product_id     Id of the product the key is being generated for.
	 * @param int    $variation_id   Variation ID of the product the key is being generated for.
	 * @param array  $variation      Data for the cart item.
	 * @param array  $cart_item_data Other cart item data passed which affects this items uniqueness in the cart.
	 *
	 * @return string cart item key.
	 */
	public static function exclude_parent_id_from_cart_id( $cart_id, $product_id, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
		// If the `iconic_wsb_fbt` is not set, we can just return the $cart_id.
		if ( ! is_array( $cart_item_data ) || ! isset( $cart_item_data['iconic_wsb_fbt'] ) ) {
			return $cart_id;
		}

		// Unset the `iconic_wsb_fbt` key for this calculation.
		unset( $cart_item_data['iconic_wsb_fbt'] );

		// Unhook this function.
		remove_filter( 'woocommerce_cart_id', array( __CLASS__, 'exclude_parent_id_from_cart_id' ), 11, 5 );

		// Do Generate the cart id as normal.
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// Re-hook this function.
		add_filter( 'woocommerce_cart_id', array( __CLASS__, 'exclude_parent_id_from_cart_id' ), 11, 5 );

		return $cart_id;
	}

	/**
	 * Add the message to the fragments.
	 *
	 * This is required when the product is added via AJAX
	 * in the product page.
	 *
	 * @param string $message           The message about add to cart action.
	 * @param bool   $added_all_to_cart Whether all products were added to the cart.
	 */
	public static function add_message_to_the_cart_fragments( $message, $added_all_to_cart ) {
		add_filter(
			'woocommerce_add_to_cart_fragments',
			function( $fragments ) use ( $message, $added_all_to_cart ) {
				$class_notice = $added_all_to_cart ? 'woocommerce-message' : 'woocommerce-error';

				$fragments['.woocommerce-notices-wrapper'] = '<div class="woocommerce-notices-wrapper"><div class="' . $class_notice . '">' . $message . '</div></div>';

				return $fragments;
			}
		);
	}
}
