<?php

/**
 * Compatiblity with Checkout Block.
 */
class Iconic_Flux_Compat_Checkout_Block {

	/**
	 * Since we replace the output of the checkout block with the checkout shortcode,
	 * we the attributes of the checkout block here.
	 *
	 * Used to modify the checkout page behaviour based on the user's settings defined in the
	 * block editor.
	 *
	 * @var array
	 */
	public static $checkout_block_attributes;

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function run() {
		add_filter( 'render_block_woocommerce/checkout', array( __CLASS__, 'replace_checkout_block_with_checkout_shortcode' ), 10, 2 );
		add_action( 'admin_footer', array( __CLASS__, 'add_checkout_block_override_notice' ), 10 );
		add_action( 'customize_render_control_woocommerce_checkout_phone_field', array( __CLASS__, 'customizer_phone_field_notice' ), 10 );
	}

	/**
	 * Replace checkout block to checkout shortcode.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block.
	 *
	 * @return string
	 */
	public static function replace_checkout_block_with_checkout_shortcode( $block_content, $block ) {
		if ( ! Iconic_Flux_Core::is_flux_template() ) {
			return $block_content;
		}

		self::$checkout_block_attributes = $block['attrs'];

		return do_shortcode( '[woocommerce_checkout]' );
	}

	/**
	 * Get checkout block attributes.
	 *
	 * @return array
	 */
	public static function get_checkout_attributes() {
		$default = array(
			'showCompanyField'    => false,
			'requireCompanyField' => false,
			'showApartmentField'  => true,
			'showPhoneField'      => true,
			'requirePhoneField'   => false,
		);

		if ( empty( self::$checkout_block_attributes ) ) {
			return false;
		}

		return wp_parse_args( self::$checkout_block_attributes, $default );
	}

	/**
	 * Add notice telling user that the checkout block is overriden by Flux checkout.
	 *
	 * @return void
	 */
	public static function add_checkout_block_override_notice() {
		global $current_screen;

		if ( empty( $current_screen ) || ! $current_screen->is_block_editor() ) {
			return;
		}

		?>
		<style>
			[data-type="woocommerce/checkout"] .wc-block-components-sidebar-layout:before {
				content: "<?php echo esc_attr( __( 'Flux Checkout will replace the output of this Checkout block when viewed on the site.', 'flux-checkout' ) ); ?>";
				background: #111;
				color: #fff;
				display: inline-block;
				margin-bottom: 20px;
				padding: 10px 20px 10px 20px;
				text-align: center;
				width: 100%;
			}
			</style>
		<?php
	}

	/**
	 * Add notice to customizer when phone field settings are overriden by checkout block.
	 *
	 * @param WP_Customize_Control $control The Phone filed control.
	 *
	 * @return void
	 */
	public static function customizer_phone_field_notice( $control ) {
		if ( ! self::is_checkout_page_using_block() ) {
			return;
		}

		$msg = esc_html__( 'This field has no effect when Checkout Blocks are in use. To change the status of the phone field, please update the settings of the Address block found inside the Checkout block on your Checkout page.', 'flux-checkout' );

		$control->description = sprintf( '<div class="notice notice-info">%s</div>', $msg );
	}

	/**
	 * If checkout page is using checkout block.
	 *
	 * @return bool
	 */
	public static function is_checkout_page_using_block() {
		$checkout_page = get_post( wc_get_page_id( 'checkout' ) );

		if ( empty( $checkout_page ) ) {
			return false;
		}

		$checkout_page_content = $checkout_page->post_content;

		return has_block( 'woocommerce/checkout', $checkout_page_content );
	}
}
