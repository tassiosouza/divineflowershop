<?php
/**
 * Iconic_Flux_Cross_Sell.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Cross_Sell.
 *
 * @class    Iconic_Flux_Cross_Sell.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Cross_Sell {
	/**
	 * Run.
	 *
	 * @return void
	 */
	public static function run() {
		add_shortcode( 'iconic_flux_cross_sell', array( __CLASS__, 'shortcode' ) );

		add_action( 'woocommerce_review_order_after_cart_contents', array( __CLASS__, 'maybe_auto_add_cross_sell' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'add_product_to_cart' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_notice_to_cross_sell_block' ) );
	}

	/**
	 * Shortcode.
	 *
	 * @param array $args Shortcode args. Allowed arguments: product_id.
	 * If product_id is not passed then it will display the cross sell
	 * products based on the products in the cart.
	 *
	 * @return string
	 */
	public static function shortcode( $args ) {
		$product_id = isset( $args['product_id'] ) ? $args['product_id'] : null;
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() || self::is_product_in_cart( $product_id ) ) {
			return;
		}

		ob_start();

		self::display_cross_sell_products( empty( $product ) ? null : array( $product ) );

		return ob_get_clean();
	}

	/**
	 * Auto add cross sell products to the order review section if the setting is enabled.
	 *
	 * @return void
	 */
	public static function maybe_auto_add_cross_sell() {
		$show_cross_sell_products = Iconic_Flux_Core_Settings::$settings['general_general_show_cross_sell_products'];

		if ( '1' !== $show_cross_sell_products ) {
			return;
		}

		self::display_cross_sell_products( null, false, true );
	}

	/**
	 * Display cross sell products. If $product is null then it will display the
	 * cross sell products based on the products in the cart. If $product is passed
	 * then it will display the given product as the only cross sell product.
	 *
	 * @param WC_Product|null $products Product object.
	 * @param string          $title    Title.
	 * @param bool            $tr_wrap  Whether to wrap the products in a table row.
	 *
	 * @return void
	 */
	public static function display_cross_sell_products( $products = null, $title = false, $tr_wrap = false ) {
		if ( ! is_checkout() ) {
			return;
		}

		if ( empty( $products ) ) {
			$products = self::get_cross_sell_product();
		}

		if ( empty( $products ) ) {
			return;
		}

		if ( $tr_wrap ) {
			echo '<tr><td colspan="2">';
		}

		self::title( $title );

		foreach ( $products as $product ) {
			if ( self::is_product_in_cart( $product->get_id() ) ) {
				continue;
			}

			self::render_single_product( $product );
		}

		if ( $tr_wrap ) {
			echo '</td></tr>';
		}
	}


	/**
	 * Display cross sell products title.
	 *
	 * @param string $title Title.
	 *
	 * @return void
	 */
	public static function title( $title = false ) {
		if ( false === $title ) {
			$title = esc_html__( 'You may also like', 'flux-checkout' );
		}

		if ( empty( $title ) ) {
			return;
		}

		echo '<h3 class="flux-crosssell__title">' . esc_html( $title ) . '</h3>';
	}

	/**
	 * Get cross sell products.
	 *
	 * @param int    $limit number of cross sell products.
	 * @param int    $columns number of columns.
	 * @param string $orderby order by.
	 * @param string $order order.
	 *
	 * @return array
	 */
	public static function get_cross_sell_product( $limit = 2, $columns = 2, $orderby = 'date', $order = 'desc' ) {
		if ( empty( WC()->cart ) ) {
			return array();
		}

		// Get visible cross sells then sort them at random.
		$cross_sells = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );

		wc_set_loop_prop( 'name', 'cross-sells' );

		// Handle orderby and limit results.

		$orderby     = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
		$order       = apply_filters( 'woocommerce_cross_sells_order', $order );
		$cross_sells = wc_products_array_orderby( $cross_sells, $orderby, $order );

		/**
		 * Filter the number of cross sell products should on the product page.
		 *
		 * @param int $limit number of cross sell products.
		 * @since 3.0.0
		 */
		$limit       = intval( apply_filters( 'woocommerce_cross_sells_total', $limit ) );
		$cross_sells = $limit > 0 ? array_slice( $cross_sells, 0, $limit ) : $cross_sells;

		return $cross_sells;
	}

	/**
	 * Render single product.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return void
	 */
	public static function render_single_product( $product ) {
		$product_id   = $product->get_id();
		$product_type = $product->get_type();
		$btn_disabled = $product->is_type( 'variable' ) ? true : false;
		?>
		<div class="flux-crosssell__product flux-crosssell__product--<?php echo esc_attr( $product_type ); ?> flux-crosssell__product-id-<?php echo esc_attr( $product_id ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-product-type="<?php echo esc_attr( $product_type ); ?>">
			<div class="flux-crosssell__row">
				<div class="flux-crosssell__col flux-crosssell__col--thumbnail">
					<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
				</div>
				<div class="flux-crosssell__col flux-crosssell__col--title">
					<h4 class="flux-crosssell__product-title">
						<?php echo esc_html( $product->get_name() ); ?>
					</h4>

					<div class="flux-crosssell__product-price">
						<?php echo wp_kses_post( $product->get_price_html() ); ?>
					</div>
				</div>
				<div class="flux-crosssell__col flux-crosssell__col--actions">
					<button class="flux-crosssell__add-to-cart-btn" <?php disabled( true, $btn_disabled ); ?> data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php esc_html_e( '+ Add', 'flux-checkout' ); ?></button>
				</div>
			</div>
			<div class="flux-crosssell__description"><?php echo wp_kses_post( $product->get_short_description() ); ?></div>
			<?php include ICONIC_FLUX_PATH . '/templates/partials/cross-sell-variable-product.php'; ?>
		</div>
		<?php
	}

	/**
	 * Add product to cart.
	 *
	 * @param array $post_data Post data.
	 *
	 * @return void
	 */
	public static function add_product_to_cart( $post_data ) {
		$data = array();
		parse_str( $post_data, $data );

		if ( empty( $data['flux_cross_sell'] ) ) {
			return;
		}

		$action     = json_decode( $data['flux_cross_sell'], true );
		$product_id = $action['product_id'];

		if ( empty( $action['variation_data'] ) ) {
			WC()->cart->add_to_cart( $product_id );
		} else {
			$variation_data = json_decode( $action['variation_data'], true );
			$variation_id   = $action['variation_id'];
			WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation_data );
		}
	}

	/**
	 * Check if product is in cart.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_product_in_cart( $product_id ) {
		if ( empty( WC()->cart ) ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( intval( $product_id ) === intval( $cart_item['product_id'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if position requires table wrap.
	 *
	 * @param string $position Position.
	 *
	 * @return bool
	 */
	public static function does_position_require_table_wrap( $position ) {
		$positions = array(
			'woocommerce_review_order_before_cart_contents',
			'woocommerce_review_order_after_cart_contents',
			'flux_after_coupon_form',
			'woocommerce_review_order_before_order_total',
			'woocommerce_review_order_after_order_total',
		);

		return in_array( $position, $positions, true );
	}

	/**
	 * Render cross sell block.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content Block content.
	 *
	 * @return string
	 */
	public static function render_block( $attributes, $content ) {
		$title = false;
		if ( array_key_exists( 'title', $attributes ) ) {
			$title = $attributes['title'];
		}

		$products = null;

		if ( ! empty( $attributes['manual_selection'] ) ) {
			$products = self::prepare_products_for_block( $attributes['product_ids'] );
		} else {
			$products = self::get_cross_sell_product();
		}

		if ( empty( $products ) ) {
			return '';
		}

		ob_start();
		self::display_cross_sell_products( $products, $title );

		return ob_get_clean();
	}

	/**
	 * Prepare products for block render.
	 *
	 * Convert product IDs to product objects and filter out products
	 * that are already in cart.
	 *
	 * @param array $product_ids Product IDs.
	 *
	 * @return array
	 */
	public static function prepare_products_for_block( $product_ids ) {
		if ( ! is_array( $product_ids ) || empty( $product_ids ) ) {
			return array();
		}

		$product_ids       = array_map( 'absint', $product_ids );
		$products          = array_map( 'wc_get_product', $product_ids );
		$products          = array_filter( $products );
		$prepared_products = array();

		foreach ( $products as $product ) {
			if ( self::is_product_in_cart( $product->get_id() ) ) {
				continue;
			}

			$prepared_products[] = $product;
		}

		return $prepared_products;
	}

	/**
	 * Get selected attributes.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return array
	 */
	public static function get_selected_attributes( $product_id ) {
		$post_data = filter_input( INPUT_POST, 'post_data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $post_data ) ) {
			return array();
		}

		parse_str( $post_data, $post_data );

		if ( empty( $post_data['flux-cross-sell-variation-data'][ $product_id ] ) ) {
			return array();
		}

		$json = $post_data['flux-cross-sell-variation-data'][ $product_id ];

		return json_decode( $json, true );
	}

	/**
	 * Find variation ID.
	 *
	 * @param WC_Product $product Product object.
	 * @param array      $selected_attributes Selected attributes.
	 *
	 * @return int
	 */
	public static function find_variation_id( $product, $selected_attributes ) {
		$variation_data = array_merge(
			$selected_attributes,
			array(
				'product_id' => $product->get_id(),
			)
		);

		$data_store = WC_Data_Store::load( 'product' );

		return $data_store->find_matching_product_variation( $product, $variation_data );
	}

	/**
	 * Add notice to cross sell block.
	 *
	 * @return void
	 */
	public static function add_notice_to_cross_sell_block() {
		$screen = get_current_screen();

		if ( 'checkout_elements' !== $screen->id ) {
			return;
		}

		$show_cross_sell_products = Iconic_Flux_Core_Settings::$settings['general_general_show_cross_sell_products'];

		if ( '1' !== $show_cross_sell_products ) {
			return;
		}

		?>
		<style>
			.wp-block-flux-checkout-cross-sell:before {
				content: '<?php esc_html_e( 'Note: the default WooCommerce cross-sells will also be visible at the checkout, based on your current Flux Checkout settings.', 'flux-checkout' ); ?>';
				height: auto;
				width: 100%;
				display: block;
				padding: 4px;
				background-color: #fef8ee;
				box-sizing: border-box;
				padding: 10px 10px;
				margin-bottom: 10px;
				border-left: 3px solid #f0b849;
				font-size: 15px;
				color: #333333;
			}
		</style>
		<?php
	}
}
