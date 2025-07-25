<?php
/**
 * Subscriptions Order Class
 *
 * Mirrors and overloads a few functions in the WC_Order class to work for subscriptions.
 *
 * @package    WooCommerce Subscriptions
 * @subpackage WC_Subscriptions_Order
 * @category   Class
 */
class WC_Subscriptions_Order {

	/**
	 * A flag to indicate whether subscription price strings should include the subscription length
	 */
	public static $recurring_only_price_strings = false;

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function init() {

		add_action( 'woocommerce_thankyou', __CLASS__ . '::subscription_thank_you' );

		add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::add_contains_subscription_hidden_field', 10, 1 );

		add_action( 'woocommerce_admin_order_data_after_order_details', __CLASS__ . '::contains_subscription_hidden_field', 10, 1 );

		// Add column that indicates whether an order is parent or renewal for a subscription
		add_filter( 'manage_edit-shop_order_columns', __CLASS__ . '::add_contains_subscription_column' );
		add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::add_contains_subscription_column_content', 10, 1 );

		// HPOS - Add column that indicates whether an order is parent or renewal for a subscription.
		add_filter( 'woocommerce_shop_order_list_table_columns', __CLASS__ . '::add_contains_subscription_column' );
		add_action( 'woocommerce_shop_order_list_table_custom_column', __CLASS__ . '::add_contains_subscription_column_content_orders_table', 10, 2 );

		// Record initial payment against the subscription & set start date based on that payment
		add_action( 'woocommerce_order_status_changed', __CLASS__ . '::maybe_record_subscription_payment', 9, 3 );

		// Sometimes, even if the order total is $0, the order still needs payment
		add_filter( 'woocommerce_order_needs_payment', __CLASS__ . '::order_needs_payment', 10, 3 );

		// Add subscription information to the order complete emails.
		add_action( 'woocommerce_email_after_order_table', __CLASS__ . '::add_sub_info_email', 15, 3 );

		// Add dropdown to admin orders screen to filter on order type
		add_action( 'restrict_manage_posts', __CLASS__ . '::restrict_manage_subscriptions', 50 );

		// For HPOS - Add dropdown to admin orders screen to filter on order type.
		add_action( 'woocommerce_order_list_table_restrict_manage_orders', __CLASS__ . '::restrict_manage_subscriptions_hpos' );

		// Add filter to queries on admin orders screen to filter on order type. To avoid WC overriding our query args, we need to hook on after them on 10.
		add_filter( 'request', __CLASS__ . '::orders_by_type_query', 11 );
		// HPOS - Add filter to queries on admin orders screen to filter on order type. Only triggered for the shop_order order type.
		add_filter( 'woocommerce_shop_order_list_table_prepare_items_query_args', __CLASS__ . '::maybe_modify_orders_by_type_query_from_request', 11 );

		// Don't display migrated order item meta on the Edit Order screen
		add_filter( 'woocommerce_hidden_order_itemmeta', __CLASS__ . '::hide_order_itemmeta' );

		add_action( 'woocommerce_order_details_after_order_table', __CLASS__ . '::add_subscriptions_to_view_order_templates', 10, 1 );

		add_action( 'woocommerce_subscription_details_after_subscription_table', __CLASS__ . '::get_related_orders_template' );
		add_action( 'woocommerce_subscription_details_after_subscription_related_orders_table', __CLASS__ . '::get_related_orders_pagination_template', 5, 4 );

		add_filter( 'woocommerce_my_account_my_orders_actions', __CLASS__ . '::maybe_remove_pay_action', 10, 2 );

		add_action( 'woocommerce_order_fully_refunded', __CLASS__ . '::maybe_cancel_subscription_on_full_refund' );

		add_filter( 'woocommerce_order_needs_shipping_address', __CLASS__ . '::maybe_display_shipping_address', 10, 3 );

		// Autocomplete subscription orders when they only contain a synchronised subscription or a resubscribe
		add_filter( 'woocommerce_payment_complete_order_status', __CLASS__ . '::maybe_autocomplete_order', 10, 3 );

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( __CLASS__, 'add_subscription_order_query_args' ), 10, 2 );

		add_filter( 'woocommerce_order_query_args', array( __CLASS__, 'map_order_query_args_for_subscriptions' ) );

		add_filter( 'woocommerce_orders_table_query_clauses', [ __CLASS__, 'filter_orders_query_by_parent_orders' ], 10, 2 );

		add_action( 'woocommerce_before_delete_order', [ __CLASS__, 'delete_order_update_order_related_subscriptions_last_order_date_created' ], 10, 2 );

		$cache_manager = new WCS_Object_Data_Cache_Manager(
			'subscription',
			[
				'parent_id',
			]
		);
		$cache_manager->init();

		add_action( 'wcs_update_post_meta_caches', [ __CLASS__, 'update_subscription_last_order_date_parent_id_changes' ], 10, 5 );

		add_action( 'wcs_orders_add_relation', [ __CLASS__, 'add_relation_update_order_related_subscriptions_last_order_date_created' ], 10, 3 );

		add_action( 'wcs_orders_delete_relation', [ __CLASS__, 'delete_relation_update_order_related_subscriptions_last_order_date_created' ], 10, 3 );
	}

	/*
	 * Helper functions for extracting the details of subscriptions in an order
	 */

	/**
	 * Returns the total amount to be charged for non-subscription products at the outset of a subscription.
	 *
	 * This may return 0 if there no non-subscription products in the cart, or otherwise it will be the sum of the
	 * line totals for each non-subscription product.
	 *
	 * @param WC_Order|int $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5.3
	 */
	public static function get_non_subscription_total( $order ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		$non_subscription_total = 0;

		foreach ( $order->get_items() as $order_item ) {
			if ( ! self::is_item_subscription( $order, $order_item ) ) {
				$non_subscription_total += $order_item['line_total'];
			}
		}

		return apply_filters( 'woocommerce_subscriptions_order_non_subscription_total', $non_subscription_total, $order );
	}

	/**
	 * Returns the total sign-up fee for all subscriptions in an order.
	 *
	 * Similar to WC_Subscription::get_sign_up_fee() except that it sums the sign-up fees for all subscriptions purchased in an order.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return float The initial sign-up fee charged when the subscription product in the order was first purchased, if any.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_sign_up_fee( $order, $product_id = 0 ) {

		$sign_up_fee = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			if ( empty( $product_id ) ) {

				$sign_up_fee += $subscription->get_sign_up_fee();

			} else {

				// We only want sign-up fees for certain product
				$order_item = self::get_item_by_product_id( $order, $product_id );

				foreach ( $subscription->get_items() as $line_item ) {
					if ( $line_item['product_id'] == $product_id || $line_item['variation_id'] == $product_id ) {
						$sign_up_fee += $subscription->get_items_sign_up_fee( $line_item );
					}
				}
			}
		}

		return apply_filters( 'woocommerce_subscriptions_sign_up_fee', $sign_up_fee, $order, $product_id );
	}

	/**
	 * Gets the product ID for an order item in a way that is backwards compatible with WC 1.x.
	 *
	 * Version 2.0 of WooCommerce changed the ID of an order item from its product ID to a unique ID for that particular item.
	 * This function checks if the 'product_id' field exists on an order item before falling back to 'id'.
	 *
	 * @param array $order_item An order item in the structure returned by WC_Order::get_items()
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 */
	public static function get_items_product_id( $order_item ) {
		return ( isset( $order_item['product_id'] ) ) ? $order_item['product_id'] : $order_item['id'];
	}

	/**
	 * Gets an item by product id from an order.
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of the order for which the meta should be sought.
	 * @param int $product_id The product/post ID of a subscription product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 */
	public static function get_item_by_product_id( $order, $product_id = 0 ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		foreach ( $order->get_items() as $item ) {
			if ( ( self::get_items_product_id( $item ) == $product_id || empty( $product_id ) ) && self::is_item_subscription( $order, $item ) ) {
				return $item;
			}
		}

		return array();
	}

	/**
	 * Gets an item by a subscription key of the form created by @see WC_Subscriptions_Manager::get_subscription_key().
	 *
	 * @param string $subscription_key The subscription key.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 */
	public static function get_item_by_subscription_key( $subscription_key ) {

		$item_id = self::get_item_id_by_subscription_key( $subscription_key );

		$item = self::get_item_by_id( $item_id );

		return $item;
	}

	/**
	 * Gets the ID of a subscription item which belongs to a subscription key of the form created
	 * by @see WC_Subscriptions_Manager::get_subscription_key().
	 *
	 * @param string $subscription_key The subscription key.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 */
	public static function get_item_id_by_subscription_key( $subscription_key ) {
		global $wpdb;

		$order_and_product_ids = explode( '_', $subscription_key );

		$item_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT `{$wpdb->prefix}woocommerce_order_items`.order_item_id FROM `{$wpdb->prefix}woocommerce_order_items`
				INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` on `{$wpdb->prefix}woocommerce_order_items`.order_item_id = `{$wpdb->prefix}woocommerce_order_itemmeta`.order_item_id
				AND `{$wpdb->prefix}woocommerce_order_itemmeta`.meta_key = '_product_id'
				AND `{$wpdb->prefix}woocommerce_order_itemmeta`.meta_value = %d
			WHERE `{$wpdb->prefix}woocommerce_order_items`.order_id = %d",
			$order_and_product_ids[1],
			$order_and_product_ids[0]
		) );

		return $item_id;
	}

	/**
	 * Gets an individual order item by ID without requiring the order ID associated with it.
	 *
	 * @param int $order_item_id The product/post ID of a subscription. Option - if no product id is provided, the first item's meta will be returned
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 */
	public static function get_item_by_id( $order_item_id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare( "
			SELECT order_item_id, order_item_name, order_item_type, order_id
			FROM   {$wpdb->prefix}woocommerce_order_items
			WHERE  order_item_id = %d
		", $order_item_id ), ARRAY_A );

		$order = wc_get_order( absint( $item['order_id'] ) );

		$item['name']      = $item['order_item_name'];
		$item['type']      = $item['order_item_type'];
		$item['item_meta'] = wc_get_order_item_meta( $item['order_item_id'], '' );

		// Put meta into item array
		if ( is_array( $item['item_meta'] ) ) {
			foreach ( $item['item_meta'] as $meta_name => $meta_value ) {
				$key = substr( $meta_name, 0, 1 ) == '_' ? substr( $meta_name, 1 ) : $meta_name;
				$item[ $key ] = maybe_unserialize( $meta_value[0] );
			}
		}

		return $item;
	}

	/**
	 * A unified API for accessing product specific meta on an order.
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of the order for which the meta should be sought.
	 * @param string $meta_key The key as stored in the post meta table for the meta item.
	 * @param int $product_id The product/post ID of a subscription. Option - if no product id is provided, we will loop through the order and find the subscription
	 * @param mixed $default (optional) The default value to return if the meta key does not exist. Default 0.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_item_meta( $order, $meta_key, $product_id = 0, $default = 0 ) {

		$meta_value = $default;

		if ( '' == $product_id ) {
			$items = self::get_recurring_items( $order );
			foreach ( $items as $item ) {
				$product_id = $item['product_id'];
				break;
			}
		}

		$item = self::get_item_by_product_id( $order, $product_id );

		if ( ! empty( $item ) && isset( $item['item_meta'][ $meta_key ] ) ) {
			$meta_value = $item['item_meta'][ $meta_key ][0];
		}

		return apply_filters( 'woocommerce_subscriptions_item_meta', $meta_value, $meta_key, $order, $product_id );
	}

	/**
	 * Access an individual piece of item metadata (@see woocommerce_get_order_item_meta returns all metadata for an item)
	 *
	 * You may think it would make sense if this function was called "get_item_meta", and you would be correct, but a function
	 * with that name was created before the item meta data API of WC 2.0, so it needs to persist with it's own different
	 * set of parameters.
	 *
	 * @param int $meta_id The order item meta data ID of the item you want to get.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 */
	public static function get_item_meta_data( $meta_id ) {
		global $wpdb;

		$item_meta = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM   {$wpdb->prefix}woocommerce_order_itemmeta
			WHERE  meta_id = %d
		", $meta_id ) );

		return $item_meta;
	}

	/**
	 * Gets the name of a subscription item by product ID from an order.
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of the order for which the meta should be sought.
	 * @param int $product_id The product/post ID of a subscription. Option - if no product id is provided, it is expected that only one item exists and the last item's meta will be returned
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_item_name( $order, $product_id = 0 ) {

		$item = self::get_item_by_product_id( $order, $product_id );

		if ( isset( $item['name'] ) ) {
			return $item['name'];
		} else {
			return '';
		}
	}

	/**
	 * Displays a few details about what happens to their subscription. Hooked
	 * to the thank you page.
	 *
	 * @param int $order_id
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function subscription_thank_you( $order_id ) {
		if ( wcs_order_contains_subscription( $order_id, 'any' ) ) {
			$subscriptions                = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) );
			$subscription_count           = count( $subscriptions );
			$thank_you_message            = '';
			$my_account_subscriptions_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );

			if ( $subscription_count ) {
				foreach ( $subscriptions as $subscription ) {
					if ( ! $subscription->has_status( 'active' ) ) {
						$thank_you_message = '<p>' . _n( 'Your subscription will be activated when payment clears.', 'Your subscriptions will be activated when payment clears.', $subscription_count, 'woocommerce-subscriptions' ) . '</p>';
						break;
					}
				}
			}

			// translators: placeholders are opening and closing link tags
			$thank_you_message .= '<p>' . sprintf( _n( 'View the status of your subscription in %1$syour account%2$s.', 'View the status of your subscriptions in %1$syour account%2$s.', $subscription_count, 'woocommerce-subscriptions' ), '<a href="' . $my_account_subscriptions_url . '">', '</a>' ) . '</p>';
			echo wp_kses(
				apply_filters(
					'woocommerce_subscriptions_thank_you_message',
					$thank_you_message,
					$order_id
				),
				array(
					'a'      => array(
						'href'  => array(),
						'title' => array(),
					),
					'p'      => array(),
					'em'     => array(),
					'strong' => array(),
				)
			);
		}

	}

	/**
	 * Output a hidden element in the order status of the orders list table to provide information about whether
	 * the order displayed in that row contains a subscription or not.
	 *
	 * It would be more semantically correct to display a hidden input element than a span element with data, but
	 * that can result in "requested URL's length exceeds the capacity limit" errors when bulk editing orders.
	 *
	 * @param string $column The string of the current column.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1
	 */
	public static function add_contains_subscription_hidden_field( $column ) {
		global $post;

		if ( 'order_status' == $column ) {
			$contains_subscription = wcs_order_contains_subscription( $post->ID, 'parent' ) ? 'true' : 'false';
			printf( '<span class="contains_subscription" data-contains_subscription="%s" style="display: none;"></span>', esc_attr( $contains_subscription ) );
		}
	}

	/**
	 * Output a hidden element on the Edit Order screen to provide information about whether the order displayed
	 * in that row contains a subscription or not.
	 *
	 * @param string $order_id The ID of the order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1
	 */
	public static function contains_subscription_hidden_field( $order_id ) {

		$has_subscription = wcs_order_contains_subscription( $order_id, 'parent' ) ? 'true' : 'false';

		echo '<input type="hidden" name="contains_subscription" value="' . esc_attr( $has_subscription ) . '">';
	}

	/**
	* Add a column to the WooCommerce -> Orders admin screen to indicate whether an order is a
	* parent of a subscription, a renewal order for a subscription, or a regular order.
	*
	* @param array $columns The current list of columns
	* @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.1
	*/
	public static function add_contains_subscription_column( $columns ) {

		$column_header = '<span class="subscription_head tips" data-tip="' . esc_attr__( 'Subscription Relationship', 'woocommerce-subscriptions' ) . '">' . esc_attr__( 'Subscription Relationship', 'woocommerce-subscriptions' ) . '</span>';

		$new_columns = wcs_array_insert_after( 'shipping_address', $columns, 'subscription_relationship', $column_header );

		return $new_columns;
	}

	/**
	 * Add column content to the WooCommerce -> Orders admin screen to indicate whether an
	 * order is a parent of a subscription, a renewal order for a subscription, or a regular order.
	 *
	 * @see add_contains_subscription_column_content_orders_table For when HPOS is enabled.
	 *
	 * @param string $column The string of the current column
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.1
	*/
	public static function add_contains_subscription_column_content( $column ) {
		global $post;

		if ( 'subscription_relationship' === $column ) {
			self::render_contains_subscription_column_content( $post->ID );
		}
	}

	/**
	 * Add column content to the WooCommerce -> Orders admin screen to indicate whether an
	 * order is a parent of a subscription, a renewal order for a subscription, or a regular order.
	 *
	 * @see add_contains_subscription_column_content For when HPOS is disabled.
	 *
	 * @since 6.3.0
	 *
	 * @param string   $column_name Identifier for the custom column.
	 * @param WC_Order $order       Current WooCommerce order object.
	 */
	public static function add_contains_subscription_column_content_orders_table( string $column_name, WC_Order $order ) {
		if ( 'subscription_relationship' === $column_name ) {
			self::render_contains_subscription_column_content( $order );
		}
	}

	/**
	 * Records the initial payment against a subscription.
	 *
	 * This function is called when an order's status is changed to completed or processing
	 * for those gateways which never call @see WC_Order::payment_complete(), like the core
	 * WooCommerce Cheque and Bank Transfer gateways.
	 *
	 * It will also set the start date on the subscription to the time the payment is completed.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 *
	 * @param int|WC_Order $order_id         The order ID or WC_Order object.
	 * @param string       $old_order_status The old order status.
	 * @param string       $new_order_status The new order status.
	 */
	public static function maybe_record_subscription_payment( $order_id, $old_order_status, $new_order_status ) {

		if ( ! wcs_order_contains_subscription( $order_id, 'parent' ) ) {
			return;
		}

		$subscriptions   = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'parent' ) );
		$was_activated   = false;
		$order           = wc_get_order( $order_id );
		$paid_statuses   = array( apply_filters( 'woocommerce_payment_complete_order_status', 'processing', $order_id, $order ), 'processing', 'completed' );
		$unpaid_statuses = apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'on-hold', 'failed' ), $order );
		$order_completed = in_array( $new_order_status, $paid_statuses, true ) && in_array( $old_order_status, $unpaid_statuses, true );

		/**
		 * Filter whether the subscription order is considered completed.
		 *
		 * Allow third party extensions to modify whether the order is considered
		 * completed and the subscription should activate. This allows for different
		 * treatment of orders and subscriptions during the completion flow.
		 *
		 * @since 6.9.0
		 *
		 * @param bool              $order_completed  Whether the order is considered completed.
		 * @param string            $new_order_status The new order status.
		 * @param string            $old_order_status The old order status.
		 * @param WC_Subscription[] $subscriptions    The subscriptions in the order.
		 * @param WC_Order          $order            The order object.
		 */
		$order_completed = apply_filters( 'wcs_is_subscription_order_completed', $order_completed, $new_order_status, $old_order_status, $subscriptions, $order );

		foreach ( $subscriptions as $subscription ) {
			// A special case where payment completes after user cancels subscription
			if ( $order_completed && $subscription->has_status( 'cancelled' ) ) {

				// Store the actual cancelled_date so as to restore it after it is rewritten by update_status()
				$cancelled_date = $subscription->get_date( 'cancelled' );

				// Force set cancelled_date and end date to 0 temporarily so that next_payment_date can be calculated properly
				// This next_payment_date will be the end of prepaid term that will be picked by action scheduler
				$subscription->update_dates( array( 'cancelled' => 0, 'end' => 0 ) );

				$next_payment_date = $subscription->calculate_date( 'next_payment' );
				$subscription->update_dates( array( 'next_payment' => $next_payment_date ) );

				$subscription->update_status( 'pending-cancel', __( 'Payment completed on order after subscription was cancelled.', 'woocommerce-subscriptions' ) );

				// Restore the actual cancelled date
				$subscription->update_dates( array( 'cancelled' => $cancelled_date ) );
			}

			// Do we need to activate a subscription?
			if ( $order_completed && ! $subscription->has_status( wcs_get_subscription_ended_statuses() ) && ! $subscription->has_status( 'active' ) ) {

				$new_start_date_offset = current_time( 'timestamp', true ) - $subscription->get_time( 'start' );

				// if the payment has been processed more than an hour after the order was first created, let's update the dates on the subscription to account for that, because it may have even been processed days after it was first placed
				if ( abs( $new_start_date_offset ) > HOUR_IN_SECONDS ) {

					$dates = array( 'start' => current_time( 'mysql', true ) );

					if ( WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription ) ) {

						$trial_end    = $subscription->get_time( 'trial_end' );
						$next_payment = $subscription->get_time( 'next_payment' );

						// if either there is a free trial date or a next payment date that falls before now, we need to recalculate all the sync'd dates
						if ( ( $trial_end > 0 && $trial_end < wcs_date_to_time( $dates['start'] ) ) || ( $next_payment > 0 && $next_payment < wcs_date_to_time( $dates['start'] ) ) ) {

							foreach ( $subscription->get_items() as $item ) {
								$product_id = wcs_get_canonical_product_id( $item );

								if ( WC_Subscriptions_Synchroniser::is_product_synced( $product_id ) ) {
									$dates['trial_end']    = WC_Subscriptions_Product::get_trial_expiration_date( $product_id, $dates['start'] );
									$dates['next_payment'] = WC_Subscriptions_Synchroniser::calculate_first_payment_date( $product_id, 'mysql', $dates['start'] );
									$dates['end']          = WC_Subscriptions_Product::get_expiration_date( $product_id, $dates['start'] );
									break;
								}
							}
						}
					} else {
						// No sync'ing to mess about with, just add the offset to the existing dates
						foreach ( array( 'trial_end', 'next_payment', 'end' ) as $date_type ) {
							if ( 0 != $subscription->get_time( $date_type ) ) {
								$dates[ $date_type ] = gmdate( 'Y-m-d H:i:s', $subscription->get_time( $date_type ) + $new_start_date_offset );
							}
						}
					}

					$subscription->update_dates( $dates );
				}

				$subscription->payment_complete_for_order( $order );
				$was_activated = true;

			} elseif ( 'failed' === $new_order_status ) {
				// When parent order fails, we want to keep the subscription status as pending unless it had another status before.
				$new_status = $subscription->has_status( 'pending' ) && $subscription->get_parent_id() === $order->get_id() ? 'pending' : 'on-hold';
				$subscription->payment_failed( $new_status );
			}
		}

		if ( $was_activated ) {
			do_action( 'subscriptions_activated_for_order', $order_id );
		}
	}

	/* Order Price Getters */

	/**
	 * Checks if a given order item matches a line item from a subscription purchased in the order.
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param array $order_item An array representing an order item or a product ID of an item in an order (not an order item ID)
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function is_item_subscription( $order, $order_item ) {

		if ( ! is_array( $order_item ) ) {
			$order_item = self::get_item_by_product_id( $order, $order_item );
		}

		$order_items_product_id = wcs_get_canonical_product_id( $order_item );
		$item_is_subscription   = false;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
			foreach ( $subscription->get_items() as $line_item ) {
				if ( wcs_get_canonical_product_id( $line_item ) == $order_items_product_id ) {
					$item_is_subscription = true;
					break 2;
				}
			}
		}

		return $item_is_subscription;
	}


	/* Edit Order Page Content */

	/**
	 * Returns all parent subscription orders for a user, specified with $user_id
	 *
	 * @return array An array of order IDs.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 */
	public static function get_users_subscription_orders( $user_id = 0 ) {
		global $wpdb;

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get all the customers orders which are not subscription renewal orders
		$custom_query_var_handler = function( $query, $query_vars ) {
			if ( ! empty( $query_vars['_non_subscription_renewal'] ) ) {
				$query['meta_query'][] = array(
					'key'     => '_subscription_renewal',
					'compare' => 'NOT EXISTS',
				);
				unset( $query_vars['_non_subscription_renewal'] );
			}

			return $query;
		};
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', $custom_query_var_handler, 10, 2 );

		$all_possible_statuses = array_values( array_unique( array_keys( wc_get_order_statuses() ) ) );

		$args = array(
			'type'        => 'shop_order',
			'status'      => $all_possible_statuses,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'customer_id' => $user_id,
			'return'      => 'ids',
		);

		$args['_non_subscription_renewal'] = true;

		$order_ids = wc_get_orders( $args );

		remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', $custom_query_var_handler, 10 );

		foreach ( $order_ids as $index => $order_id ) {
			if ( ! wcs_order_contains_subscription( $order_id, 'parent' ) ) {
				unset( $order_ids[ $index ] );
			}
		}

		// Normalise array keys
		$order_ids = array_values( $order_ids );

		return apply_filters( 'users_subscription_orders', $order_ids, $user_id );
	}

	/**
	 * Check whether an order needs payment even if the order total is $0 (because it has a recurring total and
	 * automatic payments are not switched off)
	 *
	 * @param bool $needs_payment The existing flag for whether the cart needs payment or not.
	 * @param WC_Order $order A WooCommerce WC_Order object.
	 * @return bool
	 */
	public static function order_needs_payment( $needs_payment, $order, $valid_order_statuses ) {
		// Skips checks if the order already needs payment.
		if ( $needs_payment ) {
			return $needs_payment;
		}

		// Skip checks if order doesn't contain a subscription product.
		if ( ! wcs_order_contains_subscription( $order ) ) {
			return $needs_payment;
		}

		// Skip checks if order total is greater than zero, or
		// order status isn't valid for payment.
		if ( $order->get_total() > 0 || ! $order->has_status( $valid_order_statuses ) ) {
			return $needs_payment;
		}

		// Skip checks if manual renewal is required.
		if ( wcs_is_manual_renewal_required() ) {
			return $needs_payment;
		}

		// Check if there's a subscription attached to this order that will require a payment method.
		foreach ( wcs_get_subscriptions_for_order( $order, [ 'order_type' => 'parent' ] ) as $subscription ) {
			$has_next_payment                 = false;
			$contains_expiring_limited_coupon = false;
			$contains_free_trial              = false;
			$contains_synced                  = false;

			// Check if there's a subscription with a recurring total that would require a payment method.
			$recurring_total = (float) $subscription->get_total();

			// Check that there is at least 1 subscription with a next payment that would require a payment method.
			if ( $subscription->get_time( 'next_payment' ) ) {
				$has_next_payment = true;
			}

			// Check if there's a subscription with a limited recurring coupon that is expiring that would require a payment method after the coupon expires.
			if ( class_exists( 'WCS_Limited_Recurring_Coupon_Manager' ) && WCS_Limited_Recurring_Coupon_Manager::order_has_limited_recurring_coupon( $subscription ) ) {
				$contains_expiring_limited_coupon = true;
			}

			// Check if there's a subscription with a free trial that would require a payment method after the trial ends.
			if ( $subscription->get_time( 'trial_end' ) ) {
				$contains_free_trial = true;
			}

			// Check if there's a subscription with a synced product that would require a payment method.
			if ( WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription ) ) {
				$contains_synced = true;
			}

			/**
			 * We need to collect a payment method if there's a subscription with a recurring total or a limited recurring coupon that is expiring and
			 * there's a next payment date or a free trial or a synced product.
			 */
			if ( ( $contains_expiring_limited_coupon || $recurring_total > 0 ) && ( $has_next_payment || $contains_free_trial || $contains_synced ) ) {
				$needs_payment = true;
				break; // We've found a subscription that requires a payment method.
			}
		}

		return $needs_payment;
	}

	/**
	 * Adds the subscription information to our order emails.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5
	 */
	public static function add_sub_info_email( $order, $is_admin_email, $plaintext = false, $skip_my_account_link = false ) {

		$subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );

		if ( ! empty( $subscriptions ) ) {

			$template_base  = WC_Subscriptions_Plugin::instance()->get_plugin_directory( 'templates/' );
			$template       = ( $plaintext ) ? 'emails/plain/subscription-info.php' : 'emails/subscription-info.php';

			wc_get_template(
				$template,
				array(
					'order'                => $order,
					'subscriptions'        => $subscriptions,
					'is_admin_email'       => $is_admin_email,
					'plain_text'           => $plaintext,
					'skip_my_account_link' => $skip_my_account_link,
				),
				'',
				$template_base
			);
		}
	}

	/**
	 * Add admin dropdown for order types to Woocommerce -> Orders screen
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5
	 */
	public static function restrict_manage_subscriptions() {
		global $typenow;

		if ( 'shop_order' !== $typenow ) {
			return;
		}

		self::render_restrict_manage_subscriptions_dropdown();
	}

	/**
	 * When HPOS is active, adds admin dropdown for order types to Woocommerce -> Orders screen
	 *
	 * @since 6.3.0
	 *
	 * @param string $order_type The order type.
	 */
	public static function restrict_manage_subscriptions_hpos( string $order_type ) {
		if ( 'shop_order' !== $order_type ) {
			return;
		}

		self::render_restrict_manage_subscriptions_dropdown();
	}

	/**
	 * Add request filter for order types to Woocommerce -> Orders screen
	 *
	 * Including or excluding posts with a '_subscription_renewal' meta value includes or excludes
	 * renewal orders, as required.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5
	 */
	public static function orders_by_type_query( $vars ) {
		global $typenow;

		if ( 'shop_order' === $typenow ) {
			return self::maybe_modify_orders_by_type_query_from_request( $vars );
		}

		return $vars;
	}

	/**
	 * Filters the arguments to be passed to `wc_get_orders()` under the Woocommerce -> Orders screen.
	 *
	 * @since 6.3.0
	 *
	 * @param array $order_query_args Arguments to be passed to `wc_get_orders()`.
	 *
	 * @return array
	 */
	public static function maybe_modify_orders_by_type_query_from_request( array $order_query_args ): array {
		// The order subtype selected by the user in the dropdown.
		$selected_shop_order_subtype = isset( $_GET['shop_order_subtype'] ) ? wc_clean( wp_unslash( $_GET['shop_order_subtype'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Don't modify the query args if no order subtype was selected.
		if ( empty( $selected_shop_order_subtype ) ) {
			return $order_query_args;
		}

		if ( 'original' === $selected_shop_order_subtype || 'regular' === $selected_shop_order_subtype ) {

			$order_query_args['meta_query']['relation'] = 'AND';

			$order_query_args['meta_query'][] = array(
				'key'     => '_subscription_renewal',
				'compare' => 'NOT EXISTS',
			);

			$order_query_args['meta_query'][] = array(
				'key'     => '_subscription_switch',
				'compare' => 'NOT EXISTS',
			);

		} elseif ( 'parent' === $selected_shop_order_subtype ) {
			if ( wcs_is_custom_order_tables_usage_enabled() ) {
				$order_query_args['subscription_parent'] = true;
			} else {
				$order_query_args['post__in'] = wcs_get_subscription_orders();
			}
		} else {

			switch ( $selected_shop_order_subtype ) {
				case 'renewal':
					$meta_key = '_subscription_renewal';
					break;
				case 'resubscribe':
					$meta_key = '_subscription_resubscribe';
					break;
				case 'switch':
					$meta_key = '_subscription_switch';
					break;
				default:
					$meta_key = '';
					break;
			}

			$meta_key = apply_filters( 'woocommerce_subscriptions_admin_order_type_filter_meta_key', $meta_key, $selected_shop_order_subtype );

			if ( ! empty( $meta_key ) ) {
				$order_query_args['meta_query'][] = array(
					'key'     => $meta_key,
					'compare' => 'EXISTS',
				);
			}
		}

		// Also exclude parent orders from non-subscription query
		if ( 'regular' === $selected_shop_order_subtype ) {
			if ( wcs_is_custom_order_tables_usage_enabled() ) {
				$order_query_args['subscription_parent'] = false;
			} else {
				$order_query_args['post__not_in'] = wcs_get_subscription_orders();
			}
		}

		return $order_query_args;
	}

	/**
	 * Add related subscriptions below order details tables.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	public static function add_subscriptions_to_view_order_templates( $order_id ) {

		$template      = 'myaccount/related-subscriptions.php';
		$subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) );

		if ( ! empty( $subscriptions ) ) {
			wc_get_template(
				$template,
				array(
					'order_id'      => $order_id,
					'subscriptions' => $subscriptions,
				),
				'',
				WC_Subscriptions_Plugin::instance()->get_plugin_directory( 'templates/' )
			);
		}
	}

	/**
	 * Loads the related orders table on the view subscription page
	 *
	 * @since 1.0.0 Migrated from WooCommerce Subscriptions v2.0.
	 *
	 * @param WC_Subscription $subscription The subscription whose related orders we are interested in.
	 */
	public static function get_related_orders_template( $subscription ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$page = (int) ( $_GET['related_orders_page'] ?? 1 );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$related_orders      = $subscription->get_paginated_related_orders( 'ids', array( 'parent', 'renewal', 'switch' ), $page );
		$subscription_orders = $related_orders->orders;

		if ( 0 !== count( $subscription_orders ) ) {
			wc_get_template(
				'myaccount/related-orders.php',
				array(
					'subscription_orders' => $subscription_orders,
					'subscription'        => $subscription,
					'page'                => $page,
					'max_num_pages'       => $related_orders->max_num_pages,
				),
				'',
				WC_Subscriptions_Plugin::instance()->get_plugin_directory( 'templates/' )
			);
		}
	}


	/**
	 * Introduced to support pagination of the related orders list (within the My Account > View Subscription
	 * screen).
	 *
	 * @since 7.5.0 Updated to support pagination of the related orders list.
	 *
	 * @param WC_Subscription $subscription        The subscription whose related orders we are interested in.
	 * @param int[]|null      $subscription_orders IDs of the related orders.
	 * @param int|null        $page                The current page number.
	 * @param int|null        $max_num_pages       The maximum number of pages in the set.
	 *
	 * @return void
	 */
	public static function get_related_orders_pagination_template( WC_Subscription $subscription, ?array $subscription_orders = null, ?int $page = null, ?int $max_num_pages = null ) {
		if ( null === $page || null === $max_num_pages ) {
			return;
		}

		wc_get_template(
			'myaccount/related-orders-pagination.php',
			array(
				'page'          => $page,
				'max_num_pages' => $max_num_pages,
			),
			'',
			WC_Subscriptions_Plugin::instance()->get_plugin_directory( 'templates/' )
		);
	}

	/**
	* Unset pay action for an order if a more recent order exists
	*
	* @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.2.9
	*/
	public static function maybe_remove_pay_action( $actions, $order ) {

		if ( isset( $actions['pay'] ) && wcs_order_contains_subscription( $order, array( 'any' ) ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( wcs_get_objects_property( $order, 'id' ), array( 'order_type' => 'any' ) );

			foreach ( $subscriptions as $subscription ) {
				if ( wcs_get_objects_property( $order, 'id' ) != $subscription->get_last_order( 'ids', 'any' ) ) {
					unset( $actions['pay'] );
					break;
				}
			}
		}

		return $actions;
	}

	/**
	 * Allow subscription order items to be edited in WC 2.2. until Subscriptions 2.0 introduces
	 * its own WC_Subscription object.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5.10
	 */
	public static function is_order_editable( $is_editable, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::is_editable()' );
		return $is_editable;
	}

	/**
	 * Get a subscription that has an item with the same product/variation ID as an order item, if any.
	 *
	 * In Subscriptions v1.n, a subscription's meta data, like recurring total, billing period etc. were stored
	 * against the line item on the original order for that subscription.
	 *
	 * In v2.0, this data was moved to a distinct subscription object which had its own line items for those amounts.
	 * This function bridges the two data structures to support deprecated functions used to retrieve a subscription's
	 * meta data from the original order rather than the subscription itself.
	 *
	 * @param WC_Order $order A WC_Order object
	 * @param int $product_id The product/post ID of a subscription
	 * @return null|object A subscription from the order, either with an item to the product ID (if any) or just the first subscription purchase in the order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	private static function get_matching_subscription( $order, $product_id = 0 ) {

		$subscriptions         = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) );
		$matching_subscription = null;

		if ( ! empty( $product_id ) ) {
			foreach ( $subscriptions as $subscription ) {
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$matching_subscription = $subscription;
						break 2;
					}
				}
			}
		}

		if ( null === $matching_subscription && ! empty( $subscriptions ) ) {
			$matching_subscription = array_pop( $subscriptions );
		}

		return $matching_subscription;
	}

	/**
	 * Get the subscription item that has the same product/variation ID as an order item, if any.
	 *
	 * In Subscriptions v1.n, a subscription's meta data, like recurring total, billing period etc. were stored
	 * against the line item on the original order for that subscription.
	 *
	 * In v2.0, this data was moved to a distinct subscription object which had its own line items for those amounts.
	 * This function bridges the two data structures to support deprecated functions used to retrieve a subscription's
	 * meta data from the original order rather than the subscription itself.
	 *
	 * @param WC_Order $order A WC_Order object
	 * @param int $product_id The product/post ID of a subscription
	 * @return array The line item for this product on the subscription object
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	private static function get_matching_subscription_item( $order, $product_id = 0 ) {

		$matching_item = array();
		$subscription  = self::get_matching_subscription( $order, $product_id );

		foreach ( $subscription->get_items() as $line_item ) {
			if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
				$matching_item = $line_item;
				break;
			}
		}

		return $matching_item;
	}

	/**
	 * Don't display migrated subscription meta data on the Edit Order screen
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 */
	public static function hide_order_itemmeta( $hidden_meta_keys ) {

		if ( ! defined( 'WCS_DEBUG' ) || true !== WCS_DEBUG ) {

			$hidden_meta_keys[] = '_has_trial';

			$old_recurring_meta_keys = array(
				'_line_total',
				'_line_tax',
				'_line_subtotal',
				'_line_subtotal_tax',
			);

			foreach ( $old_recurring_meta_keys as $index => $meta_key ) {
				$old_recurring_meta_keys[ $index ] = sprintf( '_wcs_migrated_recurring%s', $meta_key );
			}

			$hidden_meta_keys = array_merge( $hidden_meta_keys, $old_recurring_meta_keys );

			$old_subscription_meta_keys = array(
				'_period',
				'_interval',
				'_trial_length',
				'_trial_period',
				'_length',
				'_sign_up_fee',
				'_failed_payments',
				'_recurring_amount',
				'_start_date',
				'_trial_expiry_date',
				'_expiry_date',
				'_end_date',
				'_status',
				'_completed_payments',
				'_suspension_count',
			);

			foreach ( $old_subscription_meta_keys as $index => $meta_key ) {
				$old_subscription_meta_keys[ $index ] = sprintf( '_wcs_migrated_subscription%s', $meta_key );
			}

			$hidden_meta_keys = array_merge( $hidden_meta_keys, $old_subscription_meta_keys );
		}

		return $hidden_meta_keys;
	}

	/**
	 * If the subscription is pending cancellation and a latest order is refunded, cancel the subscription.
	 *
	 * @param WC_Order $order The order object.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	public static function maybe_cancel_subscription_on_full_refund( $order ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( wcs_order_contains_subscription( $order, array( 'parent', 'renewal' ) ) ) {

			$subscriptions = wcs_get_subscriptions_for_order( wcs_get_objects_property( $order, 'id' ), array( 'order_type' => array( 'parent', 'renewal' ) ) );

			foreach ( $subscriptions as $subscription ) {
				$latest_order = $subscription->get_last_order();

				if ( wcs_get_objects_property( $order, 'id' ) == $latest_order && $subscription->has_status( 'pending-cancel' ) && $subscription->can_be_updated_to( 'cancelled' ) ) {
					// translators: $1: opening link tag, $2: order number, $3: closing link tag
					$subscription->update_status( 'cancelled', wp_kses( sprintf( __( 'Subscription cancelled for refunded order %1$s#%2$s%3$s.', 'woocommerce-subscriptions' ), sprintf( '<a href="%s">', esc_url( wcs_get_edit_post_link( wcs_get_objects_property( $order, 'id' ) ) ) ), $order->get_order_number(), '</a>' ), array( 'a' => array( 'href' => true ) ) ) );
				}
			}
		}
	}

	/**
	 * Handles partial refunds on orders in WC versions pre 2.5 which would be considered full refunds in WC 2.5.
	 *
	 * @param $order_id
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 * @deprecated 1.0.0 - Migrated from WooCommerce Subscriptions v2.3.3
	 */
	public static function maybe_cancel_subscription_on_partial_refund( $order_id ) {
		wcs_deprecated_function( __METHOD__, '2.3.3' );
	}

	/**
	 * If the order doesn't contain shipping methods because it contains synced or trial products but the related subscription(s) does have a shipping method.
	 * This function will ensure the shipping address is still displayed in order emails and on the order received and view order pages.
	 *
	 * @param bool $needs_shipping
	 * @param array $hidden_shipping_methods shipping method IDs which should hide shipping addresses (defaulted to array( 'local_pickup' ))
	 * @param WC_Order $order
	 *
	 * @return bool $needs_shipping whether an order needs to display the shipping address
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0.14
	 */
	public static function maybe_display_shipping_address( $needs_shipping, $hidden_shipping_methods, $order ) {
		$order_shipping_methods = $order->get_shipping_methods();

		if ( ! $needs_shipping && wcs_order_contains_subscription( $order ) && empty( $order_shipping_methods ) ) {

			$subscriptions = wcs_get_subscriptions_for_order( $order );

			foreach ( $subscriptions as $subscription ) {
				foreach ( $subscription->get_shipping_methods() as $shipping_method ) {

					if ( ! in_array( $shipping_method['method_id'], $hidden_shipping_methods ) ) {
						$needs_shipping = true;
						break 2;
					}
				}
			}
		}

		return $needs_shipping;
	}

	/**
	 * Automatically set the order's status to complete if the order total is zero and all the subscriptions
	 * in an order are synced or the order contains a resubscribe.
	 *
	 * @param string   $new_order_status
	 * @param int      $order_id
	 * @param WC_Order $order
	 *
	 * @return string $new_order_status
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.1.3
	 */
	public static function maybe_autocomplete_order( $new_order_status, $order_id, $order = null ) {
		// Exit early if the order has no ID, or if the new order status is not 'processing'.
		if ( 0 === $order_id || 'processing' !== $new_order_status ) {
			return $new_order_status;
		}

		// Guard against infinite loops in WC 3.0+ where woocommerce_payment_complete_order_status is called while instantiating WC_Order objects
		if ( null === $order ) {
			remove_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10 );
			$order = wc_get_order( $order_id );
			add_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10, 3 );
		}

		// Exit early if the order subtotal is not zero, or if the order does not contain a subscription.
		if ( 0 != $order->get_subtotal() || ! wcs_order_contains_subscription( $order ) ) {
			return $new_order_status;
		}

		// Exit early if the order contains a non-subscription which needs processing product.
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			// We're only interested in non-subscription products.
			if ( WC_Subscriptions_Product::is_subscription( $item->get_product() ) ) {
				continue;
			}

			$virtual_downloadable_item = $product->is_downloadable() && $product->is_virtual();
			$needs_processing          = apply_filters( 'woocommerce_order_item_needs_processing', ! $virtual_downloadable_item, $product, $order_id );

			if ( $needs_processing ) {
				return $new_order_status;
			}
		}

		if ( wcs_order_contains_resubscribe( $order ) ) {
			$new_order_status = 'completed';
		} elseif ( wcs_order_contains_switch( $order ) ) {
			$all_switched = true;

			foreach ( $order->get_items() as $item ) {
				if ( ! isset( $item['switched_subscription_price_prorated'] ) ) {
					$all_switched = false;
					break;
				}
			}

			if ( $all_switched || 1 == count( $order->get_items() ) ) {
				$new_order_status = 'completed';
			}
		} else {
			$subscriptions = wcs_get_subscriptions_for_order( $order_id );
			$all_synced    = true;

			foreach ( $subscriptions as $subscription_id => $subscription ) {

				if ( ! WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription_id ) ) {
					$all_synced = false;
					break;
				}
			}

			if ( $all_synced ) {
				$new_order_status = 'completed';
			}
		}

		return $new_order_status;
	}

	/**
	 * Map subscription related order arguments passed to @see wc_get_orders() to WP_Query args.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.2.20
	 * @param  array $query WP_Query arguments.
	 * @param  array $args  @see wc_get_orders() arguments.
	 * @return array The WP_Query query arguments.
	 */
	public static function add_subscription_order_query_args( $query, $args ) {
		$order_type_meta_key_map = array(
			'subscription_renewal'     => '_subscription_renewal',
			'subscription_switch'      => '_subscription_switch',
			'subscription_resubscribe' => '_subscription_resubscribe',
		);

		// Add meta query args when querying by subscription related orders.
		foreach ( $order_type_meta_key_map as $order_type => $meta_key ) {
			if ( ! isset( $args[ $order_type ] ) ) {
				continue;
			}

			$value      = $args[ $order_type ];
			$meta_query = array(
				'key'   => $meta_key,
				'value' => $value,
			);

			// Map the value type to the appropriate compare arg.
			if ( empty( $value ) ) {
				$meta_query['compare'] = 'NOT EXISTS';
				unset( $meta_query['value'] );
			} elseif ( true === $value ) {
				$meta_query['compare'] = 'EXISTS';
				unset( $meta_query['value'] );
			} elseif ( is_array( $value ) ) {
				$meta_query['compare'] = 'IN';
			} else {
				$meta_query['compare'] = '=';
			}

			$query['meta_query'][] = $meta_query;
		}

		// Add query args when querying by subscription parent orders.
		if ( isset( $args['subscription_parent'] ) ) {
			$value = $args['subscription_parent'];

			// Map the value type to post_in/post__not_in arg
			if ( empty( $value ) ) {
				$query['post__not_in'] = array_values( wcs_get_subscription_orders() );
			} elseif ( true === $value ) {
				$query['post__in']     = array_values( wcs_get_subscription_orders() );
			} elseif ( is_array( $value ) ) {
				$query['post__in']     = array_keys( array_flip( array_filter( array_map( 'wp_get_post_parent_id', $value ) ) ) );
			} else {
				if ( $parent = wp_get_post_parent_id( $value ) ) {
					$query['post__in'] = array( $parent );
				}
			}
		}

		return $query;
	}

	/**
	 * Filter the query_vars of a wc_get_orders() query to map 'any' to be all valid subscription statuses instead of
	 * defaulting to only valid order statuses.
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public static function map_order_query_args_for_subscriptions( $query_vars ) {
		if ( ! wcs_is_custom_order_tables_usage_enabled() ) {
			return $query_vars;
		}

		/**
		 * Map the 'any' status to wcs_get_subscription_statuses() in HPOS environments.
		 *
		 * In HPOS environments, the 'any' status now maps to wc_get_order_statuses() statuses. Whereas, in
		 * WP Post architecture 'any' meant any status except for ‘inherit’, ‘trash’ and ‘auto-draft’.
		 *
		 * If we're querying for subscriptions, we need to map 'any' to be all valid subscription statuses otherwise it would just search for order statuses.
		 */
		if ( isset( $query_vars['post_type'] ) && '' !== $query_vars['post_type'] ) {
			// OrdersTableQuery::maybe_remap_args() will overwrite `type` with the `post_type` value.
			if ( 'shop_subscription' !== $query_vars['post_type'] ) {
				return $query_vars;
			}

			// Simplify the type logic.
			$query_vars['type'] = 'shop_subscription';
			unset( $query_vars['post_type'] );
		}

		if ( isset( $query_vars['type'] ) && 'shop_subscription' === $query_vars['type'] ) {
			if ( isset( $query_vars['post_status'] ) && '' !== $query_vars['post_status'] ) {
				// OrdersTableQuery::maybe_remap_args() will overwrite `status` with the `post_status` value.
				if ( [ 'any' ] !== (array) $query_vars['post_status'] ) {
					return $query_vars;
				}

				// Simplify the status logic.
				$query_vars['status'] = 'any';
				unset( $query_vars['post_status'] );
			}

			if ( [ 'any' ] === (array) $query_vars['status'] || [ '' ] === (array) $query_vars['status'] ) {
				$query_vars['status'] = array_keys( wcs_get_subscription_statuses() );
			}
		}

		return $query_vars;
	}

	/**
	 * Modifies the query clauses of a wc_get_orders() query to include/exclude parent orders based on the 'subscription_parent' argument.
	 *
	 * @param array            $query_clauses The query clauses.
	 * @param OrdersTableQuery $order_query   The order query object.
	 *
	 * @return array The modified query clauses to include/exclude parent orders.
	 */
	public static function filter_orders_query_by_parent_orders( $query_clauses, $order_query ) {
		$include_parent_orders = $order_query->get( 'subscription_parent' );

		// Bail if there's no argument to include/exclude parent orders.
		if ( is_null( $include_parent_orders ) ) {
			return $query_clauses;
		}

		if ( true === $include_parent_orders ) {
			// Limit query to parent orders.
			$query_clauses['join']  = ( empty( $query_clauses['join'] ) ? '' : $query_clauses['join'] . ' ' );
			$query_clauses['join'] .= "INNER JOIN {$order_query->get_table_name( 'orders' )} as subscriptions ON subscriptions.parent_order_id = {$order_query->get_table_name( 'orders' )}.id AND subscriptions.type = 'shop_subscription'";
		} elseif ( false === $include_parent_orders ) {
			// Exclude parent orders.
			$query_clauses['where']  = ( empty( $query_clauses['where'] ) ? '1=1 ' : $query_clauses['where'] . ' ' );
			$query_clauses['where'] .= "AND {$order_query->get_table_name( 'orders' )}.id NOT IN (SELECT parent_order_id FROM {$order_query->get_table_name( 'orders' )} WHERE type = 'shop_subscription')";
		}

		return $query_clauses;
	}

	/* Deprecated Functions */

	/**
	 * Checks an order to see if it contains a subscription.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @return bool True if the order contains a subscription, otherwise false.
	 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function order_contains_subscription( $order ) {
		_deprecated_function( __METHOD__, '2.0', 'wcs_order_contains_subscription( $order )' );
		return wcs_order_contains_subscription( $order );
	}

	/**
	 * This function once made sure the recurring payment method was set correctly on an order when a customer placed an order
	 * with one payment method (like PayPal), and then returned and completed payment using a different payment method.
	 *
	 * With the advent of a separate subscription object in 2.0, this became unnecessary.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 */
	public static function set_recurring_payment_method( $order_id ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * Checks if an order contains an in active subscription and if it does, denies download access
	 * to files purchased on the order.
	 *
	 * @return bool False if the order contains a subscription that has expired or is cancelled/on-hold, otherwise, the original value of $download_permitted
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.3
	 */
	public static function is_download_permitted( $download_permitted, $order ) {
		_deprecated_function( __METHOD__, '2.0' );
		return $download_permitted;
	}

	/**
	 * Add subscription related order item meta when a subscription product is added as an item to an order via Ajax.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @param WC_Order_Item $item
	 * @param int $item_id An order_item_id as returned by the insert statement of @see woocommerce_add_order_item()
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.5
	 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 * @return void
	 */
	public static function prefill_order_item_meta( $item, $item_id ) {
		_deprecated_function( __METHOD__, '2.0' );
		return $item;
	}

	/**
	 * Calculate recurring line taxes when a store manager clicks the "Calc Line Tax" button on the "Edit Order" page.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * Based on the @see woocommerce_calc_line_taxes() function.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.4
	 * @return void
	 */
	public static function calculate_recurring_line_taxes() {
		_deprecated_function( __METHOD__, '2.0' );
		die();
	}

	/**
	 * Removes a line tax item from an order by ID. Hooked to
	 * an Ajax call from the "Edit Order" page and mirrors the
	 * @see woocommerce_remove_line_tax() function.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @return void
	 */
	public static function remove_line_tax() {
		_deprecated_function( __METHOD__, '2.0' );
		die();
	}

	/**
	 * Adds a line tax item from an order by ID. Hooked to
	 * an Ajax call from the "Edit Order" page and mirrors the
	 * @see woocommerce_add_line_tax() function.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @return void
	 */
	public static function add_line_tax() {
		_deprecated_function( __METHOD__, '2.0' );
		die();
	}

	/**
	 * Display recurring order totals on the "Edit Order" page.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @param int $post_id The post ID of the shop_order post object.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.4
	 * @return void
	 */
	public static function recurring_order_totals_meta_box_section( $post_id ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * When an order is added or updated from the admin interface, check if a subscription product
	 * has been manually added to the order or the details of the subscription have been modified,
	 * and create/update the subscription as required.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @param int $post_id The ID of the post which is the WC_Order object.
	 * @param Object $post The post object of the order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1
	 */
	public static function pre_process_shop_order_meta( $post_id, $post ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * Worked around a bug in WooCommerce which ignores order item meta values of 0.
	 *
	 * Deprecated because editing a subscription's values is now done from the Edit Subscription screen and those values
	 * are stored against a 'shop_subscription' post, not the 'shop_order' used to purchase the subscription.
	 *
	 * @param int $post_id The ID of the post which is the WC_Order object.
	 * @param Object $post The post object of the order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.4
	 */
	public static function process_shop_order_item_meta( $post_id, $post ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * Checks if a subscription requires manual payment because the payment gateway used to purchase the subscription
	 * did not support automatic payments at the time of the subscription sign up. Or because we're on a staging site.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @return bool True if the subscription exists and requires manual payments, false if the subscription uses automatic payments (defaults to false for backward compatibility).
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function requires_manual_renewal( $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::is_manual()' );

		$requires_manual_renewal = true;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
			if ( ! $subscription->is_manual() ) {
				$requires_manual_renewal = false;
				break;
			}
		}

		return $requires_manual_renewal;
	}

	/**
	 * Returns the total amount to be charged at the outset of the Subscription.
	 *
	 * This may return 0 if there is a free trial period and no sign up fee, otherwise it will be the sum of the sign up
	 * fee and price per period. This function should be used by payment gateways for the initial payment.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The ID of the product.
	 * @return float The total initial amount charged when the subscription product in the order was first purchased, if any.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1
	 */
	public static function get_total_initial_payment( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Order::get_total()' );

		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		return apply_filters( 'woocommerce_subscriptions_total_initial_payment', $order->get_total(), $order, $product_id );
	}

	/**
	 * Returns the recurring amount for an item
	 *
	 * @param WC_Order $order A WC_Order object
	 * @param int $product_id The product/post ID of a subscription
	 * @return float The total amount to be charged for each billing period, if any, not including failed payments.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_item_recurring_amount( $order, $product_id ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the item on the subscription object rather than the value on the original order. A line item can be deleted from a subscription since Subscriptions v2.0, so even if it exists on an order, it may not exist as a subscription. That means for accurate results, you must use the value on the subscription object' );

		$subscription_item = self::get_matching_subscription_item( $order, $product_id );

		if ( isset( $subscription_item['line_total'] ) ) {
			$recurring_amount = $subscription_item['line_total'] / $subscription_item['qty'];
		} else {
			$recurring_amount = 0;
		}

		return $recurring_amount;
	}

	/**
	 * Returns the proportion of cart discount that is recurring for the product specified with $product_id
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_discount_cart( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different discounts, so use the subscription object' );

		$recurring_discount_cart = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total discount for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_discount_cart += $subscription->get_total_discount();
			} else {
				// We want the discount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_discount_cart += $subscription->get_total_discount();
						break;
					}
				}
			}
		}

		return $recurring_discount_cart;
	}

	/**
	 * Returns the proportion of cart discount tax that is recurring for the product specified with $product_id
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_discount_cart_tax( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different discounts, so use the subscription object' );

		$recurring_discount_cart_tax = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total discount for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_discount_cart_tax += $subscription->get_total_discount();
			} else {
				// We want the discount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_discount_cart_tax += $subscription->get_total_discount();
						break;
					}
				}
			}
		}

		return $recurring_discount_cart_tax;
	}

	/**
	 * Returns the proportion of total discount that is recurring for the product specified with $product_id
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_discount_total( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different discounts, so use the subscription object' );

		$ex_tax = ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) && wcs_get_objects_property( $order, 'display_totals_ex_tax' ) );

		$recurring_discount_cart     = (double) self::get_recurring_discount_cart( $order );
		$recurring_discount_cart_tax = (double) self::get_recurring_discount_cart_tax( $order );
		$recurring_discount_total    = 0;

		$order_version = wcs_get_objects_property( $order, 'version' );

		if ( '' === $order_version || version_compare( $order_version, '2.3.7', '<' ) ) {
			// Backwards compatible total calculation - totals were not stored consistently in old versions.
			if ( $ex_tax ) {
				if ( wcs_get_objects_property( $order, 'prices_include_tax' ) ) {
					$recurring_discount_total = $recurring_discount_cart - $recurring_discount_cart_tax;
				} else {
					$recurring_discount_total = $recurring_discount_cart;
				}
			} else {
				if ( wcs_get_objects_property( $order, 'prices_include_tax' ) ) {
					$recurring_discount_total = $recurring_discount_cart;
				} else {
					$recurring_discount_total = $recurring_discount_cart + $recurring_discount_cart_tax;
				}
			}
		// New logic - totals are always stored exclusive of tax, tax total is stored in cart_discount_tax
		} else {
			if ( $ex_tax ) {
				$recurring_discount_total = $recurring_discount_cart;
			} else {
				$recurring_discount_total = $recurring_discount_cart + $recurring_discount_cart_tax;
			}
		}

		return $recurring_discount_total;
	}

	/**
	 * Returns the amount of shipping tax that is recurring. As shipping only applies
	 * to recurring payments, and only 1 subscription can be purchased at a time,
	 * this is equal to @see WC_Order::get_total_tax()
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_shipping_tax_total( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different amounts, so use the subscription object' );

		$recurring_shipping_tax_total = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_shipping_tax_total += $subscription->get_shipping_tax();
			} else {
				// We want the amount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_shipping_tax_total += $subscription->get_shipping_tax();
						break;
					}
				}
			}
		}

		return $recurring_shipping_tax_total;
	}

	/**
	 * Returns the recurring shipping price . As shipping only applies to recurring
	 * payments, and only 1 subscription can be purchased at a time, this is
	 * equal to @see WC_Order::get_total_shipping()
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_shipping_total( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different amounts, so use the subscription object' );

		$recurring_shipping_total = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_shipping_total += $subscription->get_total_shipping();
			} else {
				// We want the amount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_shipping_total += $subscription->get_total_shipping();
						break;
					}
				}
			}
		}

		return $recurring_shipping_total;
	}

	/**
	 * Return an array of shipping costs within this order.
	 *
	 * @return array
	 */
	public static function get_recurring_shipping_methods( $order ) {
		_deprecated_function( __METHOD__, '2.0', 'the shipping for each individual subscription object rather than the original order. Shipping is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different shipping methods, so use the subscription object' );

		$recurring_shipping_methods = array();

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
			$recurring_shipping_methods = array_merge( $recurring_shipping_methods, $subscription->get_shipping_methods() );
		}

		return $recurring_shipping_methods;
	}

	/**
	 * Returns an array of taxes on an order with their recurring totals.
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_taxes( $order ) {
		_deprecated_function( __METHOD__, '2.0', 'the taxes for the subscription object rather than the original order. Taxes are stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different taxes, so use the subscription object' );

		$recurring_taxes = array();

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
			$recurring_taxes = array_merge( $recurring_taxes, $subscription->get_taxes() );
		}

		return $recurring_taxes;
	}

	/**
	 * Returns the proportion of total tax on an order that is recurring for the product specified with $product_id
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_total_tax( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different amounts, so use the subscription object' );

		$recurring_total_tax = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_total_tax += $subscription->get_total_tax();
			} else {
				// We want the discount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_total_tax += $subscription->get_total_tax();
						break;
					}
				}
			}
		}

		return $recurring_total_tax;
	}

	/**
	 * Returns the proportion of total before tax on an order that is recurring for the product specified with $product_id
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_total_ex_tax( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the value for the subscription object rather than the value on the original order. The value is stored against the subscription since Subscriptions v2.0 as an order can be used to create multiple different subscriptions with different amounts, so use the subscription object' );
		return self::get_recurring_total( $order, $product_id ) - self::get_recurring_total_tax( $order, $product_id );
	}

	/**
	 * Returns the price per period for a subscription in an order.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The ID of the product.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_total( $order, $product_id = 0 ) {
		$recurring_total = 0;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the total for all recurring items
			if ( empty( $product_id ) ) {
				$recurring_total += $subscription->get_total();
			} else {
				// We want the discount for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$recurring_total += $subscription->get_total();
						break;
					}
				}
			}
		}

		return $recurring_total;
	}

	/**
	 * Creates a string representation of the subscription period/term for each item in the cart
	 *
	 * @param WC_Order $order A WC_Order object.
	 * @param mixed $deprecated_price Never used.
	 * @param mixed $deprecated_sign_up_fee Never used.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_order_subscription_string( $order, $deprecated_price = '', $deprecated_sign_up_fee = '' ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_formatted_order_total()' );

		$initial_amount = wc_price( self::get_total_initial_payment( $order ) );

		$subscription_string = self::get_formatted_order_total( $initial_amount, $order );

		return $subscription_string;
	}

	/**
	 * Returns an array of items in an order which are recurring along with their recurring totals.
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_recurring_items( $order ) {
		_deprecated_function( __METHOD__, '2.0', 'the items on each individual subscription object (i.e. "shop_subscription")' );

		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		$items = array();

		foreach ( $order->get_items() as $item_id => $item_details ) {

			if ( ! self::is_item_subscription( $order, $item_details ) ) {
				continue;
			}

			$items[ $item_id ]      = $item_details;
			$order_items_product_id = wcs_get_canonical_product_id( $item_details );
			$matching_subscription  = self::get_matching_subscription( $order, $order_items_product_id );

			// Set the line totals to be the recurring amounts, not the initial order's amount
			if ( null !== $matching_subscription ) {
				foreach ( $matching_subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $order_items_product_id ) {
						$items[ $item_id ]['line_subtotal']     = $line_item['line_subtotal'];
						$items[ $item_id ]['line_subtotal_tax'] = $line_item['line_subtotal_tax'];
						$items[ $item_id ]['line_total']        = $line_item['line_total'];
						$items[ $item_id ]['line_tax']          = $line_item['line_tax'];
						break;
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Returns the period (e.g. month) for a each subscription product in an order.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return string A string representation of the period for the subscription, i.e. day, week, month or year.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_subscription_period( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the billing period for each individual subscription object. Since Subscriptions v2.0, an order can be used to create multiple different subscriptions with different billing schedules, so use the subscription object' );

		$billing_period = '';

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the billing period discount for all recurring items
			if ( empty( $product_id ) ) {
				$billing_period = $subscription->get_billing_period();
				break;
			} else {
				// We want the billing period for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$billing_period = $subscription->get_billing_period();
						break 2;
					}
				}
			}
		}

		return $billing_period;
	}

	/**
	 * Returns the billing interval for a each subscription product in an order.
	 *
	 * For example, this would return 3 for a subscription charged every 3 months or 1 for a subscription charged every month.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return int The billing interval for a each subscription product in an order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_subscription_interval( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the billing interval for each individual subscription object. Since Subscriptions v2.0, an order can be used to create multiple different subscriptions with different billing schedules, so use the subscription object' );

		$billing_interval = '';

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

			// Find the billing interval for all recurring items
			if ( empty( $product_id ) ) {
				$billing_interval = $subscription->get_billing_interval();
				break;
			} else {
				// We want the billing interval for a specific item (so we need to find if this subscription contains that item)
				foreach ( $subscription->get_items() as $line_item ) {
					if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
						$billing_interval = $subscription->get_billing_interval();
						break 2;
					}
				}
			}
		}

		return $billing_interval;
	}

	/**
	 * Returns the length for a subscription in an order.
	 *
	 * There must be only one subscription in an order for this to be accurate.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return int The number of periods for which the subscription will recur. For example, a $5/month subscription for one year would return 12. A $10 every 3 month subscription for one year would also return 12.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_subscription_length( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the end date each individual subscription object. Since Subscriptions v2.0, an order can be used to create multiple different subscriptions with different billing schedules. The length of a subscription is also no longer stored against the subscription and instead, it is used simply to calculate the end date for the subscription when it is purchased. Therefore, you must use the end date of a subscription object' );
		return null;
	}

	/**
	 * Returns the length for a subscription product's trial period as set when added to an order.
	 *
	 * The trial period is the same as the subscription period, as derived from @see self::get_subscription_period().
	 *
	 * For now, there must be only one subscription in an order for this to be accurate.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return int The number of periods the trial period lasts for. For no trial, this will return 0, for a 3 period trial, it will return 3.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1
	 */
	public static function get_subscription_trial_length( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the first payment date for each individual subscription object. Since Subscriptions v2.0, an order can be used to create multiple different subscriptions with different billing schedules. The trial length of a subscription is also no longer stored against the subscription and instead, it is used simply to calculate the first payment date for the subscription when it is purchased. Therefore, you must use the first payment date of a subscription object' );
		return null;
	}

	/**
	 * Returns the period (e.g. month)  for a subscription product's trial as set when added to an order.
	 *
	 * As of 1.2.x, a subscriptions trial period may be different than the recurring period
	 *
	 * For now, there must be only one subscription in an order for this to be accurate.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id (optional) The post ID of the subscription WC_Product object purchased in the order. Defaults to the ID of the first product purchased in the order.
	 * @return string A string representation of the period for the subscription, i.e. day, week, month or year.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 */
	public static function get_subscription_trial_period( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'the billing period for each individual subscription object. Since Subscriptions v2.0, an order can be used to create multiple different subscriptions with different billing schedules. The trial period of a subscription is also no longer stored against the subscription and instead, it is used simply to calculate the first payment date for the subscription when it is purchased. Therefore, you must use the billing period of a subscription object' );
		return self::get_subscription_period( $order, $product_id );
	}

	/**
	 * Takes a subscription product's ID and returns the timestamp on which the next payment is due.
	 *
	 * A convenience wrapper for @see WC_Subscriptions_Manager::get_next_payment_date() to get the
	 * next payment date for a subscription when all you have is the order and product.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The product/post ID of the subscription
	 * @param mixed $deprecated Never used.
	 * @return int If no more payments are due, returns 0, otherwise returns a timestamp of the date the next payment is due.
	 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_next_payment_timestamp( $order, $product_id, $deprecated = null ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_time( "next_payment" )' );

		$next_payment_timestamp = 0;

		if ( $subscription = self::get_matching_subscription( $order, $product_id ) ) {
			$next_payment_timestamp = $subscription->get_time( 'next_payment' );
		}

		return $next_payment_timestamp;
	}

	/**
	 * Takes a subscription product's ID and the order it was purchased in and returns the date on
	 * which the next payment is due.
	 *
	 * A convenience wrapper for @see WC_Subscriptions_Manager::get_next_payment_date() to get the next
	 * payment date for a subscription when all you have is the order and product.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The product/post ID of the subscription
	 * @param mixed $deprecated Never used.
	 * @return mixed If no more payments are due, returns 0, otherwise it returns the MySQL formatted date/time string for the next payment date.
	 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_next_payment_date( $order, $product_id, $deprecated = null ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_date( "next_payment" )' );

		$next_payment_date = 0;

		if ( $subscription = self::get_matching_subscription( $order, $product_id ) ) {
			$next_payment_date = $subscription->get_date( 'next_payment' );
		}

		return $next_payment_date;
	}

	/**
	 * Takes a subscription product's ID and the order it was purchased in and returns the date on
	 * which the last payment was made.
	 *
	 * A convenience wrapper for @see WC_Subscriptions_Manager::get_last_payment_date() to get the next
	 * payment date for a subscription when all you have is the order and product.
f	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The product/post ID of the subscription
	 * @return mixed If no more payments are due, returns 0, otherwise it returns the MySQL formatted date/time string for the next payment date.
	 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v1.2.1
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_last_payment_date( $order, $product_id ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_date( "last_payment" )' );

		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		if ( $subscription = self::get_matching_subscription( $order, $product_id ) ) {
			$last_payment_date = $subscription->get_date( 'last_order_date_created' );
		} elseif ( null !== ( $last_payment_date = wcs_get_objects_property( $order, 'date_paid' ) ) ) {
			$last_payment_date = $last_payment_date->date( 'Y-m-d H:i:s' );
		} else {
			$last_payment_date = wcs_get_datetime_utc_string( wcs_get_objects_property( $order, 'date_created' ) ); // get_date_created() can return null, but if it does, we have an error anyway
		}

		return $last_payment_date;
	}

	/**
	 * Takes a subscription product's ID and calculates the date on which the next payment is due.
	 *
	 * Calculation is based on $from_date if specified, otherwise it will fall back to the last
	 * completed payment, the subscription's start time, or the current date/time, in that order.
	 *
	 * The next payment date will occur after any free trial period and up to any expiration date.
	 *
	 * @param mixed $order A WC_Order object or the ID of the order which the subscription was purchased in.
	 * @param int $product_id The product/post ID of the subscription
	 * @param string $type (optional) The format for the Either 'mysql' or 'timestamp'.
	 * @param mixed $from_date A MySQL formatted date/time string from which to calculate the next payment date, or empty (default), which will use the last payment on the subscription, or today's date/time if no previous payments have been made.
	 * @return mixed If there is no future payment set, returns 0, otherwise it will return a date of the next payment in the form specified by $type
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function calculate_next_payment_date( $order, $product_id, $type = 'mysql', $from_date = '' ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::calculate_date( "next_payment" )' );

		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		$next_payment = 0;

		if ( $subscription = self::get_matching_subscription( $order, $product_id ) ) {
			$next_payment = $subscription->calculate_date( 'next_payment' );
		}

		$next_payment = ( 'mysql' == $type && 0 != $next_payment ) ? $next_payment : wcs_date_to_time( $next_payment );
		return apply_filters( 'woocommerce_subscriptions_calculated_next_payment_date', $next_payment, $order, $product_id, $type, $from_date, $from_date );
	}

	/**
	 * Returns the number of failed payments for a given subscription.
	 *
	 * @param WC_Order $order The WC_Order object of the order for which you want to determine the number of failed payments.
	 * @param int $product_id The ID of the subscription product.
	 * @return string The key representing the given subscription.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_failed_payment_count( $order, $product_id ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_failed_payment_count()' );

		$failed_payment_count = 0;

		if ( $subscription = self::get_matching_subscription( $order, $product_id ) ) {
			$failed_payment_count = $subscription->get_failed_payment_count();
		}

		return $failed_payment_count;
	}

	/**
	 * Returns the amount outstanding on a subscription product.
	 *
	 * Deprecated because the subscription outstanding balance on a subscription is no longer added and an order can contain more
	 * than one subscription.
	 *
	 * @param WC_Order $order The WC_Order object of the order for which you want to determine the number of failed payments.
	 * @param int $product_id The ID of the subscription product.
	 * @return string The key representing the given subscription.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_outstanding_balance( $order, $product_id ) {
		_deprecated_function( __METHOD__, '2.0' );

		$failed_payment_count = self::get_failed_payment_count( $order, $product_id );

		$outstanding_balance = $failed_payment_count * self::get_recurring_total( $order, $product_id );

		return $outstanding_balance;
	}

	/**
	 * Once payment is completed on an order, set a lock on payments until the next subscription payment period.
	 *
	 * @param int $order_id The id of the order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1.2
	 */
	public static function safeguard_scheduled_payments( $order_id ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * Appends the subscription period/duration string to order total
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_formatted_line_total( $formatted_total, $item, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_formatted_line_subtotal()' );
		return $formatted_total;
	}

	/**
	 * Appends the subscription period/duration string to order subtotal
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_subtotal_to_display( $subtotal, $compound, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_subtotal_to_display()' );
		return $subtotal;
	}

	/**
	 * Appends the subscription period/duration string to order total
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_cart_discount_to_display( $discount, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_discount_to_display()' );
		return $discount;
	}

	/**
	 * Appends the subscription period/duration string to order total
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_order_discount_to_display( $discount, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_discount_to_display()' );
		return $discount;
	}

	/**
	 * Appends the subscription period/duration string to order total
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_formatted_order_total( $formatted_total, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_formatted_order_total()' );
		return $formatted_total;
	}

	/**
	 * Appends the subscription period/duration string to shipping fee
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_shipping_to_display( $shipping_to_display, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_shipping_to_display()' );
		return $shipping_to_display;
	}

	/**
	 * Individual totals are taken care of by filters, but taxes and fees are not, so we need to override them here.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.0
	 */
	public static function get_order_item_totals( $total_rows, $order ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_order_item_totals()' );
		return $total_rows;
	}

	/**
	 * Load Subscription related order data when populating an order
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4
	 */
	public static function load_order_data( $order_data ) {
		_deprecated_function( __METHOD__, '2.0' );
		return $order_data;
	}

	/**
	 * Add request filter for order types to Woocommerce -> Orders screen
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.5.4
	 */
	public static function order_shipping_method( $shipping_method, $order ) {
		_deprecated_function( __METHOD__, '2.0' );
		return $shipping_method;
	}

	/**
	 * Returns the sign up fee for an item
	 *
	 * @param WC_Order $order A WC_Order object
	 * @param int $product_id The product/post ID of a subscription
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.2
	 * @deprecated 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	public static function get_item_sign_up_fee( $order, $product_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0', 'WC_Subscription::get_items_sign_up_fee() or WC_Subscriptions_Order::get_sign_up_fee()' );
		return self::get_sign_up_fee( $order, $product_id );
	}

	/**
	 * Records the initial payment against a subscription.
	 *
	 * This function is called when a gateway calls @see WC_Order::payment_complete() and payment
	 * is completed on an order. It is also called when an orders status is changed to completed or
	 * processing for those gateways which never call @see WC_Order::payment_complete(), like the
	 * core WooCommerce Cheque and Bank Transfer gateways.
	 *
	 * It will also set the start date on the subscription to the time the payment is completed.
	 *
	 * @param WC_Order|int $order A WC_Order object or ID of a WC_Order order.
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.1.2
	 * @deprecated 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
	 */
	public static function maybe_record_order_payment( $order ) {
		_deprecated_function( __METHOD__, '2.0', __CLASS__ . 'maybe_record_subscription_payment::( $order, $old_status, $new_status )' );

		if ( ! wcs_order_contains_renewal( $order ) ) {

			$subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) );

			foreach ( $subscriptions as $subscription_id => $subscription ) {

				// No payments have been recorded yet
				if ( 0 == $subscription->get_payment_count() ) {
					$subscription->update_dates( array( 'date_created' => current_time( 'mysql', true ) ) );
					$subscription->payment_complete();
				}
			}
		}
	}

	/**
	 * Wrapper around @see WC_Order::get_order_currency() for versions of WooCommerce prior to 2.1.
	 *
	 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v1.4.9
	 * @deprecated 1.0.0 - Migrated from WooCommerce Subscriptions v2.2.0
	 */
	public static function get_order_currency( $order ) {
		_deprecated_function( __METHOD__, '2.2.0', 'wcs_get_objects_property( $order, "currency" ) or $order->get_currency()' );
		return wcs_get_objects_property( $order, 'currency' );
	}

	/**
	 * A unified API for accessing subscription order meta, especially for sign-up fee related order meta.
	 *
	 * Because WooCommerce 2.1 deprecated WC_Order::$order_custom_fields, this function is also used to provide
	 * version independent meta data access to non-subscription meta data.
	 *
	 * Deprecated in Subscriptions Core 2.0 since we have the wcs_get_objects_property() which serves the same purpose.
	 *
	 * @deprecated 2.0
	 * @since 1.0
	 *
	 * @param WC_Order|int $order    The WC_Order object or ID of the order for which the meta should be sought.
	 * @param string       $meta_key The key as stored in the post meta table for the meta item.
	 * @param mixed        $default  The default value to return if the meta key does not exist. Default 0.
	 *
	 * @return mixed Order meta data found by key.
	 */
	public static function get_meta( $order, $meta_key, $default = 0 ) {
		wcs_deprecated_function( __METHOD__, '2.0', 'wcs_get_objects_property( $order, $meta_key, "single", $default )' );

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		$meta_key = preg_replace( '/^_/', '', $meta_key );

		if ( isset( $order->$meta_key ) ) { // WC 2.1+ magic __isset() & __get() methods
			$meta_value = $order->$meta_key;
			// @phpstan-ignore-next-line
		} elseif ( is_array( $order->order_custom_fields ) && isset( $order->order_custom_fields[ '_' . $meta_key ][0] ) && $order->order_custom_fields[ '_' . $meta_key ][0] ) {  // < WC 2.1+
			$meta_value = maybe_unserialize( $order->order_custom_fields[ '_' . $meta_key ][0] );
		} else {
			$meta_value = wcs_get_objects_property( $order, $meta_key, 'single', $default );
		}

		return $meta_value;
	}

	/**
	 * Update subscription cached last_order_date_created metadata when deleting a child order.
	 *
	 * @param int      $id    The deleted order ID.
	 * @param WC_Order $order The deleted order object.
	 */
	public static function delete_order_update_order_related_subscriptions_last_order_date_created( $id, $order ) {
		if ( $order->get_created_via() !== 'subscription' ) {
			return;
		}

		self::update_order_related_subscriptions_last_order_date_created( $order, [ 'trash' ] );
	}

	/**
	 * Update subscription cached last_order_date_created metadata when adding a child order.
	 *
	 * @param WC_Order $order         The order to link with the subscription.
	 * @param WC_Order $subscription  The order or subscription to link the order to.
	 * @param string   $relation_type The relationship between the subscription and the order. Must be 'renewal', 'switch' or 'resubscribe' unless custom relationships are implemented.
	 */
	public static function add_relation_update_order_related_subscriptions_last_order_date_created( $order, $subscription, $relation_type ) {
		self::update_order_related_subscriptions_last_order_date_created( $order );
	}

	/**
	 * Update subscription cached last_order_date_created metadata when deleting a child order relation.
	 *
	 * @param WC_Order $order         An order that may be linked with subscriptions.
	 * @param WC_Order $subscription  A subscription or order to unlink the order with, if a relation exists.
	 * @param string   $relation_type The relationship between the subscription and the order. Must be 'renewal', 'switch' or 'resubscribe' unless custom relationships are implemented.
	 */
	public static function delete_relation_update_order_related_subscriptions_last_order_date_created( $order, $subscription, $relation_type ) {
		self::update_subscription_last_order_date_created( $subscription );

		self::update_order_related_subscriptions_last_order_date_created( $order );
	}

	/**
	 * Update all subscription cached last_order_date_created metadata related to the order.
	 *
	 * @param WC_Order $order The order object.
	 * @param array    $exclude_statuses The order statuses to exclude.
	 */
	private static function update_order_related_subscriptions_last_order_date_created( $order, $exclude_statuses = [] ) {
		$subscription_ids = wcs_get_subscription_ids_for_order( $order );

		foreach ( $subscription_ids as $subscription_id ) {
			$subscription = wcs_get_subscription( $subscription_id );

			self::update_subscription_last_order_date_created( $subscription, $exclude_statuses );
		}
	}

	/**
	 * Update subscription cached last_order_date_created metadata when manually updating parent id.
	 *
	 * @param string $type The type of update to check. Only 'add' or 'delete' should be used.
	 * @param int $object_id The object the meta is being changed on.
	 * @param string $key The object meta key being changed.
	 * @param mixed $new_value The meta value.
	 * @param mixed $previous_value The previous value stored in the database. Optional.
	 */
	public static function update_subscription_last_order_date_parent_id_changes( $type, $object_id, $key, $new_value, $previous_value ) {
		if ( 'parent_id' !== $key || empty( $new_value ) ) {
			return;
		}

		$subscription = wcs_get_subscription( $object_id );

		self::update_subscription_last_order_date_created( $subscription );
	}

	/**
	 * Update subscription cached last_order_date_created metadata.
	 *
	 * @param WC_Subscription $subscription The subscription object.
	 * @param array           $exclude_statuses The order statuses to exclude.
	 */
	private static function update_subscription_last_order_date_created( $subscription, $exclude_statuses = [] ) {
		if ( ! $subscription ) {
			return;
		}

		$last_order_date_created = $subscription->get_time( 'last_order_date_created', 'gmt', $exclude_statuses );

		// If the last order date created hasn't changed, no need to update and save.
		if ( $last_order_date_created === (int) $subscription->get_last_order_date_created() ) {
			return;
		}

		$subscription->set_last_order_date_created( $last_order_date_created );
		$subscription->save();
	}

	/**
	 * Prints the HTML for the admin dropdown for order types to Woocommerce -> Orders screen.
	 *
	 * @since 6.3.0
	 */
	private static function render_restrict_manage_subscriptions_dropdown() {
		$order_types = apply_filters(
			'woocommerce_subscriptions_order_type_dropdown',
			array(
				'original'    => _x( 'Original', 'An order type', 'woocommerce-subscriptions' ),
				'parent'      => _x( 'Subscription Parent', 'An order type', 'woocommerce-subscriptions' ),
				'renewal'     => _x( 'Subscription Renewal', 'An order type', 'woocommerce-subscriptions' ),
				'resubscribe' => _x( 'Subscription Resubscribe', 'An order type', 'woocommerce-subscriptions' ),
				'switch'      => _x( 'Subscription Switch', 'An order type', 'woocommerce-subscriptions' ),
				'regular'     => _x( 'Non-subscription', 'An order type', 'woocommerce-subscriptions' ),
			)
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected_shop_order_subtype = isset( $_GET['shop_order_subtype'] ) ? wc_clean( wp_unslash( $_GET['shop_order_subtype'] ) ) : '';

		?>
		<select name='shop_order_subtype' id='dropdown_shop_order_subtype'>
			<option value=""><?php esc_html_e( 'All orders types', 'woocommerce-subscriptions' ); ?></option>

			<?php foreach ( $order_types as $order_type_key => $order_type_description ) : ?>
				<option
					value="<?php echo esc_attr( $order_type_key ); ?>"
					<?php selected( $selected_shop_order_subtype, $order_type_key ); ?>
				>
					<?php echo esc_html( $order_type_description ); ?>
				</option>
			<?php endforeach; ?>

		</select>
		<?php
	}

	/**
	 * Renders the contents of the "contains_subscription" column.
	 *
	 * This column indicates whether an order is a parent of a subscription,
	 * a renewal order for a subscription, or a regular order.
	 *
	 * @since 6.3.0
	 *
	 * @param WC_Order $order The order in the current row.
	 */
	private static function render_contains_subscription_column_content( $order ) {
		$order = ! is_object( $order ) ? wc_get_order( $order ) : $order;

		if ( ! $order ) {
			return;
		}

		if ( wcs_order_contains_renewal( $order ) ) {
			echo '<span class="subscription_renewal_order tips" data-tip="' . esc_attr__( 'Renewal Order', 'woocommerce-subscriptions' ) . '"></span>';
		} elseif ( wcs_order_contains_resubscribe( $order ) ) {
			echo '<span class="subscription_resubscribe_order tips" data-tip="' . esc_attr__( 'Resubscribe Order', 'woocommerce-subscriptions' ) . '"></span>';
		} elseif ( apply_filters( 'woocommerce_subscriptions_orders_list_render_parent_order_relation', true, $order ) && wcs_order_contains_parent( $order ) ) {
			echo '<span class="subscription_parent_order tips" data-tip="' . esc_attr__( 'Parent Order', 'woocommerce-subscriptions' ) . '"></span>';
		} else {
			echo '<span class="normal_order">&ndash;</span>';
		}
	}
}
