<?php
/**
 * Compatibility with YITH WooCommerce Added to Cart Popup.
 *
 * @see https://yithemes.com/themes/plugins/yith-woocommerce-added-to-cart-popup/
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_YITH_WooCommerce_Added_To_Cart_Popup class.
 *
 * @since 1.14.0
 */
class Iconic_WSB_Compat_YITH_WooCommerce_Added_To_Cart_Popup {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ), 15 );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		if ( ! class_exists( 'YITH_WACP' ) ) {
			return;
		}

		add_action( 'woocommerce_ajax_added_to_cart', array( __CLASS__, 'add_bump_products_to_cart' ) );
		add_action( 'iconic_wsb_fbt_after_add_selected_button', array( __CLASS__, 'add_products_via_fbt_field' ) );
		add_action( 'wp_footer', array( __CLASS__, 'script_to_handle_products_submit_via_fbt_section' ) );

		add_filter( 'iconic_wsb_l10n_frontend_data_script', array( __CLASS__, 'disable_fbt_ajax_setting_in_frontend_data_script' ) );
	}

	/**
	 * Add bump products to the cart.
	 *
	 * By default, the WACP plugin adds only the main product to the cart,
	 * that way it's necessary to add the product bumps.
	 *
	 * @param int $product_id The product ID added to the cart.
	 * @return void
	 */
	public static function add_bump_products_to_cart( $product_id ) {
		if (
			empty( $product_id ) ||
			// phpcs:disable WordPress.Security.NonceVerification
			empty( $_POST['action'] ) ||
			'yith_wacp_add_item_cart' !== $_POST['action'] ||
			empty( $_POST['iconic-wsb-products-add-to-cart'] ) ||
			empty( $_POST['iconic-wsb-fbt-this-product'] ) ||
			empty( $_POST['iconic-wsb-add-products-via-fbt'] )
			// phpcs:enable WordPress.Security.NonceVerification
		) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$wsb_products = map_deep( wp_unslash( $_POST['iconic-wsb-products-add-to-cart'] ), 'sanitize_text_field' );

		unset( $wsb_products[ $product_id ] );

		Iconic_WSB_Cart::add_products_to_cart( $wsb_products );
	}

	/**
	 * Add an input field to check if the products are added
	 * via Frequently Bought Together section.
	 *
	 * Since YITH WACP handle the form submit, all data is sent
	 * even when the user clicks on the button "Add to cart". That way,
	 * we use this field to know if the products were sent via
	 * FBT section.
	 *
	 * @return void
	 */
	public static function add_products_via_fbt_field() {
		?>
			<input type="hidden" name="iconic-wsb-add-products-via-fbt" value="" />
		<?php
	}

	/**
	 * Update the field `iconic-wsb-add-products-via-fbt` when the
	 * products are added via Frequently Bought Together section.
	 *
	 * @return void
	 */
	public static function script_to_handle_products_submit_via_fbt_section() {
		if ( ! is_product() ) {
			return;
		}

		?>
		<script>
			jQuery( function( $ ) {
				$( document ).ready( function() {
					$( document.body ).on( 'click', '[name=add-to-cart]', function() {
						$( '[name=iconic-wsb-add-products-via-fbt]' ).val(0);
					});

					$( document.body ).on( 'click', '[data-bump-product-form-submit]', function() {
						$( '[name=iconic-wsb-add-products-via-fbt]' ).val(1);
					});

					$( document ).on('added_to_cart', function() {
						$( '[name=iconic-wsb-add-products-via-fbt]' ).val(0);
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Disable `fbt_use_ajax` setting in the frontend data script.
	 *
	 * When the YITH WooCommerce Added to Cart Popup plugin is enabled,
	 * we need to disable `fbt_use_ajax` in the product page otherwise,
	 * the FBT AJAX function will take precedence over the AJAX function
	 * defined by YITH WooCommerce Added to Cart Popup plugin.
	 *
	 * @param array $data The frontend data script.
	 * @return array
	 */
	public static function disable_fbt_ajax_setting_in_frontend_data_script( $data ) {
		if ( ! is_product() ) {
			return $data;
		}

		$data['fbt_use_ajax'] = false;

		return $data;
	}
}
