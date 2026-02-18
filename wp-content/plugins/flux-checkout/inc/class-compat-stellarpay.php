<?php
/**
 * Iconic_Flux_Compat_Stellarpay.
 *
 * Compatibility with StellarPay.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Stellarpay' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Stellarpay.
 *
 * @class    Iconic_Flux_Compat_Stellarpay.
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Stellarpay {

	/**
	 * Plugin installer.
	 *
	 * @var Iconic_Flux_Core_Install_Plugin
	 */
	public static $plugin_installer = null;

	/**
	 * Run.
	 */
	public static function run() {
		self::$plugin_installer = new Iconic_Flux_Core_Install_Plugin(
			'stellarpay',
			'stellarpay/stellarpay.php',
			admin_url( 'admin.php?page=stellarpay' ),
			admin_url( 'admin.php?page=iconic-flux-settings' )
		);

		add_filter( 'flux_checkout_core_onboard_steps', array( __CLASS__, 'add_onboard_step' ) );
		add_filter( 'wpsf_register_settings_iconic_flux', array( __CLASS__, 'add_stellarpay_button_to_flux_settings' ), 20 );

		// Add cross sell if StellarPay is not active.
		if ( ! self::is_stellarpay_active() ) {
			add_filter( 'flux_checkout_core_cross_sells_before_products', array( __CLASS__, 'add_cross_sell' ) );
		}
	}

	/**
	 * Add onboard step.
	 *
	 * @param array $steps Steps.
	 */
	public static function add_onboard_step( $steps ) {
		$steps[] = array(
			'title'          => __( 'Add StellarPay (optional)', 'flux-checkout' ),
			'description'    => __( 'Streamline your payments with StellarPay.', 'flux-checkout' ),
			'button_text'    => __( 'Learn More', 'flux-checkout' ),
			'type'           => 'link',
			'link'           => 'https://links.stellarwp.com/stellarpay/flux/homepage',
			'install_plugin' => array(
				'text'             => __( 'Install StellarPay', 'flux-checkout' ),
				'installer'        => self::$plugin_installer,
				'show_manage_link' => false,
			),
		);

		return $steps;
	}

	/**
	 * Add StellarPay button to Flux settings.
	 *
	 * @param array $settings Settings.
	 */
	public static function add_stellarpay_button_to_flux_settings( $settings ) {
		$documentation_link = 'https://links.stellarwp.com/stellarpay/flux/documentation';
		foreach ( $settings['sections'] as $section_id => $section ) {
			if ( 'integrations' === $section['section_id'] && 'integrations' === $section['tab_id'] ) {
				$settings['sections'][ $section_id ]['fields'][] = array(
					'id'       => 'stripe_connect_button',
					'type'     => 'custom',
					'title'    => __( 'Stripe Payments', 'flux-checkout' ),
					'output'   => array( __CLASS__, 'output_stripe_connect_button' ),
					// translators: %s: Documentation link.
					'subtitle' => sprintf( __( 'Stripe integration via StellarPay.<br><a href="%s" target="_blank">View documentation</a>', 'flux-checkout' ), $documentation_link ),
				);
			}
		}

		$settings['sections'][] = array(
			'tab_id'              => 'general',
			'section_id'          => 'stellarpay_stripe',
			'section_title'       => __( 'Payment Gateway', 'flux-checkout' ),
			'section_description' => '',
			'section_order'       => 20,
			'fields'              => array(
				array(
					'id'       => 'stellarpay_stripe_button',
					'type'     => 'custom',
					'title'    => __( 'StellarPay Stripe', 'flux-checkout' ),
					// translators: %s: Documentation link.
					'subtitle' => sprintf( __( 'Stripe integration via StellarPay.<br><a href="%s" target="_blank">View documentation</a>', 'flux-checkout' ), $documentation_link ),
					'output'   => array( __CLASS__, 'output_stripe_connect_button' ),
				),
			),
		);

		return $settings;
	}

	/**
	 * Output Stripe connect button.
	 */
	public static function output_stripe_connect_button() {
		$args = array(
			'text'             => __( 'Install StellarPay', 'flux-checkout' ),
			'class'            => 'button',
			'show_manage_link' => true,
			'manage_link_text' => __( 'Manage Settings', 'flux-checkout' ),
			'activate_text'    => __( 'Activate StellarPay', 'flux-checkout' ),
		);

		self::$plugin_installer->output_button( $args );
	}

	/**
	 * Add cross sell.
	 */
	public static function add_cross_sell() {
		$url         = 'https://wordpress.org/plugins/stellarpay/?utm_source=Iconic&utm_medium=Plugin&utm_campaign=cross-sell&utm_content=flux-checkout';
		$title       = 'Supercharge your WooCommerce store with StellarPay';
		$description = 'StellarPay is a powerful, free WordPress plugin that connects WooCommerce stores to Stripe, giving you all the payment functionality your clients need.';
		$image       = plugin_dir_url( ICONIC_FLUX_FILE ) . 'images/stellarpay/stellarPay-banner.jpg';
		?>
		<div class="iconic-product">
			<div class="iconic-product__image" style="padding:0;">
				<a href="<?php echo esc_url( $url ); ?>" target="_blank">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
				</a>
			</div>
			<div class="iconic-product__content">
				<h4 class="iconic-product__title"><a target="_blank" href="<?php echo esc_url( $url ); ?>?utm_source=Iconic&utm_medium=Plugin&utm_campaign=cross-sell&utm_content=flux-checkout" target="_blank"><?php echo esc_html( $title ); ?></a></h4>
				<p class="iconic-product__description"><?php echo esc_html( $description ); ?></p>
				<div class="iconic-product__buttons">
					<p>
						<a href="<?php echo esc_url( $url ); ?>" class="button iconic-button iconic-button--small" target="_blank">
							<?php esc_html_e( 'Get Free Plugin', 'flux-checkout' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if StellarPay is active.
	 */
	public static function is_stellarpay_active() {
		return function_exists( 'stellarPay' );
	}
}
