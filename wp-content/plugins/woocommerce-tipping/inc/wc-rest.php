<?php

class WC_REST_WPSlash_Tipping_Controller {
	/**
	 * You can extend this class with
	 * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
	 * Found in packages/woocommerce-rest-api/src/Controllers/
	 */
	protected $namespace = 'wc-analytics';

	protected $rest_base = 'tips';


	public function get_tips( $request ) {
		$params  = $request->get_params();
		$return = array();

		$date_from = $params['after'];
		$date_to = $params['before'];
		$per_page = intval($params['per_page']);
		$order = $params['order'];
		$page = isset($params['page']) ? intval($params['page']) : 1;

	  
		$orders = wc_get_orders( array(
		'paginate' => true,

		'limit'        => $per_page, // Query all orders
		'paged'        =>$page,
		'orderby'      => 'date',
		'order'        => $order,
		'meta_key'     => '_wpslash_tip', // The postmeta key field
		'meta_compare' => 'EXISTS', // The comparison argument
		//'return' => 'ids',
		'date_created' => $date_from . '...' . $date_to,
		));
	   
		$all_orders = wc_get_orders( array(
		'limit'        => -1, // Query all orders
		'orderby'      => 'date',
		'order'        => $order,
		'meta_key'     => '_wpslash_tip', // The postmeta key field
		'meta_compare' => 'EXISTS', // The comparison argument
		'return' => 'ids',
		'date_created' => $date_from . '...' . $date_to,
		));

		$total_tips = 0;
		foreach ($all_orders as $order_id) {
			$order = wc_get_order($order_id);       
			$total_tips += floatval($order->get_meta('_wpslash_tip'));
		}

		foreach ($orders->orders as $order) {
			   
			  $return['orders'][] = $this->construct_tip_item_for_order_id($order);

		}

							$return['pages'] = $orders->max_num_pages;

							$return['total'] = $orders->total;
							$return['tips_total'] =    wc_format_decimal($total_tips, 2 );

		

		return $return;
	}



	public function construct_tip_item_for_order_id( $order ) {
		$order_id = $order->get_id();
		$return = array();
		$tip_amount = $order->get_meta('_wpslash_tip');
		if (!empty($tip_amount)) {  
			$return['date_created'] = $order->get_date_created()->date('Y-m-d');
			$return['customer'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ;
			$return['order_id'] = $order_id;

			$return['tip_amount'] = floatval($tip_amount);
			$return['order_total'] = floatval($order->get_total());

			return $return;

		} else {
			return array();
		}
	}

	

	public function register_routes() {


		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base, 
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_tips' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),

			)
		);
	}

	public function get_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}

if (!function_exists('wpslash_tipping_rest_api_endpoints')) {
	add_filter( 'woocommerce_rest_api_get_rest_namespaces', 'wpslash_tipping_rest_api_endpoints', 10, 1 );

	function wpslash_tipping_rest_api_endpoints( $controllers ) {
		$controllers['wc-analytics']['tipsr'] = 'WC_REST_WPSlash_Tipping_Controller';

		return $controllers;
	}
}
