<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'abstracts/class-order-bump-product-page-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_Product_Page_Modal_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_Product_Page_Modal_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_Product_Page_Modal_Manager extends Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_Product_Page_Modal_Manager constructor.
	 */
	public function __construct() {
		parent::__construct(
			'_iconic_wsb_product_page_bump_modal_ids',
			'iconic_wsb_product_page_bump_modal_ids',
			__( 'After Add to Cart', 'iconic-wsb' ),
			__( 'Display these suggested products in a modal popup after adding the main product to the cart.', 'iconic-wsb' )
		);

		$this->hooks();

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 *  Register hooks
	 */
	private function hooks() {
		add_filter( 'wpsf_register_settings_iconic-wsb', array( $this, 'add_settings_section' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'add_after_add_to_cart_popup_in_cart_fragments' ) );

		add_action( 'woocommerce_add_to_cart', array( $this, 'handle_show_after_add_to_cart_popup' ), 10, 2 );
		add_action( 'wp_footer', array( $this, 'show_after_add_to_cart_popup' ), 10 );
		add_action( 'wp_footer', array( $this, 'add_after_add_to_cart_popup_placeholder' ) );
	}

	/**
	 * Add service settings section
	 *
	 * @param array $settings Settings.
	 *
	 * @return mixed
	 */
	public function add_settings_section( $settings ) {
		$settings['sections']['product-page-modal'] = array(
			'tab_id'              => 'order_bump',
			'section_id'          => 'product_page_modal',
			'section_title'       => __( 'After Add to Cart Modal', 'iconic-wsb' ),
			'section_description' => __( 'These are cross-sells which appear in a modal after adding a product to the cart.', 'iconic-wsb' ),
			'section_order'       => 0,
			'fields'              => array(
				array(
					'id'      => 'header_color',
					'title'   => __( 'Header Bar Color', 'iconic-wsb' ),
					'type'    => 'color',
					'default' => '#24BDAE',
				),
				array(
					'id'      => 'title',
					'title'   => __( 'Cross Sells Title', 'iconic-wsb' ),
					'desc'    => __( 'Leave blank to disable the title.', 'iconic-wsb' ),
					'type'    => 'text',
					'default' => __( 'Customers Also Bought', 'iconic-wsb' ),
				),
				array(
					'id'      => 'show_without_cross_sales',
					'title'   => __( 'Enable "After Add to Cart" Modal for All Products?', 'iconic-wsb' ),
					'desc'    => __( 'When enabled, the added to cart modal will be displayed after adding any product to cart, even if no cross-sells are set.', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => false,
				),
			),
		);

		return $settings;
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'add_to_cart_button_attributes' ), 10, 2 );
	}

	/**
	 * After the cart has rendered.
	 */
	public function after_cart( $product_id = false ) {

		if ( ! $product_id ) {
			if ( ! empty( $_POST['variation_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$product_id = filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT );
			} elseif ( ! empty( $_POST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$product_id = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
			}
		}

		if ( false === $product_id || ! is_numeric( $product_id ) ) {
			return;
		}

		if ( ! empty( $_POST['skipAfterAddToCartModal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );

		$product = wc_get_product( $product_id );

		// see if it is a valid product.
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		// render the modal.
		$this->render_bump_modal( $product );
	}

	/**
	 * Render content of bump modal if related products exist
	 *
	 * @param WC_Product|null $added_to_cart_product The product just added to the cart.
	 */
	public function render_bump_modal( $added_to_cart_product = null ) {
		// if we have not been passed a product, see if one has just been added to the cart.
		if ( empty( $added_to_cart_product ) ) {
			$added_to_cart_product = $this->get_added_to_cart_product();
		}

		// Not a valid product.
		if ( ! ( $added_to_cart_product instanceof WC_Product ) ) {
			return;
		}

		$parent_product_id = $added_to_cart_product->is_type( 'variation' ) ? $added_to_cart_product->get_parent_id() : $added_to_cart_product->get_id();

		$bump_products = array_map(
			function ( $id ) {
				return wc_get_product( $id );
			},
			$this->get_bump_products_ids( $parent_product_id )
		);

		$bump_products = $this->remove_already_in_cart_products( $bump_products );
		$bump_products = array_filter( $bump_products, array( $this, 'isValidBump' ) );

		$settings = $this->get_settings();

		// No bump products and set to not show if no bump products.
		if ( ! $settings['show_without_cross_sales'] && empty( $bump_products ) ) {
			return;
		}

		global $iconic_wsb_class;

		/**
		 * Filter the number of offers to show in the After Add to Cart modal.
		 *
		 * @since 1.12.0
		 * @hook iconic_wsb_number_of_offers_to_show_in_the_after_add_to_cart_modal
		 * @param  int        $number_of_offers_to_show The number of offers to show. Default: the
		 *                                              number of $offers associate to the product
		 *                                              added to the car.
		 * @param  array      $offers                   The offers.
		 * @param  WC_Product $added_to_cart_product    The product added to the cart
		 * @return int New value
		 */
		$number_of_offers_to_show = apply_filters(
			'iconic_wsb_number_of_offers_to_show_in_the_after_add_to_cart_modal',
			count( $bump_products ),
			$bump_products,
			$added_to_cart_product
		);

		$offers = is_int( $number_of_offers_to_show ) ? array_slice( $bump_products, 0, $number_of_offers_to_show ) : $bump_products;

		if ( $settings['show_without_cross_sales'] || ! empty( $offers ) ) {
			if ( $this->need_variable_scripts( $offers ) ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			$iconic_wsb_class->template->include_template(
				'frontend/order-bump/product/bump-modal.php',
				array(
					'settings' => $this->get_settings(),
					'product'  => $added_to_cart_product,
					'offers'   => $offers,
				)
			);
		}
	}

	/**
	 * Check for variable product in product list
	 *
	 * @param WC_Product[] $products Products.
	 *
	 * @return bool
	 */
	private function need_variable_scripts( $products ) {
		$products = array_filter(
			$products,
			function ( WC_Product $product ) {
				return $product->is_type( 'variable' );
			}
		);

		return count( $products ) > 0;
	}

	/**
	 * Get added to cart product
	 *
	 * @return false|WC_Product
	 */
	public function get_added_to_cart_product() {
		$add_to_cart  = absint( filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT ) );
		$this_product = absint( filter_input( INPUT_POST, 'iconic-wsb-fbt-this-product', FILTER_SANITIZE_NUMBER_INT ) );

		if ( ! $add_to_cart && ! $this_product ) {
			return false;
		}

		$product_id   = false;
		$variation_id = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $variation_id ) {
			$product_id = $variation_id;
		} elseif ( $add_to_cart ) {
			$product_id = $add_to_cart;
		} elseif ( $this_product ) {
			$product_id = $this_product;
		}

		$product = wc_get_product( $product_id );

		if ( $product instanceof WC_Product ) {
			return $product;
		}

		return false;
	}

	/**
	 * Get service settings
	 *
	 * @return array
	 */
	public function get_settings() {
		global $iconic_wsb_class;

		$prefix = 'order_bump_product_page_modal_';

		$defaults = array(
			'header_color'             => '#23BDAE',
			'title'                    => __( 'Customers Also Bought', 'iconic-wsb' ),
			'show_without_cross_sales' => false,
		);

		$settings = array();

		foreach ( $defaults as $key => $default ) {
			$settings[ $key ] = is_array( $iconic_wsb_class->settings ) && array_key_exists( $prefix . $key, $iconic_wsb_class->settings ) ?
				$iconic_wsb_class->settings[ $prefix . $key ] : $default;
		}

		return apply_filters( 'iconic_wsb_product_page_modal_settings', $settings );
	}

	/**
	 * Add to cart button attributes.
	 *
	 * Add additional attributes to add to cart buttons to trigger
	 * the JavaScript After Add to Cart popup.
	 *
	 * @param array  $args    The current button arguments.
	 * @param object $product The current product.
	 *
	 * @return array $args.
	 */
	public function add_to_cart_button_attributes( $args, $product ) {

		$show_button_attributes = $this->should_show_button_attributes();

		if ( $show_button_attributes ) {
			$args['attributes']['data-show_after_cart_modal'] = $product->get_id();
		}

		return $args;
	}

	/**
	 * Should show button attributes.
	 *
	 * Decides which factors determine if additional attributes are added to
	 * the buttons to trigger the After Add to Cart Modal.
	 *
	 * Uses filter `iconic_wsb_show_button_attributes` so customers can add
	 * additional factors.
	 *
	 * @return boolean
	 */
	public function should_show_button_attributes() {
		/**
		 * Filter whether to show button attributes.
		 *
		 * By default, the button attributes are
		 * shown in all pages.
		 *
		 * @hook  iconic_wsb_show_button_attributes
		 * @since 1.5.0
		 *
		 * @param bool $show_attributes If the button attribues will be shown. Default: true.
		 */
		return apply_filters( 'iconic_wsb_show_button_attributes', true );
	}

	/**
	 * If the product was added to the cart via ajax, record it.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function ajax_added_to_cart( $product_id ) {
		set_transient( 'iconic-wsb-just-added-to-cart', $product_id, 2 );
	}

	/**
	 * Add to Cart Fragments.
	 *
	 * @param array $fragments Cart Fragments.
	 * @return array
	 */
	public static function add_to_cart_fragments( $fragments ) {
		ob_start();
		self::render_after_cart();

		$fragments['div.iconic-wsb-product-archive-fragment'] = ob_get_clean();

		ob_start();
		self::render_cart_subtotal();

		$fragments['div.iconic-wsb-modal-product-summary__cart-subtotal'] = ob_get_clean();

		ob_start();
		self::render_cart_items_count();

		$fragments['div.iconic-wsb-modal-product-summary__cart-items-count'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Render After Add to Cart Modal.
	 *
	 * Render the modal via AJAX.
	 */
	public static function render_after_cart() {
		$modal_manager = self::get_instance();

		echo '<div class="iconic-wsb-product-archive-fragment">';
		$product_id = get_transient( 'iconic-wsb-just-added-to-cart' );
		delete_transient( 'iconic-wsb-just-added-to-cart' );
		$modal_manager->after_cart( $product_id );
		echo '</div>';
	}

	/**
	 * Render After Cart Fragment.
	 */
	public function render_after_cart_fragment() {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}
		self::render_after_cart();
	}

	/**
	 * Add the key `iconic_wsb_show_after_add_to_cart_popup` to show
	 * the popup.
	 *
	 * @param string $cart_item_key ID of the item in the cart.
	 * @param int    $product_id    The product ID.
	 * @return void
	 */
	public function handle_show_after_add_to_cart_popup( $cart_item_key, $product_id ) {
		if (
			! empty( get_post_meta( $product_id, '_iconic_wsb_product_page_bump_modal_ids', true ) ) ||
			! empty( $this->get_settings()['show_without_cross_sales'] )
		) {
			WC()->cart->cart_contents[ $cart_item_key ]['iconic_wsb_show_after_add_to_cart_popup'] = true;
		}
	}

	/**
	 * Show the After Add to Cart Popup.
	 *
	 * @return void
	 */
	public function show_after_add_to_cart_popup() {
		/**
		 * If `Redirect to the cart page after successful addition` option is enabled,
		 * we'll show the popup only on cart page.
		 */
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) && ! is_cart() ) {
			return;
		}

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['iconic_wsb_show_after_add_to_cart_popup'] ) ) {
				continue;
			}

			unset( WC()->cart->cart_contents[ $cart_item_key ]['iconic_wsb_show_after_add_to_cart_popup'] );
			WC()->cart->set_session();

			if ( is_checkout() ) {
				continue;
			}

			if ( is_cart() && 'yes' !== get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				continue;
			}

			Iconic_WSB_Assets::enqueue_frontend_assets();

			$this->after_cart( $cart_item['product_id'] );

			break;
		}
	}

	/**
	 * Add the After Add to Cart Popup in the cart fragments.
	 *
	 * The content of the fragment `.iconic_wsb_after_add_to_cart_popup_placeholder`
	 * is used to render the modal in AJAX requests.
	 *
	 * @param array $fragments The cart fragments.
	 * @return array
	 */
	public function add_after_add_to_cart_popup_in_cart_fragments( $fragments ) {
		// `Redirect to the cart page after successful addition` option
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			return $fragments;
		}

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['iconic_wsb_show_after_add_to_cart_popup'] ) ) {
				continue;
			}

			unset( WC()->cart->cart_contents[ $cart_item_key ]['iconic_wsb_show_after_add_to_cart_popup'] );
			WC()->cart->set_session();

			ob_start();
			self::get_instance()->after_cart( $cart_item['product_id'] );

			$modal_html = ob_get_clean();
			$modal_html = '<div class="iconic_wsb_after_add_to_cart_popup_placeholder">' . $modal_html . '</div>';

			$fragments['.iconic_wsb_after_add_to_cart_popup_placeholder'] = $modal_html;

			break;
		}

		return $fragments;
	}

	/**
	 * Output a placeholder to render the After Add to Cart Popup
	 * when the product is added via AJAX.
	 *
	 * @return void
	 */
	public function add_after_add_to_cart_popup_placeholder() {
		?>
		<div class="iconic_wsb_after_add_to_cart_popup_placeholder"></div>
		<?php
	}

	/**
	 * Render the cart subtotal.
	 */
	public static function render_cart_subtotal() {
		?>
		<div class="iconic-wsb-modal-product-summary__cart-subtotal">
			<?php
				// translators: %s - the cart subtotal.
				echo wp_kses_post( sprintf( __( 'Cart subtotal: %s', 'iconic-wsb' ), WC()->cart->get_cart_subtotal() ) );
			?>
		</div>
		<?php
	}

	/**
	 * Render the cart items count.
	 */
	public static function render_cart_items_count() {
		$cart_items_count = WC()->cart->get_cart_contents_count();

		?>
		<div class="iconic-wsb-modal-product-summary__cart-items-count">
			<?php
				echo wp_kses_post(
					sprintf(
						// translators: %d - the number of items in the cart.
						_n( '(%d Item)', '(%d Items)', $cart_items_count, 'iconic-wsb' ),
						$cart_items_count
					)
				);
			?>
		</div>
		<?php
	}
}
