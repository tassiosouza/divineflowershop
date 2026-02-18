<?php

/**
* The plugin bootstrap file
*
* This file is read by WordPress to generate the plugin information in the plugin
* admin area. This file also includes all of the dependencies used by the plugin,
* registers the activation and deactivation functions, and defines a function
* that starts the plugin.
*
* @link              https://www.wpslash.com
* @since             1.1.8
* @package           WPSlash_Tipping
*
* @wordpress-plugin
* Plugin Name:       Tipping for WooCommerce
* Plugin URI:        wpslash-tipping
* Description:       Adds the ability for customers to add a percentage or their own tip on checkout 
* Version:           1.1.8
* Author:            WPSlash
* Author URI:        https://www.wpslash.com
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       wpslash-tipping
* Domain Path:       /languages
* WC requires at least: 3.4
* WC tested up to: 10.0.4
* Woo: 6511571:20700e5820bff44190a3dd4ec0406639
* Copyright: © 2009-2020 WooCommerce.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
define('WPSTIP_FILE', __DIR__ );
define('WPSTIP_DIR', plugin_dir_path( __FILE__ ) );

define('WPSTIP_DIR_URL', plugin_dir_url(__FILE__) );

add_action('plugins_loaded', 'WPSlash_Tipping_load_textdomain');
function WPSlash_Tipping_load_textdomain() {
	load_plugin_textdomain( 'wpslash-tipping', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	require_once __DIR__ . '/inc/reports.php';
	new WPSlash_Tipping_Reports();
}
if (!function_exists('is_plugin_active_for_network')) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php' ;

}

//check if WooCommerce is activated
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php') ) {



		//add_action( 'woocommerce_settings_tabs', 'wpslash_tipping_add_settings_tab' );
	function wpslash_tipping_add_settings_tab() {
		$current_tab =  '';
		if (isset($_GET['tab'])) {
		$current_tab = ( isset($_GET['tab']) == 'wpslash_tipping' ) ? 'nav-tab-active' : '';

		}   
		echo '<a href="admin.php?page=wc-settings&amp;tab=wpslash_tipping" class="nav-tab ' . esc_html($current_tab) . '">' . esc_html__( 'Tipping', 'wpslash-tipping' ) . '</a>';
	}

	add_filter( 'woocommerce_settings_tabs_array', 'wpslash_tipping_woocommerce_settings_tabs_array_filter', 99, 1 );


	function wpslash_tipping_woocommerce_settings_tabs_array_filter( $array ) {

	$array['wpslash_tipping'] = esc_html__( 'Tipping', 'wpslash-tipping' );
	return $array;
	}



	add_action( 'woocommerce_settings_wpslash_tipping', 'wpslash_tipping_tab_content' );
	function wpslash_tipping_tab_content() { 

		woocommerce_admin_fields( wpslash_tipping_get_settings() );
	}



	add_action('woocommerce_blocks_loaded', function () {
	
		if ( interface_exists( 'Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface' ) ) {

require_once __DIR__ . '/inc/blocks.php';

	


		}
	});


	function wpslash_tipping_enqueue_styles() {
		
		if (is_checkout()) {

			
			$styling_disabled  = get_option('wc_settings_tab_wpslash_tipping_styling_disabled', 'no');


			wp_enqueue_style('wpslash-tipping-css', WPSTIP_DIR_URL . '/css/main.css', array(), '0.1.0', 'all');
			if ('no'== $styling_disabled) {
				wp_enqueue_style('wpslash-tipping-css-styling', WPSTIP_DIR_URL . '/css/styling.css', array(), '0.1.0', 'all');

			}
			wp_enqueue_script('wpslash-tipping-js', WPSTIP_DIR_URL . '/js/main.js', array( 'jquery' ), '0.1.0');
			wp_localize_script( 'wpslash-tipping-js', 'wpslash_tipping_obj',
			array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'security' => wp_create_nonce('wpslash_tip_security'),

			)
			);
		}   
	}
	add_action( 'wp_footer', 'wpslash_tipping_enqueue_styles' );



	function wpslash_tipping_get_settings() {
		$settings = array(
		'section_title' => array(
			'name'     => __( 'Tipping Settings', 'wpslash-tipping' ),
			'type'     => 'title',
			'desc'     => '',
			'id'       => 'wc_settings_tab_WPSlash_Tipping_section_title_acs',
			),

		'tipping_title' => array(
			'name' => __( 'Title', 'wpslash-tipping' ),
			'type' => 'text',
			'desc' => __( 'This is how the title will appear above', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_title',
			),

		'tipping_amount_default' => array(
			'name' => __( 'Default Tip Amount', 'wpslash-tipping' ),
			'type' => 'text',
			'desc' => __( 'This will be the default tip amount', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_default_amount',
			),

		'tipping_title_enabled' => array(
			'name' => __( 'Enable Tipping Title', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => '',
			'id'   => 'wc_settings_tab_wpslash_tipping_title_enabled',
			'default'  => 'no',
			),
			'custom_tip_inside_buttons' => array(
				'name' => __( 'Move Custom Tip inside Tipping Buttons', 'wpslash-tipping' ),
				'type' => 'checkbox',
				'desc' => __('This option will move  Custom Tip amount inside  Tipping Pecentages & Prededfined Tipping Buttons', 'wpslash-tipping'),
				'id'   => 'wc_settings_tab_wpslash_tipping_custom_tip_inside_buttons',
				'default'  => 'no',
				),
		'tipping_taxable' => array(
			'name' => __( 'Taxable', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => '',
			'id'   => 'wc_settings_tab_wpslash_tipping_taxable',
			'default'  => 'no',

			),
		'tipping_tax_class' => array(
			'name' => esc_html__('Tax Class', 'wpslash-tipping'),
			'type' => 'select',
			'desc' => esc_html__('Select the Tax Class if Tip is Taxable', 'wpslash-tipping'),
			'id' => 'wc_settings_tab_wpslash_tipping_tax_class',
			'options' => wc_get_product_tax_class_options(),
			),


			'tipping_percentage_enabled' => array(
			'name' => __( 'Enable Tipping Percentage Buttons', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => esc_html__('Will enable percentage buttons to be added based on the below percentage options', 'wpslash-tipping'),
			'id'   => 'wc_settings_tab_wpslash_tipping_percentage_enabled',
			),
			'tipping_percentage' => array(
			'name' => __( 'Percentages(Comma Sepatated)', 'wpslash-tipping' ),
			'type' => 'text',
			'desc' => __( 'Comma seperated tipping options like 10,20,30', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_percentage',
			),
			'tipping_percentage_display' => array(
			'name' => esc_html__('Percentage Display Option', 'wpslash-tipping'),
			'type' => 'select',
			'desc' => esc_html__('Tipping Percentages Butons Display style', 'wpslash-tipping'),
			'id' => 'wc_settings_tab_wpslash_tipping_percentage_display',
			'options' => array(
				'percentage' => esc_html__('Add 20% Tip (Percentage Display)', 'wpslash-tipping'),
				'amount' => esc_html__('Add $20 Tip (Tip Amount)', 'wpslash-tipping'),
				'percentage-amount' => esc_html__('Add 20% ($7.5) Tip  (Tip Amount)', 'wpslash-tipping'),

			),
			),

			'tipping_buttons_enabled' => array(
			'name' => __( 'Enable Tipping Buttons with Predefined Amounts', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => esc_html__('Will enable tippings buttons to be added based on the below amounts you will give', 'wpslash-tipping'),
			'id'   => 'wc_settings_tab_wpslash_tipping_buttons_enabled',
			),
			'tipping_buttons' => array(
			'name' => __( 'Amounts(Comma Sepatated)', 'wpslash-tipping' ),
			'type' => 'text',
			'desc' => __( 'Comma seperated amounts like 10,20,30', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_buttons',
			),

			'tipping_buttons_display' => array(
			'name' => esc_html__('Amounts Display Option', 'wpslash-tipping'),
			'type' => 'select',
			'desc' => esc_html__('How buttons will be displayed', 'wpslash-tipping'),
			'id' => 'wc_settings_tab_wpslash_tipping_buttons_display',
			'options' => array(
				'percentage' => esc_html__('Add 20% Tip (Percentage Display)', 'wpslash-tipping'),
				'amount' => esc_html__('Add $20 Tip (Tip Amount)', 'wpslash-tipping'),
				'percentage-amount' => esc_html__('Add 20% ($7.5) Tip  (Tip Amount)', 'wpslash-tipping'),

			),
			),

		'tipping_enabled' => array(
			'name' => __( 'Enable Tipping Feature', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => '',
			'id'   => 'wc_settings_tab_wpslash_tipping_enabled',
			'default'  => 'no',
			),
		'section_end' => array(
			'type' => 'sectionend',
			'id' => 'wc_settings_tab_WPSlash_Tipping_section_end',
			),
		'section_title_styling' => array(
			'name'     => __( 'Styling Options', 'wpslash-tipping' ),
			'type'     => 'title',
			'desc'     => '',
			'id'       => 'wc_settings_tab_WPSlash_Tipping_section_title_styling_options',
			),

			'tipping_disable_styling' => array(
			'name' => __( 'Disable StyleSheet Loading', 'wpslash-tipping' ),
			'type' => 'checkbox',
			'desc' => esc_html__( 'Secting this option will disable any CSS loaded from the plugin.', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_styling_disabled',
			'default'  => 'no',
			),
		 'tipping_body_background_color' => array(
			'name' => __( 'Body Background Color', 'wpslash-tipping' ),
			'type' => 'color',
			'desc' => __( 'Background Color of Body', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_body_background_color',
			'default'=>'transparent',
			),
		 'tipping_title_background_color' => array(
			'name' => __( 'Title Background Color', 'wpslash-tipping' ),
			'type' => 'color',
			'desc' => __( 'Background Color of Title', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_title_background_color',
			'default'=>'#ffffff',
			),
		  'tipping_title_text_color' => array(
			'name' => __( 'Title Text Color', 'wpslash-tipping' ),
			'type' => 'color',
			'desc' => __( 'Text Color of Title', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_title_text_color',
			'default'=>'#000000',
			),
		'tipping_button_background_color' => array(
			'name' => __( 'Buttons Background Color', 'wpslash-tipping' ),
			'type' => 'color',
			'desc' => __( 'Background Color of Buttons', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_button_background_color',
			'default'=>'#28a745',
			),
		'tipping_button_text_color' => array(
			'name' => __( 'Buttons Text Color', 'wpslash-tipping' ),
			'type' => 'color',
			'desc' => __( 'Text Color of Buttons', 'wpslash-tipping' ),
			'id'   => 'wc_settings_tab_wpslash_tipping_button_text_color',
			'default'=>'#ffffff',
			),
		'section_end_styling' => array(
			'type' => 'sectionend',
			'id' => 'wc_settings_tab_WPSlash_Tipping_section_end_styling_options',
			),
		);
		return apply_filters( 'wc_settings_tab_WPSlash_Tipping_settings', $settings );
	}



	add_action('woocommerce_settings_save_wpslash_tipping', 'save_wpslash_tipping_settings');

	function save_wpslash_tipping_settings() {

		woocommerce_update_options( wpslash_tipping_get_settings() );
	}






	require_once __DIR__ . '/inc/activation.hook.php';
	require_once __DIR__ . '/inc/calc.hook.php';
	require_once __DIR__ . '/inc/checkout.hook.php';
	require_once __DIR__ . '/inc/ajax.hook.php';
	require_once __DIR__ . '/inc/wc-analytics.php';
	require_once __DIR__ . '/inc/wc-rest.php';

	







add_filter(
	'__experimental_woocommerce_blocks_add_data_attributes_to_namespace',
	function ( $allowed_namespaces ) {
		$allowed_namespaces[] = 'wpslash-tipping';
		return $allowed_namespaces;
	},
	10,
	1
);


	add_action( 'before_woocommerce_init', function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );

}//end check if WooCommerce is activated
