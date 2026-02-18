<?php
/**
 * WDS Admin class.
 *
 * @package Iconic_WDS
 */

namespace Iconic_WDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS Product Specific settings class.
 */
class OverrideSettings {

	/**
	 * Run.
	 */
	public static function run() {
		// Add Delivery day and min date to product category taxonomy.
		add_action( 'product_cat_add_form_fields', array( __CLASS__, 'add_product_category_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( __CLASS__, 'edit_term_product_cat_fields' ), 10, 2 );
		add_action( 'created_product_cat', array( __CLASS__, 'save_product_category_data' ) );
		add_action( 'edited_product_cat', array( __CLASS__, 'save_product_category_data' ) );

		// Add Delivery day and min date to shipping meta box on product page.
		add_action( 'woocommerce_product_options_shipping_product_data', array( __CLASS__, 'product_tab_content' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_data_tab_fields' ) );

		// Show error if there is a date conflict and disable the datepicker.
		add_action( 'template_redirect', array( __CLASS__, 'display_conflict_error_on_checkout_page' ), 10 );
	}

	/**
	 * Add product category fields to add product category screen.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public static function add_product_category_fields( $taxonomy ) {
		$weekdays = Helpers::get_weekdays();
		?>
		<div class='form-field iconic-wds-add-cat-fields iconic-wds-add-cat-fields--min-date'>
			<label><?php esc_html_e( 'Delivery Days', 'jckwds' ); ?></label>

			<label class="iconic-wds-add-cat-fields__label iconic_wds_days--override" data-override-target=".iconic-wds-add-cat-fields__label">
				<input class="iconic-wds-add-cat-fields__days" name="iconic_wds_product_cat_days_override" type="checkbox" value="1"><?php esc_html_e( 'Override', 'jckwds' ); ?>
			</label>
			<div class="iconic-wds-add-cat-fields__override_days">
				<?php
				foreach ( $weekdays as $day_key => $day_value ) {
					echo sprintf( '<label class="iconic-wds-add-cat-fields__label" style="display:none"><input class="iconic-wds-add-cat-fields__days" name="iconic_wds_product_cat_days[]" type="checkbox" value="%s">%s</label>', esc_attr( $day_key ), esc_html( $day_value ) );
				}
				?>
			</div>
			<p class="description"><?php esc_html_e( 'Delivery Slots: Only allow delivery/pickup on these days if products in this category are in the cart.', 'jckwds' ); ?></p>
		</div>
		<div class='form-field iconic-wds-add-cat-fields iconic-wds-add-cat-fields--min-date'>
			<label><?php esc_html_e( 'Lead Time', 'jckwds' ); ?></label>
			<label><input class="iconic-wds-add-cat-fields__min_date" type="number" name="iconic_wds_product_cat_min_day"></label>
			<p class="description"><?php esc_html_e( 'Delivery slots: Set minimum selectable date for this category.', 'jckwds' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Edit product category page.
	 *
	 * @param Object $term     Term.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public static function edit_term_product_cat_fields( $term, $taxonomy ) {
		// Add weekday and min-date fields.
		$weekdays               = Helpers::get_weekdays();
		$term_specific_settings = self::get_term_specific_settings( $term->term_id );
		?>
		<tr class='form-field iconic-wds-edit-cat-fields'>
			<th>
				<div class="form-field">
					<label><?php esc_html_e( 'Delivery Days', 'jckwds' ); ?></label>
				</div>
			</th>
			<td>
				<label class="iconic-wds-edit-cat-fields__day_label iconic_wds_days--override" data-override-target=".iconic-wds-edit-cat-fields__day_label">
					<input class="iconic-wds-edit-cat-fields__day" name="iconic_wds_product_cat_days_override" type="checkbox" value="1" <?php checked( $term_specific_settings['override_days'], '1' ); ?>><?php esc_html_e( 'Override', 'jckwds' ); ?>
				</label>

				<div class="iconic-wds-edit-cat-fields__override_days">
					<?php
					foreach ( $weekdays as $day_key => $day_value ) {
						$checked = in_array( $day_key, $term_specific_settings['selected_days'], true ) ? '1' : '';
						$style   = '1' !== $term_specific_settings['override_days'] ? 'display:none' : '';
						echo sprintf( '<label class="iconic-wds-edit-cat-fields__day_label" style="%s"><input class="iconic-wds-edit-cat-fields__day" name="iconic_wds_product_cat_days[]" type="checkbox" value="%s" %s>%s</label>', esc_attr( $style ), esc_attr( $day_key ), checked( $checked, '1', false ), esc_html( $day_value ) );
					}
					?>
				</div>
				<p class="description"><?php esc_html_e( 'Delivery Slots: Only allow delivery/pickup on these days if products in this category are in the cart.', 'jckwds' ); ?></p>
			</td>
		</tr>
		<tr class='form-field iconic-wds-edit-cat-fields'>
			<th>
				<div class="form-field">
					<label><?php esc_html_e( 'Lead Time', 'jckwds' ); ?></label>
				</div>
			</th>
			<td>
				<label class='iconic-wds-edit-cat-fields__min_day_label'>
					<input type="number" class="iconic-wds-edit-cat-fields__min_day" name="iconic_wds_product_cat_min_day" value="<?php echo esc_attr( $term_specific_settings['lead_time'] ); ?>" /></label>
				<p class="description"><?php esc_html_e( 'Delivery Slots: Modify the global lead time setting when products in this category are in the cart.', 'jckwds' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save product category data.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_product_category_data( $term_id ) {
		$days     = filter_input( INPUT_POST, 'iconic_wds_product_cat_days', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$days     = empty( $days ) ? array( 'any' ) : $days;
		$override = filter_input( INPUT_POST, 'iconic_wds_product_cat_days_override', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$min_day  = filter_input( INPUT_POST, 'iconic_wds_product_cat_min_day', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		update_term_meta( $term_id, 'iconic_wds_weekdays_override', $override );
		update_term_meta( $term_id, 'iconic_wds_weekdays', $days );
		update_term_meta( $term_id, 'iconic_wds_mindate', $min_day );
	}

	/**
	 * Add WDS tab field.
	 */
	public static function product_tab_content() {
		global $woocommerce, $post;

		$weekdays                  = Helpers::get_weekdays();
		$product_specific_settings = self::get_product_specific_settings( $post->ID );
		?>
		<div id="iconic-wds-delivery-slot-product-fields" class="options_group">
			<h2><?php esc_html_e( 'Delivery Slots settings', 'jckwds' ); ?></h2>
			<p class="form-field">
				<label><?php esc_html_e( 'Delivery Days', 'jckwds' ); ?></label>

				<label class="iconic-wds-edit-product__day_label iconic_wds_days--override" data-override-target='.iconic-wds-edit-product__day_label'>
					<input class="iconic-wds-edit-product__day" type="checkbox" name="iconic_wds_weekdays_override" value="1" <?php checked( '1', $product_specific_settings['override_days'] ); ?>>
					<?php esc_html_e( 'Override', 'jckwds' ); ?>
				</label>
				<?php
				foreach ( $weekdays as $weekday_key => $weekday ) {
					$checked = in_array( $weekday_key, $product_specific_settings['selected_days'], true ) ? ' checked="checked" ' : '';
					$style   = '1' !== $product_specific_settings['override_days'] ? 'display:none' : '';
					echo sprintf( '<label class="iconic-wds-edit-product__day_label" style="%s"><input class="iconic-wds-edit-product__day" type="checkbox" name="iconic_wds_weekdays[]" value="%s" %s> %s</label>', esc_attr( $style ), esc_attr( $weekday_key ), esc_attr( $checked ), esc_html( $weekday ) );
				}
				?>
			</p>

			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'iconic_wds_mindate',
					'type'        => 'number',
					'class'       => 'iconic-wds-edit-product__min_day',
					'label'       => __( 'Lead time', 'jckwds' ),
					'description' => __( 'Modify the global lead time setting when this product is in the cart.', 'jckwds' ),
					'desc_tip'    => true,
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Save product meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_data_tab_fields( $post_id ) {
		$days     = filter_input( INPUT_POST, 'iconic_wds_weekdays', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$min_day  = filter_input( INPUT_POST, 'iconic_wds_mindate', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$override = filter_input( INPUT_POST, 'iconic_wds_weekdays_override', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		update_post_meta( $post_id, 'iconic_wds_weekdays', $days );
		update_post_meta( $post_id, 'iconic_wds_weekdays_override', $override );
		update_post_meta( $post_id, 'iconic_wds_mindate', $min_day );
	}

	/**
	 * When multiple products have overridden the global days settings
	 * and there is conflict between the days then:
	 * 1. Show a notice.
	 * 2. Set has_date_conflict parameter to true.
	 */
	public static function display_conflict_error_on_checkout_page() {
		if ( ! is_checkout() ) {
			return;
		}

		global $iconic_wds_dates;

		$conflict = self::get_conflict_if_exists( $iconic_wds_dates->cart->get_products_ids() );

		if ( empty( $conflict ) || ! empty( $conflict['common_days'] ) ) {
			return;
		}

		$message = Helpers::get_conflict_error_message( $conflict );

		wc_add_notice(
			$message,
			'error',
			array(
				'iconic-wds-update-checkout' => true,
			)
		);

		add_filter( 'iconic_wds_checkout_var_has_date_conflict', '__return_true' );
	}

	/**
	 * Get conflict info if an conflict exists.
	 *
	 * @param array $product_ids Product IDs.
	 *
	 * @return array|false
	 */
	public static function get_conflict_if_exists( $product_ids ) {
		/**
		 * Cache.
		 */
		static $conflict = null;

		if ( null !== $conflict && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			return $conflict;
		}

		$overrides = array();

		// Get those products that have overridden the global days settings.
		foreach ( $product_ids as $product_id ) {
			$days = self::get_product_specific_days_setting_for_product( $product_id );

			if ( ! is_array( $days ) ) {
				continue;
			}

			$overrides[] = array(
				'product_id' => $product_id,
				'days'       => $days,
			);
		}

		// If there is only one overriding product in cart then return false.
		$count = count( $overrides );
		if ( $count < 2 ) {
			$conflict = false;
			return false;
		}

		$conflict_flag = false;

		$common_days = false;

		// Check delivery dates.
		for ( $i = 0; $i < $count - 1; $i ++ ) {
			$current_item = $overrides[ $i ];
			$next_item    = $overrides[ $i + 1 ];

			if ( $current_item['days'] !== $next_item['days'] ) {
				$conflict_flag = true;

				// If it is the first element then set the common days.
				$common_days = false === $common_days ? $current_item['days'] : $common_days;

				// Get common days.
				$common_days = array_intersect( $common_days, $next_item['days'] );
			}
		}

		// If no conflict then return.
		if ( ! $conflict_flag ) {
			$conflict = false;
			return false;
		}

		$conflict = array(
			'common_days' => $common_days,
			'overrides'   => $overrides,
		);

		return $conflict;
	}

	/**
	 * Check all the cart items to see if any of them override the delivery days.
	 * If yes, return the overriden days.
	 *
	 * @param array $product_ids Product IDs.
	 *
	 * @return array|false Array of specific days or false if no products override day settings.
	 */
	public static function get_product_specific_days_setting( $product_ids ) {
		$cache_key                    = md5( wp_json_encode( $product_ids ) );
		static $product_specific_days = array();

		if ( array_key_exists( $cache_key, $product_specific_days ) && ! defined( 'ICONIC_WDS_NO_CACHE' ) ) {
			return $product_specific_days[ $cache_key ];
		}

		$cart_weekdays = array();
		$found_flag    = false;

		foreach ( $product_ids as $product_id ) {
			$product_days = self::get_product_specific_days_setting_for_product( $product_id );

			if ( is_array( $product_days ) ) {
				$found_flag    = true;
				$cart_weekdays = array_merge( $cart_weekdays, $product_days );
			}
		}

		$result = $found_flag ? array_unique( $cart_weekdays ) : false;

		$product_specific_days[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * Get allowed days for the given product.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return array|false False if product and it's categories are not overriding default days.
	 */
	public static function get_product_specific_days_setting_for_product( $product_id ) {
		$settings         = self::get_product_specific_settings( $product_id );
		$product_override = $settings['override_days'];
		$product_weekdays = $settings['selected_days'];

		if ( '1' === $product_override ) {
			return $product_weekdays;
		}

		// Get category override days.
		$weekdays           = array();
		$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

		$category_override_flag = false;

		foreach ( $product_categories as $category ) {
			$cat_settings = self::get_term_specific_settings( $category );
			$cat_override = $cat_settings['override_days'];
			$cat_weekdays = $cat_settings['selected_days'];

			if ( '1' === $cat_override ) {
				$category_override_flag = true;
				$weekdays               = array_merge( $weekdays, $cat_weekdays );
				continue;
			}
		}

		return $category_override_flag ? array_unique( $weekdays ) : false;
	}

	/**
	 * Get lead time for the given product.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return int|false Return false if product and it's categories are not overriding default lead time.
	 */
	public static function get_lead_time_for_product( $product_id ) {
		$settings = self::get_product_specific_settings( $product_id );

		if ( false !== $settings['lead_time'] ) {
			return (int) $settings['lead_time'];
		}

		// Get lead time for categories.
		$return_min_date    = array();
		$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

		foreach ( $product_categories as $category ) {
			$cat_settings = self::get_term_specific_settings( $category );

			if ( false !== $cat_settings['lead_time'] ) {
				$return_min_date[] = $cat_settings['lead_time'];
			}
		}

		return ! empty( $return_min_date ) ? min( $return_min_date ) : false;
	}

	/**
	 * Get product specific settings.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return array
	 */
	public static function get_product_specific_settings( $product_id ) {
		$settings                  = array();
		$settings['override_days'] = get_post_meta( $product_id, 'iconic_wds_weekdays_override', true );
		$settings['lead_time']     = get_post_meta( $product_id, 'iconic_wds_mindate', true );
		$settings['lead_time']     = is_numeric( $settings['lead_time'] ) ? (int) $settings['lead_time'] : false;
		$settings['selected_days'] = get_post_meta( $product_id, 'iconic_wds_weekdays', true );
		$settings['selected_days'] = is_array( $settings['selected_days'] ) ? array_map( 'absint', $settings['selected_days'] ) : array();

		return apply_filters( 'iconic_wds_get_product_specific_settings', $settings, $product_id );
	}

	/**
	 * Get term specific settings.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return array
	 */
	public static function get_term_specific_settings( $term_id ) {
		$settings                  = array();
		$settings['override_days'] = get_term_meta( $term_id, 'iconic_wds_weekdays_override', true );
		$settings['lead_time']     = get_term_meta( $term_id, 'iconic_wds_mindate', true );
		$settings['lead_time']     = is_numeric( $settings['lead_time'] ) ? (int) $settings['lead_time'] : false;
		$settings['selected_days'] = get_term_meta( $term_id, 'iconic_wds_weekdays', true );
		$settings['selected_days'] = is_array( $settings['selected_days'] ) ? array_map( 'absint', $settings['selected_days'] ) : array();

		return apply_filters( 'iconic_wds_get_term_specific_settings', $settings, $term_id );
	}
}
