<?php
/**
 * Install core if not already.
 *
 * @package Orderable_Pro/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings class.
 */
class Iconic_Flux_Core_Install_Plugin {
	/**
	 * Slug of the plugin to install.
	 *
	 * @var string Plugin slug.
	 */
	private $slug;

	/**
	 * Plugin path for the plugin to install.
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * Setting's URL of the plugin to install.
	 *
	 * @var string Settings URL.
	 */
	public $settings_url;

	/**
	 * Setting's URL of the source plugin.
	 *
	 * @var string Source plugin settings URL.
	 */
	public $source_plugin_settings_url;

	/**
	 * Source plugin slug.
	 *
	 * @var string Source plugin slug.
	 */
	public $source_plugin_slug;

	/**
	 * Constructor.
	 *
	 * @param string $slug Slug of the plugin to install.
	 * @param string $plugin_path Path of the plugin to install. This is used to check if plugin is already installed.
	 * @param string $settings_url Settings URL: the URL to redirect to after plugin is successfully installed.
	 * @param string $source_plugin_settings_url The settings page of the source plugin.
	 */
	public function __construct( $slug, $plugin_path, $settings_url = false, $source_plugin_settings_url = false ) {
		$this->slug                       = $slug;
		$this->plugin_path                = $plugin_path;
		$this->settings_url               = $settings_url;
		$this->source_plugin_settings_url = $source_plugin_settings_url;
		$this->source_plugin_slug         = 'flux-checkout';

		add_action( 'admin_init', array( $this, 'maybe_install_plugin' ) );
		add_action( 'admin_init', array( $this, 'maybe_activate_plugin' ) );
		// Add notices to the admin header. Increase priority as the notices are removed by core settings class.
		add_action( 'in_admin_header', array( $this, 'add_notices' ), 10000 );
		add_action( sprintf( 'wp_ajax_%s_install_plugin', $this->source_plugin_slug ), array( $this, 'install_plugin_ajax' ) );
	}

	/**
	 * Add notices.
	 *
	 * @return void
	 */
	public function add_notices() {
		add_action( 'admin_notices', array( $this, 'display_error_notices' ) );
	}

	/**
	 * Display error notices for installation and activation.
	 *
	 * @return void
	 */
	public function display_error_notices() {
		$install_error  = filter_input( INPUT_GET, $this->source_plugin_slug . '-install-plugin-error', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$activate_error = filter_input( INPUT_GET, $this->source_plugin_slug . '-activate-plugin-error', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( $install_error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'There was an error installing the plugin. Please try again or install manually.', 'flux-checkout' ); ?></p>
			</div>
			<?php
		}

		if ( $activate_error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'There was an error activating the plugin. Please try again or activate manually.', 'flux-checkout' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Maybe activate the plugin.
	 *
	 * @return void
	 */
	public function maybe_activate_plugin() {
		$activate_plugin = filter_input( INPUT_GET, $this->source_plugin_slug . '-activate-plugin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $activate_plugin || $activate_plugin !== $this->plugin_path ) {
			return;
		}

		if ( is_plugin_active( $this->plugin_path ) ) {
			wp_safe_redirect( $this->settings_url );
			exit;
		}

		$result = activate_plugin( $this->plugin_path );

		if ( is_wp_error( $result ) ) {
			$add_args = array(
				$this->source_plugin_slug . '-activate-plugin-error' => 1,
			);

			$url = add_query_arg( $add_args, $this->source_plugin_settings_url );
			wp_safe_redirect( $url );
		} else {
			wp_safe_redirect( $this->settings_url );
		}

		exit;
	}

	/**
	 * Maybe install the core plugin
	 *
	 * @return void
	 */
	public function maybe_install_plugin() {
		$install_plugin = filter_input( INPUT_GET, $this->source_plugin_slug . '-install-plugin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $install_plugin || $install_plugin !== $this->slug ) {
			return;
		}

		if ( is_plugin_active( $this->plugin_path ) ) {
			wp_safe_redirect( $this->settings_url );
			exit;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$result = $this->install_plugin();

		if ( is_wp_error( $result ) ) {
			$add_args = array(
				$this->source_plugin_slug . '-install-plugin-error' => 1,
			);

			$url = add_query_arg( $add_args, $this->source_plugin_settings_url );
			wp_safe_redirect( $url );
			exit;
		}

		wp_safe_redirect( $this->settings_url );
		exit;
	}

	/**
	 * Get install URL.
	 *
	 * @return string Install URL.
	 */
	public function get_install_url() {
		return add_query_arg( $this->source_plugin_slug . '-install-plugin', $this->slug, admin_url( 'admin.php' ) );
	}

	/**
	 * Get activate URL.
	 *
	 * @return string Activate URL.
	 */
	public function get_activate_url() {
		return add_query_arg( $this->source_plugin_slug . '-activate-plugin', $this->plugin_path, admin_url( 'admin.php' ) );
	}

	/**
	 * Install the plugin from the source
	 */
	public function install_plugin() {
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// If exists and not activated, activate it.
		if ( file_exists( WP_PLUGIN_DIR . '/' . $this->plugin_path ) ) {
			return activate_plugin( $this->plugin_path );
		}

		// Seems like the plugin doesn't exists. Download and activate it.
		$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $this->slug,
				'fields' => array( 'sections' => false ),
			)
		);

		$download_link = is_wp_error( $api ) ? false : $api->download_link;

		if ( ! $download_link ) {
			return new \WP_Error( 'plugin_not_found', 'Plugin not found' );
		}

		$result = $upgrader->install( $download_link );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return activate_plugin( $this->plugin_path );
	}

	/**
	 * Get button.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string Button HTML.
	 */
	public function output_button( $args = array() ) {
		$defaults = array(
			'text'              => __( 'Install', 'flux-checkout' ),
			'class'             => 'button',
			'show_manage_link'  => false,
			'activate_text'     => __( 'Activate', 'flux-checkout' ),
			'manage_link_text'  => __( 'Manage', 'flux-checkout' ),
			'manage_link_class' => 'button',
		);

		$args      = wp_parse_args( $args, $defaults );
		$nonce     = wp_create_nonce( 'iconic_install_plugin' );
		$unique_id = uniqid( 'iconic-install-plugin-' );

		if ( $this->is_plugin_installed( $this->plugin_path ) && ! is_plugin_active( $this->plugin_path ) ) {
			?>
			<a href="<?php echo esc_attr( $this->get_activate_url() ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>"><?php echo esc_html( $args['activate_text'] ); ?></a>
			<?php
		}

		if ( is_plugin_active( $this->plugin_path ) && ! empty( $args['show_manage_link'] ) && ! empty( $args['manage_link_text'] ) ) {
			?>
			<div class="iconic-plugin-installed" style="display:flex; gap:2px;">
				<div>
					<span class="dashicons dashicons-yes"></span>
				</div>
				<div>
					<div><?php esc_html_e( 'Enabled', 'flux-checkout' ); ?></div>
					<a 
						href="<?php echo esc_attr( $this->settings_url ); ?>"
						class="">
						<?php echo esc_html( $args['manage_link_text'] ); ?>
					</a>
				</div>
			</div>
			<?php
		}

		if ( ! $this->is_plugin_installed( $this->plugin_path ) ) {
			$this->js_code( $unique_id );
			?>
			<div class="<?php echo esc_attr( $unique_id ); ?>">
				<div class="iconic-install-plugin-container" style="display:flex; align-items:center; gap:10px;">
					<a
						href="<?php echo esc_attr( $this->get_install_url() ); ?>" 
						class="<?php echo esc_attr( $args['class'] ); ?>" 
						data-plugin="<?php echo esc_attr( $this->slug ); ?>" 
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
						<?php echo esc_html( $args['text'] ); ?>
					</a>
					<img 
						class="iconic-install-plugin-spinner" 
						src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" 
						style="display:none;" 
					/>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * JS code.
	 *
	 * @param string $unique_id Unique ID for the instance.
	 *
	 * @return void
	 */
	public function js_code( $unique_id ) {
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				$( '.<?php echo esc_js( $unique_id ); ?> a' ).click( function( e ) {
					e.preventDefault();

					var $container = $( this ).parent();
					var $spinner = $container.find( '.iconic-install-plugin-spinner' );

					$spinner.show();

					var data = {
						action: '<?php echo esc_js( sprintf( '%s_install_plugin', $this->source_plugin_slug ) ); ?>',
						plugin: $( this ).data( 'plugin' ),
						nonce: $( this ).data( 'nonce' ),
					};

					$.ajax( {
						url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
						data: data,
						type: 'POST',
						success: function( response ) {
							if ( response.success ) {
								window.location.href = response.data.redirect_url;
							} else {
								iconicShowErrorNotice( $container.parent(), response.data );
								$spinner.hide();
							}
						},
						error: function( response ) {
							iconicShowErrorNotice( $container.parent(), '<?php esc_html_e( 'There was an error installing the plugin. Please try again or install manually.', 'flux-checkout' ); ?>' );
						}
					} );
				} );
			} );

			function iconicShowErrorNotice( $container, message ) {
				jQuery( $container ).find( '.iconic-notice-error' ).remove();
				jQuery( $container ).append( `<div class="iconic-notice-error" style="color:red;"><p>${message}</p></div>` );
			}
		</script>
		<?php
	}

	/**
	 * Check if the plugin is installed.
	 *
	 * @param string $plugin Plugin path.
	 *
	 * @return bool True if the plugin is installed, false otherwise.
	 */
	public function is_plugin_installed( $plugin ) {
		return file_exists( WP_PLUGIN_DIR . '/' . $plugin );
	}

	/**
	 * Install plugin via AJAX.
	 *
	 * @return void
	 */
	public function install_plugin_ajax() {
		check_ajax_referer( 'iconic_install_plugin', 'nonce' );

		$plugin = filter_input( INPUT_POST, 'plugin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $plugin ) {
			wp_send_json_error( __( 'No plugin specified', 'flux-checkout' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have permission to install plugins', 'flux-checkout' ) );
		}

		$result = $this->install_plugin();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Plugin installed successfully', 'flux-checkout' ),
				'redirect_url' => $this->settings_url,
			)
		);
	}
}
