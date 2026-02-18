<?php
/**
 * Iconic_Flux_Sidebar.
 *
 * The main jumping off point for the plugin.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Sidebar.
 *
 * @class    Iconic_Flux_Sidebar.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Sidebar {
	/**
	 * Run.
	 */
	public static function run() {
		// Cart Page Redirect.
		add_action( 'template_redirect', array( __CLASS__, 'redirect_template_to_checkout' ) );

		// Sidebar Actions.
		add_action( 'init', array( __CLASS__, 'sidebar_actions' ) );
	}

	/**
	 * Sidebar Actions.
	 *
	 * @return void
	 */
	public static function sidebar_actions() {
		if ( ! self::is_sidebar_enabled() ) {
			return;
		}

		if ( '1' === Iconic_Flux_Core_Settings::$settings['general_general_skip_cart_page'] ) {
			// Remove the cart buttons.
			remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		}

		// Add ghost row for spacing.
		add_action( 'woocommerce_review_order_before_order_total', array( __CLASS__, 'review_order_add_ghost_row' ), 100 );

		// Change the order review area position.
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
		add_action( 'flux_checkout_order_review', 'woocommerce_order_review', 10 );

		// Change the coupon form position.
		$coupon_position = self::get_coupon_position();
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		add_action( $coupon_position, array( __CLASS__, 'checkout_add_coupon_form' ), 9 );

		// Add image to checkout.
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'add_image_to_cart' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_class', array( __CLASS__, 'cart_item_class' ), 10, 3 );

		// Add cart controls.
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( __CLASS__, 'cart_quantity_control' ), 100, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'cart_remove_link' ), 100, 3 );

		// Add strikethrough regular price to the cart item subtotal in order review.
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'add_regular_price_to_cart_item_subtotal' ), 100, 3 );

		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'handle_cart_qty_update' ) );

		add_filter( 'woocommerce_order_button_html', array( __CLASS__, 'modify_place_order_button' ) );
	}

	/**
	 * Modify Place order button.
	 *
	 * @param string $html Button HTML.
	 *
	 * @return string
	 */
	public static function modify_place_order_button( $html ) {
		if ( ! Iconic_Flux_Helpers::is_modern_theme() ) {
			return $html;
		}

		ob_start();
		?>
		<footer class="flux-footer">
			<?php
			/**
			 * Fires at the start of the footer on the final step of the checkout.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_footer_start_final_step' );

			Iconic_Flux_Steps::back_button( 'payment' );
			echo wp_kses_post( $html );

			/**
			 * Fires at the end of the footer on the final step of the checkout.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_footer_end_final_step' );
			?>
		</footer>
		<?php

		return ob_get_clean();
	}

	/**
	 * Add ghost row to order review for spacing.
	 */
	public static function review_order_add_ghost_row() {
		if ( ! Iconic_Flux_Helpers::is_modern_theme() ) {
			return;
		}

		echo '<tr class="flux-checkout__order-review-ghost-row"><th></th><td></td></tr>';
	}

	/**
	 * Remove checkout shipping fields as we add them ourselves.
	 */
	public static function remove_checkout_shipping() {
		remove_action( 'woocommerce_checkout_shipping', array( WC_Checkout::instance(), 'checkout_form_shipping' ) );
	}

	/**
	 * Is Sidebar Enabled.
	 *
	 * @return boolean
	 */
	public static function is_sidebar_enabled() {
		$settings     = Iconic_Flux_Core_Settings::$settings;
		$theme        = Iconic_Flux_Core::get_theme();
		$show_sidebar = isset( $settings['styles_theme_show_sidebar'] ) && ! empty( $settings['styles_theme_show_sidebar'] ) ? (bool) $settings['styles_theme_show_sidebar'] : false;

		if ( 'classic' !== $theme ) {
			$show_sidebar = true;
		}

		return $show_sidebar;
	}

	/**
	 * Redirect Template to Checkout.
	 *
	 * @return void
	 */
	public static function redirect_template_to_checkout() {
		if ( ! self::is_sidebar_enabled() || '0' === Iconic_Flux_Core_Settings::$settings['general_general_skip_cart_page'] ) {
			return;
		}

		/**
		 * Redirection status for skip cart.
		 *
		 * @since 2.8.0
		 */
		$redirect_status = apply_filters( 'flux_skip_cart_redirection_status', 301 );

		if ( is_cart() && 0 === WC()->cart->cart_contents_count ) {
			wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ), $redirect_status );
			exit;
		}

		$queried_object = get_queried_object();

		if ( ! is_object( $queried_object ) || ! property_exists( $queried_object, 'ID' ) || wc_get_page_id( 'cart' ) !== $queried_object->ID ) {
			return;
		}

		$cancel_order = filter_input( INPUT_GET, 'cancel_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! empty( $cancel_order ) ) {
			return;
		}

		/**
		 * Check cart items are valid.
		 *
		 * @since 2.1.0
		 */
		do_action( 'woocommerce_check_cart_items' );
		if ( wc_notice_count( 'error' ) > 0 ) {
			return;
		}

		wp_safe_redirect( wc_get_checkout_url(), $redirect_status );
		exit;
	}

	/**
	 * Add coupon form inside order summary section.
	 */
	public static function checkout_add_coupon_form() {
		if ( ! Iconic_Flux_Helpers::is_coupon_enabled() ) {
			return;
		}

		?>
			<tr class="coupon-form"><td colspan="2">
				<?php Iconic_Flux_Steps::render_coupon_form(); ?>
			</td></tr>
		<?php
	}

	/**
	 * Add image to cart.
	 *
	 * @param string $name          Name.
	 * @param array  $cart_item     Cart Item.
	 * @param int    $cart_item_key Cart Item Key.
	 *
	 * @return string
	 */
	public static function add_image_to_cart( $name, $cart_item, $cart_item_key ) {
		if ( ! is_checkout() ) {
			return $name;
		}

		if ( ! $cart_item['data']->get_image_id() ) {
			return $name;
		}

		$settings = Iconic_Flux_Core_Settings::$settings;

		/**
		 * Filter to modify the cart item thumbnail.
		 *
		 * @since 2.3.0
		 */
		$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $cart_item['data']->get_image(), $cart_item, $cart_item_key );

		if ( isset( $settings['general_general_make_cart_images_clickable'] ) && '1' === $settings['general_general_make_cart_images_clickable'] ) {
			$thumbnail = sprintf( "<a href='%s'>%s</a>", $cart_item['data']->get_permalink(), $thumbnail );
		}

		$image = '<div class="flux-cart-image flux-cart-image--checkout flux-checkout__cart-image">' . $thumbnail . '</div>';

		return $image . $name;
	}

	/**
	 * Add no image class to cart item.
	 *
	 * @param string $class         Class.
	 * @param array  $cart_item     Cart item.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return mixed|string
	 */
	public static function cart_item_class( $class, $cart_item, $cart_item_key ) {
		if ( $cart_item['data']->get_image_id() ) {
			return $class;
		}

		$class .= ' flux-cart-item--no-image';

		return $class;
	}

	/**
	 * Cart Quantity Control.
	 *
	 * @param string $output        Output of quantity.
	 * @param array  $cart_item     Cart Item.
	 * @param string $cart_item_key Cart Item Key.
	 *
	 * @return string
	 */
	public static function cart_quantity_control( $output, $cart_item, $cart_item_key ) {
		$_product = wc_get_product( $cart_item['product_id'] );

		if ( ! is_object( $_product ) ) {
			return $output;
		}

		$product_quantity = woocommerce_quantity_input(
			array(
				'input_name'   => "cart[{$cart_item_key}][qty]",
				'input_value'  => $cart_item['quantity'],
				'max_value'    => $_product->get_max_purchase_quantity(),
				'min_value'    => '0',
				'product_name' => $_product->get_name(),
			),
			$_product,
			false
		);

		/**
		 * Filter to modify cart item quantity.
		 *
		 * @since 2.1.0
		 */
		return apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
	}

	/**
	 * Cart Remove Link.
	 *
	 * @param string $output Output of quantity.
	 * @param array  $cart_item Cart Item.
	 * @param string $cart_item_key Cart Item Key.
	 * @return string
	 */
	public static function cart_remove_link( $output, $cart_item, $cart_item_key ) {
		if ( ! is_checkout() ) {
			return $output;
		}

		$_product = wc_get_product( $cart_item['product_id'] );

		if ( ! is_a( $_product, 'WC_Product' ) ) {
			return $output;
		}

		/**
		 * Filter remove from cart link HTML.
		 *
		 * @param string $link          Remove link.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @since 2.0.0
		 */
		$remove_link = apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'woocommerce_cart_item_remove_link',
			sprintf(
				'<a href="%s" class="remove" aria-label="%s" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
				esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
				esc_html__( 'Remove this item', 'flux-checkout' ),
				esc_html__( 'Remove this item', 'flux-checkout' ),
				esc_attr( $cart_item['product_id'] ),
				esc_attr( $_product->get_sku() )
			),
			$cart_item_key
		);

		return '<span class="flux-checkout__remove-link">' . $remove_link . '</span>' . $output;
	}

	/**
	 * Handle cart quantity update.
	 *
	 * @param string $post_data Post data.
	 *
	 * @return void
	 */
	public static function handle_cart_qty_update( $post_data ) {
		$data   = array();
		$status = true;
		parse_str( $post_data, $data );

		if ( empty( $data['cart'] ) || ! is_array( $data['cart'] ) ) {
			return;
		}

		foreach ( $data['cart'] as $cart_key => $qty ) {
			$status = self::update_product_quantity( $cart_key, $qty['qty'] );

			if ( is_array( $status ) ) {
				break;
			}
		}

		if ( ! is_array( $status ) ) {
			return;
		}

		// Add the error message to Order review Fragments.
		add_action(
			'woocommerce_update_order_review_fragments',
			function( $fragments ) use ( $status ) {
				if ( ! is_array( $status ) || ! isset( $status['error'] ) ) {
					return $fragments;
				}

				if ( ! isset( $fragments['flux'] ) ) {
					$fragments['flux'] = array();
				}

				$fragments['flux'] = array(
					'global_error' => $status['error'],
				);

				return $fragments;
			}
		);
	}

	/**
	 * Update product item quantity.
	 *
	 * @param string $cart_item_key Cart Item key.
	 * @param int    $quantity      Quantity.
	 *
	 * @return true|array Returns `true` if update is successful, `Array` if there is an error.
	 */
	public static function update_product_quantity( $cart_item_key, $quantity ) {
		$updated   = array();
		$cart      = WC()->cart->get_cart();
		$cart_item = isset( $cart[ $cart_item_key ] ) ? $cart[ $cart_item_key ] : false;
		$product   = isset( $cart_item['data'] ) ? $cart_item['data'] : false;

		$current_session_order_id = isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0;

		if ( empty( $product ) ) {
			return false;
		}

		// is_sold_individually.
		if ( $product->is_sold_individually() && $quantity > 1 ) {
			/* Translators: %s Product title. */
			$msg     = sprintf( esc_html__( 'You can only have 1 %s in your cart.', 'flux-checkout' ), $product->get_name() );
			$updated = array(
				'error' => $msg,
			);

			return $updated;
		}

		// We only need to check products managing stock, with a limited stock qty.
		if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
			// Check stock based on all items in the cart and consider any held stock within pending orders.
			$held_stock = wc_get_held_stock_quantity( $product, $current_session_order_id );

			if ( $product->get_stock_quantity() < ( $held_stock + $quantity ) ) {
				/* translators: 1: product name 2: quantity in stock */
				$msg = sprintf( __( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'flux-checkout' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) );

				$updated = array(
					'error' => $msg,
				);

				return $updated;
			}
		}

		// Support partial qty.
		if ( ! is_numeric( $quantity ) ) {
			return $updated;
		}

		if ( empty( $quantity ) ) {
			$updated = WC()->cart->remove_cart_item( $cart_item_key );
		} else {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $cart_item, $quantity );

			if ( $passed_validation ) {
				$updated = WC()->cart->set_quantity( $cart_item_key, absint( $quantity ), true );
			}
		}

		return $updated;
	}

	/**
	 * Get coupon position.
	 *
	 * @return string
	 */
	public static function get_coupon_position() {
		if ( ! wp_is_mobile() ) {
			return 'woocommerce_review_order_after_cart_contents';
		}

		return Iconic_Flux_Core_Settings::$settings['general_mobile_coupon_position'];
	}

	/**
	 * Add strikethrough regular price to cart item subtotal in order review section.
	 *
	 * @param string $subtotal Subtotal.
	 * @param array  $cart_item Cart item.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return string
	 */
	public static function add_regular_price_to_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return $subtotal;
		}

		// return if product is not on sale.
		if ( ! $cart_item['data']->is_on_sale() ) {
			return $subtotal;
		}

		return '<del>' . wc_price( $cart_item['data']->get_regular_price() * $cart_item['quantity'] ) . '</del> <ins>' . $subtotal . '</ins>';
	}
}
