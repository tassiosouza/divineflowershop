<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class KA_Rate_Limit_Logs_Table extends WP_List_Table {
	private $logs;

	public function __construct( $logs ) {
		parent::__construct( array(
			'singular' => 'log',
			'plural'   => 'logs',
			'ajax'     => false,
		) );
		$this->logs = $logs;
	}

	public function get_columns() {
		return array(
			'ip_address' => __( 'IP Address', 'recaptcha_verification' ),
			'user_role'  => __( 'User Role', 'recaptcha_verification' ),
			'user_email' => __( 'Email', 'recaptcha_verification' ),
			'timestamp'  => __( 'Timestamp', 'recaptcha_verification' ),
			'delete'     => __( 'Delete', 'recaptcha_verification' ),
		);
	}

	protected function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ip_address':
				return esc_html( $item['ip_address'] );
			case 'user_role':
				return isset( $item['user_role'] ) && is_array( $item['user_role'] )
					? esc_html( implode( ', ', $item['user_role'] ) )
					: esc_html__( 'Guest', 'recaptcha_verification' );
			case 'user_email':
				return isset($item['user_email']) ? esc_html( $item['user_email'] ): '';
			case 'timestamp':
				return esc_html( $item['timestamp'] );
			case 'delete':
			$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

			$term_id    = isset($item['id']) ? $item['id'] : 1;
			$delete_url = admin_url( 'admin.php?page=ka_captcha&tab=checkout_rate_limit&subtab_rate=rate_log&paged=' . $paged . '&action=delete_log&log_id=' . $term_id . '&_wpnonce=' . wp_create_nonce('delete_log_nonce') );
			// Return the 'Delete' link
				return sprintf(
				'<a href="%s">%s</a>',
				esc_url( $delete_url ),
				esc_html__( 'Delete', 'recaptcha_verification' )
			);

			default:
				return '';
		}
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$per_page              = 15;
		$current_page          = $this->get_pagenum();

		$total_items = count( $this->logs );

		$this->logs = array_slice( $this->logs, ( $current_page - 1 ) * $per_page, $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$this->items = $this->logs;
	}
}
