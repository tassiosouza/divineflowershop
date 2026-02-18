<?php
/**
 * Iconic_Flux_Compat_Bacs.
 *
 * Compatibility with WooCommerce BACS payment gateway.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_Flux_Compat_Bacs' ) ) {
	return;
}

/**
 * Iconic_Flux_Compat_Bacs.
 *
 * @class    Iconic_Flux_Compat_Bacs.
 * @version  2.0.0.0
 * @package  Iconic_Flux
 */
class Iconic_Flux_Compat_Bacs {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'compat_bacs' ) );
	}

	/**
	 * Add BACS compatibility.
	 */
	public static function compat_bacs() {
		// Check if WooCommerce is loaded and payment gateways are available.
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
			return;
		}

		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		
		// Check if BACS gateway exists.
		if ( ! isset( $payment_gateways['bacs'] ) ) {
			return;
		}

		// Remove the default BACS thankyou hook.
		remove_action( 'woocommerce_thankyou_bacs', array( $payment_gateways['bacs'], 'thankyou_page' ) );
		
		// Add our custom BACS thankyou hook.
		add_action( 'woocommerce_thankyou_bacs', array( __CLASS__, 'custom_thankyou_page' ) );
	}

	/**
	 * Custom thankyou page for BACS payments.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function custom_thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		
		if ( ! $order || 'bacs' !== $order->get_payment_method() ) {
			return;
		}

		// Get BACS gateway instance to access instructions.
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		if ( ! isset( $payment_gateways['bacs'] ) ) {
			return;
		}
		$bacs_gateway = $payment_gateways['bacs'];
		
		// Display instructions if available.
		if ( $bacs_gateway->instructions ) {
			echo '<div class="flux-review-customer__row flux-review-customer__row--instructions">';
			echo '<div class="flux-review-customer__label">';
			echo '<label>' . esc_html__( 'Payment Instructions', 'flux-checkout' ) . '</label>';
			echo '</div>';
			echo '<div class="flux-review-customer__content">';
			echo wp_kses_post( wpautop( wptexturize( wp_kses_post( $bacs_gateway->instructions ) ) ) );
			echo '</div>';
			echo '</div>';
		}

		// Display bank account details.
		self::display_bank_details( $order_id );
	}

	/**
	 * Display bank account details in Flux format.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function display_bank_details( $order_id ) {
		// Get bank account details from the option.
		$account_details = get_option( 'woocommerce_bacs_accounts', array() );
		
		if ( empty( $account_details ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		$country = $order->get_billing_country();
		
		// Get BACS gateway instance to access locale settings.
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		if ( ! isset( $payment_gateways['bacs'] ) ) {
			return;
		}
		$bacs_gateway = $payment_gateways['bacs'];
		$locale = $bacs_gateway->get_country_locale();
		
		// Get sortcode label in the $locale array and use appropriate one.
		$sortcode = isset( $locale[ $country ]['sortcode']['label'] ) ? $locale[ $country ]['sortcode']['label'] : __( 'Sort code', 'woocommerce' );

		$bacs_accounts = apply_filters( 'woocommerce_bacs_accounts', $account_details, $order_id );

		if ( empty( $bacs_accounts) ) {
			return;
		}

		// Bank Details heading outside the rows.
		echo '<h3>' . esc_html__( 'Bank Details', 'flux-checkout' ) . '</h3>';
		
		foreach ( $bacs_accounts as $bacs_account ) {
			$bacs_account = (object) $bacs_account;
			$has_details = false;
			
			// Get bank name for the label.
			$bank_label = ! empty( $bacs_account->account_name ) ? $bacs_account->account_name : __( 'Bank Account', 'flux-checkout' );
			
			// BACS account fields shown on the thanks page.
			$account_fields = apply_filters(
				'woocommerce_bacs_account_fields',
				array(
					'bank_name'      => array(
						'label' => __( 'Bank', 'woocommerce' ),
						'value' => $bacs_account->bank_name,
					),
					'account_number' => array(
						'label' => __( 'Account number', 'woocommerce' ),
						'value' => $bacs_account->account_number,
					),
					'sort_code'      => array(
						'label' => $sortcode,
						'value' => $bacs_account->sort_code,
					),
					'iban'           => array(
						'label' => __( 'IBAN', 'woocommerce' ),
						'value' => $bacs_account->iban,
					),
					'bic'            => array(
						'label' => __( 'BIC', 'woocommerce' ),
						'value' => $bacs_account->bic,
					),
				),
				$order_id
			);

			$account_details_html = '';
			foreach ( $account_fields as $field_key => $field ) {
				if ( ! empty( $field['value'] ) ) {
					$account_details_html .= '
					<div class="flux-bank-detail-item">
						<div class="flux-bank-detail-item__label">'. wp_kses_post( $field['label'] ) . '</div>
						<div class="flux-bank-detail-item__value">'. wp_kses_post( wptexturize( $field['value'] ) ) . '</div>
					</div>
					';
					$has_details = true;
				}
			}

			// Only display row if there are details.
			if ( $has_details ) {
				?>
				<div class="flux-review-customer__row flux-review-customer__row--bank-details">
					<div class="flux-review-customer__label">
						<label><?php echo wp_kses_post( wp_unslash( $bank_label ) ); ?></label>
					</div>
					<div class="flux-review-customer__content">
						<div class="flux-bank-details"><?php echo wp_kses_post( $account_details_html ); ?></div>
					</div>
				</div>
				<?php
			}
		}
	}
}
