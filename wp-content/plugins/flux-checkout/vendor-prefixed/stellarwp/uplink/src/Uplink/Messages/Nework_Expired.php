<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_Flux_NS\StellarWP\Uplink\Messages;

class Network_Expired extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
		return esc_html__( 'Expired license. Consult your network administrator.', 'flux-checkout' );
	}
}
