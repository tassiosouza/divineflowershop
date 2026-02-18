<?php
/**
 * Admin panel metaboxes
 *
 * @link  http://www.powerfulwp.com
 * @since 1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
class GMFW_MetaBoxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_metaboxes' ), 10, 2 );

	}

	public function add_metaboxes() {

		$screen = gmfw_is_hpos_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';

		add_meta_box(
			'gmfw_metaboxes',
			__( 'Gift Message', 'gmfw' ),
			array( $this, 'create_metaboxes' ),
			$screen,
			'side',
			'default'
		);

	}



	/**
	 * Building the metabox
	 */
	public function create_metaboxes() {
		global $post, $theorder;

		// Determine if we're working with an order object or a post object.
		$order = gmfw_is_hpos_enabled() && ( $theorder instanceof WC_Order ) ? $theorder : wc_get_order( $post->ID );

		echo '<input type="hidden" name="gmfw_metaboxes_key" id="gmfw_metaboxes_key" value="' . esc_attr( wp_create_nonce( 'gmfw-save-order' ) ) . '" />';

		$gmfw_admin = new GMFW_Admin( '', '' );

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				// occasions
				echo $gmfw_admin->gmfw_occasions_field__premium_only( $order );
			}
		}

		echo $gmfw_admin->gmfw_message_field( $order );

		// maximum length.
		$gmfw_maximum_length = get_option( 'gmfw_maximum_length', '' );
		if ( '' === $gmfw_maximum_length ) {
			$gmfw_maximum_length = 160;
		}
		echo '<div id="gmfw_counter_wrap"><span id="gmfw_counter" data="' . esc_html( $gmfw_maximum_length ) . '">' . esc_html( $gmfw_maximum_length ) . '</span> ' . esc_html( __( 'characters left', 'gmfw' ) ) . '</div>';

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				// copy button.
				echo '<button class="button button-primary" href="#" id="gmfw_copy_text"  data-btn="' . esc_attr( __( 'Copy gift message', 'gmfw' ) ) . '" data-success="' . esc_attr( __( 'Copied successfully', 'gmfw' ) ) . '" >' . esc_html( __( 'Copy gift message', 'gmfw' ) ) . '</button>';
				// gift message suggestions.
				echo $gmfw_admin->gmfw_suggestions__premium_only();
			}
		}
	}

	/**
	 * Save the Metabox Data
	 *
	 * @param int    $post_id post number.
	 * @param object $post post object.
	 */
	public function save_metaboxes( $post_id, $post ) {

		if ( self::$saved_meta_boxes ) {
			return;
		}

		self::$saved_meta_boxes = true;

		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( ! isset( $_POST['gmfw_metaboxes_key'] ) || ! wp_verify_nonce( $_POST['gmfw_metaboxes_key'], 'gmfw-save-order' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$order = wc_get_order( $post_id );

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				if ( isset( $_POST['gmfw_occasion'] ) ) {
					$gmfw_occasion = sanitize_text_field( wp_unslash( $_POST['gmfw_occasion'] ) );
					if ( is_numeric( $gmfw_occasion ) ) {
						$term      = get_term_by( 'id', $gmfw_occasion, 'gmfw_occasions' );
						$term_name = $term->name;
						if ( '' !== $term_name ) {
							$order->update_meta_data( 'gmfw_occasion', $gmfw_occasion );
							$order->update_meta_data( 'gmfw_occasion_name', $term_name );
						}
					} else {
						$order->delete_meta_data( 'gmfw_occasion' );
						$order->delete_meta_data( 'gmfw_occasion_name' );

					}
				}
			}
		}

		if ( isset( $_POST['gmfw_gift_message'] ) ) {
			$gmfw_gift_message = wp_unslash( $_POST['gmfw_gift_message'] );
			if ( '' !== $gmfw_gift_message ) {
				$order->update_meta_data( 'gmfw_gift_message', $gmfw_gift_message );

			} else {
				$order->delete_meta_data( 'gmfw_gift_message' );

			}
		}

		$order->save();

		  // Remove the flag after saving is done
		  self::$saved_meta_boxes = false;

	}

}

// Initialize the class
$gmfw_meta_boxes = new GMFW_MetaBoxes();
