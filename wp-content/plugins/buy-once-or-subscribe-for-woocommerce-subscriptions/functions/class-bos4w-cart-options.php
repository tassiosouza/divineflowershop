<?php
/**
 * Cart settings
 *
 * @package Buy Once or Subscribe for WooCommerce Subscriptions
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

use Automattic\WooCommerce\Utilities\NumberUtil;
use function BOS4W\bos_cart_item_is_bos_product;

if ( ! class_exists( 'BOS4W_Cart_Options' ) ) {
	/**
	 * Class BOS4W_Cart_Options
	 */
	class BOS4W_Cart_Options {
		/**
		 * BOS4W_Cart_Options constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'bos4w_add_cart_item_data' ), 10, 3 );
			add_filter( 'woocommerce_is_subscription', array( $this, 'bos4w_is_subscription' ), 10, 3 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'bos4ws_apply_subscriptions' ), 5, 1 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'bos4w_apply_subscription_schemes_on_add_to_cart' ), 19, 6 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'bos4w_show_cart_item_subscription_options' ), 1000, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'bos4w_show_cart_item_show_subscription' ), 1000, 3 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'bos4w_hide_data_on_checkout' ), 10, 2 );
		}

		/**
		 * Hide data in checkout
		 *
		 * @param array  $item_data Data item array.
		 * @param object $cart_item Cart item data.
		 *
		 * @return mixed
		 */
		public function bos4w_hide_data_on_checkout( $item_data, $cart_item ) {
			if ( ! empty( $item_data ) ) {
				$i = 0;
				foreach ( $item_data as $entry_data ) {
					if ( 'bos4w_data' === $entry_data['key'] ) {
						$item_data[ $i ]['hidden'] = 1;
					}
					$i ++;
				}
			}

			return $item_data;
		}

		/**
		 * Add cart item data
		 *
		 * @param array $cart_item Cart item.
		 * @param int   $product_id Product ID.
		 * @param int   $variation_id Variation ID.
		 *
		 * @return mixed
		 */
		public function bos4w_add_cart_item_data( $cart_item, $product_id, $variation_id ) {
			if ( isset( $_REQUEST['bos4w-purchase-type'] ) && '1' === $_REQUEST['bos4w-purchase-type'] ) {
				$selected_plan = '';
				$keys          = array();

				$keys[] = 'convert_to_sub_plan_' . $product_id;

				if ( $variation_id > 0 ) {
					$keys[]    = 'convert_to_sub_plan_' . $variation_id;
					$parent_id = (int) wp_get_post_parent_id( $variation_id );
					if ( $parent_id ) {
						$keys[] = 'convert_to_sub_plan_' . $parent_id;
					}
				}

				$main_obj = BOS4W_Front_End::wpml_get_main_product( $variation_id > 0 ? $variation_id : $product_id, true, $variation_id > 0 );
				if ( $main_obj instanceof WC_Product ) {
					$main_base_id = $main_obj->is_type( 'variation' ) ? $main_obj->get_parent_id() : $main_obj->get_id();
					if ( $main_base_id ) {
						$keys[] = 'convert_to_sub_plan_' . $main_base_id;
					}
				}

				foreach ( $keys as $k ) {
					if ( isset( $_REQUEST[ $k ] ) && '' !== $_REQUEST[ $k ] ) {
						$selected_plan = sanitize_text_field( wp_unslash( $_REQUEST[ $k ] ) );
						break;
					}
				}

				if ( empty( $selected_plan ) ) {
					return $cart_item;
				}

				$req_id  = $variation_id > 0 ? $variation_id : $product_id;
				$product = wc_get_product( $req_id );

				$product_main = $product;
				if ( $variation_id > 0 ) {
					$product_main = wc_get_product( $product_id );
				}

				$plan_data = explode( '_', esc_attr( $selected_plan ) );
				$discount  = end( $plan_data );

				if ( ! $this->bos4w_validate_plan_selection( $product_id, $selected_plan, $variation_id ) ) {
					return $cart_item;
				}

				if ( $product->is_type( array( 'composite', 'bundle' ) ) ) {
					$item_price = BOS4W_Front_End::bos4w_get_product_price( $product );
				} else {
					/**
					 * Filter bos_use_regular_price.
					 *
					 * @param bool false
					 *
					 * @since 2.0.2
					 */
					$display_the_price = apply_filters( 'bos_use_regular_price', false );

					$base_product = $variation_id > 0 ? wc_get_product( $variation_id ) : wc_get_product( $product_id );

					$item_price = ! $display_the_price ? $product->get_price() : $product->get_regular_price();

					if ( class_exists( 'AF_C_S_P_Price' ) && $base_product instanceof WC_Product ) {
						$user = is_user_logged_in() ? wp_get_current_user() : false;
						$role = ( $user && ! empty( $user->roles ) ) ? reset( $user->roles ) : 'guest';

						try {
							$af_price   = new \AF_C_S_P_Price();
							$qty        = isset( $cart_item['quantity'] ) ? max( 1, (int) $cart_item['quantity'] ) : 1;
							$role_price = $af_price->get_price_of_product( $base_product, $user, $role, $qty );

							if ( false !== $role_price && '' !== $role_price && $role_price >= 0 ) {
								$item_price = (float) $role_price;
							}
						} catch ( \Throwable $e ) {
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								if ( function_exists( 'wc_get_logger' ) ) {
									wc_get_logger()->warning(
										sprintf(
											'BOS4W role price lookup failed for product %d: %s',
											$base_product instanceof WC_Product ? $base_product->get_id() : 0,
											$e->getMessage()
										),
										array( 'source' => 'bos4w' )
									);
								} else {
									error_log(
										sprintf(
											'[BOS4W] role price lookup failed for product %d: %s',
											$base_product instanceof WC_Product ? $base_product->get_id() : 0,
											$e->getMessage()
										)
									);
								}
							}
						}
					}
				}

				// Determine if a fixed price is set at the variation, product, or global level.
				$fixed_price = false;

				// Check if the variation has a fixed price.
				if ( $variation_id > 0 ) {
					$fixed_price = $product->get_meta( 'bos4w_use_variation_fixed_price_' . $variation_id, true ) === 'yes';
				}

				// If not set at variation level, check at the product level.
				if ( ! $fixed_price && ! $product->get_meta( '_bos4w_saved_variation_subs', true ) ) {
					$fixed_price = $product_main->get_meta( '_bos4w_use_fixed_price', true ) === 'yes';
				}

				// If still not set, check at the global level.
				if ( ! $fixed_price && ! $product_main->get_meta( '_bos4w_saved_variation_subs', true ) ) {
					$global_plans = $this->product_has_global_subscription_plans( $product );
					if ( ! empty( $global_plans ) ) {
						$fixed_price = false;
					}
				}

				// Apply the discount based on the determined fixed price setting.
				if ( isset( $discount ) && $discount > 0 ) {
					if ( $fixed_price ) {
						$discounted_price = $item_price - $discount;
					} else {
						$discounted_price = $item_price - ( $item_price * ( $discount / 100 ) );
					}
				} else {
					$discounted_price = $item_price;
				}

				if ( isset( $_REQUEST['bos4w-selected-price'] ) && ! empty( $_REQUEST['bos4w-selected-price'] ) && $product->is_type( array( 'bundle', 'composite' ) ) ) {
					$discounted_price = self::parse_price_to_float( sanitize_text_field( wp_unslash( $_REQUEST['bos4w-selected-price'] ) ) );
					/**
					 * Filter formatted price.
					 *
					 * @param float $formatted_price Formatted price.
					 * @param float $price Un-formatted price.
					 * @param int $decimals Number of decimals.
					 * @param string $decimal_separator Decimal separator.
					 * @param string $thousand_separator Thousand separator.
					 * @param float|string $original_price Original price as float, or empty string.
					 *
					 * @since 5.0.0.
					 */
					// $discounted_price = apply_filters( 'formatted_woocommerce_price', number_format( sanitize_text_field( wp_unslash( $_REQUEST['bos4w-selected-price'] ) ), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ), sanitize_text_field( wp_unslash( $_REQUEST['bos4w-selected-price'] ) ), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator(), sanitize_text_field( wp_unslash( $_REQUEST['bos4w-selected-price'] ) ) );
				}

				$cart_item['bos4w_data'] = array(
					'selected_subscription' => $selected_plan,
					'discounted_price'      => $discounted_price,
				);

				$cart_item['_subscription_period']          = $plan_data[1];
				$cart_item['_subscription_period_interval'] = $plan_data[0];
			}

			return $cart_item;
		}

		/**
		 * Price display in cart
		 *
		 * @param string $price The price.
		 * @param array  $cart_item Cart item array.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return mixed|string
		 */
		public static function bos4w_show_cart_item_subscription_options( $price, $cart_item, $cart_item_key ) {
			$display_plans = array();

			$product = $cart_item['data'];

			$is_mini_cart = did_action( 'woocommerce_before_mini_cart' ) !== did_action( 'woocommerce_after_mini_cart' );

			// Only show options in cart.
			if ( ! is_cart() || $is_mini_cart ) {
				return $price;
			}

			$allow_cart_subscription = get_option( 'bos4w_allow_cart_subscription', 'no' );

			if ( $allow_cart_subscription && 'yes' !== $allow_cart_subscription ) {
				if ( ! self::bos4w_has_plans( $product ) ) {
					return $price;
				}
			}

			$product_id   = $cart_item['product_id'];
			$product_data = wc_get_product( $cart_item['product_id'] );
			$plan_options = ( new BOS4W_Front_End() )->get_discounted_prices( $product_data );
			$selected     = bos_cart_item_is_bos_product( $cart_item ) ? bos_cart_item_is_bos_product( $cart_item ) : '';

			$plan_type = '';
			if ( $product_data->is_type( 'variable' ) ) {
				$plan_type = 'variation';
			}

			$is_container = $product->is_type( array( 'bundle', 'composite' ) );
			$is_component =
				! empty( $cart_item['bundled_by'] ) ||
				! empty( $cart_item['bundled_item_id'] ) ||
				! empty( $cart_item['composite_parent'] ) ||
				! empty( $cart_item['composite_item'] );

			if ( $plan_options && ( $is_container || $is_component ) ) {
				return $price;
			}

			if ( $plan_options && $allow_cart_subscription && 'yes' === $allow_cart_subscription ) {
				ob_start();

				if ( $cart_item['variation_id'] ) {
					$product_id = $cart_item['variation_id'];

					$plans = ( new BOS4W_Front_End() )->product_has_subscription_plans( $product_data );

					if ( isset( $plans['variation'][ $product_id ] ) ) {
						// Variation-level discounts.
						$plan_options[ $product_id ] = $plans['variation'][ $product_id ];
					} elseif ( isset( $plans['product'] ) ) {
						// Product-level discounts.
						$plan_options = $plans['product'];
						$plan_type    = '';
					} elseif ( isset( $plans['global'] ) ) {
						// Global discounts.
						$plan_options = $plans['global'];
						$plan_type    = '';
					}
				}
				$product_in = wc_get_product( $product_id );

				$label           = '';
				$display_plans[] = array(
					'label'    => $label,
					'value'    => array(
						'price' => BOS4W_Front_End::bos4w_get_product_price( $product_in ),
						'product_id' => $cart_item['product_id'],
					),
					'selected' => $selected,
				);

				if ( 'variation' === $plan_type ) {
					foreach ( $plan_options as $variation_id => $plans ) {
						if ( $cart_item['variation_id'] === $variation_id ) {
							$variation = wc_get_product( $variation_id );
							if ( ! $variation ) {
								continue;
							}

							$product_price   = BOS4W_Front_End::bos4w_get_product_price( $variation );
							$use_fixed_price = $variation->get_meta( '_bos4w_use_fixed_price' );

							foreach ( $plans as $plan ) {
								if ( isset( $plan['subscription_price'] ) && ! empty( $plan['subscription_price'] ) ) {
									$bos_type           = 'fixed_price';
									$subscription_price = ! empty( $plan['subscription_price'] ) ? floatval( $plan['subscription_price'] ) : 0;
									$discounted_price   = wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_price;
									$use_fixed_price    = true;
								} else {
									$bos_type              = 'percentage_price';
									$subscription_discount = ! empty( $plan['subscription_discount'] ) ? floatval( $plan['subscription_discount'] ) : 0;
									$discounted_price      = wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( wc_format_decimal( $product_price, wc_get_price_decimals() ) * ( (float) $subscription_discount / 100 ) );
								}

								$discount_plan = $use_fixed_price ? esc_attr( $plan['subscription_price'] ) : esc_attr( $plan['subscription_discount'] );
								$discount_plan = $discount_plan ?? 0;

								$discount_data = array(
									'product_id' => $cart_item['product_id'],
									'discount'   => $discount_plan,
									'price'      => $discounted_price,
									'type'       => $bos_type,
									'interval'   => $plan['subscription_period_interval'],
									'period'     => $plan['subscription_period'],
								);

								$display_plans[] = array(
									'label'    => $discount_plan,
									'value'    => $discount_data,
									'selected' => $selected,
								);
							}
						}
					}
				} else {
					foreach ( $plan_options as $plan ) {
						$product_price   = BOS4W_Front_End::bos4w_get_product_price( $product_data );
						$use_fixed_price = $product_data->get_meta( '_bos4w_use_fixed_price' );

						if ( $cart_item['variation_id'] ) {
							$variation     = wc_get_product( $cart_item['variation_id'] );
							$product_price = BOS4W_Front_End::bos4w_get_product_price( $variation );
						}

						if ( isset( $plan['subscription_price'] ) && ! empty( $plan['subscription_price'] ) ) {
							$bos_type           = 'fixed_price';
							$subscription_price = ! empty( $plan['subscription_price'] ) ? floatval( $plan['subscription_price'] ) : 0;
							$discounted_price   = wc_format_decimal( $product_price, wc_get_price_decimals() ) - $subscription_price;
							$use_fixed_price    = true;
						} else {
							$bos_type              = 'percentage_price';
							$subscription_discount = ! empty( $plan['subscription_discount'] ) ? floatval( $plan['subscription_discount'] ) : 0;
							$discounted_price      = wc_format_decimal( $product_price, wc_get_price_decimals() ) - ( wc_format_decimal( $product_price, wc_get_price_decimals() ) * ( (float) $subscription_discount / 100 ) );
						}

						$discount_plan = $use_fixed_price && isset( $plan['subscription_price'] ) ? esc_attr( $plan['subscription_price'] ) : esc_attr( $plan['subscription_discount'] );
						$discount_plan = $discount_plan ?? 0;

						$discount_data = array(
							'product_id' => $cart_item['product_id'],
							'discount'   => $discount_plan,
							'price'      => $discounted_price,
							'type'       => $bos_type,
							'interval'   => $plan['subscription_period_interval'],
							'period'     => $plan['subscription_period'],
						);

						$display_plans[] = array(
							'label'    => $discount_plan,
							'value'    => $discount_data,
							'selected' => $selected,
						);
					}
				}

				wc_get_template(
					'cart/cart-item-bos-options.php',
					array(
						'product_plans' => $display_plans,
						'cart_item_key' => $cart_item_key,
					),
					false,
					BOS_FUNC_PATH . '/templates/'
				);

				$convert_to_sub_options = ob_get_clean();

				$price = $convert_to_sub_options;
			} else {
				if ( WC()->cart->get_product_price( $cart_item['data'] ) ) {
					if ( ! self::bos4w_has_plans( $product ) ) {
						return $price;
					}
					// Grab bare price without subscription details.
					remove_filter( 'woocommerce_cart_product_price', array( 'WC_Subscriptions_Cart', 'cart_product_price' ), 10 );
					remove_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'bos4w_show_cart_item_subscription_options' ), 1000 );

					$price = wcs_price_string( self::bos4w_display_format_the_frequency( WC()->cart->get_product_price( $cart_item['data'] ), $product->get_meta( '_subscription_period', true ), $product->get_meta( '_subscription_period_interval', true ) ) );

					add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'bos4w_show_cart_item_subscription_options' ), 1000, 3 );
					add_filter( 'woocommerce_cart_product_price', array( 'WC_Subscriptions_Cart', 'cart_product_price' ), 10, 2 );
				}
			}

			return $price;
		}

		/**
		 * Price display in cart for item
		 *
		 * @param string $price The price.
		 * @param object $cart_item Cart item object.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return mixed|string
		 */
		public static function bos4w_show_cart_item_show_subscription( $price, $cart_item, $cart_item_key ) {
			$product = $cart_item['data'];

			$is_mini_cart = did_action( 'woocommerce_before_mini_cart' ) !== did_action( 'woocommerce_after_mini_cart' );

			// Only show options in cart.
			if ( ! is_cart() || $is_mini_cart ) {
				return $price;
			}

			if ( ! self::bos4w_has_plans( $product ) ) {
				return $price;
			}

			if ( ! $product->is_type( array( 'bundle', 'composite' ) ) ) {
				return $price;
			}

			if ( class_exists( 'WC_PB_Product_Prices' ) && WC_PB_Product_Prices::is_bundled_pricing_context( $product ) ) {
				return $price;
			}

			if ( class_exists( 'WC_CP_Products' ) && WC_CP_Products::is_component_option_pricing_context( $product ) ) {
				return $price;
			}

			if ( WC()->cart->get_product_price( $cart_item['data'] ) ) {
				// Grab bare price without subscription details.
				remove_filter( 'woocommerce_cart_product_price', array( 'WC_Subscriptions_Cart', 'cart_product_price' ), 10 );
				remove_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'bos4w_show_cart_item_subscription_options' ), 1000 );

				$price = wcs_price_string( self::bos4w_display_format_the_frequency( $price, $product->get_meta( '_subscription_period', true ), $product->get_meta( '_subscription_period_interval', true ) ) );

				add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'bos4w_show_cart_item_subscription_options' ), 1000, 3 );
				add_filter( 'woocommerce_cart_product_price', array( 'WC_Subscriptions_Cart', 'cart_product_price' ), 10, 2 );
			}

			return $price;
		}

		/**
		 * Add to cart action.
		 *
		 * @param string $item_key Cart item key.
		 * @param int    $product_id Product added to the cart.
		 * @param int    $quantity Cart item quantity.
		 * @param int    $variation_id Variation ID being added to the cart or 0.
		 * @param array  $variation Variation attributes.
		 * @param array  $item_data Cart item custom data.
		 */
		public static function bos4w_apply_subscription_schemes_on_add_to_cart( $item_key, $product_id, $quantity, $variation_id, $variation, $item_data ) {
			self::bos4ws_apply_subscriptions( WC()->cart );

		}

		/**
		 * Apply the subscriptions.
		 *
		 * @param object $cart Cart object.
		 */
		public static function bos4ws_apply_subscriptions( $cart ) {
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['bos4w_data'] ) ) {
					$cart->cart_contents[ $cart_item_key ] = self::bos4ws_apply_subscription( $cart->cart_contents[ $cart_item_key ] );
				}
			}
		}

		/**
		 * Apply the subscription plan.
		 *
		 * @param object $cart_item Cart item data.
		 *
		 * @return mixed|void
		 */
		public static function bos4ws_apply_subscription( $cart_item ) {
			$scheme_to_apply = self::bos4w_get_subscription_scheme( $cart_item );
			/**
			 * Filter bos_use_regular_price.
			 *
			 * @param bool false
			 *
			 * @since 2.0.2
			 */
			$display_the_price = apply_filters( 'bos_use_regular_price', false );

			if ( $scheme_to_apply ) {
				self::bos4w_set_subscription_scheme( $cart_item, $scheme_to_apply );

				if ( $cart_item['data']->is_type( array( 'bundle', 'composite' ) ) ) {
					$plan_data = explode( '_', $cart_item['bos4w_data']['selected_subscription'] );
					$product   = wc_get_product( $cart_item['product_id'] );

					// Check if the cart item has a variation ID.
					$variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
					$fixed_price  = false;

					if ( $variation_id > 0 ) {
						$fixed_price = $product->get_meta( 'bos4w_use_variation_fixed_price_' . $variation_id, true ) === 'yes';
					} else {
						$fixed_price = $product->get_meta( '_bos4w_use_fixed_price', true ) === 'yes';
					}

					if ( $product->contains( 'priced_individually' ) ) {
						if ( $cart_item['data']->is_type( array( 'bundle' ) ) ) {
							$bundles = wc_pb_get_bundled_cart_items( $cart_item );

							foreach ( $bundles as $bundle ) {
								$bundled_item       = wc_pb_get_bundled_item( $bundle['bundled_item_id'], $cart_item['data'] );
								$component_discount = $bundled_item->get_discount();

								if ( $bundle['data']->get_price() ) {
									$select_price = ! $display_the_price ? $bundle['data']->get_price() : $bundle['data']->get_regular_price();

									if ( $fixed_price && $product->get_meta( '_bos4w_saved_subs', true ) ) {
										$calculated_price = wc_format_decimal( $select_price, wc_get_price_decimals() );
									} else {
										$calculated_price = wc_format_decimal( $select_price - ( $select_price * ( (float) wc_format_decimal( $plan_data[2] ) / 100 ) ), wc_get_price_decimals() );
									}

									if ( $component_discount > 0 ) {
										if ( $bundle['variation_id'] ) {
											$product_bundle = wc_get_product( $bundle['variation_id'] );
										} else {
											$product_bundle = wc_get_product( $bundle['product_id'] );
										}

										$pick_price = ! $display_the_price ? $product_bundle->get_price() : $product_bundle->get_regular_price();

										if ( $fixed_price && $product->get_meta( '_bos4w_saved_subs', true ) ) {
											$calculated_price = wc_format_decimal( $pick_price, wc_get_price_decimals() );
										} else {
											$calculated_price = wc_format_decimal( $pick_price - ( $pick_price * ( (float) wc_format_decimal( $plan_data[2] ) / 100 ) ), wc_get_price_decimals() );
										}
									}

									$bundle['data']->set_price( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								}
							}
						}

						if ( $cart_item['data']->is_type( array( 'composite' ) ) ) {
							$composite = wc_cp_get_composited_cart_items( $cart_item );

							foreach ( $composite as $comp ) {
								$component_id       = $comp['composite_item'];
								$comp_obj           = new WC_Product_Composite( $product->get_id() );
								$component_obj      = new WC_CP_Component( $component_id, $comp_obj );
								$component_discount = $component_obj->get_discount();

								if ( $comp['data']->get_price() ) {
									$select_price = ! $display_the_price ? $comp['data']->get_price() : $comp['data']->get_regular_price();

									if ( $fixed_price && $product->get_meta( '_bos4w_saved_subs', true ) ) {
										$calculated_price = wc_format_decimal( $select_price, wc_get_price_decimals() );
									} else {
										$calculated_price = wc_format_decimal( $select_price - ( $select_price * ( (float) wc_format_decimal( $plan_data[2] ) / 100 ) ), wc_get_price_decimals() );
									}

									if ( $component_discount > 0 ) {
										if ( $comp['variation_id'] ) {
											$product_comp = wc_get_product( $comp['variation_id'] );
										} else {
											$product_comp = wc_get_product( $comp['product_id'] );
										}

										$pick_price = ! $display_the_price ? $product_comp->get_price() : $product_comp->get_regular_price();

										if ( $fixed_price && $product->get_meta( '_bos4w_saved_subs', true ) ) {
											$calculated_price = wc_format_decimal( $pick_price, wc_get_price_decimals() );
										} else {
											$calculated_price = wc_format_decimal( $pick_price - ( $pick_price * ( (float) wc_format_decimal( $plan_data[2] ) / 100 ) ), wc_get_price_decimals() );
										}
									}

									$comp['data']->set_price( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								}
							}
						}

						$select_price = ! $display_the_price ? $cart_item['data']->get_price() : $cart_item['data']->get_regular_price();

						if ( $fixed_price ) {
							$discounter_price = wc_format_decimal( $select_price - $plan_data[2], wc_get_price_decimals() );
						} else {
							$discounter_price = wc_format_decimal( $select_price - ( $select_price * ( (float) wc_format_decimal( $plan_data[2] ) / 100 ) ), wc_get_price_decimals() );
						}

						$cart_item['data']->set_price( NumberUtil::round( $discounter_price, wc_get_price_decimals() ) );
					} else {
						$dp = isset( $cart_item['bos4w_data']['discounted_price'] )
							? self::parse_price_to_float( $cart_item['bos4w_data']['discounted_price'] )
							: 0.0;

						$cart_item['data']->set_price( NumberUtil::round( $dp, wc_get_price_decimals() ) );
					}
				} else {
					$dp = isset( $cart_item['bos4w_data']['discounted_price'] )
						? self::parse_price_to_float( $cart_item['bos4w_data']['discounted_price'] )
						: 0.0;

					$cart_item['data']->set_price( NumberUtil::round( $dp, wc_get_price_decimals() ) );
				}
			}

			/**
			 * Filter bos4w_cart_item_data
			 *
			 * @param object $cart_item The cart item object data.
			 *
			 * @since 2.0.2
			 */
			return apply_filters( 'bos4w_cart_item_data', $cart_item );
		}

		/**
		 * Get the subscription scheme
		 *
		 * @param object $cart_item Cart item data.
		 *
		 * @return array|mixed
		 */
		public static function bos4w_get_subscription_scheme( $cart_item ) {
			return isset( $cart_item['bos4w_data'] ) ? $cart_item['bos4w_data'] : array();
		}

		/**
		 * Set as subscription product if has subscription plan.
		 *
		 * @param bool   $subscription The bool value.
		 * @param int    $product_id Product ID.
		 * @param object $product product object.
		 *
		 * @return mixed
		 */
		public static function bos4w_is_subscription( $subscription, $product_id, $product ) {
			if ( ! $product ) {
				return $subscription;
			}

			if ( self::bos4w_has_plans( $product ) ) {
				$subscription = true;
			}

			return $subscription;
		}

		/**
		 * Set the subscription selected plan.
		 *
		 * @param object $product Product Object.
		 * @param array  $scheme The selected scheme.
		 *
		 * @return bool
		 */
		public static function bos4w_set_subscription_scheme( $product, $scheme ) {
			$plan_data = explode( '_', $scheme['selected_subscription'] );
			$period    = $plan_data[1];
			$interval  = $plan_data[0];
			$discount  = end( $plan_data );

			if ( $product['data']->is_type( array( 'bundle', 'composite' ) ) ) {
				if ( $product['data']->contains( 'priced_individually' ) ) {
					$item_price = BOS4W_Front_End::bos4w_get_product_price( $product['data'] );

					if ( $product['data']->is_type( array( 'bundle' ) ) ) {
						$bundles = wc_pb_get_bundled_cart_items( $product );

						foreach ( $bundles as $bundle ) {
							$bundle['data']->update_meta_data( '_subscription_period', $period );
							$bundle['data']->update_meta_data( '_subscription_period_interval', $interval );
							$bundle['data']->update_meta_data( '_subscription_plan_data', $plan_data );
						}
					}

					if ( $product['data']->is_type( array( 'composite' ) ) ) {
						$composite = wc_cp_get_composited_cart_items( $product );

						foreach ( $composite as $comp ) {
							$comp['data']->update_meta_data( '_subscription_period', $period );
							$comp['data']->update_meta_data( '_subscription_period_interval', $interval );
							$comp['data']->update_meta_data( '_subscription_plan_data', $plan_data );
						}
					}
				} else {
					$item_price = $product['data']->get_price();
				}
			} else {
				$item_price = $product['data']->get_price();
			}

			if ( isset( $discount ) && $discount > 0 ) {
				$discounted_price = $item_price - ( $item_price * ( $discount / 100 ) );
			} else {
				$discounted_price = $item_price;
			}

			$product['data']->update_meta_data( '_subscription_period', $period );
			$product['data']->update_meta_data( '_subscription_period_interval', $interval );
			$product['data']->update_meta_data( '_subscription_plan_data', $plan_data );
			$product['data']->update_meta_data( '_subscription_price', $discounted_price );

			return true;
		}

		/**
		 * Check if product has subscription plans
		 *
		 * @param object $product Product object.
		 *
		 * @return bool
		 */
		public static function bos4w_has_plans( $product ) {
			$bos4w_plans = $product->get_meta( '_subscription_plan_data' );
			if ( $bos4w_plans ) {
				return true;
			}

			return false;
		}

		/**
		 * Format the price display
		 *
		 * @param string $amount The amount.
		 * @param int    $bill_period Billing period.
		 * @param int    $bill_interval Billing interval.
		 * @param string $display_ex_tax_label Label text.
		 *
		 * @return array
		 */
		public static function bos4w_display_format_the_frequency( $amount, $bill_period, $bill_interval, $display_ex_tax_label = false ) {
			return array(
				'currency'                    => get_woocommerce_currency(),
				'recurring_amount'            => $amount,
				'subscription_period'         => $bill_period,
				'subscription_interval'       => $bill_interval,
				'display_excluding_tax_label' => $display_ex_tax_label,
			);
		}

		/**
		 * Check if a product has subscription plans
		 *
		 * @param object $product Product object data.
		 *
		 * @return array
		 */
		public function product_has_subscription_plans( $product ) {
			$display_plans = array();

			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_children();
				foreach ( $variations as $variation_id ) {
					$variation       = wc_get_product( $variation_id );
					$variation_plans = $variation->get_meta( '_bos4w_saved_variation_subs', true );
					if ( $variation_plans ) {
						$display_plans['variation'][ $variation_id ] = $variation_plans;
					}
				}
			}

			$product_plans = $product->get_meta( '_bos4w_saved_subs', true );
			if ( $product_plans ) {
				$display_plans['product'] = $product_plans;
			}

			$global_plans = $this->product_has_global_subscription_plans( $product );
			if ( $global_plans ) {
				$display_plans['global'] = $global_plans;
			}

			return $display_plans;
		}

		/**
		 * Get global discounts
		 *
		 * @param object $product Product Object.
		 *
		 * @return false|mixed|void
		 */
		public function product_has_global_subscription_plans( $product ) {
			$plans = get_option( 'bos4w_global_saved_subs' );
			if ( empty( $plans ) || ! is_array( $plans ) ) {
				return array();
			}

			$product = ( $product instanceof WC_Product ) ? $product : wc_get_product( $product );
			if ( ! $product ) {
				return array();
			}
			$base_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

			// Also check MAIN/original product in case plans were saved against default-language cats.
			$main_product = BOS4W_Front_End::wpml_get_main_product( $product, true, true );
			$main_base_id = $main_product ? ( $main_product->is_type( 'variation' ) ? $main_product->get_parent_id() : $main_product->get_id() ) : $base_id;

			$cats_current = wp_get_post_terms( $base_id, 'product_cat', array( 'fields' => 'ids' ) );
			$cats_main    = ( $main_base_id !== $base_id ) ? wp_get_post_terms( $main_base_id, 'product_cat', array( 'fields' => 'ids' ) ) : array();

			// Map any term ID to a stable "group id" (WPML trid when available).
			$get_trid = function ( $term_id ) {
				$term_id = (int) $term_id;
				if ( $term_id <= 0 ) {
					return 0;
				}
				if ( has_filter( 'wpml_element_trid' ) ) {
					/**
					 * Filter the term trid.
					 *
					 * @param int $trid The term trid.
					 * @param int $term_id The term id.
					 * @param string $taxonomy The taxonomy.
					 * @return int The term trid.
					 *
					 * @since 5.0.2
					 */
					$trid = apply_filters( 'wpml_element_trid', null, $term_id, 'tax_product_cat' );
					if ( $trid ) {
						return (int) $trid;
					}
				}

				return (int) $term_id;
			};

			$product_trids = array();
			foreach ( $cats_current as $cid ) {
				$t = $get_trid( $cid );
				if ( $t ) {
					$product_trids[ $t ] = true;
				}
			}
			foreach ( $cats_main as $cid ) {
				$t = $get_trid( $cid );
				if ( $t ) {
					$product_trids[ $t ] = true;
				}
			}

			$plan_signature = function ( $p ) {
				$period   = isset( $p['subscription_period'] ) ? (string) $p['subscription_period'] : '';
				$interval = isset( $p['subscription_period_interval'] ) ? (int) $p['subscription_period_interval'] : 0;
				$percent  = isset( $p['subscription_discount'] ) ? (float) $p['subscription_discount'] : 0.0;
				$fixed    = isset( $p['subscription_price'] ) ? (float) $p['subscription_price'] : 0.0;
				$id       = ( isset( $p['id'] ) && '' !== $p['id'] ) ? 'id:' . $p['id'] : '';

				return implode( '|', array( $id, 'per:' . $period, 'int:' . $interval, 'pc:' . $percent, 'fx:' . $fixed ) );
			};

			$out  = array();
			$seen = array();

			foreach ( $plans as $plan ) {
				$raw = array();
				if ( isset( $plan['product_cat'] ) ) {
					$raw = is_array( $plan['product_cat'] ) ? $plan['product_cat'] : explode( ',', (string) $plan['product_cat'] );
					$raw = array_filter( array_map( 'intval', array_map( 'trim', $raw ) ) );
				}

				$matches = false;

				if ( empty( $raw ) ) {
					$matches = true;
				} else {
					foreach ( $raw as $tid ) {
						$trid = $get_trid( $tid );
						if ( $trid && isset( $product_trids[ $trid ] ) ) {
							$matches = true;
							break;
						}
					}
				}

				if ( $matches ) {
					$sig = $plan_signature( $plan );
					if ( isset( $seen[ $sig ] ) ) {
						continue;
					}
					$seen[ $sig ] = true;
					$out[]        = $plan;
				}
			}

			return array_values( $out );
		}

		/**
		 * Validate the selected subscription plan for a given product.
		 *
		 * @param int    $product_id The ID of the product.
		 * @param string $selected_plan The selected plan identifier.
		 * @param int    $variation_id The ID of the product variation.
		 *
		 * @return bool True if the plan is valid, false otherwise.
		 */
		public function bos4w_validate_plan_selection( $product_id, $selected_plan, $variation_id ) {
			$product = wc_get_product( $product_id );
			$plans   = $this->product_has_subscription_plans( $product );

			if ( empty( $plans ) ) {
				return false;
			}

			$plan_data = explode( '_', esc_attr( $selected_plan ) );
			$period    = $plan_data[1];
			$interval  = $plan_data[0];
			$discount  = end( $plan_data );

			if ( isset( $plans['variation'] ) && is_array( $plans['variation'] ) && $variation_id ) {
				if ( isset( $plans['variation'][ $variation_id ] ) && is_array( $plans['variation'][ $variation_id ] ) ) {
					$variation = wc_get_product( $variation_id );
					if ( $this->validate_plan_data( $plans['variation'][ $variation_id ], $period, $interval, $discount, $variation, 'variation' ) ) {
						return true;
					}
				}
			}

			if ( isset( $plans['product'] ) && is_array( $plans['product'] ) ) {
				if ( $this->validate_plan_data( $plans['product'], $period, $interval, $discount, $product, 'product' ) ) {
					return true;
				}
			}

			if ( isset( $plans['global'] ) && is_array( $plans['global'] ) ) {
				if ( $this->validate_plan_data( $plans['global'], $period, $interval, $discount, $product, 'global' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Validate the plan data against the given criteria.
		 *
		 * @param array  $plans List of subscription items.
		 * @param string $period Subscription period.
		 * @param string $interval Subscription period interval.
		 * @param float  $discount Discount amount.
		 * @param object $product Product object.
		 * @param string $plan_type Type of the plan.
		 *
		 * @return bool
		 */
		private function validate_plan_data( $plans, $period, $interval, $discount, $product, $plan_type ) {
			foreach ( $plans as $subscription_item ) {
				$subscription_period   = isset( $subscription_item['subscription_period'] ) ? esc_attr( $subscription_item['subscription_period'] ) : '';
				$subscription_interval = isset( $subscription_item['subscription_period_interval'] ) ? esc_attr( $subscription_item['subscription_period_interval'] ) : '';

				if ( $product->is_type( 'variation' ) ) {
					$fixed_variable_price = $product->get_meta( 'bos4w_use_variation_fixed_price_' . $product->get_id(), true );
					$fixed_price          = false;
					if ( 'yes' === $fixed_variable_price ) {
						$fixed_price = true;
					}
				} else {
					$fixed_price = $product->get_meta( '_bos4w_use_fixed_price', true );
				}

				if ( $fixed_price && 'global' !== $plan_type ) {
					$subscription_discount = isset( $subscription_item['subscription_price'] ) && ! empty( $subscription_item['subscription_price'] ) ? esc_attr( $subscription_item['subscription_price'] ) : 0;
				} else {
					$subscription_discount = isset( $subscription_item['subscription_discount'] ) && ! empty( $subscription_item['subscription_discount'] ) ? esc_attr( $subscription_item['subscription_discount'] ) : 0;
				}

				if ( trim( $period ) === trim( $subscription_period ) && trim( $interval ) === trim( $subscription_interval ) && (float) trim( $discount ) === (float) trim( $subscription_discount ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Parse the price
		 *
		 * @param string $value Do format the price.
		 *
		 * @return float
		 */
		private static function parse_price_to_float( $value ): float {
			if ( is_numeric( $value ) ) {
				return (float) $value;
			}

			$raw = (string) $value;

			// Remove tags and decode entities (e.g. &nbsp;).
			$raw = html_entity_decode( wp_strip_all_tags( $raw ), ENT_QUOTES, get_bloginfo( 'charset' ) );

			// WooCommerce separators.
			$thousand = wc_get_price_thousand_separator();
			$decimal  = wc_get_price_decimal_separator();

			// Normalize weird spaces.
			$raw = str_replace( array( "\xC2\xA0", "\xE2\x80\xAF" ), ' ', $raw );
			$raw = trim( preg_replace( '/\s+/u', '', $raw ) );

			// Keep only digits, sign and known separators.
			$allowed = preg_quote( $thousand, '/' ) . preg_quote( $decimal, '/' );
			$raw     = preg_replace( '/[^0-9\-\+' . $allowed . ']/u', '', $raw );

			// Strip thousands sep (if defined and different from decimal).
			if ( $thousand && $thousand !== $decimal ) {
				$raw = str_replace( $thousand, '', $raw );
			}

			// Convert decimal sep to dot.
			if ( $decimal && '.' !== $decimal ) {
				$raw = str_replace( $decimal, '.', $raw );
			}

			// Final sanitize to a decimal, then cast.
			$raw = wc_format_decimal( $raw, wc_get_price_decimals() );

			return (float) $raw;
		}
	}
}

new BOS4W_Cart_Options();
