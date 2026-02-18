<?php
/**
 * Prevent deleting/trashing products if linked to active subscriptions.
 *
 * Drop into your plugin/theme (admin area) or a must-use plugin.
 *
 * @package Buy Once or Subscribe for WooCommerce Subscriptions
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BOS4W_Product_Delete_Guard' ) ) {
	/**
	 * Class BOS4W_Product_Delete_Guard
	 */
	class BOS4W_Product_Delete_Guard {
		/**
		 * Constructor.
		 */
		public function __construct() {
			// Soft-prevent layer.
			add_filter( 'pre_trash_post', array( $this, 'prevent_trash' ), 10, 2 );
			add_filter( 'pre_delete_post', array( $this, 'prevent_delete' ), 10, 2 );

			// Hard-stop layer.
			add_action( 'before_delete_post', array( $this, 'hard_stop_delete' ), 10, 1 );
			add_action( 'wp_trash_post', array( $this, 'hard_stop_trash' ), 10, 1 );

			add_filter( 'post_row_actions', array( $this, 'filter_product_row_actions' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'filter_product_row_actions' ), 10, 2 );
		}

		/**
		 * Remove Trash/Delete from products list table row actions when product is protected.
		 *
		 * @param array   $actions Action links.
		 * @param WP_Post $post  Post object.
		 *
		 * @return array
		 */
		public function filter_product_row_actions( $actions, $post ) {
			if ( ! $post || 'product' !== $post->post_type ) {
				return $actions;
			}

			if ( 'trash' !== $post->post_status ) {
				return $actions;
			}

			$product = wc_get_product( $post->ID );
			if ( ! $product instanceof WC_Product ) {
				return $actions;
			}

			if ( $this->is_deletion_blocked( $product ) ) {
				// Remove trash and delete actions if present.
				if ( isset( $actions['delete'] ) ) {
					unset( $actions['delete'] );
				}

				// Add a disabled notice action to explain why.
				$actions['bos4w_protected'] = sprintf(
					'<span title="%s" style="color:#888;cursor:not-allowed;">%s</span>',
					esc_attr__( 'This product is assigned to existing subscriptions and cannot be trashed or deleted.', 'bos4w' ),
					esc_html__( 'Protected', 'bos4w' )
				);
			}

			return $actions;
		}

		/**
		 * Deny delete cap for blocked products.
		 *
		 * It checks if the user has the delete_post cap on a product,
		 * and if the product is blocked, denies the delete cap.
		 *
		 * @param array $allcaps User capabilities.
		 * @param array $caps Capabilities to check.
		 * @param array $args Arguments.
		 * @return array
		 */
		public function deny_delete_cap( $allcaps, $caps, $args ) {
			if (
				isset( $args[0], $args[2] )
				&& in_array( $args[0], array( 'delete_post', 'delete_product' ), true )
				&& 0 === strpos( (string) get_post_type( (int) $args[2] ), 'product' )
				&& ( ! isset( $_GET['action'] ) || 'untrash' !== $_GET['action'] )
			) {
				$product = wc_get_product( (int) $args[2] );
				if ( $product instanceof WC_Product && $this->is_deletion_blocked( $product ) ) {
					$allcaps[ $caps[0] ] = false;
				}
			}

			return $allcaps;
		}

		/**
		 * Stop moving to trash.
		 *
		 * @param boolean $trash Trash status.
		 * @param WP_Post $post Post object.
		 * @return boolean
		 */
		public function prevent_trash( $trash, $post ) {
			if ( $post && 'product' === $post->post_type ) {
				$product = wc_get_product( $post->ID );
				if ( $product instanceof WC_Product && $this->is_deletion_blocked( $product ) ) {
					return new WP_Error( 'product_trash_blocked', __( 'This product is linked to active subscriptions and cannot be trashed.', 'your-textdomain' ) );
				}
			}

			return $trash;
		}

		/**
		 * Stop permanent deletion.
		 *
		 * @param boolean $delete Delete status.
		 * @param WP_Post $post Post object.
		 * @return boolean
		 */
		public function prevent_delete( $delete, $post ) {
			if ( $post && 'product' === $post->post_type ) {
				$product = wc_get_product( $post->ID );
				if ( $product instanceof WC_Product && $this->is_deletion_blocked( $product ) ) {
					return new WP_Error( 'product_delete_blocked', __( 'This product is linked to active subscriptions and cannot be deleted.', 'your-textdomain' ) );
				}
			}

			return $delete;
		}

		/**
		 * Final guard just before deletion.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
		public function hard_stop_delete( $post_id ) {
			if ( 'product' !== get_post_type( $post_id ) ) {
				return;
			}
			$product = wc_get_product( $post_id );
			if ( $product instanceof WC_Product && $this->is_deletion_blocked( $product ) ) {
				wp_die( esc_html__( 'This product is linked to active subscriptions and cannot be deleted.', 'your-textdomain' ), 403 );
			}
		}

		/**
		 * Final guard on trash action.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
		public function hard_stop_trash( $post_id ) {
			if ( 'product' !== get_post_type( $post_id ) ) {
				return;
			}
			$product = wc_get_product( $post_id );
			if ( $product instanceof WC_Product && $this->is_deletion_blocked( $product ) ) {
				wp_die( esc_html__( 'This product is linked to active subscriptions and cannot be trashed.', 'your-textdomain' ), 403 );
			}
		}

		/**
		 * Decide if deletion should be blocked.
		 *
		 * You can tailor the supported product types and the linkage detection.
		 *
		 * @param WC_Product $product Product object.
		 * @return boolean
		 */
		private function is_deletion_blocked( WC_Product $product ): bool {
			$supported_types = array( 'simple', 'variable', 'variation', 'bundle', 'composite' );

			// If variable parent: check itself and its variations.
			if ( $product->is_type( 'variable' ) ) {
				if ( $this->has_subscription_links( (int) $product->get_id() ) ) {
					return true;
				}
				foreach ( (array) $product->get_children() as $child_id ) {
					if ( $this->has_subscription_links( (int) $child_id ) ) {
						return true;
					}
				}

				return false;
			}

			// Variation or any other supported type: check its own ID.
			return $this->has_subscription_links( (int) $product->get_id() );
		}

		/**
		 * Fast existence check: does any order item referencing this product carry a "linked to subscription" marker?
		 * Adjust meta_key/joins to match your store’s implementation.
		 *
		 * @param int $product_id Product ID.
		 * @return boolean
		 */
		private function has_subscription_links( int $product_id ): bool {
			if ( $product_id <= 0 ) {
				return false;
			}

			global $wpdb;

			$like_wcs = $wpdb->esc_like( '_wcs_' ) . '%';
			$like_sub = '%' . $wpdb->esc_like( 'subscription' ) . '%';

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT EXISTS (
							SELECT 1
							FROM {$wpdb->prefix}woocommerce_order_items oi
							INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_prod
								ON oi.order_item_id = oim_prod.order_item_id
								AND (
									(oim_prod.meta_key = '_product_id' AND oim_prod.meta_value = %d)
									OR
									(oim_prod.meta_key = '_variation_id' AND oim_prod.meta_value = %d)
								)
							INNER JOIN {$wpdb->posts} p
								ON p.ID = oi.order_id
							LEFT JOIN {$wpdb->postmeta} pm_rel
								ON pm_rel.post_id = p.ID
								AND (
									pm_rel.meta_key LIKE %s
									OR pm_rel.meta_key LIKE %s
								)
							LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_marker
								ON oim_marker.order_item_id = oi.order_item_id
								AND oim_marker.meta_key = %s
							WHERE
								(
									p.post_type = 'shop_subscription'
									OR pm_rel.post_id IS NOT NULL
								)
								AND oi.order_item_type IN ('line_item','subscription_line_item')
							LIMIT 1
						)
					",
					$product_id,
					$product_id,
					$like_wcs,
					$like_sub,
					'bos4w_data'
				)
			);

			// Fallback: If above returns empty (DB drivers can return null), normalize to boolean.
			return (bool) (int) $exists;
		}
	}

	new BOS4W_Product_Delete_Guard();
}
