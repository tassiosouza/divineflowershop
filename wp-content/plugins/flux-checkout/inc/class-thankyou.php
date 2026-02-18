<?php
/**
 * Iconic_Flux_Thankyou.
 *
 * Handle the steps.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Thankyou.
 *
 * Functions related to the Thank you page.
 *
 * @class    Iconic_Flux_Thankyou
 * @version  2.1.0
 */
class Iconic_Flux_Thankyou {

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'on_init' ) );
	}

	/**
	 * On init.
	 */
	public static function on_init() {
		$content_position = Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_content_position'];

		if ( '1' === Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_enable_thankyou_page'] ) {
			remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table' );
		}

		if ( $content_position ) {
			add_action( $content_position, array( __CLASS__, 'render_content_box' ) );
		}
	}


	/**
	 * Left column of the Thank you page.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function left_column( $order ) {
		if ( empty( $order ) ) {
			return;
		}

		$settings = Iconic_Flux_Core_Settings::$settings;

		if ( Iconic_Flux_Helpers::is_modern_theme() ) {
			Iconic_Flux_Steps::render_header();
		}

		self::render_status( $order );
		self::render_map( $order );
		self::render_customer_details( $order );
		self::downloads( $order );
		self::payment_method_thankyou_hook( $order );
		self::contact_us( $order );

		/**
		 * WooCommerce Thank you hook.
		 *
		 * @since 2.4.0
		 */
		do_action( 'woocommerce_thankyou', $order->get_id() );
	}

	/**
	 * Render Status section.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function render_status( $order ) {
		/**
		 * Thank you page: Before order status.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_before_order_status', $order );
		?>
		<div class="flux-ty-status">
			<div class="flux-ty-status__left">
				<p>
					<?php
					// Translators: Order ID.
					echo sprintf( esc_html__( 'Order #%d', 'flux-checkout' ), esc_html( $order->get_order_number() ) );
					?>
				</p>
				<h2>
					<?php
					// Translators: First name.
					echo sprintf( esc_html__( 'Thank you, %s!', 'flux-checkout' ), esc_html( $order->get_billing_first_name() ) );
					?>
				</h2>
			</div>
			<div class="flux-ty-status__right">
			</div>
		</div>
		<?php

		/**
		 * Thank you page: After order status.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_after_order_status', $order );
	}

	/**
	 * Render Map on Thank you page.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool|void
	 */
	public static function render_map( $order ) {
		$address  = $order->get_address( 'shipping' );
		$settings = Iconic_Flux_Core_Settings::$settings;

		if ( empty( $settings['integrations_integrations_google_api_key'] ) || '0' === $settings['thankyou_thankyou_show_map'] ) {
			return false;
		}

		if ( ! self::need_to_show_map( $order ) ) {
			return false;
		}

		$formatted_address = sprintf( '%s, %s, %s, %s, %s ', $address['address_1'], $address['address_2'], $address['city'], $address['state'], $address['country'] );
		$map_src           = sprintf( 'https://www.google.com/maps/embed/v1/place?key=%s&q=%s', $settings['integrations_integrations_google_api_key'], rawurlencode( $formatted_address ) );

		/**
		 * Flux thank you page map iFrame src URL.
		 *
		 * @since 2.1.0.
		 */
		$map_src = apply_filters( 'flux_thankyou_map_url', $map_src );

		/**
		 * Thank you page: Before Map.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_before_map', $order );
		?>
		<div class="flux-ty-map">
			<div class="flux-ty-map__map" id="flux-ty-map-canvas" data-address="<?php echo esc_attr( $formatted_address ); ?>"></div>
		</div>
		<?php

		/**
		 * Thank you page: After Map.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_after_map', $order );
	}

	/**
	 * Content box.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function render_content_box( $order ) {
		$settings = Iconic_Flux_Core_Settings::$settings;

		/**
		 * Thank you page content.
		 *
		 * @since 2.1.0
		 */
		$content = apply_filters( 'flux_thankyou_content', $settings['thankyou_thankyou_content'], $order );

		if ( empty( trim( $content ) ) ) {
			return;
		}

		global $allowedposttags;

		$allowed_tags = array_merge(
			$allowedposttags,
			array(
				'iframe' => array(
					'title'           => true,
					'frameborder'     => true,
					'src'             => true,
					'allow'           => true,
					'allowfullscreen' => true,
					'width'           => true,
					'height'          => true,
				),
				'form'   => array(
					'id'           => true,
					'class'        => true,
					'action'       => true,
					'method'       => true,
					'enctype'      => true,
					'novalidate'   => true,
					'data-options' => true,
				),
				'input'  => array(
					'type' => true,
					'name' => true,
					'id'   => true,
				),
			)
		);

		?>
		<div class="flux-ty-content flux-ty-box flux-ty-box--content">
		<?php
			/**
			 * The content filter for things like oEmbed, capital_P_dangit etc.
			 *
			 * @since 2.2.1
			 */
			echo wp_kses( apply_filters( 'the_content', wpautop( $content ) ), $allowed_tags );

			/**
			 * Hook: after thank you page content.
			 *
			 * @param WC_Order $order Order.
			 *
			 * @since 2.4.0
			 */
			do_action( 'flux_thankyou_after_content', $order );
		?>
		</div>
		<?php
	}

	/**
	 * Customer details box.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function render_customer_details( $order ) {
		/**
		 * Thank you page: Before customer details.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_before_customer_details', $order );
		$billing_address  = $order->get_formatted_billing_address();
		$shipping_address = $order->get_formatted_shipping_address();

		$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $billing_address !== $shipping_address;
		?>
		<div class="flux-review-customer flux-review-customer--ty">
			<div class="flux-review-customer__row flux-review-customer__row--contact">
				<div class='flux-review-customer__label'><label><?php esc_html_e( 'Contact', 'flux-checkout' ); ?></label></div>
				<div class='flux-review-customer__content'><p><?php echo esc_html( $order->get_billing_email() ); ?></p></div>
			</div>

			<div class="flux-review-customer__row flux-review-customer__row--address">
				<div class='flux-review-customer__label'>
					<label>
						<?php
						if ( $show_shipping ) {
							esc_html_e( 'Billing', 'flux-checkout' );
						} else {
							esc_html_e( 'Address', 'flux-checkout' );
						}
						?>
					</label>
				</div>
				<div class='flux-review-customer__content'>
					<address>
						<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
						<?php endif; ?>

						<?php if ( $order->get_billing_email() ) : ?>
							<p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
						<?php endif; ?>
					<address>
				</div>
			</div>

			<?php
			if ( $show_shipping ) {
				?>
				<div class="flux-review-customer__row flux-review-customer__row--shipping-address">
					<div class='flux-review-customer__label'><label><?php esc_html_e( 'Shipping', 'flux-checkout' ); ?></label></div>
					<div class='flux-review-customer__content'>
						<address>
							<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
						<address>
					</div>
				</div>
				<?php
			}
			?>

			<div class="flux-review-customer__row">
				<div class='flux-review-customer__label'><label><?php esc_html_e( 'Payment', 'flux-checkout' ); ?></label></div>
				<div class='flux-review-customer__content'>
					<p>
					<?php
						echo esc_html( $order->get_payment_method_title() );
					?>
					</p>
				</div>
			</div>

			<?php
			/**
			 * After Customer detail rows.
			 *
			 * @since 2.1.0
			 */
			do_action( 'flux_thankyou_after_customer_details_payment_row', $order );
			?>

		</div>
		<?php

		/**
		 * Thank you page: After customer details.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_after_customer_details', $order );
	}

	/**
	 * Render Product details.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function render_product_details( $order ) {
		if ( empty( $order ) ) {
			return;
		}

		$order_items = $order->get_items( 'line_item' );

		/**
		 * Thank you page: Before Product details.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_before_product_details', $order );
		?>
		<div class="flux-ty-product-details">
			<h2 class="flux-heading flux-heading--cart-icon flux-order-review-heading--ty">
				<?php esc_html_e( 'Your Order', 'flux-checkout' ); ?>
				<span class="flux-heading__count"><?php echo esc_html( $order->get_item_count() ); ?></span>
			</h2>	
			<div class="flux-cart-order-item-wrap">
				<?php
				foreach ( $order_items as $item_id => $item ) {
					$product      = $item->get_product();
					$qty          = $item->get_quantity();
					$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

					if ( empty( $product ) ) {
						continue;
					}

					if ( $refunded_qty ) {
						$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
					} else {
						$qty_display = esc_html( $qty );
					}

					/**
					 * Order item class.
					 *
					 * @since 2.4.0
					 */
					$item_class = apply_filters( 'flux_order_items_class', 'flux-cart-order-item flux-cart-order-item--ty', $item, $order );
					?>
					<div class="<?php echo esc_attr( $item_class ); ?>">
						<?php if ( $product->get_image_id() ) { ?>
							<div class="flux-cart-image flux-cart-image--ty">
								<?php
									echo wp_kses_post( $product->get_image() );
								?>
							</div>
						<?php } ?>
						<div class="flux-cart-order-item__info">
							<h3 class="flux-cart-order-item__info-name">
								<?php
								echo esc_html( $product->get_name() );
								?>
							</h3>
							<span class="flux-cart-order-item__info-varient">
								<?php
								/**
								 * Thank you page: Order item meta start.
								 *
								 * @since 2.1.0
								 */
								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

								$meta = wc_display_item_meta( $item );

								if ( $meta ) {
									echo wp_kses_post( $meta );
								}

								/**
								 * Thank you page: Order item meta end.
								 *
								 * @since 2.1.0
								 */
								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
								?>
							</span>
							<div class="flux-cart-order-item__info-qty">
								<?php
								/**
								 * Order item quantity HTML.
								 *
								 * @since 2.1.0
								 */
								echo wp_kses_post( apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', esc_html( $qty_display ) ) . '</strong>', $item ) );
								?>
							</div>
						</div>
						<div class="flux-cart-order-item__price">
							<?php
							echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) );
							?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
				<?php

				$totals = $order->get_order_item_totals();
				foreach ( $totals as $key => $total ) {
					if ( 'payment_method' === $key ) {
						continue;
					}
					?>
					<div class="flux-cart-totals <?php echo 'flux-cart-totals--' . esc_html( $key ); ?>">
						<div class="flux-cart-totals__label"><span><?php echo esc_html( trim( $total['label'], ':' ) ); ?></span></div>
						<div class="flux-cart-totals__value">
							<?php
							if ( 'order_total' === $key ) {
								echo sprintf( '<div class="flux-cart-totals__currency-badge">%s</div>', esc_html( $order->get_currency() ) );
							}
							?>
							<span><?php echo wp_kses_post( $total['value'] ); ?></span>
						</div>
					</div>
					<?php
				}
				?>
		</div>
		<?php

		/**
		 * Thank you page: After Product details.
		 *
		 * @since 2.1.0.
		 */
		do_action( 'flux_thankyou_after_product_details', $order );
	}

	/**
	 * Need to show the map.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return bool.
	 */
	public static function need_to_show_map( $order ) {
		// Return false if shipping is not enabled.
		if ( ! wc_shipping_enabled() || 0 === wc_get_shipping_method_count( true ) ) {
			return false;
		}

		// Return false if shipping method is 'Local Pickup'.
		if ( ! $order->needs_shipping_address() ) {
			return false;
		}

		// Check if at least one product needs shipping.
		$needs_shipping = false;
		foreach ( $order->get_items() as $item ) {
			if ( $item->is_type( 'line_item' ) ) {
				$product = $item->get_product();

				if ( $product && $product->needs_shipping() ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Show Contact Us at the footer.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function contact_us( $order ) {
		$contact_page = Iconic_Flux_Core_Settings::$settings['thankyou_thankyou_contact_page'];
		$shop_url     = Iconic_Flux_Helpers::get_shop_page_url();
		?>
		<div class="flux-ty-footer">
			<span class="flux-ty-footer__contact">
				<?php
				if ( ! empty( $contact_page ) ) {
					$contact_page_url = get_permalink( $contact_page );
					echo sprintf( '<span class="flux-ty-footer__contact-span">%s <a href="%s">%s</a></span>', esc_html__( 'Need Help?', 'flux-checkout' ), esc_url( $contact_page_url ), esc_html__( 'Contact Us', 'flux-checkout' ) );
				}
				?>
			</span>
			<span class="flux-ty-footer__continue-shipping">
				<a class="flux-button flux-button--ty" href="<?php echo esc_attr( $shop_url ); ?>" ><?php esc_html_e( 'Continue Shopping', 'flux-checkout' ); ?></a>
			</span>
		</div>
		<?php
	}

	/**
	 * Get Thank you page link of the latest Order.
	 *
	 * @param bool $add_force_arg Add "Force" argument.
	 *
	 * @return string
	 */
	public static function get_thankyou_page_preview_link( $add_force_arg = false ) {
		if ( ! Iconic_Flux_Core_Settings::is_settings_page() ) {
			return '';
		}

		$args = array(
			'limit'   => 1,
			'orderby' => 'date',
			'order'   => 'desc',
			'type'    => 'shop_order',
		);

		$query  = new WC_Order_Query( $args );
		$orders = $query->get_orders();

		if ( 0 === count( $orders ) ) {
			return '';
		}

		$order        = $orders[0];
		$thankyou_url = $order->get_checkout_order_received_url();

		if ( $add_force_arg ) {
			$thankyou_url = $thankyou_url . '&flux_force_ty=1';
		}

		// Translators: URL to the preview page.
		$text = sprintf( wp_kses_post( __( "<a href='%s' target='_blank'>Click here</a> to preview the Thank You page.", 'flux-checkout' ) ), $thankyou_url );

		return $text;
	}

	/**
	 * Display downloads table.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return void
	 */
	public static function downloads( $order ) {
		$downloads      = $order->get_downloadable_items();
		$show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();
		if ( ! $show_downloads ) {
			return;
		}
		?>
		<div class="flux-ty-downloads flux-ty-box flux-ty-box--downloads">
			<?php
			/**
			 * Before thank you downloads hook.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_thankyou_before_downloads', $order );

			wc_get_template(
				'order/order-downloads.php',
				array(
					'downloads'  => $downloads,
					'show_title' => true,
				)
			);

			/**
			 * After thank you downloads hook.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_thankyou_after_downloads', $order );
			?>
		</div>
		<?php
	}

	/**
	 * Get order ID when in the thank you page.
	 *
	 * @return int|false
	 */
	public static function get_thankyou_page_order_id() {
		global $wp;

		if ( ! isset( $wp->query_vars['order-received'] ) ) {
			return false;
		}

		return absint( $wp->query_vars['order-received'] );
	}

	/**
	 * Output the woocommerce_thankyou_<payment_method> hook.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function payment_method_thankyou_hook( $order ) {
		if ( ! $order ) {
			return;
		}

		?>
		<div class="flux-ty-content flux-ty-box">
			<?php
			do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
			?>
		</div>
		<?php
	}
}
