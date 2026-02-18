<?php
/**
 * Subscription field class for managing delivery slots in checkout.
 *
 * @package Iconic_WDS\Subscriptions
 */

namespace Iconic_WDS\Subscriptions;

use Iconic_WDS\Cart;
use Iconic_WDS\Subscriptions\SubscriptionSession;
use WC_Product;
use Iconic_WDS\Helpers;
use Iconic_WDS\Subscriptions\ValueObjects\SubscriptionProductType;

/**
 * Subscription field class.
 */
class SubscriptionField {
	/**
	 * Cart.
	 *
	 * @var Cart
	 */
	private $cart;

	/**
	 * Active integration.
	 *
	 * @var Integration
	 */
	private $active_integration;

	/**
	 * Constructor.
	 *
	 * @param Cart $cart Cart.
	 */
	public function __construct( $cart ) {
		$this->cart               = $cart;
		$this->active_integration = Boot::get_active_integration();
	}

	/**
	 * Add subscription field to the "classic" checkout page.
	 */
	public function add_subscription_field() {
		if ( ! $this->active_integration ) {
			return;
		}

		$subscription_product = $this->find_subscription_product_in_cart();
		$regular_products     = $this->find_regular_products_in_cart();

		// If no products need delivery slots, don't show anything.
		if ( ! $subscription_product && empty( $regular_products ) ) {
			return;
		}

		?>
			<tr>
				<td colspan="2">
					<div id="iconic-wds-subscription-field" class="iconic-wds-subscription-field">
					</div>
				</td>
			</tr>
		<?php
	}

	/**
	 * Get field data for a subscription product.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return array
	 */
	public function get_subscription_field_data( WC_Product $product ) {
		$shipping_method_id   = $this->cart->get_subscription_shipping_method_id();
		$shipping_method_type = Helpers::get_shipping_method_type( $shipping_method_id );
		$session              = SubscriptionSession::from_woo_session( SubscriptionProductType::SUBSCRIPTION, $shipping_method_id );
		$reserved_slot        = null;

		if ( $session ) {
			$reserved_slot = $session->to_array_formatted();
			// If shipping method has changed, reserved_slot will be false.
			if ( false === $reserved_slot ) {
				$reserved_slot = null;
			}
		}

		$data = array(
			'products'             => array(
				$product->get_id() => array(
					'id'   => $product->get_id(),
					'name' => $product->get_name(),
				),
			),
			'reserved_slot'        => $reserved_slot,
			'shipping_method_type' => $shipping_method_type,
		);

		// Only include subscription data for subscription product type.
		$data['subscription'] = array(
			'interval' => $this->active_integration->get_subscription_interval( $product ),
			'period'   => $this->active_integration->get_subscription_period( $product ),
		);

		return $data;
	}

	/**
	 * Get field data for regular products.
	 *
	 * @param array $products Array of regular products.
	 *
	 * @return array
	 */
	public function get_regular_field_data( $products ) {
		$shipping_method_id = $this->cart->get_regular_shipping_method_id();
		$session            = SubscriptionSession::from_woo_session( SubscriptionProductType::REGULAR, $shipping_method_id );
		$reserved_slot      = null;

		if ( $session ) {
			$reserved_slot = $session->to_array_formatted();
			// If shipping method has changed, reserved_slot will be false.
			if ( false === $reserved_slot ) {
				$reserved_slot = null;
			}
		}

		$products_data = array();
		foreach ( $products as $product ) {
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$products_data[ $product->get_id() ] = array(
				'id'   => $product->get_id(),
				'name' => $product->get_name(),
			);
		}

		return array(
			'products'      => $products_data,
			'product_count' => count( $products_data ),
			'reserved_slot' => $reserved_slot,
		);
	}

	/**
	 * Find subscription product in cart.
	 *
	 * @return WC_Product|null
	 */
	public function find_subscription_product_in_cart(): ?WC_Product {
		if ( empty( $this->cart->products ) ) {
			$this->cart->refresh_cart();
		}

		foreach ( $this->cart->products as $cart_data ) {
			$product = wc_get_product( $cart_data['id'] );
			if ( $this->active_integration && $this->active_integration->is_subscription_product( $product ) ) {
				return $product;
			}
		}

		return null;
	}

	/**
	 * Get regular products in cart.
	 *
	 * @return array Array of WC_Product objects.
	 */
	public function find_regular_products_in_cart(): array {
		$regular_products = array();

		foreach ( $this->cart->products as $cart_data ) {
			$product = wc_get_product( $cart_data['id'] );
			if ( $this->active_integration && $this->active_integration->is_subscription_product( $product ) ) {
				continue;
			}

			// if its not a subscription product, add it to the regular products array.
			$regular_products[] = $product;
		}

		return $regular_products;
	}

	/**
	 * Get formatted interval.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 */
	public function get_formatted_interval( WC_Product $product, $shipping_type = 'delivery' ) {
		$interval = $this->active_integration->get_subscription_interval( $product );
		$period   = $this->active_integration->get_subscription_period( $product );

		if ( ! is_numeric( $interval ) ) {
			return '';
		}

		if ( ! $shipping_type ) {
			$shipping_type = Helpers::get_label_type();
		}

		if ( 'delivery' === $shipping_type ) {
			if ( 'week' === $period ) {
				return 1 === intval( $interval ) ? sprintf(
					// Translators: The subscription period (week, month, year, day).
					_x( 'Will be delivered on or around the selected day <strong>every %1$s</strong>', 'subscription', 'jckwds' ),
					$this->get_formatted_period( $period, $interval )
				) : sprintf(
					// Translators: 1. is the interval, 2. is the period (week, month, year, day).
					_x( 'Will be delivered on or around the selected day <strong>every %1$d %2$s</strong>', 'subscription', 'jckwds' ),
					$interval,
					$this->get_formatted_period( $period, $interval )
				);
			}
			if ( 'day' === $period ) {
				if ( 1 === intval( $interval ) ) {
					return sprintf(
						// Translators: %s is the period (week, month, year, day).
						_x( 'Your subscription will be delivered <strong>every %1$s</strong>', 'subscription', 'jckwds' ),
						$this->get_formatted_period( $period, $interval )
					);
				} else {
					return sprintf(
						// Translators: %s is the period (week, month, year, day).
						_x( 'Your subscription will be delivered <strong>every %1$s %2$s</strong>', 'subscription', 'jckwds' ),
						$interval,
						$this->get_formatted_period( $period, $interval ),
					);
				}
			}
			// Translators: %s is the period (week, month, year, day).
			return 1 === intval( $interval ) ? sprintf(
				// Translators: The subscription period (week, month, year, day).
				_x( 'Will be delivered on or around the selected date <strong>every %1$s</strong>', 'subscription', 'jckwds' ),
				$this->get_formatted_period( $period, $interval )
			) : sprintf(
				// Translators: 1. is the interval, 2. is the period (week, month, year, day).
				_x( 'Will be delivered on or around the selected date <strong>every %1$d %2$s</strong>', 'subscription', 'jckwds' ),
				$interval,
				$this->get_formatted_period( $period, $interval )
			);
		} else {
			// Translators: %s is the period (week, month, year, day).
			return 1 === intval( $interval ) ? sprintf(
				// Translators: The subscription period (week, month, year, day).
				_x( 'Your subscription can be collected <strong>every %s</strong>', 'subscription', 'jckwds' ),
				$this->get_formatted_period( $period, $interval )
			) : sprintf(
				// Translators: 1. is the interval, 2. is the period (week, month, year, day).
				_x( 'Your subscription can be collected <strong>every %1$d %2$s</strong>', 'subscription', 'jckwds' ),
				$interval,
				$this->get_formatted_period( $period, $interval )
			);
		}
	}

	/**
	 * Add ordinal suffix to a number.
	 *
	 * @param int $number Number.
	 *
	 * @return string
	 */
	public function add_ordinal_suffix( $number ) {
		if ( ! is_numeric( $number ) ) {
			return $number;
		}

		$suffixes = array( 'th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th' );
		$mod100   = $number % 100;

		if ( $mod100 >= 11 && $mod100 <= 13 ) {
			return $number . 'th';
		}

		return $number . $suffixes[ $number % 10 ];
	}

	/**
	 * Get formatted period.
	 *
	 * @param string $period   Period.
	 * @param int    $interval Interval.
	 *
	 * @return string
	 */
	public function get_formatted_period( $period, $interval ) {
		if ( 'day' === $period ) {
			return _nx( 'day', 'days', $interval, 'subscription', 'jckwds' );
		}

		if ( 'week' === $period ) {
			return _nx( 'week', 'weeks', $interval, 'subscription', 'jckwds' );
		}

		if ( 'month' === $period ) {
			return _nx( 'month', 'months', $interval, 'subscription', 'jckwds' );
		}

		if ( 'year' === $period ) {
			return _nx( 'year', 'years', $interval, 'subscription', 'jckwds' );
		}

		return $period;
	}
}
