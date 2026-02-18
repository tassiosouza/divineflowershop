<?php
/**
 * Compatibility with WPML Multilingual CMS.
 *
 * @see https://wpml.org/
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Compat_WPML class.
 *
 * @since 1.13.0
 */
class Iconic_WSB_Compat_WPML {
	/**
	 * Run
	 */
	public static function run() {
		if ( ! Iconic_WSB_Core_Helpers::is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'hooks' ), 10 );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		add_filter( 'wpml_sync_custom_field_copied_value', array( __CLASS__, 'map_fbt_data_copied' ), 15, 4 );
		add_filter( 'iconic_wsb_order_bump_id', [ __CLASS__, 'get_translated_bump_id' ], 50 );
	}

	/**
	 * Handle the Frequently Bought Together data copied to the new translation.
	 *
	 * When copying the data stored in `_iconic_wsb_product_page_order_bump_ids`
	 * or `_iconic_wsb_product_page_bump_modal_ids` we try to use the
	 * translated version instead of the original product ID.
	 *
	 * @param mixed  $copied_value The unserialized and slashed value.
	 * @param int    $post_id_from The ID of the source post.
	 * @param int    $post_id_to   The ID of the destination post.
	 * @param string $meta_key     The key of the post meta being copied.
	 * @return mixed
	 */
	public static function map_fbt_data_copied( $copied_value, $post_id_from, $post_id_to, $meta_key ) {
		global $sitepress;

		if ( ! is_array( $copied_value ) ) {
			return $copied_value;
		}

		if ( empty( $sitepress ) ) {
			return $copied_value;
		}

		if (
			'_iconic_wsb_product_page_order_bump_ids' !== $meta_key &&
			'_iconic_wsb_product_page_bump_modal_ids' !== $meta_key
		) {
			return $copied_value;
		}

		if ( empty( $_POST['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $copied_value;
		}

		$target_lang = sanitize_text_field( wp_unslash( $_POST['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $target_lang ) ) {
			return $copied_value;
		}

		$copied_value = array_map(
			/**
			 * Try to find the translated version.
			 *
			 * @param int $product_id The product ID.
			 * @return int
			 */
			function( $product_id ) use ( $sitepress, $target_lang ) {
				$trid = $sitepress->get_element_trid( $product_id );

				if ( empty( $trid ) ) {
					return $product_id;
				}

				$translations = $sitepress->get_element_translations( $trid );

				if ( empty( $translations[ $target_lang ] ) ) {
					return $product_id;
				}

				$translated_product_id = absint( $translations[ $target_lang ]->element_id );

				if ( empty( $translated_product_id ) ) {
					return $product_id;
				}

				return $translated_product_id;
			},
			$copied_value
		);

		return $copied_value;
	}

	/**
	 * Get the translated bump ID if available.
	 *
	 * @param int $bump_id The bump ID.
	 * @return int
	 */
	public static function get_translated_bump_id( $bump_id ) {
		// phpcs:ignore WooCommerce.Commenting.CommentHooks
		$translated_bump_id = apply_filters( 'wpml_object_id', $bump_id, 'at_checkout_ob' );

		if ( is_numeric( $translated_bump_id ) ) {
			return $translated_bump_id;
		}

		return $bump_id;
	}
}
