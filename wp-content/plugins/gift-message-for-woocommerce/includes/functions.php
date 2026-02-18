<?php
use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Premium feature.
 *
 * @param string $value text.
 * @return html
 */
function gmfw_premium_feature( $value ) {
	$result = $value;
	if ( gmfw_is_free() ) {
		$result = '<div class="gmfw_premium_feature">
							<a class="gmfw_star_button" href="#"><svg style="color:#ffc106" width=20 aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" class=" gmfw_premium_iconsvg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"> <title>' . esc_attr__( 'Premium Feature', 'gmfw' ) . '</title><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></a>
							  <div class="gmfw_premium_feature_note" style="display:none">
							  <a href="#" class="gmfw_premium_close">
							  <svg aria-hidden="true"  width=10 focusable="false" data-prefix="fas" data-icon="times" class="svg-inline--fa fa-times fa-w-11" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><path fill="currentColor" d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg></a>
							  <h2>' . esc_html( __( 'Premium Feature', 'gmfw' ) ) . '</h2>
							  <p>' . esc_html( __( 'You Discovered a Premium Feature!', 'gmfw' ) ) . '</p>
							  <p>' . esc_html( __( 'Upgrading to Premium will unlock it.', 'gmfw' ) ) . '</p>
							  <a target="_blank" href="https://powerfulwp.com/gift-message-for-woocommerce-premium/#pricing" class="gmfw_premium_buynow">' . esc_html( __( 'UNLOCK PREMIUM', 'gmfw' ) ) . '</a>
							  </div>
						  </div>';
	}
	return $result;
}

/**
 * Check for free version
 *
 * @return boolean
 */
function gmfw_is_free() {
	if ( gmfw_fs()->is__premium_only() && gmfw_fs()->can_use_premium_code() ) {
		return false;
	} else {
		return true;
	}
}


/**
 * Get gift message fee function.
 *
 * @return void
 */
function gmfw_get_gift_message_fee() {
	$result = false;
	if ( gmfw_fs()->is__premium_only() ) {
		if ( gmfw_fs()->can_use_premium_code() ) {

			// Add gift message fee.
			$gmfw_gift_message_fee = get_option( 'gmfw_gift_message_fee', '' );

			if ( '' !== $gmfw_gift_message_fee && is_numeric( $gmfw_gift_message_fee ) ) {
				if ( 0 < $gmfw_gift_message_fee ) {
					$result = $gmfw_gift_message_fee;
				}
			}
		}
	}
	return $result;
}

	/**
	 * Determines whether HPOS is enabled.
	 *
	 * @return bool
	 */
function gmfw_is_hpos_enabled() : bool {
	if ( version_compare( get_option( 'woocommerce_version' ), '7.1.0' ) < 0 ) {
		return false;
	}

	if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
		return true;
	}

	return false;
}



