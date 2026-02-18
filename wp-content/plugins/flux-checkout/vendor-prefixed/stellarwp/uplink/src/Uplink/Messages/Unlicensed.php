<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_Flux_NS\StellarWP\Uplink\Messages;

class Unlicensed extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
        $message  = '<div class="notice notice-warning"><p>';
        $message .= esc_html__( 'No license entered.', 'flux-checkout' );
        $message .= '</p></div>';

		return $message;
	}
}
