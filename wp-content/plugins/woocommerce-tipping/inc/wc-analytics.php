<?php

function wpslash_tipping_add_wc_analytics_register_script() {
 
	if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_page() ) {
		return;
	}
 
	$script_path       = WPSTIP_DIR_URL.'/build/index.js';
	$script_asset_path = WPSTIP_DIR . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require( $script_asset_path )
		: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
	$script_url = plugins_url( $script_path, __FILE__ );
 
	wp_register_script(
		'woocommerce-tipping-wc-analytics',
		$script_path,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);
 
 
	wp_enqueue_script( 'woocommerce-tipping-wc-analytics' );
}
 
add_action( 'admin_enqueue_scripts', 'wpslash_tipping_add_wc_analytics_register_script' );



add_filter( 'woocommerce_analytics_report_menu_items', 'wpslash_tipping_add_tips_to_analytics_menu' );
function wpslash_tipping_add_tips_to_analytics_menu( $report_pages ) {
	$report_pages[] = array(
		'id' => 'tips',
		'title' => __('Tips', 'wpslash-tipping'),
		'parent' => 'woocommerce-analytics',
		'path' => '/analytics/tips',
	);
	return $report_pages;
}



add_filter('woocommerce_rest_prepare_report_orders', 'wpslash_tipping_wc_analytics_prepare_response_item', 10, 3 );
function wpslash_tipping_wc_analytics_prepare_response_item( $response, $report, $request) {
	$report['tip_amount'] = get_post_meta($report['order_id'], '_wpslash_tip', true);
	return rest_ensure_response( $report );

}


add_filter( 'woocommerce_analytics_orders_stats_query_args', 'wpslash_tipping_wc_analytics_filter_query', 10, 1 );
add_filter('woocommerce_analytics_orders_query_args', 'wpslash_tipping_wc_analytics_filter_query', 10, 1);



function wpslash_tipping_wc_analytics_filter_query( $args) {

	if ( isset( $_GET['with_tip'] ) ) { 
		
		$args['with_tip'] = true;
	}



	return $args;
}


function add_join_subquery( $clauses, $context ) {
	global $wpdb;
 
	$clauses[] = "JOIN {$wpdb->postmeta} with_tip_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = with_tip_postmeta.post_id";
	return array();

	return $clauses;
}
 
//add_filter( 'woocommerce_analytics_clauses_join', 'add_join_subquery', 10, 2 );



function add_where_subquery( $clauses, $context ) {
 
 
		 $clauses[] = "AND with_tip_postmeta.meta_key = '_wpslash_tip'";
   

	return array();
	return $clauses;
}
 
//add_filter( 'woocommerce_analytics_clauses_where', 'add_where_subquery', 10, 2 );



function add_select_subquery( $clauses, $context ) {

	$clauses[] = ', with_tip_postmeta.meta_value AS tip_amount';
	return array();
	return $clauses;
}
 
//add_filter( 'woocommerce_analytics_clauses_select', 'add_select_subquery', 10, 2 );






