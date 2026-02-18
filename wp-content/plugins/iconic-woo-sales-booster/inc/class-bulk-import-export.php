<?php
/**
 * Bulk import and export data.
 *
 * @see https://iconicwp.com/docs/iconic-sales-booster-for-woocommerce/how-to-bulk-export-and-import-data-in-iconic-sales-booster-for-woocommerce
 * @package iconic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Bulk_Import_Export class.
 *
 * @since 1.12.0
 */
class Iconic_WSB_Bulk_Import_Export {

	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ), 15 );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		add_action( 'woocommerce_product_export_row', array( __CLASS__, 'add_wsb_field_in_product_export_page' ) );

		add_filter( 'iconic_wsb_admin_l10n_data_script', array( __CLASS__, 'add_data_script' ) );
		add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'add_columns_to_export' ) );
		add_filter( 'woocommerce_product_export_row_data', array( __CLASS__, 'add_wsb_data' ), 10, 2 );
		add_filter( 'woocommerce_product_importer_parsed_data', array( __CLASS__, 'handle_wsb_data_to_import' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'filter_out_options_to_wsb_columns_' ), 10, 2 );
		add_filter( 'woocommerce_exporter_product_types', array( __CLASS__, 'remove_product_variation' ), 50 );
	}

	/**
	 * Get the columns name and meta_key name associated.
	 *
	 * This function returns an array with the following strucuture
	 *
	 * [
	 *    [column_name] => 'meta_key_name',
	 *    [...]
	 * ]
	 *
	 * @return array
	 */
	protected static function get_columns_name_and_meta_key() {
		$columns_name_and_meta_key = array();

		foreach ( self::get_columns() as $key => $value ) {
			$columns_name_and_meta_key[ strtolower( $value['label'] ) ] = $value['meta_key_name'];
		}

		return $columns_name_and_meta_key;
	}

	/**
	 * Get columns to be exported.
	 *
	 * @return array
	 */
	protected static function get_columns() {
		return array(
			'frequently_bought_together_title'          => array(
				'label'         => __( 'FBT Title', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_fbt_title',
			),
			'frequently_bought_together_description'    => array(
				'label'         => __( 'FBT Description', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_fbt_sales_pitch',
			),
			'frequently_bought_together_products'       => array(
				'label'         => __( 'FBT Products', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_product_page_order_bump_ids',
			),
			'frequently_bought_together_unchecked_by_default' => array(
				'label'         => __( 'FBT Unchecked by Default', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_fbt_set_unchecked',
			),
			'frequently_bought_together_discount_value' => array(
				'label'         => __( 'FBT Discount Value', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_fbt_discount_value',
			),
			'frequently_bought_together_discount_type'  => array(
				'label'         => __( 'FBT Discount Type', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_fbt_discount_type',
			),
			'after_add_to_cart_popup_products'          => array(
				'label'         => __( 'After Add to Cart Popup Products', 'iconic-wsb' ),
				'meta_key_name' => '_iconic_wsb_product_page_bump_modal_ids',
			),
		);
	}

	/**
	 * Add the WSB columns to be exported in the main script.
	 *
	 * @param array $data The localization (l10n) data.
	 * @return array
	 */
	public static function add_data_script( $data ) {
		$screen = get_current_screen();

		if ( empty( $screen ) || 'product_page_product_exporter' !== $screen->id ) {
			return $data;
		}

		$columns = array();

		// wrap in an array to preserve the order.
		foreach ( self::get_columns() as $key => $value ) {
			$columns[] = array(
				$key => $value['label'],
			);
		}

		$data['wsb_columns_to_export'] = $columns;

		return $data;
	}

	/**
	 * Add the WSB columns to be exported.
	 *
	 * @param array $column_names The columns ids and names array.
	 * @return array
	 */
	public static function add_columns_to_export( $column_names ) {
		foreach ( self::get_columns() as $key => $value ) {
			$column_names[ $key ] = $value['label'];
		}

		return $column_names;
	}

	/**
	 * Add the field "Export Iconic Sales Booster data?" to
	 * the Export Products page.
	 *
	 * @return void
	 */
	public static function add_wsb_field_in_product_export_page() {
		?>
		<tr>
			<th scope="row">
				<label for="woocommerce-exporter-meta"><?php esc_html_e( 'Export Iconic Sales Booster data?', 'iconic-wsb' ); ?></label>
			</th>
			<td>
				<input
					type="checkbox"
					id="wsb-export-data"
					name="wsb-export-data"
					value="1"
				/>
				<label
					for="wsb-export-data"
				>
					<?php esc_html_e( 'Include all Sales Booster data', 'iconic-wsb' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Check if the field `Export Iconic Sales Booster data?` is
	 * checked.
	 *
	 * @return boolean
	 */
	protected static function should_export_wsb_data() {
		if ( empty( $_POST['form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		$form = filter_input( INPUT_POST, 'form' );

		if ( empty( $form ) ) {
			return false;
		}

		parse_str( $form, $data );

		return ! empty( $data['wsb-export-data'] );
	}

	/**
	 * Add the WSB data to the product row.
	 *
	 * @param array      $row The row data.
	 * @param WC_Product $product The product.
	 * @return array
	 */
	public static function add_wsb_data( $row, $product ) {
		if ( ! self::should_export_wsb_data() ) {
			return $row;
		}

		$map_from_id_to_slug = function ( $product_ids ) {
			if ( ! is_array( $product_ids ) ) {
				return $product_ids;
			}

			$value = array_unique(
				array_filter(
					array_map(
						function( $product_id ) {
							$product = wc_get_product( $product_id );
							return $product ? $product->get_slug() : false;
						},
						$product_ids
					)
				)
			);

			return join( ' | ', $value );
		};

		foreach ( self::get_columns() as $key => $value ) {
			$row[ $key ] = get_post_meta( $product->get_ID(), $value['meta_key_name'], true );

			switch ( $key ) {
				case 'frequently_bought_together_products':
				case 'after_add_to_cart_popup_products':
					$row[ $key ] = $map_from_id_to_slug( $row[ $key ] );
					break;

				default:
					break;
			}
		}

		return $row;
	}

	/**
	 * Handle WSB data to import
	 *
	 * @param array $data The row data.
	 * @return array
	 */
	public static function handle_wsb_data_to_import( $data ) {
		if ( empty( $data ) ) {
			return $data;
		}

		$columns = self::get_columns_name_and_meta_key();

		foreach ( $data as $key => $value ) {
			if ( empty( $columns[ $key ] ) ) {
				continue;
			}

			$meta_key = $columns[ $key ];

			if (
				'_iconic_wsb_product_page_order_bump_ids' === $meta_key ||
				'_iconic_wsb_product_page_bump_modal_ids' === $meta_key
			) {
				$value = self::map_product_slugs_to_ids( $value );
			}

			$data['meta_data'][] = array(
				'key'   => $columns[ $key ],
				'value' => $value,
			);

			unset( $data[ $key ] );
		}

		return $data;
	}

	/**
	 * Map a string with product slugs to array of IDs.
	 *
	 * @param string $value The slugs of the products separated by ` | `.
	 *                      E.g.: hoodie | album.
	 * @return array
	 */
	protected static function map_product_slugs_to_ids( $value ) {
		/**
		 * Get the product ID by slug
		 *
		 * @param string $slug The product slug.
		 * @return int|false
		 */
		$get_product_id_by_slug = function( $slug ) {
			$slug = trim( $slug );

			if ( empty( $slug ) ) {
				return false;
			}

			$product_query = new WP_Query(
				array(
					'fields'         => 'ids',
					'name'           => $slug,
					'post_type'      => array( 'product', 'product_variation' ),
					'posts_per_page' => 1,
				)
			);

			if ( empty( $product_query->get_posts()[0] ) ) {
				return false;
			}

			return empty( $product_query->get_posts()[0] ) ? false : $product_query->get_posts()[0];
		};

		$value = explode( ' | ', $value );

		$value = array_filter(
			array_map( $get_product_id_by_slug, $value )
		);

		return $value;
	}

	/**
	 * Filter out options to WSB columns.
	 *
	 * By default, WooCommerce shows all columns as options
	 * to be selected in the process of importa data. To avoid
	 * a wrong column being selected, we remove all columns
	 * except the WSB column.
	 *
	 * @param array  $options The available options.
	 * @param string $item    The item name.
	 * @return array
	 */
	public static function filter_out_options_to_wsb_columns_( $options, $item ) {
		foreach ( self::get_columns() as $value ) {
			if ( strtolower( $value['label'] ) === $item ) {
				return array(
					$item => $value['label'],
				);
			}
		}

		return $options;
	}

	/**
	 * Remove product type `variation`.
	 *
	 * @param array $product_types The product types.
	 * @return array
	 */
	public static function remove_product_variation( $product_types ) {
		if ( ! self::should_export_wsb_data() ) {
			return $product_types;
		}

		if ( empty( $product_types['variation'] ) ) {
			return $product_types;
		}

		unset( $product_types['variation'] );

		return $product_types;
	}
}
