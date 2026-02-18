<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
class WooCommerce_Tipping_Blocks_Integration implements IntegrationInterface {



	public function get_name() {
		return 'wc-tipping-blocks';
	}

	public function initialize() {

		$script_path_integration       = WPSTIP_DIR . '/build/blocks-integration.js';
		$script_asset_path_integration = WPSTIP_DIR . '/build/blocks-integration.asset.php';


		$script_asset_integration      =file_exists( $script_asset_path_integration )
			? require $script_asset_path_integration  :  array( 'dependencies' => array( 'wp-plugins', 'wp-polyfill' ), 'version' => filemtime( $script_path_integration ) );
		$script_url_integration = WPSTIP_DIR_URL . 'build/blocks-integration.js';



		wp_register_script(
			'woocommerce-tipping-wc-checkout-blocks-integration',
			$script_url_integration,
			$script_asset_integration['dependencies'],
			$script_asset_integration['version'],
			true
		);
	   wp_enqueue_style('wpslash-tipping-block-css', WPSTIP_DIR_URL . '/css/main.css', array(), '0.1.0', 'all');
	   $styling_disabled  = get_option('wc_settings_tab_wpslash_tipping_styling_disabled', 'no');


		if ('no'== $styling_disabled) {
			wp_enqueue_style('wpslash-tipping-css-styling', WPSTIP_DIR_URL . '/css/styling.css', array(), '0.1.0', 'all');

		}


		$script_path       = WPSTIP_DIR . '/build/blocks.js';

		$script_asset_path = WPSTIP_DIR . '/build/blocks.asset.php';

		$script_asset      =  file_exists( $script_asset_path )
			? require $script_asset_path  :  array( 'dependencies' => array( 'wp-blocks', 'wc-blocks-checkout', 'wp-i18n', 'wp-data', 'wp-compose', 'wp-components', 'wp-editor', 'wp-element', 'wp-polyfill' ), 'version' => filemtime( $script_path ) );
		$script_url = WPSTIP_DIR_URL . 'build/blocks.js';

		wp_register_script(
			'woocommerce-tipping-wc-checkout-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);





		





		$script_path_frontend       = WPSTIP_DIR . 'build/blocks-frontend.js';
		$script_asset_path_frontend =   WPSTIP_DIR . 'build/blocks-frontend.asset.php';
		$script_asset_frontend      = file_exists( $script_asset_path_frontend )
			? require $script_asset_path_frontend  :  array( 'dependencies' => array( 'wc-blocks-checkout', 'wp-element', 'wp-polyfill' ), 'version' => filemtime( $script_path_frontend ) );
		$script_url_frontend = WPSTIP_DIR_URL . 'build/blocks-frontend.js';

		wp_register_script(
			'woocommerce-tipping-wc-checkout-blocks-frontend',
			$script_url_frontend,
			$script_asset_frontend['dependencies'],
			$script_asset_frontend['version'],
			true
		);
	}

	public function get_script_handles() {
		return array( 'woocommerce-tipping-wc-checkout-blocks-integration', 'woocommerce-tipping-wc-checkout-blocks-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {

		return array( 'woocommerce-tipping-wc-checkout-blocks-integration', 'woocommerce-tipping-wc-checkout-blocks' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'tipping_enabled' => get_option( 'wc_settings_tab_wpslash_tipping_enabled', false ),
			'tip_percentages' => !empty(get_option( 'wc_settings_tab_wpslash_tipping_percentage', true )) ? explode(',', get_option( 'wc_settings_tab_wpslash_tipping_percentage', true ))  : array( 5, 10, 20, 30 ),
			'buttons_background_color' => get_option( 'wc_settings_tab_wpslash_tipping_button_background_color', '#28a745' ),
			'buttons_text_color' => get_option( 'wc_settings_tab_wpslash_tipping_button_text_color', '#28a745' ),
			'body_background_color' => get_option( 'wc_settings_tab_wpslash_tipping_body_background_color', '#28a745' ),
			'title_background_color' => get_option( 'wc_settings_tab_wpslash_tipping_title_background_color', '#ffffff' ),
			'title_text_color' => get_option( 'wc_settings_tab_wpslash_tipping_title_text_color', '#ffffff' ),
			'taxable' => ( !empty(get_option( 'wc_settings_tab_wpslash_tipping_taxable', true )) ) && ( get_option( 'wc_settings_tab_wpslash_tipping_taxable', true ) =='yes' )  ?  true : false,
			'tax_class' => !empty(get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true ) : '',
			'tipping_percentage_enabled' => ( !empty(get_option( 'wc_settings_tab_wpslash_tipping_percentage_enabled', true )) ) && ( get_option( 'wc_settings_tab_wpslash_tipping_percentage_enabled', true ) =='yes' )  ?  true : false,
			'title' => !empty(get_option( 'wc_settings_tab_wpslash_tipping_title', true )) ? get_option( 'wc_settings_tab_wpslash_tipping_title', true ) : '',

			'default_tip_amount' => !empty(get_option( 'wc_settings_tab_wpslash_tipping_default_amount', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_default_amount', true ) : 0,
			'tipping_percentage_display' => get_option('wc_settings_tab_wpslash_tipping_percentage_display', 'percentage'),
			'tipping_buttons_display' => get_option('wc_settings_tab_wpslash_tipping_buttons_display', 'amount'),
			'tip_buttons' => !empty(get_option( 'wc_settings_tab_wpslash_tipping_buttons', false )) ? explode(',', get_option( 'wc_settings_tab_wpslash_tipping_buttons', array() ))  : array( 5, 10, 20, 30 ),
			'tipping_buttons_enabled' => ( !empty(get_option( 'wc_settings_tab_wpslash_tipping_buttons_enabled', true )) ) && ( 'yes' ==  get_option( 'wc_settings_tab_wpslash_tipping_buttons_enabled', 'no' ) )  ?  true : false,
			'move_custom_tip'=>( !empty(get_option( 'wc_settings_tab_wpslash_tipping_custom_tip_inside_buttons', 'no' )) ) && ( 'yes' ==  get_option( 'wc_settings_tab_wpslash_tipping_custom_tip_inside_buttons', 'no' ) )  ?  true : false,
			'tipping_default_amount' => get_option( 'wc_settings_tab_wpslash_tipping_enabled', false ),

		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		// As above, let's assume that WooCommerce_Example_Plugin_Assets::VERSION resolves to some versioning number our
		// extension uses.
		return 2.3;
	}
}

add_action(
	'woocommerce_blocks_mini-cart_block_registration',
	function ( $integration_registry ) {
		$integration_registry->register( new WooCommerce_Tipping_Blocks_Integration() );
	}
);
add_action(
	'woocommerce_blocks_cart_block_registration',
	function ( $integration_registry ) {
		
		$integration_registry->register( new WooCommerce_Tipping_Blocks_Integration() );
	}
);
add_action(
	'woocommerce_blocks_checkout_block_registration',
	function ( $integration_registry ) {

		$integration_registry->register( new WooCommerce_Tipping_Blocks_Integration() );
	}
);



add_action('woocommerce_init', 'wpslash_tipping_register_store_api');

function wpslash_tipping_register_store_api() {
	if (function_exists('woocommerce_store_api_register_update_callback')) {


		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'wpslash-tipping-for-woocommerce',
				'callback'  => function ( $data ) {
	
					$tipping_taxable=  ( !empty(get_option( 'wc_settings_tab_wpslash_tipping_taxable', true )) ) && ( get_option( 'wc_settings_tab_wpslash_tipping_taxable', true ) =='yes' )  ?  true : false;
					$tax_class =  !empty(get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true )) ?  get_option( 'wc_settings_tab_wpslash_tipping_tax_class', true ) : '';
					$is_pecentage = false; 
					$percentage = 0;
					$without_taxes_filter = apply_filters('wpslash_tipping_without_taxes_percentage_calculation', false);
					$tip = null;
					if (isset($data['percentage']) ||isset($data['amount'])  ) {
						if ($data['percentage']) {
							$is_pecentage = true;
							$percentage = intval( $data['percentage'] );
	
						}
	
	
						if ($is_pecentage) {
							$subtotal = WC()->cart->get_subtotal();
							$taxes  = WC()->cart->get_subtotal_tax();
							if ($without_taxes_filter) {
	
							$taxes = 0;
	
							}
	
	
	
							$tip =  ( ( $subtotal+$taxes ) * ( $percentage/100 ) );
							if ($tipping_taxable) {
							$wc_tax = new WC_Tax();
							$tax_rates = $wc_tax->find_rates( array( 'country' => WC()->customer->get_billing_country(), 'tax_class' => $tax_class ) );
							$tax_rate = 0;
								if ( ! empty($tax_rates) ) {
								$tax_rate = reset($tax_rates)['rate'];
								}
	
							$tip = $tip / ( ( 100+ $tax_rate )/100 );
							}
	
	
						} elseif (isset($data['amount'])) {
							$tip = floatval( $data['amount'] );
	
						}
	
							WC()->session->set( 'wpslash_tip_selected', $tip   );
	
					} else {
	
							 WC()->session->set( 'wpslash_tip_selected', false   );
	
	
					}
				},
			)
		);

	}
}
