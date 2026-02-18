<?php
/**
 * Cart.
 *
 * All the cart functions.
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */

/**
 * Driver Login.
 *
 * All the login functions.
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */
class GMFW_CART {
	/**
	 * Add to cart.
	 *
	 * @param int $product_id product id
	 * @return void
	 */
	public function add_to_cart( $product_id ) {
		ob_start();
		$quantity          = 1;
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) && 'publish' === $product_status ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			wc_add_to_cart_message( $product_id );
			ob_end_clean();
		} else {
			$data = array(
				'error' => true,
			);
			// @var TYPE_NAME $data.
			return wp_send_json( $data );
		}

		return wp_send_json(
			array(
				'status' => 200,
				'body'   => 'added',
			)
		);
	}
	/**
	 * Remove from cart.
	 *
	 * @param int $product_id product id
	 * @return void
	 */
	public function remove_from_cart( $product_id ) {

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( strval( $cart_item['product_id'] ) === strval( $product_id ) ) {

				WC()->cart->remove_cart_item( $cart_item_key );

				$product   = wc_get_product( $product_id );
				$cart_item = WC()->cart->get_cart_item( $cart_item_key );
				/* translators: %s: Item name. */
				$item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'gmfw' ), $product->get_name() ) : __( 'Item', 'gmfw' ), $cart_item );

				/* Translators: %s Product title. */
				$removed_notice = sprintf( __( '%s removed.', 'gmfw' ), $item_removed_title );

				wc_add_notice( $removed_notice, apply_filters( 'woocommerce_cart_item_removed_notice_type', 'gmfw' ) );

				return wp_send_json(
					array(
						'status' => 200,
						'body'   => 'removed',
					)
				);
			}
		}

		wp_send_json_error();

	}

}
