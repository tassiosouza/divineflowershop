<?php
/**
 * Class Helpers.
 *
 * Useful helper functions.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Helpers.
 *
 * @class    Iconic_Flux_Helpers
 * @version  1.0.0
 */
class Iconic_Flux_Helpers {
	/**
	 * Get Google Fonts.
	 *
	 * @return array Google Fonts.
	 */
	public static function get_google_fonts() {
		require __DIR__ . '/admin/google-fonts.php';

		$google_fonts_all = iconic_flux_get_google_fonts();
		$google_fonts     = array();
		foreach ( $google_fonts_all as $font ) {
			$google_fonts[ $font['name'] ] = $font['name'];
		}

		return $google_fonts;
	}

	/**
	 * Get Web Safe Fonts.
	 *
	 * @return array Web Safe Fonts.
	 */
	public static function get_web_safe_fonts() {
		return array(
			'Arial, Helvetica, sans-serif'          => 'Arial',
			'~Arial Black~, Gadget, sans-serif'     => 'Arial Black',
			'~Comic Sans MS~, cursive, sans-serif'  => 'Comic Sans',
			'~Courier New~, Courier, monospace'     => 'Courier New',
			'Georgia, serif'                        => 'Geogia',
			'Impact, Charcoal, sans-serif'          => 'Impact',
			'~Lucida Console~, Monaco, monospace'   => 'Lucida Console',
			'~Lucida Sans Unicode~, ~Lucida Grande~, sans-serif' => 'Lucida Sans',
			'~Palatino Linotype~, ~Book Antiqua~, Palatino, serif' => 'Palatino',
			'Tahoma, Geneva, sans-serif'            => 'Tahoma',
			'~Times New Roman~, Times, serif'       => 'Times New Roman',
			'~Trebuchet MS~, Helvetica, sans-serif' => 'Trebuchet',
			'Verdana, Geneva, sans-serif'           => 'Verdana',
		);
	}

	/**
	 * Get Font Size (px).
	 *
	 * @return array Font Sizes.
	 */
	public static function get_font_size_px() {
		$font_size = array();
		for ( $i = 1; $i <= 150; $i++ ) {
			$font_size[ $i . 'px' ] = $i . 'px';
		}
		return $font_size;
	}

	/**
	 * Get Font Size (em).
	 *
	 * @return array Font Sizes.
	 */
	public static function get_font_size_em() {
		$font_size = array();
		for ( $i = 0.1; $i <= 3.1; $i += 0.1 ) {
			$font_size[ $i . 'em' ] = $i . 'em';
		}
		return $font_size;
	}

	/**
	 * Get Gradients.
	 *
	 * @return array Gradients.
	 */
	public static function get_gradients() {
		$gradients_json = json_decode( file_get_contents( dirname( __FILE__ ) . '/admin/gradients.json' ) );
		$gradients      = array();
		foreach ( $gradients_json as $data ) {
			$gradients[ join( ', ', $data->colors ) ] = $data->name;
		}
		asort( $gradients );
		return $gradients;
	}

	/**
	 * Get Classic Pallet.
	 *
	 * Loads the Material Design Pallet for the Classic Theme.
	 *
	 * @param boolean $is_accent Colour is Accent.
	 *
	 * @return array Colours.
	 */
	public static function get_classic_pallet( $is_accent = false ) {
		$colors = array(
			'#f44336' => 'red',
			'#E91E63' => 'pink',
			'#9C27B0' => 'purple',
			'#673AB7' => 'deep_purple',
			'#3F51B5' => 'indigo',
			'#2196F3' => 'blue',
			'#03A9F4' => 'light_blue',
			'#00BCD4' => 'cyan',
			'#009688' => 'teal',
			'#4CAF50' => 'green',
			'#8BC34A' => 'light_green',
			'#CDDC39' => 'lime',
			'#FFEB3B' => 'yellow',
			'#FFC107' => 'amber',
			'#FF9800' => 'orange',
			'#FF5722' => 'deep_orange',
		);
		if ( ! $is_accent ) {
			$colors['#795548'] = 'brown';
			$colors['#9E9E9E'] = 'grey';
			$colors['#607D8B'] = 'blue_grey';
		}

		return $colors;
	}

	/**
	 * Control Radio Classic Palette.
	 *
	 * Custom Control for the Classic Pallete Swatches.
	 *
	 * @param array $args Control Args.
	 *
	 * @return void
	 */
	public static function control_radio_classic_palette( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		foreach ( $args['choices'] as $value => $text ) {
			$field_id = sprintf( '%s_%s', $args['id'], $value );
			$checked  = $value === $args['value'] ? 'checked="checked"' : '';
			$text     = sprintf( '<span class="swatch__colour"><span style="background:%s" title="%s"></span></span>', $value, $text );

			echo sprintf( '<label class="swatch"><input type="radio" name="%s" id="%s" value="%s" class="%s" %s> %s</label>', esc_attr( $args['name'] ), esc_attr( $field_id ), esc_attr( $value ), esc_attr( $args['class'] ), esc_attr( $checked ), wp_kses_post( $text ) );
		}
	}

	/**
	 * Control Textarea Custom CSS.
	 *
	 * Custom Control for the Custom CSS.
	 *
	 * @param array $args Control Args.
	 *
	 * @return void
	 */
	public static function control_textarea_custom_css( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );
		echo '<div id="' . esc_attr( $args['id'] ) . '_ace_editor">' . esc_html( $args['value'] ) . '</div>';
		echo '<textarea style="display: none;" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" rows="5" cols="60" class="' . esc_attr( $args['class'] ) . '">' . esc_html( $args['value'] ) . '</textarea>';
	}

	/**
	 * Get Details Fields.
	 *
	 * @param object $checkout Checkout.
	 *
	 * @return array
	 */
	public static function get_details_fields( $checkout ) {
		$all_fields = $checkout->checkout_fields['billing'];
		$allowed    = self::get_allowed_details_fields();

		return array_intersect_key( $all_fields, array_flip( $allowed ) );
	}

	/**
	 * Get billing fields used at checkout.
	 *
	 * @return array
	 */
	public static function get_allowed_details_fields() {
		$details_fields = array( 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_email' );

		if ( 'hidden' !== Iconic_Flux_Core::phone_field_status() ) {
			$details_fields [] = 'billing_phone';
		}

		/**
		 * Filter Flux checkout details fields.
		 *
		 * @param array $fields Fields.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		return apply_filters(
			'flux_checkout_details_fields',
			$details_fields
		);
	}

	/**
	 * Get shipping fields.
	 *
	 * @param object $checkout Checkout.
	 *
	 * @return array
	 */
	public static function get_shipping_fields( $checkout ) {
		$all_fields      = $checkout->checkout_fields['shipping'];
		$allowed         = array( 'shipping_phone', 'shipping_email' );
		$shipping_fields = array_diff_key( $all_fields, array_flip( $allowed ) );

		if ( 'hidden' === get_option( 'woocommerce_checkout_address_2_field', 'optional' ) ) {
			unset( $shipping_fields['shipping_address_2'] );
		}

		/**
		 * Shipping fields.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'flux_shipping_fields', $shipping_fields, $checkout );
	}

	/**
	 * Use Autocomplete.
	 *
	 * @return bool
	 */
	public static function use_autocomplete() {
		$settings = Iconic_Flux_Core_Settings::$settings;

		return $settings['general_general_use_autocomplete'];
	}

	/**
	 * Get billing fields.
	 *
	 * @param object $checkout Checkout fields.
	 *
	 * @return array
	 */
	public static function get_billing_fields( $checkout ) {
		$all_fields = $checkout->checkout_fields['billing'];
		$allowed    = self::get_allowed_details_fields();
		$fields     = array_diff_key( $all_fields, array_flip( $allowed ) );

		if ( 'hidden' === get_option( 'woocommerce_checkout_address_2_field', 'optional' ) ) {
			unset( $fields['billing_address_2'] );
		}

		return $fields;
	}

	/**
	 * Check if the checkout has any pre-populated fields.
	 *
	 * @param string $type Type.
	 *
	 * @return bool
	 */
	public static function has_prepopulated_fields( $type ) {
		$has_prepopulated_fields = false;
		$checkout  = WC_Checkout::instance();
		$address_1 = $checkout->get_value( $type . '_address_1' );
		$address_2 = $checkout->get_value( $type . '_address_2' );

		if ( ! empty( $address_1 ) || ! empty( $address_2 ) ) {
			$has_prepopulated_fields = true;
		}

		/**
		 * Filter whether Flux has prepoulated fields.
		 *
		 * @param bool   $has_prepopulated_fields Had prepopulated fields.
		 * @param string $type                    Type
		 *
		 * @return bool
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'flux_has_prepopulated_fields', $has_prepopulated_fields, $type );
	}

	/**
	 * Render Address Panel.
	 *
	 * @return void
	 */
	public static function render_address_panel() {
		wc_get_template( 'flux/form-address.php', array( 'checkout' => WC_Checkout::instance() ) );
	}

	/**
	 * Render Details Panel.
	 *
	 * @return void
	 */
	public static function render_details_panel() {
		wc_get_template( 'flux/form-details.php', array( 'checkout' => WC_Checkout::instance() ) );
	}

	/**
	 * Get Logo Image.
	 *
	 * @return string
	 */
	public static function get_logo_image() {
		$settings = Iconic_Flux_Core_Settings::$settings;

		$logo_image = $settings['styles_header_logo_image'];

		if ( $logo_image ) {
			return $logo_image;
		}

		return ICONIC_FLUX_PATH . '/assets/logo.png';
	}

	/**
	 * Get Logo Image.
	 *
	 * @return string
	 */
	public static function get_logo_width() {
		$settings = Iconic_Flux_Core_Settings::$settings;

		$width = intval( $settings['styles_header_logo_image_width'] );

		if ( ! $width ) {
			$width = self::is_modern_theme() ? 200 : 40; // Default.
		}

		return $width;
	}

	/**
	 * Get Header Text.
	 *
	 * @return string
	 */
	public static function get_header_text() {
		$settings = Iconic_Flux_Core_Settings::$settings;

		$use_image = 'image' === $settings['styles_header_branding'];
		if ( $use_image ) {
			return false;
		}
		$header_text = $settings['styles_header_header_text'];

		return $header_text;
	}

	/**
	 * Convert hex to rgba.
	 *
	 * @param string $color Colour.
	 * @param bool   $opacity Opacity.
	 *
	 * @return string
	 */
	public static function hex2rgba( $color, $opacity = false ) {
		$default = 'rgb(0,0,0)';

		// Return default if no color provided.
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided.
		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values.
		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb.
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb).
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string.
		return $output;
	}

	/**
	 * Remove Class Filter.
	 *
	 * Removes action when Object is Anonymous.
	 *
	 * @param string $tag         Tag.
	 * @param string $class_name  Class Name.
	 * @param string $method_name Method name.
	 * @param int    $priority    Priority.
	 *
	 * @return bool.
	 */
	public static function remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $tag ] ) ) {
			return false;
		}

		if ( ! is_object( $wp_filter[ $tag ] ) || ! isset( $wp_filter[ $tag ]->callbacks ) ) {
			return false;
		}

		$filter_object      = $wp_filter[ $tag ];
		$callbacks          = &$wp_filter[ $tag ]->callbacks;
		$callbacks_priority = isset( $callbacks[ $priority ] ) ? (array) $callbacks[ $priority ] : array();

		if ( ! empty( $callbacks_priority ) ) {
			foreach ( $callbacks_priority as $filter ) {
				if ( ! isset( $filter['function'] ) || ! is_array( $filter['function'] ) ) {
					continue;
				}

				if ( ! is_object( $filter['function'][0] ) ) {
					continue;
				}

				if ( $filter['function'][1] !== $method_name ) {
					continue;
				}

				if ( get_class( $filter['function'][0] ) === $class_name ) {
					if ( ! isset( $filter_object ) ) {
						return false;
					}

					$filter_object->remove_filter( $tag, $filter['function'], $priority );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is Modern Theme.
	 *
	 * @return boolean
	 */
	public static function is_modern_theme() {
		$settings = Iconic_Flux_Core_Settings::$settings;
		return isset( $settings['styles_theme_choose_theme'] ) && 'modern' === $settings['styles_theme_choose_theme'];
	}

	/**
	 * Get shop page URL.
	 *
	 * @return string
	 */
	public static function get_shop_page_url() {
		$shop_page_id = wc_get_page_id( 'shop' );

		if ( -1 === $shop_page_id ) {
			/**
			 * Shop Page URL.
			 *
			 * @since 2.3.0
			 */
			return apply_filters( 'flux_checkout_shop_page_url', site_url() );
		}

		/**
		 * Shop Page URL.
		 *
		 * @since 2.3.0
		 */
		return apply_filters( 'flux_checkout_shop_page_url', get_permalink( $shop_page_id ) );
	}

	/**
	 * Is coupon enabled.
	 *
	 * @return bool
	 */
	public static function is_coupon_enabled() {
		$settings = Iconic_Flux_Core_Settings::$settings;

		$hide_coupon = isset( $settings['general_general_hide_coupon'] ) ? $settings['general_general_hide_coupon'] : '';

		/**
		 * Is coupon enabled?
		 *
		 * @since 2.3.0
		 */
		return apply_filters( 'flux_checkout_is_coupon_enabled', wc_coupons_enabled() && '1' !== $hide_coupon );
	}

	/**
	 * Get Order Pay button text.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string
	 */
	public static function get_order_pay_btn_text( $order ) {
		// Translators: Amount.
		return esc_html__( 'Pay for order', 'flux-checkout' ) . ' - ' . wc_price( $order->get_total() );
	}

	/**
	 * Get a specific property of an array without needing to check if that property exists.
	 *
	 * @param array  $arr     Array.
	 * @param string $prop    Property.
	 * @param any    $default Default.
	 *
	 * @return any
	 */
	public static function rgar( $arr, $prop, $default = '' ) {
		if ( isset( $arr[ $prop ] ) ) {
			return $arr[ $prop ];
		}

		return $default;
	}

	/**
	 * Get allowed tags for kses.
	 *
	 * @return array
	 */
	public static function get_kses_allowed_tags() {
		$allowed_tags = array(
			'br'     => array(),
			'a'      => array(
				'id'    => true,
				'href'  => true,
				'title' => true,
			),
			'strong' => array(),
			'p'      => array(),
			'style'  => true,
		);

		return $allowed_tags;
	}

	/**
	 * Is a page builder installed?
	 *
	 * @return bool
	 */
	public static function is_a_page_builder_installed() {
		return defined( 'ELEMENTOR_PRO_VERSION' ) || function_exists( 'et_setup_theme' ) ||
		class_exists( 'FLThemeBuilderLoader' ) || defined( 'BRICKS_VERSION' ) || defined( '__BREAKDANCE_PLUGIN_FILE__' ) ||
		defined( 'VCV_VERSION' );
	}

	/**
	 * Is AJAX action.
	 *
	 * @param string $action Action.
	 *
	 * @return bool
	 */
	public static function is_ajax_action( $actions ) {
		if ( is_string( $actions ) ) {
			$actions = array( $actions );
		}

		return defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $actions ) && in_array( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), $actions, true );
	}
}
