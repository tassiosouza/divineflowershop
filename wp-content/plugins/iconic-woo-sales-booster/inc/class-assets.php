<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Assets.
 *
 * Register/enqueue frontend and backend scripts.
 *
 * @class    Iconic_WSB_Assets
 * @version  1.0.0
 */
class Iconic_WSB_Assets {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'block_editor_assets' ), 10 );
	}

	/**
	 * Frontend assets.
	 */
	public static function frontend_assets() {
		global $post;

		$post_content = '';

		if ( is_object( $post ) ) {
			$post_content = $post->post_content;
		}

		$render_assets = (
			is_product() ||
			is_checkout() ||
			is_cart() ||
			( has_shortcode( $post_content, 'iconic_wsb_fbt' ) ) ||
			( false !== strpos( $post_content, 'wp:iconic-wsb/fbt' ) )
		);

		/**
		 * Filter whether to enqueue frontend assets.
		 *
		 * By default, the frontend assets are rendered
		 * only on WooCommerce pages or if the page is
		 * using the Frequently Bought Together shortcode
		 * or block.
		 *
		 * @hook  iconic_wsb_enqueue_frontend_assets
		 * @since 1.11.0
		 *
		 * @param bool $render_assets If the assets will be enqueue.
		 */
		$render_assets = apply_filters( 'iconic_wsb_enqueue_frontend_assets', $render_assets );

		// Check to see where the buttons have been rendered, we will need the JS assets there too.
		if ( ! $render_assets ) {
			$render_assets = Iconic_WSB_Order_Bump_Product_Page_Modal_Manager::get_instance()->should_show_button_attributes();
		}

		if ( $render_assets ) {
			self::enqueue_frontend_assets();

			$settings = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance()->get_settings();

			$args = array(
				'ajax_url'      => WC()->ajax_url(),
				'nonce'         => wp_create_nonce( 'iconic_wsb_nonce' ),
				'fbt_use_ajax'  => $settings['use_ajax'],
				'is_checkout'   => is_checkout(),
				'i18n'          => array(
					'error'                => __( 'Please Try Again', 'iconic-wsb' ),
					'success'              => __( 'Added to Cart', 'iconic-wsb' ),
					'add_selected'         => __( 'Add Selected to Cart', 'iconic-wsb' ),
					'disabled_add_to_cart' => __( 'Please select a variation before adding the selected products to your cart.', 'iconic-wsb' ),
				),
				/**
				 * Filter the options to show the modal.
				 *
				 * @see https://dimsemenov.com/plugins/magnific-popup/documentation.html#options
				 *
				 * @hook  iconic_wsb_options_to_show_the_modal
				 * @since 1.12.0
				 *
				 * @param  array $modal_options The modal options.
				 * @return array
				 */
				'modal_options' => apply_filters( 'iconic_wsb_options_to_show_the_modal', array( 'showCloseBtn' => false ) ),
			);

			/**
			 * Filter the localized data used by the frontend script.
			 *
			 * @since 1.14.0
			 * @hook iconic_wsb_l10n_frontend_data_script
			 * @param  array $args The localized data.
			 * @return array New value
			 */
			$args = apply_filters( 'iconic_wsb_l10n_frontend_data_script', $args );

			wp_localize_script( 'iconic_wsb_frontend_scripts', 'iconic_wsb_frontend_vars', $args );

			wp_set_script_translations('iconic_wsb_frontend_scripts', 'iconic-wsb');
		}

		self::enqueue_shared_assets();
	}

	/**
	 * Enqueue Shared Assets.
	 *
	 * These need to be rendered on the front end, and in the block editor.
	 *
	 * @return void
	 */
	public static function enqueue_shared_assets() {
		wp_enqueue_style( 'iconic_wsb_frontend_style', ICONIC_WSB_URL . 'assets/frontend/css/main.css', array(), ICONIC_WSB_VERSION );
	}

	/**
	 * Admin assets.
	 */
	public static function admin_assets() {
		global $wp_query;

		if ( is_admin() || 'sales-booster_page_iconic-wsb-settings' === get_current_screen()->base
			|| in_array(
				get_current_screen()->post_type,
				array(
					Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->get_post_type(),
					Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance()->get_post_type(),
					'product',
				)
			)
		) {
			// WooCommerce
			wp_enqueue_script(
				'jquery-blockui',
				WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . '.min' . '.js',
				array( 'jquery' ),
				'2.70',
				true
			);
			wp_enqueue_style(
				'woocommerce_admin_styles',
				WC()->plugin_url() . '/assets/css/admin.css',
				array(),
				WC_VERSION
			);
			wp_enqueue_script( 'jquery-ui-sortable' );

			// color picker
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style(
				'iconic_wsb_admin_bump_edit_style',
				ICONIC_WSB_URL . 'assets/admin/css/main.css',
				array( 'wc-admin-layout' ),
				WC_VERSION
			);

			$args = array();

			if ( get_current_screen()->base == 'edit' ) {
				$args['posts']     = array_map(
					function ( $post ) {
						return $post->ID;
					},
					$wp_query->posts
				);
				$args['post_type'] = get_current_screen()->post_type;
			}

			if ( get_current_screen()->base == 'post' ) {
				$args['postId'] = get_the_ID();
			}

			wp_enqueue_script(
				'iconic_wsb_admin_bump_edit_script',
				ICONIC_WSB_URL . '/assets/admin/js/main.js',
				array( 'jquery', 'jquery-ui-sortable', 'jquery-blockui', 'wp-color-picker' ),
				ICONIC_WSB_VERSION
			);

			/**
			 * Filter whether the Checkout Bump shortcode should be rendered or not.
			 *
			 * @since 1.10.0
			 * @hook iconic_wsb_admin_l10n_data_script
			 * @param  array $order_bump_html The output of the shortcode.
			 * @return array New value
			 */
			$args = apply_filters( 'iconic_wsb_admin_l10n_data_script', $args );

			wp_localize_script( 'iconic_wsb_admin_bump_edit_script', 'iconic_wsb_admin_vars', $args );
		}
	}

	/**
	 * Enqueue Block Editor Assets
	 *
	 * @throws \Error Warn if asset dependencies do not exist.
	 *
	 * @return void
	 */
	public static function block_editor_assets() {
		$asset_path = ICONIC_WSB_PATH . 'assets/admin/js/blocks/block-editor.asset.php';

		if ( ! file_exists( $asset_path ) ) {
			throw new \Error(
				esc_html__( 'You need to run `npm start` or `npm run build` in the root of the plugin "iconic-woo-sales-booster" first.', 'iconic-wsb' )
			);
		}

		if (!self::is_site_editor()) {
			$scripts = '/assets/admin/js/blocks/block-editor.js';
			$assets  = include $asset_path;

			wp_enqueue_script(
				'iconic-wsb-block-scripts',
				plugins_url( $scripts, ICONIC_WSB_BASENAME ),
				$assets['dependencies'],
				$assets['version'],
				false
			);

			wp_set_script_translations(
				'iconic-wsb-block-scripts',
				'iconic-wsb',
				ICONIC_WSB_PATH . 'languages'
			);
		}

		self::enqueue_shared_assets();
	}

	/**
	 * Check if the current page is the site editor screen
	 *
	 * @return boolean
	 */
	protected static function is_site_editor() {
		$screen = get_current_screen();

		if (empty($screen)) {
			return false;
		}

		if ('site-editor' !== $screen->id) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public static function enqueue_frontend_assets() {
		wp_enqueue_script( 'magnific-popup', ICONIC_WSB_URL . 'assets/vendor/magnific/jquery.magnific-popup.min.js', array( 'jquery' ), ICONIC_WSB_VERSION, true );
		wp_enqueue_style( 'magnific-popup-style', ICONIC_WSB_URL . 'assets/vendor/magnific/magnific-popup.css', array(), ICONIC_WSB_VERSION );
		wp_enqueue_script(
			'iconic_wsb_frontend_scripts',
			ICONIC_WSB_URL . 'assets/frontend/js/main.js',
			array( 'jquery', 'wc-add-to-cart', 'wp-data', 'wc-cart-fragments' ),
			ICONIC_WSB_VERSION,
			true
		);
	}
}
